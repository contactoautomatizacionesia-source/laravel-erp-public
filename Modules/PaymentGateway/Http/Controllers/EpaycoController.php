<?php

namespace Modules\PaymentGateway\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Repositories\OrderRepository;
use \Modules\Wallet\Repositories\WalletRepository;
use Modules\Account\Repositories\TransactionRepository;
use Modules\Account\Entities\Transaction;
use Modules\FrontendCMS\Entities\SubsciptionPaymentInfo;
use App\Traits\Accounts;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;
use Modules\UserActivityLog\Traits\LogActivity;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\OrderPayment;

class EpaycoController extends Controller
{
    use Accounts;

    private const DEFAULT_SELLER_ID = 1;

    public function __construct()
    {
        $this->middleware('maintenance_mode');
    }

    /**
     * Get default income account
     */
    private function defaultIncomeAccount()
    {
        return 1; // Default account ID
    }

    /**
     * Get Epayco credentials
     */
    private function getCredential()
    {
        $url = explode('?',url()->previous());
        if(isset($url[0]) && $url[0] == url('/checkout')){
            $is_checkout = true;
        }else{
            $is_checkout = false;
        }
        if(session()->has('order_payment') && app('general_setting')->seller_wise_payment && session()->has('seller_for_checkout') && $is_checkout){
            $credential = getPaymentInfoViaSellerId(session()->get('seller_for_checkout'), 'ePayco');
        }else{
            $credential = getPaymentInfoViaSellerId(self::DEFAULT_SELLER_ID, 'ePayco');
        }
        return $credential;
    }

    /**
     * Process payment - redirects to Epayco checkout page
     */
    public function payment($data)
    {
        $data['amount'] = round($data['amount'], 2);
        $credential = $this->getCredential();

        if (!$credential) {
            Toastr::error(__('payment_gatways.epayco_not_configured'), __('common.error'));
            return \redirect()->back()->send();
        }

        // Store payment data in session for the checkout page
        session([
            'epayco_public_key' => $credential->perameter_3,
            'epayco_amount' => $data['amount'],
            'epayco_currency' => app('general_setting')->currency_code,
            'epayco_invoice' => $data['order_id'] ?? time(),
            'epayco_email' => auth()->check() ? auth()->user()->email : '',
            'epayco_name' => auth()->check() ? auth()->user()->name : '',
            'epayco_phone' => auth()->check() ? (auth()->user()->phone ?? '') : '',
        ]);

        return redirect()->route('epayco.checkout')->send();
    }

    /**
     * Show Epayco checkout page with JS SDK
     */
    public function checkout()
    {
        if (!session()->has('epayco_public_key')) {
            Toastr::error(__('common.error_message'), __('common.error'));
            return redirect(url('/checkout'));
        }

        // Pre-create OrderPayment BEFORE opening Epayco (only for order_payment flow)
        // This ensures the callback always finds the payment record
        $order_payment_id = null;
        $seller_id = null;

        if (session()->has('order_payment') && !session()->has('epayco_order_payment_id')) {
            $credential = $this->getCredential();
            if (!$credential) {
                Toastr::error(__('payment_gatways.epayco_not_configured'), __('common.error'));
                return redirect(url('/checkout'));
            }

            $seller_to_seller_payment = 0;
            if (isModuleActive('MultiVendor') && app('general_setting')->seller_wise_payment && session()->has('seller_for_checkout')) {
                $seller_to_seller_payment = 1;
            }

            $seller_id = session()->has('seller_for_checkout') ? session('seller_for_checkout') : self::DEFAULT_SELLER_ID;

            $order_payment = OrderPayment::create([
                'user_id' => auth()->check() ? auth()->user()->id : null,
                'amount' => session('epayco_amount'),
                'payment_method' => $credential->method->id,
                'txn_id' => 'epayco_' . session('epayco_invoice'),
                'status' => 0,
                'amount_goes_to_seller' => $seller_to_seller_payment,
            ]);

            $order_payment_id = $order_payment->id;
            session(['epayco_order_payment_id' => $order_payment_id]);
            session(['epayco_seller_id' => $seller_id]);

            Log::info('Epayco: OrderPayment pre-creado', [
                'id' => $order_payment_id,
                'txn_id' => $order_payment->txn_id,
                'invoice' => session('epayco_invoice'),
                'seller_id' => $seller_id,
            ]);
        } else {
            $order_payment_id = session('epayco_order_payment_id');
            $seller_id = session('epayco_seller_id');
        }

        $epayco_data = [
            'public_key' => session('epayco_public_key'),
            'amount' => session('epayco_amount'),
            'currency' => session('epayco_currency'),
            'invoice' => session('epayco_invoice'),
            'email' => session('epayco_email'),
            'name' => session('epayco_name'),
            'phone' => session('epayco_phone'),
            'response_url' => url(route('epayco.success')),
            'confirmation_url' => url(route('epayco.callback')),
            'test' => config('services.epayco.test_mode', 'true'),
            'extra1' => $order_payment_id ? (string)$order_payment_id : '',
            'extra2' => $seller_id ? (string)$seller_id : '',
            'extra3' => session('epayco_invoice'),
        ];

        return view('paymentgateway::epayco_checkout', compact('epayco_data'));
    }

    /**
     * Handle successful payment
     */
    public function success(Request $request)
    {
        try {
            $ref_payco = $request->input('ref_payco');
            Log::info('Epayco success', ['ref_payco' => $ref_payco, 'session_order_payment' => session()->has('order_payment') ? 'yes' : 'no']);
            
            if (!$ref_payco) {
                Log::error('Epayco: No ref_payco recibido');
                Toastr::error(__('payment_gatways.payment_failed'), __('common.error'));
                return $this->failed();
            }
            
            $url = config('services.epayco.validation_url') . $ref_payco;
            $response = Http::get($url);
            
            if (!$response->successful()) {
                Log::error('Epayco: API call failed - status: ' . $response->status());
                Toastr::error(__('payment_gatways.payment_failed'), __('common.error'));
                return $this->failed();
            }
            
            $responseData = $response->json();
            
            if (!isset($responseData['success']) || !$responseData['success']) {
                Log::error('Epayco: API response failed - ' . ($responseData['text_response'] ?? 'Unknown error'));
                Toastr::error(__('payment_gatways.payment_failed'), __('common.error'));
                return $this->failed();
            }
            
            $data = $responseData['data'];
            $x_cod_response = $data['x_cod_response'];
            $x_response = $data['x_response'];
            
            // Find pre-created OrderPayment (created in checkout() before opening Epayco)
            $pre_created_payment = null;
            if (session()->has('epayco_order_payment_id')) {
                $pre_created_payment = OrderPayment::find(session('epayco_order_payment_id'));
            }
            
            // Según documentación de Epayco: 1=Aceptada, 2=Rechazada, 3=Pendiente, 4=Fallida
            if (in_array($x_cod_response, [2, 4])) {
                Log::error('Epayco: Pago rechazado/fallido - cod: ' . $x_cod_response);
                
                // Update pre-created OrderPayment as rejected
                if ($pre_created_payment) {
                    $pre_created_payment->update(['txn_id' => $ref_payco, 'status' => 2]);
                    session()->forget(['epayco_order_payment_id', 'epayco_seller_id']);
                }
                
                // Mensajes específicos según el código
                if ($x_cod_response == 2) {
                    Toastr::error(__('payment_gatways.payment_rejected'), __('common.error'));
                } elseif ($x_cod_response == 4) {
                    Toastr::error(__('payment_gatways.payment_failed'), __('common.error'));
                } else {
                    Toastr::error(__('payment_gatways.payment_failed'), __('common.error'));
                }
                
                return $this->failed();
            }
            
            // Si es pendiente (3), crear registro de pago pendiente pero no confirmar orden
            if ($x_cod_response == 3) {
                Toastr::warning(__('payment_gatways.payment_pending'), __('common.warning'));
                
                // Obtener credenciales del gateway
                $credential = $this->getCredential();
                if (!$credential) {
                    Log::error('Epayco: Credenciales no encontradas');
                    Toastr::error(__('payment_gatways.payment_gateway_not_available'), __('common.error'));
                    return $this->failed();
                }
                
                if (session()->has('order_payment')) {
                    if ($pre_created_payment) {
                        // Update pre-created OrderPayment with real Epayco txn_id
                        $pre_created_payment->update(['txn_id' => $ref_payco]);
                        $payment_id = $pre_created_payment->id;
                    } else {
                        // Fallback: create new if pre-created not found
                        Log::warning('Epayco: Pre-created OrderPayment not found for pending, creating new');
                        $order_payment = OrderPayment::create([
                            'user_id' => (auth()->check()) ? auth()->user()->id : null,
                            'amount' => $data['x_amount'],
                            'payment_method' => $credential->method->id,
                            'txn_id' => $ref_payco,
                            'status' => 0,
                        ]);
                        
                        if(!$order_payment){
                            Log::error('Epayco: No se pudo crear payment pendiente');
                            Toastr::error(__('payment_gatways.pending_payment_process_error'), __('common.error'));
                            return redirect(url('/checkout'));
                        }
                        $payment_id = $order_payment->id;
                    }
                    
                    Session()->forget('order_payment');
                    session()->forget(['epayco_order_payment_id', 'epayco_seller_id']);
                    $redirectData = [];
                    $redirectData['payment_id'] = encrypt($payment_id);
                    $redirectData['gateway_id'] = encrypt($credential->method->id);
                    $redirectData['step'] = 'complete_order';
                    $redirectData['pending'] = 'true';
                    
                    LogActivity::successLog('Order payment pending successful.');
                    return redirect()->route('frontend.checkout', $redirectData);
                }
                
                // Para otros tipos (wallet, subscription)
                return redirect()->route('frontend.checkout', ['step' => 'payment'])
                    ->with('pending_payment', true)
                    ->with('pending_message', __('payment_gatways.payment_pending'));
            }
            
            // Solo procesar pagos ACEPTADOS (1)
            if ($x_cod_response == 1) {
                $credential = $this->getCredential();
                if (!$credential) {
                    Log::error('Epayco: Credenciales no encontradas');
                    Toastr::error(__('payment_gatways.payment_gateway_not_available'), __('common.error'));
                    return $this->failed();
                }
            } else {
                Log::error('Epayco: Código de respuesta no reconocido: ' . $x_cod_response);
                Toastr::error(__('payment_gatways.payment_failed'), __('common.error'));
                return $this->failed();
            }
            
            if (session()->has('wallet_recharge')) {
                // Handle wallet recharge
                $walletService = new WalletRepository;
                $return_data = $data['x_ref_payco'];
                return $walletService->walletRecharge($data['x_amount'], $credential->method->id, $return_data);
            }
            
            if (session()->has('order_payment')) {
                if ($pre_created_payment) {
                    // Update pre-created OrderPayment to confirmed
                    $pre_created_payment->update([
                        'txn_id' => $ref_payco,
                        'status' => 1,
                    ]);
                    $payment_id = $pre_created_payment->id;
                } else {
                    // Fallback: create new via orderPaymentDone
                    Log::warning('Epayco: Pre-created OrderPayment not found for accepted, using orderPaymentDone');
                    $orderPaymentService = new OrderRepository;
                    $order_payment = $orderPaymentService->orderPaymentDone($data['x_amount'], $credential->method->id, $ref_payco, auth()->user());
                    
                    if($order_payment == 'failed'){
                        Log::error('Epayco: orderPaymentDone failed');
                        Toastr::error(__('payment_gatways.payment_failed'), __('common.error'));
                        return redirect(url('/checkout'));
                    }
                    $payment_id = $order_payment->id;
                }
                
                Session()->forget('order_payment');
                session()->forget(['epayco_order_payment_id', 'epayco_seller_id']);
                $redirectData = [];
                $redirectData['payment_id'] = encrypt($payment_id);
                $redirectData['gateway_id'] = encrypt($credential->method->id);
                $redirectData['step'] = 'complete_order';
                
                LogActivity::successLog('Order payment successful.');
                return redirect()->route('frontend.checkout', $redirectData);
            }
            
            if (session()->has('subscription_payment')) {
                // Handle subscription payment
                $return_data = $data['x_ref_payco'];
                $tnx_check = SubsciptionPaymentInfo::where('txn_id', $return_data)->first();
                
                if($tnx_check){
                    Toastr::error(__('payment_gatways.invalid_payment'), __('common.error'));
                } else {
                    $defaultIncomeAccount = $this->defaultIncomeAccount();
                    $seller_subscription = getParentSeller()->SellerSubscriptions;
                    $transactionRepo = new TransactionRepository(new Transaction);
                    $transaction = $transactionRepo->makeTransaction(
                        getParentSeller()->first_name." - Subscription Payment", 
                        "in", 
                        "EPAYCO", 
                        "subscription_payment", 
                        $defaultIncomeAccount, 
                        "Subscription Payment", 
                        $seller_subscription, 
                        $data['x_amount'], 
                        Carbon::now()->format('Y-m-d'), 
                        getParentSellerId(), 
                        null, 
                        null
                    );
                    $seller_subscription->update(['last_payment_date' => Carbon::now()->format('Y-m-d')]);
                    SubsciptionPaymentInfo::create([
                        'transaction_id' => $transaction->id,
                        'txn_id' => $return_data,
                        'seller_id' => getParentSellerId(),
                        'subscription_type' => getParentSeller()->sellerAccount->subscription_type,
                        'commission_type' => @$seller_subscription->pricing->name
                    ]);
                    session()->forget('subscription_payment');
                    Toastr::success(__('common.payment_successfully'),__('common.success'));
                    LogActivity::successLog('Subscription payment successful.');
                }
                return redirect()->route('seller.dashboard');
            }
            
            return redirect()->back();

        } catch (\Exception $e) {
            Log::error('=== EPAYCO SUCCESS CATCH EXCEPTION ===');
            Log::error('Exception message: ' . $e->getMessage());
            Log::error('Exception file: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Exception trace: ' . $e->getTraceAsString());
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('payment_gatways.payment_failed'), __('common.error'));
            return redirect()->route('frontend.checkout');
        }
    }

    /**
     * Handle failed payment
     */
    public function failed()
    {
        // Clean up pre-created OrderPayment if still pending
        if (session()->has('epayco_order_payment_id')) {
            $pending_payment = OrderPayment::find(session('epayco_order_payment_id'));
            if ($pending_payment && $pending_payment->status == 0) {
                $pending_payment->update(['status' => 2]);
            }
            session()->forget(['epayco_order_payment_id', 'epayco_seller_id']);
        }

        if (session()->has('wallet_recharge')) {
            if (auth()->user()->role->type == 'customer') {
                return redirect(url('wallet/customer/my-wallet-index'));
            } elseif (auth()->user()->role->type == 'seller') {
                return redirect(url('wallet/seller/my-wallet-index'));
            }elseif (auth()->user()->role->type == 'admin') {
                return redirect(url('wallet/admin/my-wallet-index'));
            }
            return redirect(url('/'));
        }elseif (session()->has('order_payment')) {
            return redirect(url('/checkout'));
        }elseif (session()->has('subscription_payment')) {
            return redirect()->route('seller.dashboard');
        }
        return redirect(url('/'));
    }

    /**
     * Handle Epayco callback for payment confirmation
     */
    public function callback(Request $request)
    {
        try {
            $x_ref_payco = $request->input('x_ref_payco');
            $x_cod_response = $request->input('x_cod_response');
            $x_response = $request->input('x_response');
            $x_signature = $request->input('x_signature');
            $x_amount = $request->input('x_amount');
            $x_currency_code = $request->input('x_currency_code');
            $x_transaction_id = $request->input('x_transaction_id');
            $x_extra1 = $request->input('x_extra1'); // OrderPayment ID (pre-created in checkout)
            $x_extra2 = $request->input('x_extra2'); // Seller ID (for credentials)

            if (!$x_ref_payco) {
                Log::warning('Epayco callback: No x_ref_payco recibido');
                return response()->json(['status' => 'error', 'message' => 'Missing x_ref_payco'], 400);
            }

            // Find OrderPayment: by txn_id (x_ref_payco) first, then by ID (x_extra1)
            $payment = OrderPayment::with('order.packages')->where('txn_id', $x_ref_payco)->first();

            if (!$payment && $x_extra1) {
                $payment = OrderPayment::with('order.packages')->find($x_extra1);
                if ($payment && $payment->status == 0 && str_starts_with($payment->txn_id, 'epayco_')) {
                    // Actualiza el txn_id temporal a x_ref_payco numérico. Esto evita sobrescribir el ID alfanumérico final si success() se ejecutó primero.
                    $payment->update(['txn_id' => $x_ref_payco]);
                    Log::info('Epayco callback: OrderPayment encontrado por extra1', [
                        'id' => $payment->id,
                        'old_txn_id' => 'epayco_*',
                        'new_txn_id' => $x_ref_payco,
                    ]);
                }
            }

            if (!$payment) {
                Log::warning('Epayco callback: OrderPayment no encontrado', [
                    'x_ref_payco' => $x_ref_payco,
                    'x_extra1' => $x_extra1,
                ]);
                return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
            }

            // Get seller_id for credentials: x_extra2 > order packages
            // No fallback to a default seller — using wrong credentials would cause signature mismatch
            if ($x_extra2) {
                $seller_id = $x_extra2;
            } elseif ($payment->order && $payment->order->packages->isNotEmpty()) {
                $seller_id = $payment->order->packages->first()->seller_id;
            } else {
                Log::error('Epayco callback: No se pudo determinar seller_id', [
                    'x_ref_payco' => $x_ref_payco,
                    'x_extra2' => $x_extra2,
                    'payment_id' => $payment->id,
                ]);
                return response()->json(['status' => 'error', 'message' => 'Cannot determine seller credentials'], 422);
            }

            $credential = getPaymentInfoViaSellerId($seller_id, 'ePayco');

            if (!$credential) {
                Log::error('Epayco callback: Credenciales no encontradas para seller_id: ' . $seller_id);
                return response()->json(['status' => 'error', 'message' => 'Gateway credentials not found'], 500);
            }

            // Verificación de firma obligatoria
            if (!$x_signature) {
                Log::error('Epayco callback: No se recibió firma para txn_id: ' . $x_ref_payco);
                return response()->json(['status' => 'error', 'message' => 'Signature missing'], 403);
            }

            $p_cust_id = $credential->perameter_1; // P_CUST_ID_CLIENTE
            $p_key = $credential->perameter_2;     // P_KEY
            $signature_string = $p_cust_id . '^' . $p_key . '^' . $x_ref_payco . '^' . $x_transaction_id . '^' . $x_amount . '^' . $x_currency_code;
            $expected_signature = hash('sha256', $signature_string);

            if (!hash_equals($expected_signature, $x_signature)) {
                Log::error('Epayco callback: Firma inválida para txn_id: ' . $x_ref_payco, ['expected' => $expected_signature, 'received' => $x_signature]);
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
            }

            // Firma válida, procesar el pago con transacción
            if ($payment->status == 0 && $x_cod_response == 1) {
                DB::beginTransaction();
                try {
                    $payment->status = 1;
                    $payment->save();

                    $order = $payment->order;
                    if ($order) {
                        $order->is_paid = 1;
                        $order->save();

                        foreach ($order->packages as $package) {
                            $package->is_paid = 1;
                            $package->save();
                        }
                    }

                    DB::commit();
                    LogActivity::successLog('Epayco callback: Payment ' . $x_ref_payco . ' confirmado');
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Epayco callback: Error actualizando payment ' . $x_ref_payco . ': ' . $e->getMessage());
                    return response()->json(['status' => 'error', 'message' => 'Payment update failed'], 500);
                }
            } elseif ($payment->status == 0 && in_array($x_cod_response, [2, 4])) {
                $payment->status = 2;
                $payment->save();
                LogActivity::successLog('Epayco callback: Payment ' . $x_ref_payco . ' rechazado');
            }
            
            return response()->json(['status' => 'success', 'message' => 'Callback processed']);

        } catch (\Exception $e) {
            Log::error('=== EPAYCO CALLBACK ERROR ===');
            Log::error('Exception: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            LogActivity::errorLog('Epayco callback error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Callback processing failed: ' . $e->getMessage()]);
        }
    }

}

