<?php

namespace Modules\ClubPoint\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\OrderManage\Services\OrderManageService;
use Modules\Seller\Repositories\ProductRepository;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Modules\ClubPoint\Http\Requests\ClubPointStoreRequest;
use Modules\ClubPoint\Repositories\ClubPointFrontedRepository;
use Modules\ClubPoint\Repositories\ClubPointRepository;
use Modules\OrderManage\Repositories\DeliveryProcessRepository;
use Modules\UserActivityLog\Traits\LogActivity;
use Yajra\DataTables\Facades\DataTables;
use Modules\GeneralSetting\Services\ParameterSettingService;
use App\Services\DoubleApprovalService;
use Modules\GeneralSetting\Entities\ParameterSetting;
use App\Exceptions\CustomHandledException;
use App\Enums\DoubleApprovalActionTypes;
use App\Enums\ModulesName;

class ClubPointController extends Controller
{
    protected $doubleApprovalService;
    protected $productRepository;
    protected $ordermanageService;
    protected $clubPointRepository;
    protected $clubPointFrontedRepository;
    protected $deliveryProcessRepo;
    protected $parameterService;

    public function __construct(ProductRepository $productRepository , OrderManageService $ordermanageService,ClubPointRepository $clubPointRepository, DeliveryProcessRepository $deliveryProcessRepo, ParameterSettingService $parameterService, ClubPointFrontedRepository $clubPointFrontedRepository, DoubleApprovalService $doubleApprovalService)
    {
        $this->middleware('maintenance_mode');
        $this->productRepository = $productRepository;
        $this->ordermanageService = $ordermanageService;
        $this->clubPointRepository = $clubPointRepository;
        $this->doubleApprovalService = $doubleApprovalService;
        $this->deliveryProcessRepo = $deliveryProcessRepo;
        $this->clubPointFrontedRepository = $clubPointFrontedRepository;
        $this->parameterService = $parameterService;
    }

    public function index($hash = null)
    {
        try {
            $wallet_point = $this->clubPointFrontedRepository->getAll();
            $parameters = $this->parameterService->getAll();

            // Buscamos el parámetro específico
            $doubleApproval = $parameters->firstWhere('slug', 'double-approval');

            // Definimos la variable booleana. Si no existe el parámetro, por defecto es false.
            $isDoubleApprovalActive = $doubleApproval ? $doubleApproval->is_active : false;

            // Verifica si existe un hash de revisión
            $modalCode = $hash;

            // Enviamos la variable específica a la vista
            return view(('clubpoint::index'), compact('wallet_point', 'isDoubleApprovalActive', 'modalCode'));
        } catch (Exception $e) {
            throw new CustomHandledException($e, 'club_point', ['operation' => __('clubpoint.listing_error')]);
        }
    }

    public function create()
    {
        return view('clubpoint::create');
    }

    public function getData()
    {
        $products = $this->productRepository->getPointProduct();
        return DataTables::of($products)
            ->addIndexColumn()
            ->editColumn('product_name', function ($products) {
                return $products->product_name ?? '';
            })
            ->addColumn('owner', function ($products) {
                return $products->seller->name ?? '';
            })
            ->addColumn('price', function ($products) {
                return single_price($products->skus->first()->selling_price ?? 0);
            })
            ->addColumn('price_based_point', function ($products) {
                return $products->price_from_points ? __('common.yes') : __('common.no');
            })
            ->addColumn('point', function ($products) {
                if($products->club_point_type=='multiple'){
                    return getNumberTranslate($products->club_point ?? 0) ;
                }else{
                    return getNumberTranslate($products->club_point ?? 0) ;
                }
            })
            ->addColumn('action', function ($products) {
                return view('clubpoint::components._projectPointAction', compact('products'));
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function store(ClubPointStoreRequest $request)
    {
        try {
          $data =  $this->clubPointRepository->save($request->except('_token'));
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json([
                'status'    =>  false,
                'message'   =>  $e
            ],503);
        }
    }

    public function storeSetPoint(Request $request)
    {
        $request->validate([
            'set' => 'required|numeric|min:1'
        ],
        [
            'set.required' => 'The set point field is required',
            'set.numeric' => 'The set point field must be numeric',
            'set.min' => 'minimum value must be 1',
        ]);
        
        try {
            if ($approvalParam = $this->getActiveApprovalParam()) {
                $created = $this->doubleApprovalService->createPendingApproval([
                    'module' => ModulesName::CLUBPOINT->value,
                    'action_type' => DoubleApprovalActionTypes::CLUBPOINT_SET_MASSIVE_POINTS->value,
                    'new_data' => ['set' => $request->set],
                    'staff_id' => $approvalParam->staff_id,
                    'notification_url' => 'clubpoint.set-product-point'
                ]);

                if (!$created) {
                    $message = __('common.pending_request_exists');
                    
                    if ($request->ajax()) {
                        return response()->json(['status' => false, 'message' => $message], 400);
                    }
                    
                    Toastr::warning($message, __('common.warning'));
                    return redirect()->back();
                }

                if ($request->ajax()) {
                    return response()->json(['status' => true, 'message' => __('clubpoint.approval_requested')], 200);
                }

                Toastr::info(__('clubpoint.approval_requested'), __('common.attention'), ['timeOut' => 5000]);
                return redirect()->route('clubpoint.set-product-point');
            }

            // --- FLUJO ORIGINAL (Si no hay doble aprobación activa) ---
            $data =  $this->clubPointRepository->storeSetPoint($request['set']);
        }catch (Exception $e) {
              LogActivity::errorLog($e->getMessage());
              return response()->json([
                  'status'    =>  false,
                  'message'   =>  $e
              ],503);
        }
    }

    public function show($id)
    {
        return view('clubpoint::show');
    }

    public function edit($id)
    {
        try{
            $clubpoint = $this->clubPointRepository->findClubPointProduct($id);
            return view('clubpoint::_edit_multiple',compact('clubpoint'));
        }catch(\Exception $e){
            LogActivity::errorLog($e->getMessage());
            return response()->json([
                'status'    =>  false,
                'message'   =>  $e
            ],503);
        }
    }

    public function update(Request $request, $id){
        $request->validate([
            'multiple' => 'required',
        ]);
        try{
            $this->clubPointRepository->updateclubpoint($request->except('_token'), $id);
            Toastr::success(__('common.updated_successfully'), __('common.success'));
            LogActivity::successLog('Point Updated Successfully.');
            return redirect()->route('clubpoint.set-product-point');
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }

    public function userPoint()
    {
        return view('clubpoint::user.userpoint');
    }

    public function total_sales_get_data()
    {
            $order = $this->ordermanageService->totalSalesList();
            return DataTables::of($order)
                ->addIndexColumn()
                ->addColumn('date', function ($order) {
                    return dateConvert($order->created_at);
                })
                ->editColumn('order_number', function ($order) {
                    return getNumberTranslate($order->order_number);
                })
                ->addColumn('name', function ($order) {
                    return getNumberTranslate($order->customer_id) ? getNumberTranslate(@$order->customer->name) : getNumberTranslate(@$order->guest_info->shipping_name);
                })
                ->addColumn('total_qty', function ($order) {
                    $count = 0;
                    foreach($order->packages as $key => $package){
                        foreach($package->products as $product){
                            $count  += $product->qty;
                        }
                    }
                    return getNumberTranslate($count);
                })
                ->addColumn('total_amount', function ($order) {
                    return single_price($order->grand_total);
                })
                ->addColumn('point', function ($order) {
                    return getNumberTranslate($order->club_point);
                })
                ->addColumn('order_status', function ($order) {
                    return view('ordermanage::order_manage.components._order_status_td', compact('order'));
                })
                ->addColumn('action', function ($order) {
                    return view('clubpoint::components._orderPointAction', compact('order'));
                })
                ->rawColumns(['order_confirm','order_status', 'action'])
                ->make(true);
    }

    public function show_details($id)
    {
        $data['order'] = $this->ordermanageService->findOrderByID($id);
        $deliveryProcessRepo = new DeliveryProcessRepository();
        $data['processes'] = $deliveryProcessRepo->getAll();
        return view('clubpoint::user._details', $data);
    }

    public function customer(){
        return view('clubpoint::customer.index');
    }

    public function get_data()
    {
        if (isset($_GET['table'])) {
            $table = $_GET['table'];
            if ($table == 'pending') {
                $order = $this->ordermanageService->totalSalesList()->where('is_confirmed', 0)->where('is_cancelled', 0);
            } elseif ($table == 'confirmed') {
                $order = $this->ordermanageService->totalSalesList()->where('is_confirmed', 1)->where('is_cancelled', 0)->where('is_completed', 0);
            } elseif ($table == 'completed') {
                $order = $this->ordermanageService->totalSalesList()->where('is_completed', 1)->where('is_cancelled',0);
            } elseif ($table == 'pending_payment') {
                $order = $this->ordermanageService->totalSalesList()->where('is_paid', 0)->where('is_cancelled', 0);
            } elseif ($table == 'canceled') {
                $order = $this->ordermanageService->totalSalesList()->where('is_cancelled', 1);
            } elseif ($table == 'inhouse') {
                $order = $this->ordermanageService->totalSalesList()->where('order_type', 'inhouse_order');
            } elseif ($table == 'all') {
                $order = $this->ordermanageService->totalSalesList();
            } else {
                $order = [];
            }
            return DataTables::of($order)
                ->addIndexColumn()
                ->addColumn('date', function ($order) {
                    return dateConvert($order->created_at);
                })
                ->addColumn('point', function ($order) {
                    return getNumberTranslate($order->customer->club_point);
                })
                ->addColumn('is_paid', function ($order) {
                    return view('clubpoint::components._is_paid_td', compact('order'));
                })
                ->addColumn('action', function ($order) use($table) {
                    return view('clubpoint::components._action_td', compact('order', 'table'));
                })
                ->rawColumns(['order_confirm','order_status', 'is_paid', 'action'])
                ->make(true);
        } else {
            return [];
        }
    }

    // AGREGAR ESTE MÉTODO PARA MANEJAR EL TERCER FORMULARIO
    public function saveWalletPoint(Request $request)
    {
        $request->validate([
            'wallet_point' => 'required|numeric|min:0'
        ]);

        try {
            // 1. Verificar Doble Aprobación
            if ($approvalParam = $this->getActiveApprovalParam()) {
                // Intentamos crear la solicitud
                $created = $this->doubleApprovalService->createPendingApproval([
                    'module'      => ModulesName::CLUBPOINT->value,
                    'action_type' => DoubleApprovalActionTypes::CLUBPOINT_CONVERT_POINT_TO_WALLET->value,
                    'new_data'    => ['wallet_point' => $request->wallet_point],
                    'staff_id'    => $approvalParam->staff_id,
                    'notification_url' => 'clubpoint.set-product-point'
                ]);

                // --- AQUÍ ESTÁ EL AJUSTE ---
                if (!$created) {
                    Toastr::warning(__('common.pending_request_exists'), __('common.warning'));
                    return redirect()->back();
                }
                // ---------------------------

                Toastr::info(__('clubpoint.approval_requested'), __('common.attention'));
                return redirect()->route('clubpoint.set-product-point');
            }

            // 2. Flujo Normal
            $this->clubPointRepository->create($request->all());
            
            Toastr::success(__('common.updated_successfully'), __('common.success'));
            return redirect()->route('clubpoint.set-product-point');

        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }

    private function getActiveApprovalParam(): ?ParameterSetting
    {
        $approvalParam = ParameterSetting::where('slug', 'double-approval')->first();
        if ($approvalParam && $approvalParam->is_active && $approvalParam->staff_id) {
            return $approvalParam;
        }
        return null;
    }

    public function history($id)
    {
        try{
            $clubpoint = $this->clubPointRepository->findClubPointProduct($id);
            return view('clubpoint::_history',compact('clubpoint'));
        }catch(\Exception $e){
            LogActivity::errorLog($e->getMessage());
            return response()->json([
                'status'    =>  false,
                'message'   =>  $e
            ],503);
        }
    }

    public function getHistoryChartData(Request $request)
    {
        $productId = $request->get('product_id');

        if (!$productId) {
            return response()->json(['points' => [], 'price' => []]);
        }

        $pointRows = $this->clubPointRepository->getHistoryByProduct($productId, 'points');
        $priceRows = $this->clubPointRepository->getHistoryByProduct($productId, 'price');

        $points = $pointRows->map(function ($row) {
            return [
                'date'      => $row->created_at->toDateString(),
                'new_value' => $row->new_points,
            ];
        });

        $price = $priceRows->map(function ($row) {
            return [
                'date'      => $row->created_at->toDateString(),
                'new_value' => $row->new_price,
            ];
        });

        return response()->json(['points' => $points, 'price' => $price]);
    }

    public function getHistoryData(Request $request)
    {
        $type      = $request->get('type');
        $productId = $request->get('product_id');

        if (!$type || !$productId) {
            return DataTables::of([])->make(true);
        }

        $history = $this->clubPointRepository->getHistoryByProduct($productId, $type);

        $formatPoints = function ($value) {
            if ($value === null) {
                return '-';
            }
            $float = (float) $value;
            $formatted = $float == floor($float) ? number_format($float, 0) : rtrim(number_format($float, 4), '0');
            return getNumberTranslate($formatted);
        };

        return DataTables::of($history)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return dateConvert($row->created_at);
            })
            ->addColumn('previous_value', function ($row) use ($type, $formatPoints) {
                if ($type === 'points') {
                    return $formatPoints($row->previous_points);
                }
                return $row->previous_price !== null ? single_price($row->previous_price) : '-';
            })
            ->addColumn('new_value', function ($row) use ($type, $formatPoints) {
                if ($type === 'points') {
                    return $formatPoints($row->new_points);
                }
                return $row->new_price !== null ? single_price($row->new_price) : '-';
            })
            ->make(true);
    }


}
