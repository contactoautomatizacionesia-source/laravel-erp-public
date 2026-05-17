<?php

namespace Modules\Incidents\Services;

use Illuminate\Support\Facades\DB;
use Modules\Incidents\Entities\Incident;
use Modules\Incidents\Repositories\IncidentAuditLogRepository;
use Modules\Incidents\Repositories\IncidentRepository;

class ResolutionService
{
    public function __construct(
        protected IncidentRepository         $repo,
        protected IncidentAuditLogRepository $auditRepo
    ) {}

    /**
     * Registra la decisión del administrador sobre una novedad.
     *
     * @param string $incidentId UUID
     * @param string $resolutionParty 'advisor' | 'organization' | 'voided'
     * @param string $notes Justificación obligatoria
     * @param int $adminId ID del administrador
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function resolve(string $incidentId, string $resolutionParty, string $notes, int $adminId): Incident
    {
        $incident = $this->repo->findById($incidentId);

        if (! in_array($incident->status, ['under_investigation', 'pending'], true)) {
            throw new \LogicException('Solo se pueden resolver novedades en investigación o pendientes.');
        }

        if (! in_array($resolutionParty, ['advisor', 'organization', 'voided'], true)) {
            throw new \InvalidArgumentException('Tipo de responsable inválido.');
        }

        if (empty(trim($notes))) {
            throw new \InvalidArgumentException('La justificación del administrador es obligatoria.');
        }

        return DB::transaction(function () use ($incident, $resolutionParty, $notes, $adminId) {
            $previousStatus = $incident->status;
            $newStatus = $resolutionParty === 'voided' ? 'voided' : 'closed';

            $this->repo->update($incident, [
                'status'           => $newStatus,
                'resolution_party' => $resolutionParty,
                'resolution_notes' => $notes,
                'resolved_at'      => now(),
                'resolved_by'      => $adminId,
            ]);

            $actionMap = [
                'advisor'      => ['es' => 'Administrador determinó que el asesor responde. Novedad cerrada.',       'en' => 'Administrator determined the advisor is responsible. Incident closed.'],
                'organization' => ['es' => 'Administrador determinó que la organización asume la pérdida. Novedad cerrada.', 'en' => 'Administrator determined the organization assumes the loss. Incident closed.'],
                'voided'       => ['es' => 'Administrador anuló la novedad.',                                         'en' => 'Administrator voided the incident.'],
            ];

            $this->auditRepo->log($incident->id, [
                'actor_label'     => ['es' => 'Administrador', 'en' => 'Administrator'],
                'user_id'         => $adminId,
                'action'          => $actionMap[$resolutionParty],
                'previous_status' => $previousStatus,
                'new_status'      => $newStatus,
                'metadata'        => ['resolution_party' => $resolutionParty],
            ]);

            // Si la organización asume, disparar evento al módulo contable (stub)
            // if ($resolutionParty === 'organization') {
            //     event(new IncidentOrganizationAssumesLoss($incident->fresh()));
            // }

            return $incident->fresh();
        });
    }

    /**
     * Anula una novedad directamente (atajo del flujo void).
     */
    public function void(string $incidentId, string $reason, int $adminId): Incident
    {
        return $this->resolve($incidentId, 'voided', $reason, $adminId);
    }
}
