<?php

namespace Modules\Incidents\Services;

use Illuminate\Support\Facades\DB;
use Modules\Incidents\Entities\Incident;
use Modules\Incidents\Repositories\IncidentAuditLogRepository;
use Modules\Incidents\Repositories\IncidentRepository;

class StatementService
{
    public function __construct(
        protected IncidentRepository      $repo,
        protected IncidentAuditLogRepository $auditRepo
    ) {}

    /**
     * Procesa el pronunciamiento de la sede origen ante una novedad de transferencia.
     *
     * @param string $incidentId UUID
     * @param string $statementType 'acknowledged' | 'rejected'
     * @param string|null $notes Notas obligatorias cuando es acknowledged
     * @param int $userId ID del usuario que se pronuncia (debe ser de origin_branch)
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function submit(string $incidentId, string $statementType, ?string $notes, int $userId): Incident
    {
        $incident = $this->repo->findById($incidentId);

        // Validaciones
        if ($incident->status !== 'awaiting_statement') {
            throw new \LogicException('La novedad no está en estado de espera de pronunciamiento.');
        }

        if ($incident->statementDeadlineExpired()) {
            throw new \LogicException('El plazo para pronunciarse ha vencido.');
        }

        if (! in_array($statementType, ['acknowledged', 'rejected'], true)) {
            throw new \InvalidArgumentException('Tipo de pronunciamiento inválido.');
        }

        return DB::transaction(function () use ($incident, $statementType, $notes, $userId) {
            $previousStatus = $incident->status;

            if ($statementType === 'acknowledged') {
                return $this->handleAcknowledged($incident, $notes, $userId, $previousStatus);
            }

            return $this->handleRejected($incident, $notes, $userId, $previousStatus);
        });
    }

    private function handleAcknowledged(Incident $incident, ?string $notes, int $userId, string $previousStatus): Incident
    {
        // Para acknowledged se requiere al menos una evidencia adjunta con nota
        if ($incident->evidences()->where('actor_role', 'origin')->whereNotNull('notes')->where('notes', '!=', '')->count() === 0) {
            throw new \LogicException('Debe adjuntar al menos una evidencia con su respectiva nota antes de reconocer el error.');
        }

        $this->repo->update($incident, [
            'status'                  => 'closed',
            'statement_submitted_at'  => now(),
            'statement_type'          => 'acknowledged',
            'resolution_party'        => 'organization', // Origen asume — reversión automática
            'resolution_notes'        => $notes,
            'resolved_at'             => now(),
            'resolved_by'             => $userId,
        ]);

        $this->auditRepo->log($incident->id, [
            'actor_label'     => ['es' => 'Asesor Origen', 'en' => 'Origin Advisor'],
            'user_id'         => $userId,
            'action'          => [
                'es' => 'Origen reconoció el error. Novedad cerrada. Pendiente reversión de inventario.',
                'en' => 'Origin acknowledged the error. Incident closed. Inventory reversal pending.',
            ],
            'previous_status' => $previousStatus,
            'new_status'      => 'closed',
            'metadata'        => ['statement_type' => 'acknowledged'],
        ]);

        // TODO: Emitir evento de reversión de inventario al módulo correspondiente // NOSONAR
        // event(new IncidentInventoryReversalRequested($incident->fresh()));

        return $incident->fresh();
    }

    private function handleRejected(Incident $incident, ?string $notes, int $userId, string $previousStatus): Incident
    {
        $this->repo->update($incident, [
            'status'                 => 'under_investigation',
            'statement_submitted_at' => now(),
            'statement_type'         => 'rejected',
        ]);

        $this->auditRepo->log($incident->id, [
            'actor_label'     => ['es' => 'Asesor Origen', 'en' => 'Origin Advisor'],
            'user_id'         => $userId,
            'action'          => [
                'es' => 'Origen rechazó la responsabilidad. Novedad escalada al administrador.',
                'en' => 'Origin rejected responsibility. Incident escalated to administrator.',
            ],
            'previous_status' => $previousStatus,
            'new_status'      => 'under_investigation',
            'metadata'        => ['statement_type' => 'rejected', 'notes' => $notes],
        ]);

        return $incident->fresh();
    }
}
