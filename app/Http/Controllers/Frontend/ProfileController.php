<?php

namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;
use App\Traits\GoogleAnalytics4;
use Illuminate\Support\Facades\App;
use Modules\Customer\Entities\CustomerAddress;
use Modules\Visitor\Entities\VisitorHistory;
use Modules\Marketing\Entities\ReferralCode;
use Modules\MultiVendor\Entities\SellerAccount;
use Modules\Seller\Entities\SellerProduct;
use Modules\Review\Entities\ProductReview;
use Modules\Refund\Entities\RefundRequest;
use Modules\Account\Entities\Transaction;
use Modules\Marketing\Entities\CouponUse;
use Modules\Marketing\Entities\Coupon;
use Modules\Product\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Setup\Entities\Country;
use App\Models\Subscription;
use App\Models\SearchTerm;
use App\Models\Wishlist;
use App\Models\Order;
use App\Models\User;
use App\Models\Cart;
use App\Models\OrderPackageDetail;
use App\Models\OrderProductDetail;
use App\Models\TypeDocument;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\GeneralSetting\Entities\Catalogs\Bank;
use Modules\GeneralSetting\Entities\Catalogs\BankAccountType;
use Modules\GeneralSetting\Entities\Catalogs\CivilStatus;
use Modules\GeneralSetting\Entities\Catalogs\CountryPhoneCode;
use Modules\GeneralSetting\Entities\Catalogs\EconomicActivity;
use Modules\GeneralSetting\Entities\Catalogs\Gender;
use Modules\GeneralSetting\Entities\Catalogs\LeadSource;
use Modules\GeneralSetting\Entities\Catalogs\Profession;
use Modules\Setup\Repositories\CityRepository;
use Modules\Setup\Repositories\StateRepository;
use Modules\UserActivityLog\Traits\LogActivity;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use Modules\Plans\Helpers\PlanContextHelper;
use Modules\Plans\Helpers\PlanHistoryHelper;
use Modules\NetworkTree\Services\NetworkTreeManager;
use Modules\Customer\Entities\SignatureBatch;
use Modules\GeneralSetting\Entities\ParameterSetting;
use App\Services\KycUpdateService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use GoogleAnalytics4;

    public function __construct()
    {
        $this->middleware(['maintenance_mode','auth','customer']);
    }

    public function index(){
        try{
            $data['typeDocuments'] = TypeDocument::active()->orderBy('name')->get(['name', 'id']);
            $data['genders'] = Gender::active()->orderBy('sort_order')->get(['name', 'id']);
            $data['professions'] = Profession::active()->orderBy('name')->get(['name', 'id']);
            $data['leadSources'] = LeadSource::active()->orderBy('sort_order')->get(['name', 'id']);
            $data['economicActivities'] = EconomicActivity::active()->orderBy('code')->get();
            $data['maritalStatus'] = CivilStatus::active()->orderBy('name')->get(['name', 'id']);
            $data['user_info'] = User::find(auth()->user()->id);
            $data['addressList'] = CustomerAddress::where('customer_id',auth()->user()->id)->where('is_updated',0)->get();
            $data['countries'] = Country::where('status', 1)->orderBy('name')->get();
            $data['states'] = (new StateRepository())->getByCountryId(app('general_setting')->default_country)->where('status', 1);
            $data['cities'] = (new CityRepository())->getByStateId(app('general_setting')->default_state)->where('status', 1);
            $data['banks'] = Bank::active()->orderBy('name')->get(['name', 'id']);
            $data['accountTypes'] = BankAccountType::active()->orderBy('name')->get(['name', 'id']);
            $data['countryPhoneCodes'] = CountryPhoneCode::active()->orderBy('sort_order')->get(['name', 'id']);

            if (auth()->user()->role->type != 'customer') {
                return view('backEnd.pages.customer_data.profile',$data);
            }
            else {
                return view(theme('pages.profile.profile'),$data);
            }
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }
    public function dashboard()
    {
        try{
            if (auth()->user()->role->type == "superadmin" || auth()->user()->role->type == "admin" || auth()->user()->role->type == "staff") {
                if (app('business_settings')->where('type', 'google_analytics')->first()->status == 1 &&  env('ANATYTIC_RESULT_DASHBOARD') == 1){
                    $analytic = Analytics::fetchVisitorsAndPageViews(Period::days(1));
                    $a = Analytics::fetchVisitorsAndPageViews(Period::days(1));
                    $data['total_page_visitor'] = $a->sum('visitors');
                    $data['total_page_views'] = $a->sum('pageViews');
                    $userType = Analytics::fetchUserTypes(Period::days(3));
                    $data['total_new_visitor'] = $userType->where('type', 'New Visitor')->sum('sessions');
                    $data['total_old_views'] = $userType->where('type', 'Returning Visitor')->sum('sessions');
                    $data['total_in_session'] = $data['total_new_visitor'] + $data['total_old_views'];
                }
                $data['totalProducts'] = Product::where('is_approved',1)->count();
                $data['totalSellers'] = isModuleActive('MultiVendor')?SellerAccount::all()->count():0;
                $data['totalCustomers'] = User::where('role_id',4)->get()->count();
                $data['totalvisitors'] = VisitorHistory::VisitorCount('today');
                $data['total_sale'] = Order::TotalSaleCount('today');
                $data['total_review'] = ProductReview::TotalReviewCount('today');
                $data['categories'] = Category::whereHas('products')->limit(10)->take(10)->get();
                $data['topSaleCategories'] = Category::orderBy('total_sale','desc')->limit(10)->take(10)->get();
                $data['categoriesTotal'] = Category::where('status', 1)->count();
                $data['top_ten_products'] = SellerProduct::with('product','product.categories','product.brand')->orderBy('total_sale','desc')->limit(10)->take(10)->get();
                $data['top_ten_sellers'] = isModuleActive('MultiVendor')?SellerAccount::with('user')->orderBy('total_sale_qty','desc')->limit(10)->take(10)->get():[];
                $data['coupon_wise_sales'] = Coupon::with('coupon_uses')->whereHas('coupon_uses')->limit(10)->take(10)->latest()->get();
                $data['total_coupon'] = Coupon::with('coupon_uses')->get();
                $data['total_order'] = Order::OrderInfo('today', 'all');
                $data['total_pending_order'] = Order::OrderInfo('today', 0);
                $data['total_completed_order'] = Order::OrderInfo('today', 1);
                $data['income'] = Transaction::GetIncome('today');
                $data['expense'] = Transaction::GetExpense('today');
                $data['total_revenue'] = $data['income'] - $data['expense'];
                $data['new_customers'] = User::where('role_id', 4)->latest()->limit(10)->take(10)->get();
                $data['total_active_customers'] = User::where('role_id', 4)->where('is_active', 1)->get()->count();
                $data['total_subscriber'] = Subscription::count();
                $data['latest_search_keywords'] = SearchTerm::latest()->limit(10)->take(10)->get();
                $data['recently_added_products'] = SellerProduct::with('product','product.categories','product.brand')->latest()->take(10)->get();
                $data['top_refferers'] = ReferralCode::with('user')->orderBy('total_used','desc')->take(10)->get();
                $data['latest_orders'] = Order::with('packages', 'customer')->latest()->take(10)->get();
                
                $data['graph_total_product'] = Product::where('status',1)->count();
                $data['graph_admin_product'] = Product::where('is_approved',1)->count();                

                $data['graph_inactive_product'] = Product::where('status',0)->count();

                $data['graph_seller_product'] = SellerProduct::whereHas('seller', function($q){
                    $q->where('role_id', 5);
                })->where('status',1)->count();

                $data['graph_total_sales'] = Order::count();
                $data['graph_cancelled_sales'] = Order::where('is_cancelled', 1)->count();
                $data['graph_completed_sales'] = Order::where('is_completed', 1)->count();
                $data['graph_pending_sales'] = Order::where('is_confirmed', 0)->count();

                $data['graph_sales_today'] = count(Order::where('is_confirmed', 0)->whereBetween('created_at', [Carbon::now()->format('y-m-d')." 00:00:00", Carbon::now()->format('y-m-d')." 23:59:59"])->get());
                $data['graph_pending_sales_today'] = count(Order::where('is_confirmed', 0)->whereBetween('created_at', [Carbon::now()->format('y-m-d')." 00:00:00", Carbon::now()->format('y-m-d')." 23:59:59"])->get());
                $data['graph_processing_sales_today'] = count(Order::where('is_confirmed', 1)->whereBetween('created_at', [Carbon::now()->format('y-m-d')." 00:00:00", Carbon::now()->format('y-m-d')." 23:59:59"])->get());
                $data['graph_completed_sales_today'] = count(Order::where('is_completed', 1)->whereBetween('created_at', [Carbon::now()->format('y-m-d')." 00:00:00", Carbon::now()->format('y-m-d')." 23:59:59"])->get());
                $data['graph_total_sellers'] = isModuleActive('MultiVendor')?count(SellerAccount::all()):0;
                $data['graph_normal_sellers'] = isModuleActive('MultiVendor')?count(SellerAccount::where('is_trusted', 0)->get()):0;
                $data['graph_trusted_sellers'] = isModuleActive('MultiVendor')?count(SellerAccount::where('is_trusted', 1)->get()):0;

                $data['top_disputed_customer'] = DB::table('refund_requests')
                                                    ->select(DB::raw('customer_id as customer_id'), DB::raw('sum(total_return_amount) as total'))
                                                    ->groupBy(DB::raw('customer_id'))
                                                    ->orderBy('total','desc')
                                                    ->take(10)
                                                    ->get();
                $data['top_disputed_sellers'] = DB::table('refund_request_details')
                                                    ->select(DB::raw('seller_id as seller_id'), DB::raw('count(seller_id) as total'))
                                                    ->groupBy(DB::raw('seller_id'))
                                                    ->orderBy('total','desc')
                                                    ->take(10)
                                                    ->get(['seller_id']);
                $data['graph_total_authorized_order'] = count(Order::where('customer_id', '!=', null)->whereBetween('created_at', [Carbon::now()->format('y-m-d')." 00:00:00", Carbon::now()->format('y-m-d')." 23:59:59"])->get());;
                $data['graph_total_guest_order'] = count(Order::where('customer_id', null)->whereBetween('created_at', [Carbon::now()->format('y-m-d')." 00:00:00", Carbon::now()->format('y-m-d')." 23:59:59"])->get());;
                $data['total_product_in_cart'] = Cart::TotalCart('today');
                return view('backEnd.dashboard', $data);
            }else {
                $data['total_order_count'] = Order::where('customer_id', auth()->user()->id)->count();
                $data['total_confirmed_order_count'] = Order::where('customer_id', auth()->user()->id)->where('is_confirmed',1)->count();
                $data['total_completed_order_count'] = Order::where('customer_id', auth()->user()->id)->where('is_completed',1)->count();
                $data['total_processing_order_count'] = Order::where('customer_id', auth()->user()->id)->where('is_confirmed',1)->where('is_completed',0)->count();
                $wishlist_query = Wishlist::query()->where('user_id', auth()->user()->id)->whereHas('product', function($q){
                    $q->activeSeller();
                });
                $data['wishlists'] = $wishlist_query->with(['product.product','product.skus'])->latest()->take(4)->get();
                $data['total_wishlist_count'] = $wishlist_query->count();
                $data['total_item_in_carts'] = Cart::where('user_id', auth()->user()->id)->count();
                $data['total_success_refund'] = RefundRequest::where('customer_id', auth()->user()->id)->where('is_completed', 1)->count();
                $data['total_coupon_used'] = CouponUse::where('user_id', auth()->user()->id)->count();
                $data['purchase_histories'] = OrderPackageDetail::with(['order','products'])->whereHas('order', function($query){
                    $query->where('customer_id', auth()->id());
                })->latest()->paginate(5);

                $data['planContext'] = PlanContextHelper::resolve(userId: auth()->user()->id);
                $data['planHistory'] = PlanHistoryHelper::resolve(auth()->user()->id);
                $data['descendantsCount'] = app(NetworkTreeManager::class)->countDescendants(auth()->user()->id);

                // Batch de firma pendiente o parcial más reciente del usuario.
                $data['pendingSignatureBatch'] = SignatureBatch::with(['documents' => function ($q) {
                    $q->where('status', 'pending');
                }])
                ->where('user_id', auth()->id())
                ->whereIn('status', [SignatureBatch::STATUS_PENDING, SignatureBatch::STATUS_PARTIAL])
                ->latest()
                ->first();

                $data['carts'] = \App\Models\Cart::with('product.product.product','giftCard','product.product_variations.attribute', 'product.product_variations.attribute_value.color')->where('user_id',auth()->user()->id)->where('product_type', 'product')->whereHas('product',function($query){
                        return $query->where('status', 1)->whereHas('product', function($q){
                            return $q->activeSeller();
                        });
                    })->orWhere('product_type', 'gift_card')->where('user_id',auth()->user()->id)->whereHas('giftCard', function($query){
                        return $query->where('status', 1);
                    })->latest()->take(4)->get();
                $data['recent_order_products'] = OrderProductDetail::with(['seller_product_sku.product.product','giftCard'])->select('order_product_details.*')->join('order_package_details', function($query){
                    $query->on('order_package_details.id','=','order_product_details.package_id')->join('orders', function($query1){
                        $query1->on('orders.id','=','order_package_details.order_id')->where('orders.customer_id',auth()->id());
                    });
                })->distinct('order_product_details.id')->orderByDesc('order_product_details.id')->take(4)->get();
                return view(theme('pages.profile.dashboard'), $data);
            }
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }

    public function dashboardCards($type)
    {
        $total_visitors = VisitorHistory::VisitorCount($type);
        $total_sale = Order::TotalSaleCount($type);
        $total_order = Order::OrderInfo($type, 'all');
        $total_pending_order = Order::OrderInfo($type, 0);
        $total_completed_order = Order::OrderInfo($type, 1);
        $total_review = ProductReview::TotalReviewCount($type);
        $income = Transaction::GetIncome($type);
        $expense = Transaction::GetExpense($type);
        $total_revenue = $income - $expense;
        return [
            'total_visitors' => $total_visitors,
            'total_sale' => single_price($total_sale),
            'total_order' => $total_order,
            'total_pending_order' => $total_pending_order,
            'total_completed_order' => $total_completed_order,
            'total_review' => $total_review,
            'total_revenue' => single_price($total_revenue),
        ];
    }

    public function order(){

        try{

            return view(theme('pages.profile.order'));
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }

    public function refund(){
        try{

            return view(theme('pages.profile.refund'));
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }

    public function network(){
        try{
            return view(theme('pages.profile.network'));
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * Muestra la vista forzada de actualización de datos (KYC).
     */
    public function kycUpdateIndex()
    {
        try {
            $customer = auth()->user();

            // 1. Obtenemos los campos que el administrador decidió ocultar
            $setting = \Modules\GeneralSetting\Entities\ParameterSetting::where('slug', 'kyc-readonly-fields')->where('is_active', 1)->first();
            $kyc_hidden_fields = [];

            if ($setting && $setting->json_value) {
                $kyc_hidden_fields = is_string($setting->json_value) ? json_decode($setting->json_value, true) : $setting->json_value;
            }

            // 2. Cargamos TODOS los catálogos necesarios para los "select" de los archivos register_*.blade.php
            $data['typeDocuments'] = TypeDocument::active()->orderBy('name')->get(['name', 'id']);
            $data['genders'] = Gender::active()->orderBy('sort_order')->get(['name', 'id']);
            $data['professions'] = Profession::active()->orderBy('name')->get(['name', 'id']);
            $data['leadSources'] = LeadSource::active()->orderBy('sort_order')->get(['name', 'id']);
            $data['economicActivities'] = EconomicActivity::active()->orderBy('code')->get();
            $data['maritalStatus'] = CivilStatus::active()->orderBy('name')->get(['name', 'id']);
            $data['countries'] = Country::where('status', 1)->orderBy('name')->get();
            $data['states'] = (new StateRepository())->getByCountryId(app('general_setting')->default_country)->where('status', 1);
            $data['cities'] = (new CityRepository())->getByStateId(app('general_setting')->default_state)->where('status', 1);
            $data['banks'] = Bank::active()->orderBy('name')->get(['name', 'id']);
            $data['accountTypes'] = BankAccountType::active()->orderBy('name')->get(['name', 'id']);
            $data['countryPhoneCodes'] = CountryPhoneCode::active()->orderBy('sort_order')->get(['name', 'id']);

            // Aquí está el responsable del error. Lo obtenemos del modelo de plantillas de contratos:
            $data['contractTypes'] = \Modules\Customer\Entities\ContractTemplate::where('is_active', 1)->get();

            // Inyectamos las variables principales
            $data['customer'] = $customer;
            $data['kyc_hidden_fields'] = $kyc_hidden_fields;

            // 3. Retornamos la vista (Usa el theme() si fue el que te funcionó en el paso anterior)
            return view(theme('pages.profile.kyc_update'), $data);
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return back()->with('error', __('common.error_message'));
        }
    }

    /**
     * Procesa la actualización y guarda el log de auditoría.
     */
    public function kycUpdateStore(Request $request, KycUpdateService $kycService)
    {
        try {
            $user = auth()->user();

            // Usamos $request->except() para no guardar tokens ni métodos
            $dataToUpdate = $request->except(['_token', '_method']);

            // Llamamos a nuestro servicio (Fase 3)
            $kycService->processUpdate($user, $dataToUpdate, $request->ip(), $request->userAgent());

            // CORRECCIÓN AQUÍ: Cambiamos 'frontend.profile' por 'frontend.customer_profile'
            return redirect()->route('frontend.customer_profile')->with('success', __('Datos revalidados correctamente.'));
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return back()->withInput()->with('error', __('common.error_message'));
        }
    }


}
