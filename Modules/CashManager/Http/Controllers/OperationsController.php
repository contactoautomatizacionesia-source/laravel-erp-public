<?php

namespace Modules\CashManager\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\CashManager\Services\CashSessionService;
use Modules\CashManager\Entities\CashBox;
use Modules\CashManager\Entities\CashBoxAssignment;
use Modules\CashManager\Entities\CashSession;
use Modules\CashManager\Entities\CashTransfer;
use Modules\CashManager\Http\Requests\CloseCashSessionRequest;
use Modules\GeneralSetting\Entities\Catalogs\PaymentForm;
use Modules\GeneralSetting\Entities\Catalogs\CashDiscrepancyType;

class OperationsController extends Controller
{
    public function __construct(private CashSessionService $sessionService) {}

    public function index()
    {
        $userId  = auth()->id();
        $session = $this->sessionService->getActiveSession($userId);

        return $session
            ? $this->indexWithSession($session)
            : $this->indexWithoutSession($userId);
    }

    private function indexWithSession(CashSession $session): \Illuminate\View\View
    {
        $box    = $session->assignment->box;
        $locale = app()->getLocale();

        if (in_array($box->type, ['PRINCIPAL', 'VAULT'])) {
            return $this->reviewIndex($session, $box);
        }

        return view('cashmanager::operations.index', [
            'session'          => $session,
            'box'              => $box,
            'denominations'    => $this->sessionService->getDenominationsForBox($box),
            'paymentForms'     => PaymentForm::active()->orderBy('sort_order')->get(),
            'discrepancyTypes' => CashDiscrepancyType::active()->orderBy('sort_order')->get(),
            'history'          => $this->loadAuxiliaryHistory($box, $session->id, $locale),
        ]);
    }

    private function indexWithoutSession(int $userId): \Illuminate\View\View
    {
        $locale         = app()->getLocale();
        $lastAssignment = CashBoxAssignment::with('box')
            ->where('user_id', $userId)
            ->latest('created_at')
            ->first();

        if (!$lastAssignment) {
            return view('cashmanager::operations.no_session', ['history' => collect(), 'box' => null]);
        }

        $box = $lastAssignment->box;

        if ($box->type === 'AUXILIARY') {
            return view('cashmanager::operations.no_session', [
                'history' => $this->loadAuxiliaryHistory($box, '', $locale),
                'box'     => $box,
            ]);
        }

        $history = $box->type === 'PRINCIPAL'
            ? $this->loadPrincipalHistory($box, $locale)
            : $this->loadVaultHistory($box);

        return view('cashmanager::operations.review', [
            'session'           => null,
            'box'               => $box,
            'pendingSessions'   => collect(),
            'receivedTransfers' => collect(),
            'allChildrenClosed' => false,
            'history'           => $history,
        ]);
    }

    private function reviewIndex(CashSession $session, CashBox $box): \Illuminate\View\View
    {
        $locale = app()->getLocale();

        if ($box->type === 'PRINCIPAL') {
            // PRINCIPAL: ve las sesiones AUXILIARY en PENDING_RECEIPT (aún por confirmar)
            // más el historial de transfers ya recibidos (ya confirmados)
            $pendingSessions = CashSession::with([
                'assignment.box',
                'assignment.user',
                'discrepancies.discrepancyType',
                'payments.paymentForm',
            ])
            ->whereHas('assignment.box', fn ($q) => $q->where('parent_id', $box->id))
            ->where('status', 'PENDING_RECEIPT')
            ->get()
            ->map(fn ($cs) => $this->mapAuxiliarySession($cs, $locale));

            $receivedTransfers = CashTransfer::with([
                'originSession.assignment.box',
                'originSession.assignment.user',
                'originSession.discrepancies.discrepancyType',
                'originSession.payments.paymentForm',
            ])
            ->where('destination_box_id', $box->id)
            ->where('status', 'RECEIVED')
            ->get()
            ->map(fn ($t) => $this->mapAuxiliarySession($t->originSession, $locale));

            $allChildrenClosed = !CashBox::where('parent_id', $box->id)
                ->whereIn('status', ['OPEN', 'PENDING_RECEIPT'])
                ->exists();

            $history = $this->loadPrincipalHistory($box, $locale);

            return view('cashmanager::operations.review', [
                'session'           => $session,
                'box'               => $box,
                'pendingSessions'   => $pendingSessions,
                'receivedTransfers' => $receivedTransfers,
                'allChildrenClosed' => $allChildrenClosed,
                'history'           => $history,
            ]);
        }

        // VAULT: ve las sesiones PRINCIPAL en PENDING_RECEIPT
        // Para cada PRINCIPAL muestra el desglose de transfers que recibió de sus AUXILIARY
        $pendingSessions = CashSession::with([
            'assignment.box',
            'assignment.user',
        ])
        ->whereHas('assignment.box', fn ($q) => $q->where('parent_id', $box->id))
        ->where('status', 'PENDING_RECEIPT')
        ->get()
        ->map(function ($cs) use ($locale) {
            $principalBox = $cs->assignment->box;

            // Transfers que llegaron a esta PRINCIPAL desde sus AUXILIARY
            $transfers = CashTransfer::with([
                'originSession.assignment.box',
                'originSession.assignment.user',
                'originSession.discrepancies.discrepancyType',
                'originSession.payments.paymentForm',
            ])
            ->where('destination_box_id', $principalBox->id)
            ->where('status', 'RECEIVED')
            ->get();

            $totalReceived  = $transfers->sum('amount');
            $hasIncidents   = $transfers->contains(
                fn ($t) => $t->originSession?->has_incidents
            );

            return [
                'session_id'      => $cs->id,
                'box_code'        => $principalBox->code,
                'box_name'        => $principalBox->name,
                'operator_name'   => trim($cs->assignment->user->first_name . ' ' . $cs->assignment->user->last_name),
                'closed_at'       => $cs->closed_at,
                'total_received'  => $totalReceived,
                'has_incidents'   => $hasIncidents,
                'breakdown'       => $transfers->map(
                    fn ($t) => $this->mapAuxiliarySession($t->originSession, $locale)
                ),
            ];
        });

        $history = $this->loadVaultHistory($box);

        return view('cashmanager::operations.review', [
            'session'           => $session,
            'box'               => $box,
            'pendingSessions'   => $pendingSessions,
            'receivedTransfers' => collect(),
            'allChildrenClosed' => false,
            'history'           => $history,
        ]);
    }

    private function mapAuxiliarySession(?CashSession $cs, string $locale): array
    {
        if (!$cs) {
            return [];
        }

        return [
            'session_id'         => $cs->id,
            'box_code'           => $cs->assignment->box->code,
            'box_name'           => $cs->assignment->box->name,
            'operator_name'      => trim($cs->assignment->user->first_name . ' ' . $cs->assignment->user->last_name),
            'opened_at'          => $cs->opened_at,
            'closed_at'          => $cs->closed_at,
            'opening_base'       => $cs->opening_base,
            'total_physical'     => $cs->total_physical_counted,
            'total_declared'     => $cs->total_system_expected,
            'discrepancy_amount' => $cs->discrepancy_amount,
            'has_incidents'      => $cs->has_incidents,
            'payments'           => $cs->payments->map(fn ($p) => [
                'name'   => $p->paymentForm?->getTranslation('name', $locale) ?? '—',
                'amount' => $p->total_amount,
            ]),
            'discrepancies'      => $cs->discrepancies->map(fn ($d) => [
                'type'          => $d->discrepancyType?->getTranslation('name', $locale) ?? '—',
                'amount'        => $d->amount,
                'justification' => $d->justification,
                'notes'         => $d->notes,
            ]),
        ];
    }

    /**
     * Historial de sesiones CLOSED de una caja AUXILIARY (o del usuario sobre esa caja).
     * Excluye la sesión activa actual.
     */
    private function loadAuxiliaryHistory(CashBox $box, string $currentSessionId, string $locale): \Illuminate\Support\Collection
    {
        return CashSession::with([
            'assignment.user',
            'payments.paymentForm',
            'denominations.denomination',
            'discrepancies.discrepancyType',
        ])
        ->whereHas('assignment', fn ($q) => $q->where('cash_box_id', $box->id))
        ->where('status', 'CLOSED')
        ->where('id', '!=', $currentSessionId)
        ->orderBy('closed_at', 'desc')
        ->limit(20)
        ->get()
        ->map(fn ($cs) => array_merge(
            $this->mapAuxiliarySession($cs, $locale),
            [
                'denominations' => $cs->denominations->map(fn ($d) => [
                    'label'    => '$ ' . number_format($d->denomination->value, 0, ',', '.'),
                    'type'     => $d->denomination->type,
                    'quantity' => $d->quantity,
                    'subtotal' => $d->subtotal,
                ]),
            ]
        ));
    }

    /**
     * Historial de turnos anteriores de una caja PRINCIPAL:
     * sesiones CLOSED de esa PRINCIPAL, con los transfers de AUXILIARY que recibió en cada turno.
     */
    private function loadPrincipalHistory(CashBox $box, string $locale): \Illuminate\Support\Collection
    {
        return CashSession::with(['assignment.user'])
            ->whereHas('assignment', fn ($q) => $q->where('cash_box_id', $box->id))
            ->where('status', 'CLOSED')
            ->orderBy('closed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($cs) use ($locale) {
                $transfers = CashTransfer::with([
                    'originSession.assignment.box',
                    'originSession.assignment.user',
                    'originSession.payments.paymentForm',
                    'originSession.denominations.denomination',
                    'originSession.discrepancies.discrepancyType',
                ])
                ->where('destination_box_id', $cs->assignment->cash_box_id)
                ->whereHas('originSession', fn ($q) => $q->whereBetween('closed_at', [
                    $cs->opened_at,
                    $cs->closed_at ?? now(),
                ]))
                ->get();

                return [
                    'session_id'     => $cs->id,
                    'operator_name'  => trim($cs->assignment->user->first_name . ' ' . $cs->assignment->user->last_name),
                    'opened_at'      => $cs->opened_at,
                    'closed_at'      => $cs->closed_at,
                    'has_incidents'  => $cs->has_incidents,
                    'reviewer_notes' => $cs->reviewer_notes,
                    'total_received' => $transfers->sum('amount'),
                    'breakdown'      => $transfers->map(
                        fn ($t) => array_merge(
                            $this->mapAuxiliarySession($t->originSession, $locale),
                            [
                                'denominations' => $t->originSession?->denominations->map(fn ($d) => [
                                    'label'    => '$ ' . number_format($d->denomination->value, 0, ',', '.'),
                                    'type'     => $d->denomination->type,
                                    'quantity' => $d->quantity,
                                    'subtotal' => $d->subtotal,
                                ]) ?? collect(),
                            ]
                        )
                    ),
                ];
            });
    }

    /**
     * Historial del VAULT: cada transfer recibido de una PRINCIPAL es un ítem.
     * El VAULT no cierra sesión (es el nivel más alto), así que el historial
     * se construye directamente desde los transfers recibidos.
     */
    private function loadVaultHistory(CashBox $box): \Illuminate\Support\Collection
    {
        return CashTransfer::with([
            'originSession.assignment.box',
            'originSession.assignment.user',
            'originSession.transfers.originSession.assignment.box',
            'originSession.transfers.originSession.assignment.user',
            'originSession.transfers.originSession.payments.paymentForm',
            'originSession.transfers.originSession.denominations.denomination',
            'originSession.transfers.originSession.discrepancies.discrepancyType',
        ])
        ->where('destination_box_id', $box->id)
        ->where('status', 'RECEIVED')
        ->orderBy('received_at', 'desc')
        ->limit(20)
        ->get()
        ->map(function ($t) {
            $principalSession = $t->originSession;
            $principalBox     = $principalSession?->assignment?->box;

            // Transfers que la PRINCIPAL recibió de sus AUXILIARY (el desglose real)
            $auxiliaryTransfers = $principalSession?->transfers ?? collect();

            $hasIncidents = $auxiliaryTransfers->contains(
                fn ($at) => $at->originSession?->has_incidents
            ) || $principalSession?->has_incidents;

            return [
                'session_id'     => $principalSession?->id ?? $t->id,
                'box_code'       => $principalBox?->code ?? '—',
                'box_name'       => $principalBox?->name ?? '—',
                'operator_name'  => $principalSession
                    ? trim($principalSession->assignment->user->first_name . ' ' . $principalSession->assignment->user->last_name)
                    : '—',
                'opened_at'      => $principalSession?->opened_at,
                'closed_at'      => $t->received_at,
                'total_received' => $t->amount,
                'has_incidents'  => $hasIncidents,
                'reviewer_notes' => $principalSession?->reviewer_notes,
                'principals'     => $auxiliaryTransfers->map(fn ($at) => [
                    'box_code'      => $at->originSession?->assignment->box->code ?? '—',
                    'box_name'      => $at->originSession?->assignment->box->name ?? '—',
                    'operator_name' => $at->originSession
                        ? trim($at->originSession->assignment->user->first_name . ' ' . $at->originSession->assignment->user->last_name)
                        : '—',
                    'amount'        => $at->amount,
                    'closed_at'     => $at->originSession?->closed_at,
                ]),
            ];
        });
    }

    public function close(CloseCashSessionRequest $request)
    {
        $session = CashSession::with('assignment')->findOrFail($request->session_id);

        [$errorMessage, $statusCode] = $this->validateCloseGuards($session);
        if ($errorMessage) {
            return response()->json(['message' => $errorMessage], $statusCode);
        }

        try {
            $updated = $this->sessionService->closeSession(
                session: $session,
                denominations: $request->denominations,
                payments: $request->payments,
                discrepancyTypeId: $request->discrepancy_type_id,
                justification: $request->justification,
                notes: $request->notes
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message'       => __('cashmanager::cash_manager.session_closed_success'),
            'status'        => $updated->status,
            'discrepancy'   => $updated->discrepancy_amount,
            'has_incidents' => $updated->has_incidents,
        ]);
    }

    private function validateCloseGuards(CashSession $session): array
    {
        if ((int) $session->assignment->user_id !== auth()->id()) {
            return [__('cashmanager::cash_manager.error_not_your_session'), 403];
        }

        if ($session->status !== 'OPEN') {
            return [__('cashmanager::cash_manager.error_session_not_open'), 422];
        }

        return [null, 200];
    }
}
