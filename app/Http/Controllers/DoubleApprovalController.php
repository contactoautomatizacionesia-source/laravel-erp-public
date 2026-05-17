<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DoubleApprovalService;
use Brian2694\Toastr\Facades\Toastr;
use App\Exceptions\CustomHandledException;
use Yajra\DataTables\Facades\DataTables;
use Modules\UserActivityLog\Traits\LogActivity;

class DoubleApprovalController extends Controller
{
    protected $doubleApprovalservice;

    public function __construct(DoubleApprovalService $doubleApprovalservice)
    {
        $this->doubleApprovalservice = $doubleApprovalservice;
    }

    /**
     * Muestra el listado de aprobaciones pendientes para el usuario actual
     */
    public function index()
    {
        return view('backEnd.approvals.review_modal_content');
    }

    /**
     * Muestra el modal de revisión con la comparativa de datos
     */
    public function showReviewModal()
    {
        try {
            $currentUserId = auth()->user()->id;
            $availablePendingApprovals = collect();

            // Status states
            $statusPending = 0;

            $pendingApprovals = $this->doubleApprovalservice->findByAssignedApproverId($currentUserId);

            $availablePendingApprovals = $pendingApprovals->filter(function ($pendingApproval) use ($statusPending, $currentUserId) {
                return $pendingApproval->status === $statusPending && $pendingApproval->assigned_approver_id === $currentUserId;
            });

            return DataTables::of($availablePendingApprovals)
                ->addColumn('module', function($pendingApproval){
                    return $pendingApproval->translatedModule();
                })
                ->addColumn('new_data', function($pendingApproval){
                    $label = '-';

                    foreach($pendingApproval->new_data as $key => $value){
                        if ($key === 'wallet_point') {
                            $label = __('product.Convert Point To Wallet') . ': ' . single_price($value);
                        }
                    }

                    return $label;
                })
                ->addColumn('status', function($pendingApproval){
                    return view('backEnd.approvals._status_td');
                })
                ->addColumn('actions', function($pendingApproval){
                    return view('backEnd.approvals._action_td', compact('pendingApproval'));
                })
                ->rawColumns(['module', 'new_data', 'status', 'actions'])
                ->toJson();
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 500);
        }
    }

    /**
     * Procesa la decisión (Aprobar/Rechazar)
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'rejection_reason' => ['nullable', 'string', 'max:500', 'regex:/^(?!.*\b(SELECT|INSERT|UPDATE|DELETE|DROP|TRUNCATE|ALTER|CREATE|EXEC|UNION|CAST|CONVERT|DECLARE|FETCH|CURSOR|KILL|BACKUP|RESTORE|GRANT|REVOKE|XP_)\b)/i'],
            'status' => 'required|integer|in:1,2', // 1: Aprobar, 2: Rechazar
        ]);

        try {
            $currentUserId = auth()->user()->id;

            // El servicio se encarga de ejecutar la lógica final según el action_type
            $this->doubleApprovalservice->processApproval($request->id, $request->status, $currentUserId, $request->rejection_reason);

            // Centralizar el Log de Actividad (Se ejecuta una sola vez para ambos casos)
            $logMessage = ($request->status == 1)
                ? __('double_approval.approved_message')
                : __('double_approval.rejected_message');

            LogActivity::successLog($logMessage);

            // Respuesta adaptativa
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => __('common.operation_successful')
                ], 200);
            }

            Toastr::success(__('common.operation_successful'), __('common.success'));
            return redirect()->back();

        } catch (\Exception $e) {
            throw new CustomHandledException($e, __('common.unknown_error_message'));
        }
    }
}
