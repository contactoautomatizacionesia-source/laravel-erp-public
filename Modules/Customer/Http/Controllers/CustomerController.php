<?php

namespace Modules\Customer\Http\Controllers;
use App\Models\Order;
use App\Models\TypeDocument;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customer\Http\Requests\ProfileRequest;
use Modules\Customer\Http\Requests\CreateAddressRequest;
use Modules\Customer\Entities\CustomerAddress;
use Modules\Customer\Rules\MatchOldPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Traits\ImageStore;
use Modules\Customer\Services\CustomerService;
use App\Repositories\UserRepository;
use Brian2694\Toastr\Facades\Toastr;
use Modules\Setup\Entities\Country;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Http\Requests\StoreCustomerRequest;
use Modules\Customer\Http\Requests\UpdateCustomerRequest;
use Modules\Customer\Http\Requests\UpdateApprovalStatusRequest;
use Modules\Customer\Http\Requests\UpdateProfileRequest;
use Modules\GeneralSetting\Entities\Catalogs\Bank;
use Modules\GeneralSetting\Entities\Catalogs\BankAccountType;
use Modules\GeneralSetting\Entities\Catalogs\CivilStatus;
use Modules\GeneralSetting\Entities\Catalogs\ContractType;
use Modules\GeneralSetting\Entities\Catalogs\CountryPhoneCode;
use Modules\GeneralSetting\Entities\Catalogs\EconomicActivity;
use Modules\GeneralSetting\Entities\Catalogs\Gender;
use Modules\GeneralSetting\Entities\Catalogs\LeadSource;
use Modules\GeneralSetting\Entities\Catalogs\Profession;
use Modules\UserActivityLog\Traits\LogActivity;
use Yajra\DataTables\Facades\DataTables;
use Modules\UserActivityLog\Entities\LogActivity as LogActivityModel;
use Modules\Customer\Entities\CustomerProfile;
use Modules\Customer\Entities\EntrepreneurPlanHistory;
use Modules\Customer\Services\EntrepreneurPlanService;
use Modules\Marketing\Entities\ReferralCode;
use Modules\Plans\Entities\Plan;
use Modules\Plans\Helpers\PlanContextHelper;
use Carbon\Carbon;
class CustomerController extends Controller
{
    use ImageStore;
    protected $customerService;

    public function __construct(CustomerService  $customerService)
    {
        $this->middleware(['auth','maintenance_mode'])->except(['getActiveCustomers']);
        $this->middleware(['prohibited_demo_mode'])->only('updatePassword');
        $this->customerService = $customerService;
    }
    public function customer_index()
    {
        $data['customers'] = $this->customerService->getAll();
        return view('customer::customers.index', $data);
    }
    public function customer_index_get_data(){
        if(isset($_GET['table'])){
            $table = $_GET['table'];
            $type = 'customer_list';
            $manualActivationEnabled = manualActivation();
            $customer = $this->customerService->getAll()->whereRaw('1 = 0');
            
            switch ($table) {
                case 'active_customer':
                    $customer = $this->customerService->getAll()->where('is_active', 1);
                    break;

                case 'inactive_customer':
                    $customer = $this->customerService->getAll()->where('is_active', 0);
                    if ($manualActivationEnabled) {
                        $customer->where('approval_status', User::APPROVAL_STATUS_APPROVED);
                    }
                    break;

                case 'all_customer':
                    $customer = $this->customerService->getAll()->whereNotIn('is_active', ['2']);
                    break;

                case 'pending_approval_customer':
                    if ($manualActivationEnabled) {
                        $customer = $this->customerService->getAll()
                            ->where('is_active', 0)
                            ->where('approval_status', User::APPROVAL_STATUS_PENDING);
                        $type = 'pending_approval_lists';
                    }
                    break;

                case 'rejected_customer':
                    if ($manualActivationEnabled) {
                        $customer = $this->customerService->getAll()
                            ->where('is_active', 0)
                            ->where('approval_status', User::APPROVAL_STATUS_REJECTED);
                        $type = 'rejected_approval_lists';
                    }
                    break;

                case 'deleted_customer':
                    $customer = $this->customerService->getTrashedOnly();
                    $type = 'deleted_lists';
                    break;
            }
            
            return DataTables::of($customer)
                ->addIndexColumn()
                ->addColumn('avatar', function($customer){
                    return view('customer::customers.components._avatar_td',compact('customer'));
                })
                ->addColumn('unique_code', function($customer){
                    return 'COL' . str_pad($customer->id, 5, '0', STR_PAD_LEFT);
                })
                ->addColumn('name', function($customer){
                    return view('customer::customers.components._name_td',compact('customer'));
                })
                ->addColumn('phone', function($customer){
                    return getNumberTranslate($customer->customerProfile?->whatsapp ?? $customer->phone);
                })
                ->addColumn('email_verified', function($customer){
                    return view('customer::customers.components._email_verified_td', compact('customer'));
                })
                ->addColumn('status', function($customer){
                    return view('customer::customers.components._status_td',compact('customer'));
                })
                ->addColumn('current_plan', function($customer){
                    $planContext = PlanContextHelper::resolve(userId: $customer->id);
                    return view('customer::customers.components._current_plan_td', compact('customer', 'planContext'));
                })
                ->addColumn('wallet_balance', function($customer){
                    return single_price($customer->CustomerCurrentWalletAmounts);
                })
                ->addColumn('orders', function($customer){
                    return getNumberTranslate(count($customer->orders));
                })
                ->addColumn('action',function($customer) use ($type){
                    return view('customer::customers.components._action_td',compact('customer', 'type'));
                })
                ->rawColumns(['avatar','status','action','name','email_verified'])
                ->make(true);
        }else{
            return [];
        }
    }
    public function profile(ProfileRequest $request)
    {
        try {
            $customer_id=auth()->user()->id;
            $address_type=$request['address_type'];
            $match_data=['customer_id'=> $customer_id,'address_type' => $address_type];
            $form_data=[
                'name' => $request['name'],
                'address_one'=> $request['address_one'],
                'address_two' => $request['address_two'],
                'email' => $request['email'],
                'phone' => $request['phone'],
                'city' => $request['city'],
                'state' => $request['state'],
                'country' => $request['country'],
                'postal_code' => $request['postal_code']
            ];
            $data=CustomerAddress::updateOrCreate($match_data,$form_data);
            LogActivity::successLog('profile update');
            return response()->json($data);

        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'));
            return back();
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => 'required|min:8',
            'new_password_confirmation' => 'same:new_password',
        ]);
        try {
            User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password), 'password_updated_at' => now()]);
            LogActivity::successLog('customer password update');
            return response()->json(__('common.updated_successfully'));
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'));
            return back();
        }
    }
    public function create(){
        $typeDocuments = TypeDocument::active()->orderBy('name')->get(['name', 'id']);
        $genders = Gender::active()->orderBy('sort_order')->get(['name', 'id']);
        $professions = Profession::active()->orderBy('name')->get(['name', 'id']);
        $leadSources = LeadSource::active()->orderBy('sort_order')->get(['name', 'id']);
        $economicActivities = EconomicActivity::active()->orderBy('code')->get();
        $maritalStatus = CivilStatus::active()->orderBy('name')->get(['name', 'id']);
        $banks = Bank::active()->orderBy('name')->get(['name', 'id']);
        $accountTypes = BankAccountType::active()->orderBy('name')->get(['name', 'id']);
        $contractTypes = ContractType::active()->orderBy('name')->get(['name', 'id']);
        $countryPhoneCodes = CountryPhoneCode::active()->orderBy('sort_order')->get(['name', 'id']);
        
        return view('customer::customers.create', compact('countryPhoneCodes', 'typeDocuments', 'maritalStatus', 'genders', 'professions', 'leadSources', 'economicActivities', 'banks', 'accountTypes', 'contractTypes'));
    }
    public function store(StoreCustomerRequest $request){
        try{
            $user = $this->customerService->store($request->validated());
            Toastr::success(__('common.created_successfully'), __('common.success'));
            LogActivity::successLog(__('hr.customer_created_successfully') . ' ' . __('common.full_name') . ": {$user->full_name}, " . __('common.email') . ": {$user->email} (ID: {$user->id})");
            return redirect()->route('cusotmer.list_active');
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }
    public function edit($id){
        $typeDocuments = TypeDocument::active()->orderBy('name')->get(['name', 'id']);
        $genders = Gender::active()->orderBy('sort_order')->get(['name', 'id']);
        $professions = Profession::active()->orderBy('name')->get(['name', 'id']);
        $leadSources = LeadSource::active()->orderBy('sort_order')->get(['name', 'id']);
        $economicActivities = EconomicActivity::active()->orderBy('code')->get();
        $maritalStatus = CivilStatus::active()->orderBy('name')->get(['name', 'id']);
        $banks = Bank::active()->orderBy('name')->get(['name', 'id']);
        $accountTypes = BankAccountType::active()->orderBy('name')->get(['name', 'id']);
        $contractTypes = ContractType::active()->orderBy('name')->get(['name', 'id']);
        $countryPhoneCodes = CountryPhoneCode::active()->orderBy('sort_order')->get(['name', 'id']);

        $customer = $this->customerService->find($id);
        return view('customer::customers.edit', compact('countryPhoneCodes', 'customer', 'typeDocuments', 'maritalStatus', 'genders', 'professions', 'leadSources', 'economicActivities', 'banks', 'accountTypes', 'contractTypes'));
    }

    public function update(UpdateCustomerRequest $request, $id){
        try{
            $result = $this->customerService->update($request->validated(), $id);
            $user = $result['user'];
            $changes = $result['changes'];
            $message = __('hr.customer_updated_successfully');
            if (!empty($changes)) {
                $changeLog = __('common.user') . ": {$user->full_name}. " . __('hr.changes') . ": " . implode(", ", $changes);
            } else {
                $changeLog = __('common.user') . ": {$user->full_name}. " . __('hr.no_changes_made');
            }
            Toastr::success($message, __('common.success'));
            LogActivity::successLog($message . ' ' . $changeLog);
            return redirect()->route('cusotmer.list_active');
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }

    public function destroy(Request $request, $id){
        try{
            $result = $this->customerService->destroy($id);
            
            if ($result['success']) {
                $user = $result['data'];
                $logMsg = __('common.deleted_successfully') . " (ID: {$user->id}, " . __('common.user') . ": {$user->full_name}, ". __('common.email') .": {$user->email})";
                LogActivity::successLog($logMsg);
                
                Toastr::success(__('common.deleted_successfully'), __('common.success'));
                return redirect()->route('cusotmer.list_active');
            }

            // Manejo de errores específicos para el Log y el UI
            switch ($result['code']) {
                case 'HAS_ORDERS':
                case 'HAS_WALLET_BALANCE':
                    // Mapeamos el código del error a su variable de lenguaje correspondiente
                    $reason = ($result['code'] == 'HAS_ORDERS') ? __('hr.error_has_orders') : __('hr.error_has_wallet_balance');
                    $notPossibleMsg = __('hr.deleted_not_possible_for_this_customer');
                    
                    // Construimos el mensaje de error para el UI (Toastr)
                    $errorMsg = $notPossibleMsg . ' ' . $reason;
                    
                    // Generamos un log de error detallado y consistente
                    $logMsg = "{$notPossibleMsg} " . __('common.email') . ": {$result['data']['email']} {$reason}";
                    LogActivity::errorLog($logMsg);
                    
                    // Mostramos la advertencia al usuario
                    Toastr::warning($errorMsg, __('common.warning'));
                    break;

                default:
                    Toastr::error(__('common.error_message'), __('common.error'));
            }

            return redirect()->route('cusotmer.list_active');

        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }
    public function show($id)
    {
        $data['customer'] = $this->customerService->find($id);
        $data['customer']->load([
            'customerProfile.planHistory.planChild.plan.planChildren',
            'customerProfile.planHistory.assignedBy',
        ]);
        $data['plans'] = Plan::where('is_active', true)
        ->with([
            'planChildren' => function ($q) {
                $q->where('is_active', true)
                ->orderBy('level_order')
                ->with([
                    'rules' => function ($r) {
                        $r->where('is_active', true)
                            ->with([
                                'category',
                                'dependencies'
                            ]);
                    },
                    'benefits' => function ($b) {
                        $b->where('is_active', true)
                            ->with([
                                'category'
                            ]);
                    }
                ]);
            }
        ])
        ->orderBy('order')
        ->get();
        $data['planContext'] = PlanContextHelper::resolve(userId: $id);
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $personalPointsMonth = Order::where('customer_id', $id)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('total_points');
        $personalPointsAccumulated = Order::where('customer_id', $id)->sum('total_points');
        $networkPointsMonth = DB::table('network_paths as np')
            ->join('orders as o', 'o.customer_id', '=', 'np.entrepreneur_id')
            ->where('np.ancestor_id', $id)
            ->where('np.depth', '>', 0)
            ->whereBetween('o.created_at', [$monthStart, $monthEnd])
            ->sum('o.total_points');
        $data['scoreData'] = [
            'personal_points_month' => (float) $personalPointsMonth,
            'personal_points_accumulated' => (float) $personalPointsAccumulated,
            'net_points_month' => (float) $networkPointsMonth,
        ];
        $logins = LogActivityModel::where('user_id',$id)->where('login',1)->orderBy('id','DESC')->limit(20)->get();
        $data['logins'] = $logins;
        $referralCode = ReferralCode::where('user_id', $id)->first();
        $data['referralCode'] = $referralCode ? $referralCode->referral_code : null;
        return view('customer::customers.show_details', $data);
    }

    public function getOrders($id){
        $customer = $this->customerService->find($id);
        $order = $customer->orders;
        return DataTables::of($order)
            ->addIndexColumn()
            ->addColumn('date', function($order){
                return dateConvert($order->created_at);
            })
            ->addColumn('order_number',function($order){
                return  getNumberTranslate($order->order_number);
            })
            ->addColumn('number_of_product',function($order){
                return  getNumberTranslate($order->packages->sum('number_of_product'));
            })
            ->addColumn('total_amount',function($order){
                return  single_price($order->grand_total);
            })
            ->addColumn('order_status', function($order){
                return view('customer::customers.components._show_order_status_td',compact('order'));
            })
            ->addColumn('is_paid', function($order){
                return view('customer::customers.components._show_order_is_paid_td',compact('order'));
            })
            ->addColumn('action',function($order){
                return view('customer::customers.components._show_order_action_td',compact('order'));
            })
            ->rawColumns(['order_status','is_paid','action'])
            ->make(true);
    }

    public function getWalletHistory($id){
        $customer = $this->customerService->find($id);
        $transaction = $customer->wallet_balances;
        return DataTables::of($transaction)
            ->addIndexColumn()
            ->addColumn('date', function($transaction){
                return dateConvert($transaction->created_at);
            })
            ->addColumn('user',function($transaction){
                return  $transaction->user->first_name;

            })
            ->addColumn('amount',function($transaction){
                return  single_price($transaction->amount);

            })
            ->addColumn('payment_method', function($transaction){
                return $transaction->GatewayName;

            })
            ->addColumn('approval', function($transaction){
                return view('customer::customers.components._wallet_approval_td',compact('transaction'));
            })
            ->rawColumns(['approval'])
            ->make(true);
    }


    public function updateInfo(UpdateProfileRequest $request)
    {
        
        try {
            $data = $request->validated();

            $user = $this->customerService->updateProfileFromPortal($data, auth()->id());

            LogActivity::successLog('update info');
            return response()->json($user);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            // return back();
            return response()->json([
                'message' => __('common.error_message'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeAddress(CreateAddressRequest $request)
    {
        try {
            $data=[
                'customer_id'=>auth()->user()->id,
                'name'=>$request->name,
                'email'=>$request->email,
                'address'=>$request->address,
                'phone'=>$request->phone,
                'city'=>$request->city,
                'state'=>$request->state,
                'country'=>$request->country,
                'postal_code'=>$request->postal_code
            ];
            if(isset($request->shipping_address)){
                CustomerAddress::where('is_shipping_default',1)->update(['is_shipping_default'=> 0]);
                $data['is_shipping_default'] = 1;
            }
            if(isset($request->billing_address)){
                CustomerAddress::where('is_billing_default',1)->update(['is_billing_default'=> 0]);
                $data['is_billing_default'] = 1;
            }
            $customer=CustomerAddress::create($data);
            $list=CustomerAddress::where('customer_id',$customer->customer_id)->get();
            if(count($list)<=1){
                $setDefaltData=CustomerAddress::find($customer->id);
                $setDefaltData->is_shipping_default=1;
                $setDefaltData->is_billing_default=1;
                $setDefaltData->save();
            }
            LogActivity::successLog('address added');
            return  $this->loadTableData();
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'));
            return back();
        }
    }

    public function updateAddress(CreateAddressRequest $request){
        try {
            $data = [
                'name'=>$request->name,
                'email'=>$request->email,
                'address'=>$request->address,
                'phone'=>$request->phone,
                'city'=>$request->city,
                'state'=>$request->state,
                'country'=>$request->country,
                'postal_code'=>$request->postal_code,
            ];
            if(isset($request->shipping_address)){
                CustomerAddress::where('is_shipping_default',1)->first()->update(['is_shipping_default'=> 0]);
                $data['is_shipping_default'] = 1;
            }
            if(isset($request->billing_address)){
                CustomerAddress::where('is_billing_default',1)->first()->update(['is_billing_default'=> 0]);
                $data['is_billing_default'] = 1;
            }
            $customer = CustomerAddress::findOrFail($request->address_id);
            $old_data = CustomerAddress::findOrFail($request->address_id);
            $customer->update($data);
            // CustomerAddress::where('id',$request->address_id)->update([
            //     "customer_id" => $old_data->customer_id,
            //     "name" => $old_data->name,
            //     "email" => $old_data->email,
            //     "phone" => $old_data->phone,
            //     "address" => $old_data->address,
            //     "city" => $old_data->city,
            //     "state" => $old_data->state,
            //     "country" => $old_data->country,
            //     "postal_code" => $old_data->postal_code,
            //     "is_shipping_default" => $old_data->is_shipping_default,
            //     "is_billing_default" => $old_data->is_billing_default,
            //     "is_updated" => 1,
            // ]);

            LogActivity::successLog('update address');
            return  $this->loadTableData();
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'));
            return back();
        }
    }

    public function setDefaultShipping(Request $request)
    {
        CustomerAddress::where('customer_id',$request->c_id)->update(['is_shipping_default'=> 0]);
        $customer=CustomerAddress::find($request->c_list_id);
        $customer->is_shipping_default=1;
        $customer->save();
        LogActivity::successLog('set default shipping.');
        return  $this->loadTableData();
    }

    public function setDefaultBilling(Request $request)
    {
        CustomerAddress::where('customer_id',$request->c_id)->update(['is_billing_default'=> 0]);
        $customer=CustomerAddress::find($request->c_list_id);
        $customer->is_billing_default = 1 ;
        $customer->save();
        LogActivity::successLog('set default billing.');
        return  $this->loadTableData();
    }

    public function editAddress($c_id){
        try {
            $address=CustomerAddress::findOrFail($c_id);
            $countries = Country::where('status', 1)->orderBy('name')->get();
            if (auth()->user()->role->type != 'customer') {
                return view('backEnd.pages.customer_data._edit_address_form',compact('address', 'countries'));
            }
            else {
                return view(theme('pages.profile.partials._edit_form'),compact('address', 'countries'));
            }

        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());

            Toastr::error(__('common.operation_failed'));
            return back();
        }
    }

    public function deleteAddress(Request $request){
        try{
            $addressExist = Order::where('customer_id',auth()->user()->id)->where('customer_shipping_address', $request->id)->orWhere('customer_billing_address', $request->id)->first();
            if (!$addressExist) {
                $customer_address = CustomerAddress::where('id',$request->id)->where('customer_id', auth()->user()->id)->first();
                if($customer_address->is_shipping_default == 1 || $customer_address->is_billing_default == 1){
                    return 'is_default';
                }
                $customer_address->delete();
                LogActivity::successLog('address deleted');
                return $this->loadTableData();
            }else{
                return 'is_used';
            }

        }catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }

    public function imageDelete(Request $request){
        try{
            $this->customerService->imageDelete($request->except("_token"));
            LogActivity::successLog('address deleted');
            return true;
        }catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }

    private function loadTableData()
    {
        try {
            $addressList=CustomerAddress::where('customer_id',auth()->user()->id)->get();
            return response()->json([
                'addressList' =>  (auth()->user()->role->type != 'customer') ?(string)view('backEnd.pages.customer_data._table',compact('addressList')) : (string)view(theme('pages.profile.partials._table'),compact('addressList')),
                'addressListForShipping' =>  (auth()->user()->role->type != 'customer') ?(string)view('backEnd.pages.customer_data._shipping_address',compact('addressList')) : (string)view(theme('pages.profile.partials._shipping'), compact('addressList')),
                'addressListForBilling' =>  (auth()->user()->role->type != 'customer') ?(string)view('backEnd.pages.customer_data._billing_address',compact('addressList')) : (string)view(theme('pages.profile.partials._billing'), compact('addressList')),
            ]);

        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return back();
        }
    }

    public function update_active_status(Request $request)
    {
        try {
            $userRepo = app(UserRepository::class);
            $userRepo->statusUpdate($request->all());
            LogActivity::successLog('customer update active status');
            return 1;
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return 0;
        }
    }

    public function update_approval_status(UpdateApprovalStatusRequest $request)
    {
        try {
            $targetCustomer = $request->targetCustomer();
            if (!$targetCustomer) {
                return response()->json([
                    'status' => 0,
                    'message' => __('common.error_message'),
                ], 422);
            }

            $reason = trim((string) $request->reason);
            $reason = $reason ?: ($request->status === User::APPROVAL_STATUS_APPROVED
                ? __('common.default_approval_reason')
                : __('common.default_rejection_reason'));

            $customer = $this->customerService->updateApprovalStatus(
                (int) $request->id,
                $request->status,
                $reason,
                auth()->id()
            );

            $successMessage = $request->status === User::APPROVAL_STATUS_APPROVED
                ? __('common.approved_successfully')
                : __('common.rejected_successfully');

            $statusLabel = $request->status === User::APPROVAL_STATUS_APPROVED
                ? __('common.approved')
                : __('common.declined');

            LogActivity::successLog(
                __('common.customer_approval_status_updated_log', [
                    'status' => $statusLabel,
                    'id' => $customer->id,
                    'email' => $customer->email,
                ])
            );

            return response()->json([
                'status' => 1,
                'message' => $successMessage,
            ]);
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());

            return response()->json([
                'status' => 0,
                'message' => __('common.error_message'),
            ], 500);
        }
    }

    public function customerBulkUpload()
    {
        try {
            return view('customer::customers.bulk_upload');
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return 0;
        }
    }

    public function customerBulkUploadStore(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xls,xlsx|max:2048'
        ]);
        ini_set('max_execution_time', 0);
        DB::beginTransaction();
        try {
            $this->customerService->BulkUploadStore($request->except('_token'));
            DB::commit();
            Toastr::success(__('common.created_successfully'), __('common.success'));
            LogActivity::successLog('Customer Bluk Upload Successfully.');
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e->getCode() == 23000) {
                Toastr::error(__('common.duplicate_entry_is_exist_in_your_file'));
            } else {
                Toastr::error(__('common.invalid_csv_file'));
            }
            LogActivity::errorLog($e->getMessage());
            return back();
        }
    }

    // Restaurar usuario
    public function restore(Request $request, $id)
    {
        try {
            $user = $this->customerService->restore($id);
            
            // Mensaje y Log consistente con el sistema
            $message = __('hr.customer') . ' ' . __('hr.restored_successfully');
            $logDescription = __('common.user') . ": {$user->full_name}, " . __('common.email') . ": {$user->email} (ID: {$user->id}).";
            
            LogActivity::successLog($message . ' ' . $logDescription);
            Toastr::success($message, __('common.success'));
            
            return redirect()->route('cusotmer.list_active');
            
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }

    public function getActiveCustomers(Request $request)
    {
        $search = $request->term ?? $request->search;
        
        $customers = $this->customerService->getActiveCustomersForSelect($search);
        
        return response()->json($customers);
    }

    public function customerToChangePlan(Request $request)
    {
        $data['customer']    = $this->customerService->find($request->id);
        $data['plans'] = Plan::where('is_active', true)
        ->with([
            'planChildren' => function ($q) {
                $q->where('is_active', true)
                ->orderBy('level_order')
                ->with([
                    'rules' => function ($r) {
                        $r->where('is_active', true)
                            ->with([
                                'category',
                                'dependencies'
                            ]);
                    },
                    'benefits' => function ($b) {
                        $b->where('is_active', true)
                            ->with([
                                'category'
                            ]);
                    }
                ]);
            }
        ])
        ->orderBy('order')
        ->get();
        $data['planContext'] = PlanContextHelper::resolve(userId: $request->id);
        return view('customer::customers.components.modals._change_plan_modal', $data);
    }

    public function assignPlanManually(Request $request)
    {
        $request->validate([
            'customer_id'   => 'required|integer|exists:users,id',
            'plan_child_id' => 'required|integer|exists:plan_child,id',
            'reason_notes'  => 'required|string|min:20|max:300',
        ]);

        $profile = CustomerProfile::where('user_id', $request->customer_id)->first();
        if (! $profile) {
            return response()->json(['message' => __('common.customer_profile_not_found')], 422);
        }

        app(EntrepreneurPlanService::class)->assignPlan(
            userId:      $request->customer_id,
            planChildId: $request->plan_child_id,
            reason:      EntrepreneurPlanHistory::REASON_MANUAL,
            assignedBy:  auth()->id(),
        );

        $profile->update(['is_manual_assignment' => true]);

        return response()->json(['message' => __('common.plan_assigned_successfully')]);
    }

    /**
     * [DEV] Genera y devuelve el PDF del contrato para el último usuario registrado.
     * Eliminar cuando ya no se necesite para depuración.
     */
    public function contractPreview()
    {
        $user = User::latest('id')->first();

        if (! $user) {
            abort(404, 'No hay usuarios registrados.');
        }

        /** @var \Modules\Customer\Services\ContractBuilderService $builder */
        $builder = app(\Modules\Customer\Services\ContractBuilderService::class);

        $pdfContent = $builder->previewLastContract($user);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="contrato_preview.pdf"',
        ]);
    }

}
