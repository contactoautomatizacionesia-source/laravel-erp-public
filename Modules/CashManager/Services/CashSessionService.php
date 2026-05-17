<?php

namespace Modules\CashManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CashManager\Entities\CashBox;
use Modules\CashManager\Entities\CashBoxAssignment;
use Modules\CashManager\Entities\CashSession;
use Modules\CashManager\Entities\CashSessionDenomination;
use Modules\CashManager\Entities\CashSessionPayment;
use Modules\CashManager\Entities\CashDiscrepancy;
use Modules\CashManager\Entities\CashTransfer;
use Modules\CashManager\Entities\CatDenomination;
use Modules\GeneralSetting\Entities\Catalogs\CashDiscrepancyType;

class CashSessionService
{
    /**
     * Abre una nueva sesión de caja al momento de la asignación.
     * Se llama dentro de una transacción existente desde AssignmentsController.
     */
    public function openSession(CashBoxAssignment $assignment, float $openingBase): CashSession
    {
        return CashSession::create([
            'assignment_id'          => $assignment->id,
            'opened_at'              => now(),
            'opening_base'           => $openingBase,
            'total_system_expected'  => 0,
            'total_physical_counted' => 0,
            'discrepancy_amount'     => 0,
            'status'                 => 'OPEN',
        ]);
    }

    /**
     * Retorna la sesión activa (OPEN o PENDING_RECEIPT) del usuario autenticado.
     * Incluye todas las relaciones necesarias para la vista de operaciones.
     */
    public function getActiveSession(int $userId): ?CashSession
    {
        $assignment = CashBoxAssignment::with('box.costCenter')
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->first();

        if (!$assignment) {
            return null;
        }

        return CashSession::with([
            'assignment.box.costCenter',
            'assignment.box.parentBox',
            'payments.paymentForm',
            'denominations.denomination',
            'discrepancies.discrepancyType',
        ])
        ->where('assignment_id', $assignment->id)
        ->whereIn('status', ['OPEN', 'PENDING_RECEIPT'])
        ->first();
    }

    /**
     * Carga las denominaciones activas asociadas al país del centro de costo.
     */
    public function getDenominationsForBox(CashBox $box): \Illuminate\Support\Collection
    {
        $countryId = $this->resolveCountryForBox($box);

        return CatDenomination::where('country_id', $countryId)
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('value', 'desc')
            ->get();
    }

    /**
     * Ejecuta el cierre completo de una sesión (AUXILIARY).
     * Valida integridad, persiste denominaciones, pagos y discrepancia si existe.
     * Transición: OPEN → PENDING_RECEIPT
     *
     * @param  CashSession  $session
     * @param  array        $denominations     [['denomination_id'=>uuid, 'quantity'=>int], ...]
     * @param  array        $payments          [['payment_form_id'=>int, 'total_amount'=>float, ...], ...]
     * @param  int|null     $discrepancyTypeId ID de system_catalogs (cash_discrepancy_type)
     * @param  string|null  $justification     Razón breve (obligatoria si hay discrepancia)
     * @param  string|null  $notes             Nota libre (obligatoria si type.code = 'other')
     */
    public function closeSession(
        CashSession $session,
        array $denominations,
        array $payments,
        ?int $discrepancyTypeId,
        ?string $justification,
        ?string $notes = null
    ): CashSession {
        return DB::transaction(function () use ($session, $denominations, $payments, $discrepancyTypeId, $justification, $notes) {

            $totalPhysical = collect($denominations)->sum(
                fn ($d) => $d['quantity'] * $this->getDenominationValue($d['denomination_id'])
            );
            $totalDeclared = collect($payments)->sum('total_amount');
            $discrepancy   = ($totalPhysical - $session->opening_base) - $totalDeclared;

            $this->validateDiscrepancy($discrepancy, $discrepancyTypeId, $justification, $notes);
            $this->persistDenominations($session, $denominations);
            $this->persistPayments($session, $payments);

            $hasIncidents = abs($discrepancy) > 0;
            if ($hasIncidents) {
                CashDiscrepancy::create([
                    'session_id'          => $session->id,
                    'discrepancy_type_id' => $discrepancyTypeId,
                    'type'                => $discrepancy < 0 ? 'SHORTAGE' : 'SURPLUS',
                    'amount'              => abs($discrepancy),
                    'justification'       => $justification,
                    'notes'               => $notes,
                    'authorized_by'       => auth()->id(),
                ]);
            }

            $session->update([
                'total_physical_counted' => $totalPhysical,
                'total_system_expected'  => $totalDeclared,
                'discrepancy_amount'     => $discrepancy,
                'has_incidents'          => $hasIncidents,
                'closed_at'              => now(),
                'status'                 => 'PENDING_RECEIPT',
            ]);

            return $session->fresh();
        });
    }

    private function validateDiscrepancy(float $discrepancy, ?int $typeId, ?string $justification, ?string $notes): void
    {
        if (abs($discrepancy) === 0.0) {
            return;
        }

        if (empty($justification)) {
            throw new \InvalidArgumentException(__('cashmanager::cash_manager.error_justification_required'));
        }

        if (!$typeId) {
            throw new \InvalidArgumentException(__('cashmanager::cash_manager.error_discrepancy_type_required'));
        }

        $typeRecord = CashDiscrepancyType::find($typeId);
        if ($typeRecord && $typeRecord->code === CashDiscrepancyType::OTHER_CODE && empty($notes)) {
            throw new \InvalidArgumentException(__('cashmanager::cash_manager.notes_required_for_other'));
        }
    }

    private function persistDenominations(CashSession $session, array $denominations): void
    {
        $session->denominations()->delete();
        foreach ($denominations as $d) {
            $value = $this->getDenominationValue($d['denomination_id']);
            CashSessionDenomination::create([
                'session_id'      => $session->id,
                'denomination_id' => $d['denomination_id'],
                'quantity'        => $d['quantity'],
                'subtotal'        => $d['quantity'] * $value,
            ]);
        }
    }

    private function persistPayments(CashSession $session, array $payments): void
    {
        $session->payments()->delete();
        foreach ($payments as $p) {
            CashSessionPayment::create([
                'session_id'        => $session->id,
                'payment_form_id'   => $p['payment_form_id'],
                'total_amount'      => $p['total_amount'],
                'transaction_count' => $p['transaction_count'] ?? 0,
                'reference_data'    => isset($p['reference_data']) ? ['note' => $p['reference_data']] : null,
            ]);
        }
    }

    /**
     * Confirma la recepción física del dinero por parte del responsable de la caja superior.
     *
     * Validaciones:
     *   - La sesión debe estar en PENDING_RECEIPT.
     *   - El usuario autenticado debe tener asignación activa sobre la caja parent_id.
     *
     * Transición: PENDING_RECEIPT → CLOSED. La caja vuelve a AVAILABLE.
     *
     * @param  CashSession  $session
     * @param  int          $reviewerId     auth()->id() del revisor
     * @param  bool         $hasIncidents   el revisor encontró novedades adicionales
     * @param  string|null  $reviewerNotes  notas del revisor
     *
     * @throws \InvalidArgumentException si el revisor no tiene autoridad sobre la caja superior
     */
    public function confirmReceipt(
        CashSession $session,
        int $reviewerId,
        bool $hasIncidents = false,
        ?string $reviewerNotes = null
    ): CashTransfer {
        // Validar autoridad jerárquica
        if (!$session->parentBoxIsAssignedTo($reviewerId)) {
            throw new \InvalidArgumentException(
                __('cashmanager::cash_manager.error_not_parent_box_responsible')
            );
        }

        return DB::transaction(function () use ($session, $hasIncidents, $reviewerNotes) {
            $box       = $session->getBox();
            $parentBox = $session->getParentBox();

            // Registrar el cambio de custodia: el dinero pasa a la caja superior.
            // Para AUXILIARY: efectivo físico menos la base.
            // Para PRINCIPAL/VAULT: suma de los transfers recibidos de sus hijas.
            $amount = ($box->type === 'AUXILIARY')
                ? $session->total_physical_counted - $session->opening_base
                : CashTransfer::where('destination_box_id', $box->id)
                    ->where('status', 'RECEIVED')
                    ->sum('amount');

            $transfer = CashTransfer::create([
                'origin_session_id'  => $session->id,
                'destination_box_id' => $parentBox->id,
                'amount'             => $amount,
                'transfer_hash'      => Str::random(32),
                'status'             => 'RECEIVED',
                'received_at'        => now(),
            ]);

            $session->update([
                'status'         => 'CLOSED',
                'has_incidents'  => $session->has_incidents || $hasIncidents,
                'reviewer_notes' => $reviewerNotes,
                'closed_at'      => $session->closed_at ?? now(),
            ]);

            $box->update(['status' => 'AVAILABLE']);
            $session->assignment->update(['revoked_at' => now()]);

            return $transfer;
        });
    }

    /**
     * Envía el reporte de la caja PRINCIPAL al VAULT.
     * Solo se permite cuando TODAS las cajas AUXILIARY hijas activas tienen sesión CLOSED.
     *
     * Transición de la sesión de la PRINCIPAL: OPEN → PENDING_RECEIPT
     *
     * @param  CashBox  $principalBox   La caja PRINCIPAL que envía el reporte
     * @param  int      $requesterId    auth()->id() del responsable de la PRINCIPAL
     *
     * @throws \InvalidArgumentException si hay cajas hijas aún abiertas o pendientes
     * @throws \InvalidArgumentException si el solicitante no está asignado a esta caja
     */
    public function submitToParent(CashBox $principalBox, int $requesterId): CashSession
    {
        // Verificar que el solicitante tiene asignación activa sobre esta caja
        $assignment = CashBoxAssignment::where('cash_box_id', $principalBox->id)
            ->where('user_id', $requesterId)
            ->whereNull('revoked_at')
            ->first();

        if (!$assignment) {
            throw new \InvalidArgumentException(
                __('cashmanager::cash_manager.error_not_box_responsible')
            );
        }

        // Verificar que no hay cajas hijas aún abiertas o pendientes de recibir
        $pendingChildren = CashBox::where('parent_id', $principalBox->id)
            ->whereIn('status', ['OPEN', 'PENDING_RECEIPT'])
            ->count();

        if ($pendingChildren > 0) {
            throw new \InvalidArgumentException(
                __('cashmanager::cash_manager.error_children_not_closed', ['count' => $pendingChildren])
            );
        }

        return DB::transaction(function () use ($principalBox, $assignment) {
            // La sesión activa de la PRINCIPAL pasa a PENDING_RECEIPT
            $session = CashSession::where('assignment_id', $assignment->id)
                ->where('status', 'OPEN')
                ->firstOrFail();

            $session->update([
                'status'    => 'PENDING_RECEIPT',
                'closed_at' => now(),
            ]);

            $principalBox->update(['status' => 'PENDING_RECEIPT']);

            return $session->fresh();
        });
    }

    // ─── Helpers privados ────────────────────────────────────────────────────────

    private function resolveCountryForBox(CashBox $box): int
    {
        $costCenter = $box->costCenter;

        if ($costCenter?->city_id) {
            $countryId = DB::table('cities')
                ->join('states', 'cities.state_id', '=', 'states.id')
                ->where('cities.id', $costCenter->city_id)
                ->value('states.country_id');

            if ($countryId) {
                return (int) $countryId;
            }
        }

        return (int) (DB::table('countries')->where('code', 'CO')->value('id') ?? 47);
    }

    private function getDenominationValue(string $denominationId): float
    {
        return (float) CatDenomination::findOrFail($denominationId)->value;
    }
}
