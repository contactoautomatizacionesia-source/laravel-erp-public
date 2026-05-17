<?php

namespace Modules\OrderManage\Repositories;
use App\Models\Order;
use App\Models\OrderPackageDetail;
use App\Models\OrderProductDetail;
use App\Models\DigitalFileDownload;
use App\Models\OrderPayment;
use App\Services\DoubleApprovalService;
use Modules\OrderManage\Entities\OrderDeliveryState;
use Modules\Account\Repositories\TransactionRepository;
use Modules\Wallet\Repositories\WalletRepository;
use Modules\MultiVendor\Repositories\MerchantRepository;
use Modules\Account\Entities\Transaction;
use App\Traits\SendMail;
use App\Traits\Accounts;
use App\Traits\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\GeneralSetting;
use Modules\GeneralSetting\Entities\NotificationSetting;
use Modules\MultiVendor\Entities\PackageWiseSellerCommision;
use Modules\OrderManage\Entities\DeliveryProcess;
use Modules\Product\Entities\Category;
use Modules\InventoryEntry\Entities\InventoryEntry;
use Modules\CostCenter\Actions\DeductCostCenterStock;
use Modules\CostCenter\Entities\CostCenter;
use Modules\CostCenter\Entities\CostCenterInventory;
use Modules\CostCenter\Entities\CostCenterProductAlert;
use Modules\CostCenter\Exceptions\InsufficientStockException;

class OrderManageRepository
{
    use SendMail, Accounts, Notification;

    public function myConfirmedSalesList()
    {
        $seller_id = getParentSellerId();
        return OrderPackageDetail::whereHas('order', function ($q) {
            $q->where('orders.is_cancelled', 0)->where('is_confirmed', 1)->where('is_completed', 0);
        })->where('order_package_details.is_cancelled', 0)->with('order', 'seller', 'order.customer')->where('seller_id', $seller_id)->select('order_package_details.*')->latest();
    }

    public function myCompletedSalesList()
    {
        $seller_id = getParentSellerId();
        return OrderPackageDetail::whereHas('order', function ($q) {
            $q->where('orders.is_cancelled', 0)->where('is_completed', 1);
        })->where('order_package_details.is_cancelled', 0)->with('order', 'seller', 'order.customer')->where('seller_id', $seller_id)->select('order_package_details.*')->latest();
    }

    public function myPendingPaymentSalesList()
    {
        $seller_id = getParentSellerId();
        return OrderPackageDetail::whereHas('order', function ($q) {
            $q->where('orders.is_cancelled', 0)->where('is_paid', 0);
        })->where('order_package_details.is_cancelled', 0)->with('order', 'seller', 'order.customer')->where('seller_id', $seller_id)->select('order_package_details.*')->latest();
    }

    public function myCancelledPaymentSalesList()
    {
        $seller_id = getParentSellerId();
        $orderpackage =  OrderPackageDetail::where('seller_id', $seller_id)
        ->whereHas('order', function ($q) {
                $q->where('is_cancelled', 1);
            })->orWhere('is_cancelled', 1)
        ->with('order', 'seller', 'order.customer')->latest();

        return $orderpackage->where('seller_id', $seller_id);
    }

    public function totalSalesList()
    {
        return Order::with(['packages', 'customer', 'costCenter:id,code,name'])
            ->when($this->shouldFilterByUserCostCenter(), function ($query) {
                $query->where('cost_center_id', auth()->user()->cost_center_id);
            })
            ->latest();
    }

    private function shouldFilterByUserCostCenter(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();
        $roleType = $user->role?->type;

        if (in_array($roleType, ['superadmin', 'admin'], true)) {
            return false;
        }

        return !empty($user->cost_center_id);
    }

    public function findOrderByID($id)
    {
        return Order::findOrFail($id);
    }

    public function orderInfoUpdate($data, $id)
    {
        $order = $this->findOrderByID($id);
        $defaultIncomeAccount = $this->defaultIncomeAccount();
        $defaultGSTAccount = $this->defaultGSTAccount();
        $defaultSellerAccount = $this->defaultSellerAccount();
        $defaultProductTaxAccount = $this->defaultProductTaxAccount();
        $defaultSellerCommisionAccount = $this->defaultSellerCommisionAccount();
        if ($defaultIncomeAccount == null || $defaultSellerAccount == null || $defaultProductTaxAccount == null) {
            return false;
        }
        $total_seller_amount = 0;
        $total_gst_amount = 0;
        $total_product_tax_amount = 0;
        $revenue_amount = 0;
        $total_package_amount = 0;
        $total_sale_qty = 0;
        $seller_commision = 0;

        if(!isModuleActive('MultiVendor')){
            $package = $order->packages->first();

            $last_delivery_state = DeliveryProcess::orderByDesc('id')->firstOrFail();
            if($last_delivery_state->id == $data['delivery_status'] && $package->delivery_status != $data['delivery_status']){
                if ($package->is_cancelled == 0) {
                    $revenue_amount = $package->products->sum('total_price') + $package->shipping_cost;
                    $total_product_tax_amount = $package->tax_amount;
                } else {
                    if (file_exists(base_path() . '/Modules/GST/') && (app('gst_config')['enable_gst'] == "gst" || app('gst_config')['enable_gst'] == "flat_tax")) {
                        $package_price = $package->products->sum('total_price') + $package->shipping_cost + $package->tax_amount;
                    } else {
                        $package_price = $package->products->sum('total_price') + $package->shipping_cost + $package->tax_amount;
                    }
                    $total_package_amount += $package_price;
                }

                $transactionRepo = new TransactionRepository(new Transaction);

                $transactionRepo->makeTransaction("Earning from Sales", "in", $order->GatewayName, "sales_income", $defaultIncomeAccount, "Product Sale", $order, $revenue_amount, Carbon::now()->format('Y-m-d'), auth()->id(), null, null);
                if ($total_product_tax_amount > 0) {
                    $transactionRepo->makeTransaction("Product Tax on Sale", "in", $order->GatewayName, "product_tax", $defaultProductTaxAccount, "ProductWise Tax Inhouse", $order, $total_product_tax_amount, Carbon::now()->format('Y-m-d'), auth()->id(), null, null);
                }
            }

            if ($order->is_confirmed == 0 && $data['is_confirmed'] == 1) {
                OrderDeliveryState::create([
                    'order_package_id' => $package->id,
                    'delivery_status' => 2,
                    'note' => 'Order is under processing.',
                    'created_by' => auth()->user()->id,
                    'date' => Carbon::now()->format('Y-m-d')
                ]);
                $package->update([
                    'delivery_status' => 2
                ]);

                //customer and seller get Notification When super admin change any delivery status
                $notificationUrl = route('frontend.my_purchase_order_detail',encrypt($order->id));
                $notificationUrl = str_replace(url('/'),'',$notificationUrl);
                $this->notificationUrl = $notificationUrl;
                $this->adminNotificationUrl = 'ordermanage/total-sales-list';
                $this->routeCheck = 'order_manage.total_sales_index';
                $this->typeId = EmailTemplateType::where('type','order_email_template')->first()->id;//order email templete type id
                $this->order_on_notification = $order;
                $notification = NotificationSetting::where('slug','order-confirmation')->first();
                if ($notification) {
                    $this->notificationSend($notification->id, $order->customer_id);
                }
            }else{
                if($package->delivery_status != $data['delivery_status']){
                        OrderDeliveryState::create([
                            'order_package_id' => $package->id,
                            'delivery_status' => $data['delivery_status'],
                            'note' => !empty($data['note']) ? $data['note'] : null,
                            'created_by' => auth()->user()->id,
                            'date' => Carbon::now()->format('Y-m-d')
                        ]);
                    $package->update([
                        'delivery_status' => $data['delivery_status']
                    ]);
                    //customer get Notification When super admin change any delivery status
                    $notificationUrl = route('frontend.my_purchase_order_detail',encrypt($order->id));
                    $notificationUrl = str_replace(url('/'),'',$notificationUrl);
                    $this->notificationUrl = $notificationUrl;
                    $this->adminNotificationUrl = 'ordermanage/total-sales-list';
                    $this->routeCheck = 'order_manage.total_sales_index';
                    $this->typeId = EmailTemplateType::where('type','delivery_process_template')->first()->id;//order email templete type id
                    $this->relatable_type = 'Modules\OrderManage\Entities\DeliveryProcess';
                    $this->relatable_id = $data['delivery_status'];
                    $this->order_on_notification = $order;
                    $this->notificationSend(null,$order->customer_id,$data['delivery_status']);
                }
            }

        }else{
            if ($order->is_confirmed == 1 && $data['is_confirmed'] == 1) {
                foreach ($order->packages as $key => $package) {
                    $this->updateStock($package);
                    $package->update([
                        'delivery_status' => 2
                    ]);
                }
            }
        }

        if ($data['is_confirmed'] == 2) {
            $order->update([
                'is_cancelled' => 1,
                "cancel_reason_id" => isset($data['cancel_reason_id']) ? $data['cancel_reason_id']:null,
            ]);
            foreach($order->packages as $pkg){
                $pkg->update([
                    'is_cancelled' => 1
                ]);
            }
            if(isModuleActive('Affiliate') && $order->affiliatePayments->count() > 0){
                foreach($order->affiliatePayments as $key => $aff_payment){
                    $aff_payment->update([
                        'status' => 2
                    ]);
                }
            }
            if(@$order->order_payment->payment_method == 2 && $order->customer_id){
                $wallet_service = new WalletRepository;
                $wallet_service->cartPaymentData($order->id, $order->grand_total, "Refund Back", $order->customer_id, 'registered');
            }
            $notification = NotificationSetting::where('slug','order-declined')->first();
            if ($notification) {
                $this->notificationSend($notification->id, $order->customer_id);
            }
        }

        if ($order->is_paid != $data['is_paid']) {
            if (app('business_settings')->where('type', 'mail_notification')->first()->status == 1) {
                try {
                    switch ($data['is_paid']) {
                        case 1:
                            $this->sendOrderRefundInfoUpdateMail($order, 5);
                            break;
                        default:
                            break;
                    }
                } catch (\Exception $e) {
                }
            }
        }
        if ($order->is_confirmed != $data['is_confirmed']) {
            if (app('business_settings')->where('type', 'mail_notification')->first()->status == 1) {
                switch ($data['is_confirmed']) {
                    case 0:
                        $this->sendOrderRefundInfoUpdateMail($order, 2);
                        break;
                    case 1:
                        $this->sendOrderRefundInfoUpdateMail($order, 3);
                        break;
                    case 2:
                        $this->sendOrderRefundInfoUpdateMail($order, 4);
                        break;
                    default:
                        break;
                }
            }
        }
        if ($order->is_completed != $data['is_completed']) {
            if (app('business_settings')->where('type', 'mail_notification')->first()->status == 1) {
                switch ($data['is_completed']) {
                    case 1:
                        $this->sendOrderRefundInfoUpdateMail($order, 6);
                        $this->sendOrderRefundInfoUpdateMail($order, 16);
                        break;
                    default:
                        break;
                }
            }
        }

        $order->update([
            'is_paid' => $data['is_paid'],
            'is_confirmed' => $data['is_confirmed'],
            'is_completed' => $data['is_completed'],
        ]);


        return true;
    }

    public function get_commission_rate($seller_id, $package)
    {
        $merchantRepo = new MerchantRepository();
        $seller = $merchantRepo->findUserByID($package->seller_id);
        if ($seller) {
            $seller_account = $seller->SellerAccount;
            $seller_business_info = $seller->SellerBusinessInformation;
            $claim_gst = $seller_business_info->claim_gst;

            // flat rate
            if ($seller_account->seller_commission_id == 1) {
                $total_amount_of_package = $package->products->sum('total_price');

                $commission_rate = $seller_account->commission_rate;
                $final_commission = ($commission_rate * $total_amount_of_package) / 100;
                $seller_rcv_money = $total_amount_of_package - $final_commission;
            }

            // category_wise_calculation baki
            elseif ($seller_account->seller_commission_id == 2) {
                $final_commission = 0;
                $order_products = OrderProductDetail::with('seller_product_sku', 'seller_product_sku.sku', 'seller_product_sku.sku.product', 'seller_product_sku.sku.product.categories')->where('package_id', $package->id)->get();
                foreach ($order_products as $key => $order_product) {
                    $commission_rate = 0;
                    if(app('general_setting')->commission_by == 1){
                        $commission_rate = $order_product->seller_product_sku->sku->product->categories->min('commission_rate');
                    }
                    elseif(app('general_setting')->commission_by == 2){
                        $commission_rate = $order_product->seller_product_sku->sku->product->categories->max('commission_rate');
                    }
                    elseif(app('general_setting')->commission_by == 3){
                        $commission_rate = $order_product->seller_product_sku->sku->product->categories->avg('commission_rate');
                    }

                    if ($commission_rate > 0) {
                        $commission_amount = ($commission_rate * $order_product->total_price) / 100;
                        $final_commission += $commission_amount;
                    } else {
                       $cat_parent_id = $order_product->seller_product_sku->sku->product->categories->pluck('parent_id');
                       if(app('general_setting')->commission_by == 1){
                            $commission_rate = Category::whereIn('id',$cat_parent_id)->min('commission_rate');
                        }
                        elseif(app('general_setting')->commission_by == 2){
                            $commission_rate = Category::whereIn('id',$cat_parent_id)->max('commission_rate');
                        }
                        elseif(app('general_setting')->commission_by == 3){
                            $commission_rate = Category::whereIn('id',$cat_parent_id)->avg('commission_rate');
                        }
                        if($commission_rate != NULL && $commission_rate > 0){
                            $commission_amount = ($commission_rate * $order_product->total_price) / 100;
                            $final_commission += $commission_amount;
                        }
                    }
                }
                $seller_rcv_money = $package->products->sum('total_price') - $final_commission;
            }

            // Subscription Package wise transaction fee
            elseif ($seller_account->seller_commission_id == 3) {
                if ($seller->SellerSubscriptions->pricing->transaction_fee > 0) {
                    $total_amount_of_package = $package->products->sum('total_price');

                    $commission_rate = $seller->SellerSubscriptions->pricing->transaction_fee;
                    $final_commission = ($commission_rate * $total_amount_of_package) / 100;
                    $seller_rcv_money = $total_amount_of_package - $final_commission;
                } else {
                    $final_commission = 0;
                    $seller_rcv_money = $package->products->sum('total_price');
                }
            }
            $data['seller_rcv_money'] = $seller_rcv_money;
            $data['claim_gst'] = $claim_gst;
            $data['final_commission'] = $final_commission;


            return $data;
        }
    }

    public function findOrderPackageByID($id)
    {
        return OrderPackageDetail::findOrFail($id);
    }

    public function updateDeliveryStatus($data, $id)
    {
        $order_package = $this->findOrderPackageByID($id);
        $order = $this->findOrderByID($order_package->order_id);

        if ($order_package->delivery_status != $data['delivery_status']) {
            if (app('business_settings')->where('type', 'mail_notification')->first()->status == 1) {
                $this->sendOrderRefundorDeliveryProcessMail($order, "Modules\OrderManage\Entities\DeliveryProcess", $data['delivery_status']);
            }
            // Notification : when status changed
            $notificationUrl = route('frontend.my_purchase_order_detail',encrypt($order->id));
            $notificationUrl = str_replace(url('/'),'',$notificationUrl);
            $this->notificationUrl = $notificationUrl;
            $this->adminNotificationUrl = 'ordermanage/sales-details/'.$order_package->order_id;
            $this->routeCheck = 'order_manage.show_details';
            $this->typeId = EmailTemplateType::where('type','order_email_template')->first()->id;//order email templete type id
            $this->notificationSend(null, $order->customer_id,$data['delivery_status']);

            OrderDeliveryState::create([
                'order_package_id' => $order_package->id,
                'delivery_status' => $data['delivery_status'],
                'note' => $data['note'],
                'created_by' => getParentSellerId(),
                'date' => Carbon::now()->format('Y-m-d')
            ]);

            $orderPayment = OrderPayment::findOrFail($order->order_payment_id);
            $orderPayment->update([
                'status' => 1
            ]);
        }

        $last_delivery_state = DeliveryProcess::orderByDesc('id')->firstOrFail();
        if($data['delivery_status'] == $last_delivery_state->id && $order_package->delivery_status != $last_delivery_state->id){
            $defaultIncomeAccount = $this->defaultIncomeAccount();
            $defaultSellerAccount = $this->defaultSellerAccount();
            $defaultProductTaxAccount = $this->defaultProductTaxAccount();
            $defaultSellerCommisionAccount = $this->defaultSellerCommisionAccount();
            if ($defaultIncomeAccount == null || $defaultSellerAccount == null || $defaultProductTaxAccount == null) {
                return false;
            }

            $total_seller_amount = 0;
            $total_gst_amount = 0;
            $total_product_tax_amount = 0;
            $revenue_amount = 0;
            $total_package_amount = 0;
            $total_sale_qty = 0;
            $seller_commision = 0;

            if ($order_package->seller->role->type != "superadmin") {
                $amount = $this->get_commission_rate($order_package->seller_id, $order_package);
                $seller_amount = $amount['seller_rcv_money'] + $order_package->tax_amount;
                $seller_commision = $amount['final_commission'];
                if ($amount['claim_gst'] == 0) {
                    $total_gst_amount = $order_package->gst_taxes->sum('amount');
                } else {
                    $seller_amount += $order_package->gst_taxes->sum('amount');
                    $order_package->update(['gst_claimed' => 1]);
                }

                $current_seller_amount = $seller_amount + $order_package->shipping_cost;
                $total_seller_amount = $current_seller_amount;

                if(!app('general_setting')->seller_wise_payment || in_array($order->order_payment->payment_method, [1,2])){
                    // for package wise seller commission
                    PackageWiseSellerCommision::create([
                        'seller_id' => $order_package->seller_id,
                        'amount' => $amount['final_commission'],
                        'package_id' => $order_package->id
                    ]);
                    $wallet_service = new WalletRepository;
                    $wallet_service->walletSalePaymentAdd($order->id, $current_seller_amount, "Sale Payment", $order_package->seller_id);
                }
                elseif(app('general_setting')->seller_wise_payment && !in_array($order->order_payment->payment_method, [1,2])){
                    $order->order_payment->update([
                        'commision_amount' => $seller_commision
                    ]);
                    $seller_commision = 0;
                }
            } else {
                if(isModuleActive('MultiVendor')){
                    $revenue_amount = $order_package->products->sum('total_price') + $order_package->shipping_cost;
                    $total_gst_amount = $order_package->gst_taxes->sum('amount');
                    $total_product_tax_amount = $order_package->tax_amount;
                }
            }

            $transactionRepo = new TransactionRepository(new Transaction);

            if($total_seller_amount > 0){
                $transactionRepo->makeTransaction("Product Selling Amount for Seller", "in", $order->GatewayName, "sales_expense", $defaultSellerAccount, "Product Selling Amount for Seller", $order, $total_seller_amount, Carbon::now()->format('Y-m-d'), auth()->id(), null, null);
            }
            if($revenue_amount > 0){
                $transactionRepo->makeTransaction("Earning from Sales", "in", $order->GatewayName, "sales_income", $defaultIncomeAccount, "Product Sale", $order, $revenue_amount, Carbon::now()->format('Y-m-d'), auth()->id(), null, null);
            }

            if ($total_product_tax_amount > 0) {
                $transactionRepo->makeTransaction("Product Tax on Sale", "in", $order->GatewayName, "product_tax", $defaultProductTaxAccount, "ProductWise Tax Inhouse", $order, $total_product_tax_amount, Carbon::now()->format('Y-m-d'), auth()->id(), null, null);
            }
            if($seller_commision > 0){
                $transactionRepo->makeTransaction("Seller Order Commision", "in", $order->GatewayName, "seller_commision", $defaultSellerCommisionAccount, "Seller Order Commision", $order, $seller_commision, Carbon::now()->format('Y-m-d'), auth()->id(), null, null);
            }

            $order_package->is_paid = 1;
        }

        $order_package->delivery_status = $data['delivery_status'];
        $order_package->last_updated_by = getParentSellerId();
        $order_package->save();

        $total_is_paid = 0;
        $total_is_complete = 0;
        $total_package = 0;
        foreach($order->packages as $key => $pack){
            if($pack->is_paid == 1){
                $total_is_paid += 1;
            }
            if($pack->delivery_status == $last_delivery_state->id){
                $total_is_complete += 1;
            }
            $total_package += 1;
        }

        $order->order_status = $data['delivery_status'];
        if($order->is_paid == 0 && $total_package == $total_is_paid){
            $order->is_paid = 1;
        }
        if($order->is_completed == 0 && $total_package == $total_is_complete){
            $order->is_completed = auth()->user()->role->type != 'seller' ? 1:0;
        }
        $order->save();

        // TODO: Dispersión diferencial — disparar aquí cuando el módulo esté listo. // NOSONAR
        // El punto de inyección es: $order->is_paid acaba de pasar de 0 a 1.
        // Ver: Modules/NetworkTree/Services/DifferentialDistributionService (a crear en su momento).

        return true;
    }

    public function updateDeliveryStatusRecieve($data)
    {
        $order_package = $this->findOrderPackageByID($data);
        $order = $this->findOrderByID($order_package->order_id);
        $order->update([
            'order_status' => 4,
        ]);
        $order_package->update([
            'delivery_status' => 4,
        ]);
        OrderDeliveryState::create([
            'order_package_id' => $order_package->id,
            'delivery_status' => 4,
            'note' => "Order Has been Recieved",
            'date' => Carbon::now()->format('Y-m-d')
        ]);
    }

    public function updateStock($orderpackage)
    {
        try {
            $order = $orderpackage->order;
            $costCenterId = $order->cost_center_id ?? $this->defaultCostCenterId();

            foreach ($orderpackage->products as $package_product) {
                if ($package_product->type !== 'product') {
                    continue;
                }

                if ($package_product->seller_product_sku->product->stock_manage != 1) {
                    continue;
                }

                $skuId = $package_product->seller_product_sku->product_sku_id;
                $qty   = $package_product->qty;

                if ($costCenterId) {
                    app(DeductCostCenterStock::class)->execute(
                        $costCenterId, $skuId, $qty, $order->id, auth()->id() ?? $order->customer_id
                    );
                    $this->checkAndSendStockAlerts(
                        $package_product->seller_product_sku->product->product,
                        $costCenterId,
                        $skuId
                    );
                } else {
                    // Fallback legado: sin CC asignado ni default — descuenta de bodega principal
                    $stock = $package_product->seller_product_sku->product_stock;
                    $package_product->seller_product_sku->update([
                        'product_stock' => $stock - $qty,
                    ]);
                    if (@$package_product->package->seller->role->type == 'superadmin') {
                        $package_product->seller_product_sku->sku->update([
                            'product_stock' => $stock - $qty,
                        ]);
                    }
                    $this->checkAndSendStockAlertsLegacy(
                        $package_product->seller_product_sku->product->product,
                        $stock - $qty
                    );
                }
            }

            return true;

        } catch (InsufficientStockException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("updateStock error orden #{$orderpackage->order_id}: " . $e->getMessage());
            return false;
        }
    }

    private function defaultCostCenterId(): ?int
    {
        return CostCenter::where('status', 1)->where('is_default', 1)->value('id');
    }

    /**
     * Verificar niveles de stock del centro de costo y enviar alertas si es necesario.
     * Usa los umbrales de cost_center_product_alerts (por CC) — no el min_stock global del producto.
     * Si el producto no tiene alerta configurada para ese CC, no se dispara ninguna notificación.
     */
    private function checkAndSendStockAlerts($product, int $costCenterId, int $skuId): void
    {
        $alert = CostCenterProductAlert::where('cost_center_id', $costCenterId)
            ->where('product_id', $product->id)
            ->first();

        if (!$alert || $alert->min_stock === 0) {
            return;
        }

        $currentStock = (int) (CostCenterInventory::where('cost_center_id', $costCenterId)
            ->where('product_sku_id', $skuId)
            ->value('qty') ?? 0);

        $notificationSlug = $this->determineStockAlertType($currentStock, $alert->min_stock);

        if (!$notificationSlug) {
            return;
        }

        $notificationData = [
            'product_id'    => $product->id,
            'product_name'  => $product->product_name,
            'skus'          => $product->skus->pluck('sku')->toArray(),
            'current_stock' => $currentStock,
            'min_stock'     => $alert->min_stock,
            'max_stock'     => $alert->max_stock,
        ];

        app(DoubleApprovalService::class)->sendStockAlertNotification(
            $notificationData,
            $notificationSlug
        );
    }

    /**
     * Fallback: alertas para órdenes sin centro de costo — usa stock de product_sku.
     */
    private function checkAndSendStockAlertsLegacy($product, int $currentStock): void
    {
        $notificationSlug = $this->determineStockAlertType($currentStock, $product->min_stock);

        if (!$notificationSlug) {
            return;
        }

        $notificationData = [
            'product_id'    => $product->id,
            'product_name'  => $product->product_name,
            'skus'          => $product->skus->pluck('sku')->toArray(),
            'current_stock' => $currentStock,
            'min_stock'     => $product->min_stock,
            'max_stock'     => $product->max_stock,
        ];

        app(DoubleApprovalService::class)->sendStockAlertNotification(
            $notificationData,
            $notificationSlug
        );
    }

    /**
     * Determinar el tipo de alerta de stock según el nivel actual.
     * @param int $currentStock
     * @param int $minStock
     * @return string|null Slug de notificación o null si no hay alerta
     */
    private function determineStockAlertType(int $currentStock, $minStock): ?string
    {
        if ($currentStock === 0) {
            return 'empty_stock_alert';
        }

        if ($currentStock < $minStock) {
            return 'low_stock_alert';
        }

        return null;
    }


    public function orderConfirm($id)
    {

        $order = Order::with(['packages','packages.order','packages.order.billing_address','packages.order.shipping_address'])->find($id);
        if($order){
            $order->update([
                'is_confirmed' => 1
            ]);

            //customer and seller get Notification When super admin change any delivery status
            $notificationUrl = route('frontend.my_purchase_order_detail',encrypt($order->id));
            $notificationUrl = str_replace(url('/'),'',$notificationUrl);
            $this->notificationUrl = $notificationUrl;
            $this->adminNotificationUrl = 'ordermanage/total-sales-list';
            $this->routeCheck = 'order_manage.total_sales_index';
            $this->typeId = EmailTemplateType::where('type','order_email_template')->first()->id;//order email templete type id
            $notification = NotificationSetting::where('slug','order-confirmation')->first();
            if ($notification) {
                $this->notificationSend($notification->id, $order->customer_id);
            }
            foreach($order->packages as $key => $package){
                $package->update([
                    'delivery_status' => 2
                ]);
                $this->updateStock($package);
            }
            return 'done';
        }else{
            return 'failed';
        }

    }


    public function getPackageInfo($id){
        return OrderPackageDetail::find($id);
    }

}
