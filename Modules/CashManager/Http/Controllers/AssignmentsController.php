<?php

namespace Modules\CashManager\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use Modules\CashManager\Entities\CashBox;
use Modules\CashManager\Entities\CashBoxAssignment;
use Modules\CashManager\Entities\CashSession;
use Modules\CashManager\Http\Requests\AssignCashBoxRequest;
use Modules\CashManager\Services\CashSessionService;

class AssignmentsController extends Controller
{
    public function __construct(private CashSessionService $sessionService) {}

    public function index()
    {
        $operatorRoleIds = $this->getOperatorRoleIds();

        $cashiers = User::whereIn('role_id', $operatorRoleIds)
            ->where('is_active', 1)
            ->orderBy('first_name')
            ->get();

        $boxes = CashBox::with([
            'assignments' => fn ($q) => $q->whereNull('revoked_at')->with('user'),
            'costCenter',
            'parentBox',
            'childBoxes',
        ])
        ->orderBy('type')
        ->orderBy('name')
        ->get()
        ->map(function ($box) {
            $active  = $box->assignments->first();
            $session = $active
                ? CashSession::where('assignment_id', $active->id)
                    ->whereIn('status', ['OPEN', 'PENDING_RECEIPT'])
                    ->first()
                : null;

            // Para PRINCIPAL: verificar si todas las cajas hijas están cerradas
            $allChildrenClosed = false;
            if ($box->type === 'PRINCIPAL') {
                $allChildrenClosed = !CashBox::where('parent_id', $box->id)
                    ->whereIn('status', ['OPEN', 'PENDING_RECEIPT'])
                    ->exists();
            }

            return [
                'id'                 => $box->id,
                'code'               => $box->code,
                'name'               => $box->name,
                'type'               => $box->type,
                'type_label'         => __('cashmanager::cash_manager.type_' . strtolower($box->type)),
                'status'             => $box->status,
                'base'               => $box->base_amount,
                'cc_name'            => $box->costCenter?->name ?? '—',
                'parent_id'          => $box->parent_id,
                'parent_name'        => $box->parentBox?->name,
                'assignment_id'      => $active?->id,
                'session_id'         => $session?->id,
                'session_status'     => $session?->status,
                'has_incidents'      => $session?->has_incidents ?? false,
                'all_children_closed'=> $allChildrenClosed,
                'assigned_user'      => $active ? [
                    'id'    => $active->user->id,
                    'name'  => trim($active->user->first_name . ' ' . $active->user->last_name),
                    'photo' => $active->user->photo,
                    'since' => $active->assigned_at?->diffForHumans(),
                ] : null,
            ];
        });

        return view('cashmanager::assignments.index', compact('boxes', 'cashiers'));
    }

    public function store(AssignCashBoxRequest $request)
    {
        $box    = CashBox::findOrFail($request->cash_box_id);
        $userId = $request->user_id;

        $error = $this->validateAssignment($box, $userId);
        if ($error) {
            return response()->json(['message' => $error], 422);
        }

        DB::transaction(function () use ($box, $userId) {
            $assignment = CashBoxAssignment::create([
                'cash_box_id'    => $box->id,
                'user_id'        => $userId,
                'assigned_by_id' => auth()->id(),
                'assigned_at'    => now(),
            ]);

            $box->update(['status' => 'OPEN']);

            $this->sessionService->openSession($assignment, $box->base_amount);
        });

        return response()->json([
            'message' => __('cashmanager::cash_manager.assignment_success'),
        ]);
    }

    /**
     * Confirma la recepción del dinero de una caja AUXILIARY o PRINCIPAL.
     * Solo puede hacerlo el responsable de la caja superior (parent_id).
     */
    public function confirmReceipt(Request $request, string $sessionId)
    {
        $session = CashSession::with('assignment.box.parentBox')->findOrFail($sessionId);

        if ($session->status !== 'PENDING_RECEIPT') {
            return response()->json([
                'message' => __('cashmanager::cash_manager.error_session_not_pending'),
            ], 422);
        }

        $hasIncidents  = (bool) $request->input('has_incidents', false);
        $reviewerNotes = $request->input('reviewer_notes');

        try {
            $this->sessionService->confirmReceipt(
                session: $session,
                reviewerId: auth()->id(),
                hasIncidents: $hasIncidents,
                reviewerNotes: $reviewerNotes
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->json([
            'message' => __('cashmanager::cash_manager.receipt_confirmed_success'),
        ]);
    }

    /**
     * Envía el reporte de una caja PRINCIPAL al VAULT.
     * Solo disponible cuando todas las AUXILIARY hijas están cerradas.
     */
    public function submitToParent(Request $request, string $boxId)
    {
        $box = CashBox::findOrFail($boxId);

        if ($box->type !== 'PRINCIPAL') {
            return response()->json([
                'message' => __('cashmanager::cash_manager.error_only_principal_can_submit'),
            ], 422);
        }

        try {
            $this->sessionService->submitToParent($box, auth()->id());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => __('cashmanager::cash_manager.submitted_to_vault_success'),
        ]);
    }

    public function revoke(string $id)
    {
        $assignment    = CashBoxAssignment::findOrFail($id);
        $activeSession = $assignment->sessions()
            ->whereIn('status', ['OPEN', 'PENDING_RECEIPT'])
            ->first();

        if ($activeSession?->status === 'PENDING_RECEIPT') {
            return response()->json(['message' => __('cashmanager::cash_manager.error_revoke_pending')], 422);
        }

        DB::transaction(function () use ($assignment, $activeSession) {
            $this->closeActiveSessionIfNeeded($activeSession);
            $assignment->update(['revoked_at' => now()]);
            CashBox::where('id', $assignment->cash_box_id)->update(['status' => 'AVAILABLE']);
        });

        return response()->json(['message' => __('cashmanager::cash_manager.revoke_success')]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    private function validateAssignment(CashBox $box, int $userId): ?string
    {
        $checks = [
            CashBoxAssignment::where('cash_box_id', $box->id)->whereNull('revoked_at')->exists()
                => 'error_box_already_assigned',
            CashBoxAssignment::where('user_id', $userId)->whereNull('revoked_at')->exists()
                => 'error_user_already_assigned',
            $box->status !== 'AVAILABLE'
                => 'error_box_not_available',
        ];

        foreach ($checks as $condition => $key) {
            if ($condition) {
                return __("cashmanager::cash_manager.{$key}");
            }
        }

        return null;
    }

    private function closeActiveSessionIfNeeded(?CashSession $activeSession): void
    {
        if ($activeSession) {
            $activeSession->update([
                'status'    => 'CLOSED',
                'closed_at' => now(),
            ]);
        }
    }

    private function getOperatorRoleIds(): array
    {
        $setting = DB::table('cash_manager_settings')
            ->where('key', 'operator_role_ids')
            ->value('value');

        return $setting ? json_decode($setting, true) : [];
    }
}
