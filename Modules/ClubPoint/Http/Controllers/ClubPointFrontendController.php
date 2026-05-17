<?php

namespace Modules\ClubPoint\Http\Controllers;

use App\Enums\DoubleApprovalActionTypes;
use App\Enums\HttpStatusCode;
use App\Enums\ModulesName;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Routing\Controller;
use Modules\ClubPoint\Repositories\ClubPointFrontedRepository;
use Modules\Refund\Repositories\RefundRepository;
use Modules\ClubPoint\Repositories\ClubPointRepository;
use Modules\OrderManage\Services\OrderManageService;
use Modules\Refund\Entities\RefundRequest;
use Modules\UserActivityLog\Traits\LogActivity;
use Modules\Wallet\Entities\WalletBalance;
use Modules\GeneralSetting\Entities\ParameterSetting;
use App\Services\DoubleApprovalService;
use App\Services\StaffService;

class ClubPointFrontendController extends Controller
{
    protected $doubleApprovalService;
    protected $orderService;
    protected $clubPointRepository;
    protected $clubPointFrontedRepository;
    protected $ordermanageService;
    protected $refundRepository;
    protected $staffService;

    public function __construct(
        OrderService $orderService,
        ClubPointRepository $clubPointRepository,
        ClubPointFrontedRepository $clubPointFrontedRepository,
        OrderManageService $ordermanageService,
        RefundRepository $refundRepository,
        DoubleApprovalService $doubleApprovalService,
        StaffService $staffService
    )
    {
        $this->orderService = $orderService;
        $this->clubPointRepository = $clubPointRepository;
        $this->clubPointFrontedRepository = $clubPointFrontedRepository;
        $this->doubleApprovalService = $doubleApprovalService;
        $this->ordermanageService = $ordermanageService;
        $this->refundRepository = $refundRepository;
        $this->staffService = $staffService;
    }

    public function index(Request $request){
        $orders = $this->orderService->purchaseHistories('complete',true);
        $wallet_point = $this->clubPointFrontedRepository->getAll();
        return view(theme('pages.clubpoint.index'),compact('orders','wallet_point'));
    }

    public function getModalData(Request $request, string $hash) {
        try {
            // Recuperar los datos de la doble aprobación
            $data = $this->doubleApprovalService->findByHash($hash);

            if(!$data){
                return response()->json([
                    'success' => false,
                    'message' => __('double_approval.error_messages.invalid_code')
                ], 404);
            }

            if($data->status !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => __('double_approval.error_messages.approval_completed')
                ], HttpStatusCode::CONFLICT->value);
            }

            $currentUserId = auth()->user()->id;

            $staff = $this->staffService->findByUserId($currentUserId);
            if(!$staff) {
                return response()->json([
                    'success' => false,
                    'message' => __('common.error_messages.not_found', ['attribute' => __('common.user')])
                ], HttpStatusCode::NOT_FOUND->value);
            }

            // Valida si el usuario es el asignado
            if($data->assigned_approver_id != $staff->id) {
                LogActivity::errorLog(__('double_approval.error_messages.access_forbidden', ['user_id' => $staff->id]));
                return response()->json([
                    'success' => false,
                    'message' => __('double_approval.error_messages.unauthorized')
                ], HttpStatusCode::FORBIDDEN->value);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $data->id,
                    'new_data' => $data->new_data
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('common.unknown_error_message'),
                // 'error' => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        return view('clubpoint::create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'wallet_point' => 'required|numeric|min:1'
        ],
        [
            'wallet_point.required' => 'The wallet point field is required',
            'wallet_point.numeric' => 'The wallet point field must be numeric',
            'wallet_point.min' => 'minimum value must be 1',
        ]);

        try {
            // Verificar si la Doble Aprobación está activa en los parámetros
            $approvalParam = ParameterSetting::where('slug', 'double-approval')->first();

            if ($approvalParam && $approvalParam->is_active && $approvalParam->staff_id) {

                // Llamamos a la función de validación/creación de aprobación
                $isDoubleApprovalCreated = $this->doubleApprovalService->createPendingApproval([
                    'module' => ModulesName::CLUBPOINT->value,
                    'action_type' => DoubleApprovalActionTypes::CLUBPOINT_CONVERT_POINT_TO_WALLET->value,
                    'new_data' => $request->except('_token'),
                    'staff_id' => $approvalParam->staff_id,
                    'notification_url' => 'clubpoint.set-product-point'
                ]);

                if (!$isDoubleApprovalCreated) {
                    LogActivity::errorLog(__('double_approval.error_messages.blocked_due_to_pending_approval'));
                    Toastr::info(__('double_approval.error_messages.blocked_due_to_pending_approval'), __('common.attention'));
                    return redirect()->back();
                }

                if ($request->ajax()) {
                    return response()->json(['status' => true, 'message' => __('clubpoint.approval_requested')], 200);
                }

                LogActivity::successLog(__('double_approval.approval_created_message', [
                    'action_type' => DoubleApprovalActionTypes::CLUBPOINT_CONVERT_POINT_TO_WALLET->label()
                ]));

                Toastr::info(__('clubpoint.approval_requested'), __('common.attention'), ['timeOut' => 5000]);
                return redirect()->route('clubpoint.set-product-point');
            }

            // --- FLUJO ORIGINAL (Si no hay doble aprobación activa) ---
            $this->clubPointRepository->create($request->except("_token"));
            LogActivity::successLog(__('clubpoint.approval_requested'));
            return back();

        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'));
            return back();
        }
    }

    public function point($id){
        $order = $this->ordermanageService->findOrderByID($id);

        if (
            ! $order ||
            $order->point_convert == 1 ||
            $order->customer_id != auth()->user()->id ||
            $order->is_completed != 1
        ) {
            return redirect('clubpoint/earning-points');
        }

        WalletBalance::create([
            'user_id' => $order->customer_id,
            'amount'  => $order->point_value,
            'type'    => 'point',
            'status'  => 1,
        ]);

        $order->update(['point_convert' => 1]);

        return redirect('clubpoint/earning-points');
    }

}
