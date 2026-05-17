<?php

namespace Modules\Incidents\Services;

use Illuminate\Support\Facades\DB;
use Modules\Incidents\Entities\CashClosingIncident;
use Modules\Incidents\Repositories\IncidentAuditLogRepository;
use Modules\Incidents\Repositories\IncidentRepository;

class CashClosingLinkService
{
    public function __construct(
        protected IncidentRepository         $repo,
        protected IncidentAuditLogRepository $auditRepo
    ) {}

    /**
     * Vincula una novedad resuelta a un cierre de caja.
     *
     * @param string $incidentId UUID
     * @param int $cashClosingId ID del cierre de caja
     * @param int $userId ID del usuario que ejecuta la vinculación
     *
     * @throws \LogicException
     */
    public function link(string $incidentId, int $cashClosingId, int $userId): CashClosingIncident
    {
        $incident = $this->repo->findById($incidentId, ['cashClosingLink']);

        if ($incident->status !== 'closed') {
            throw new \LogicException('Solo se pueden vincular novedades cerradas a un cierre de caja.');
        }

        if ($incident->cashClosingLink) {
            throw new \LogicException('Esta novedad ya está vinculada a un cierre de caja.');
        }

        return DB::transaction(function () use ($incident, $cashClosingId, $userId) {
            $link = CashClosingIncident::create([
                'cash_closing_id' => $cashClosingId,
                'incident_id'     => $incident->id,
                'value_snapshot'  => $incident->total_value,
                'included_at'     => now(),
                'included_by'     => $userId,
            ]);

            $this->repo->update($incident, ['cash_closing_id' => $cashClosingId]);

            $this->auditRepo->log($incident->id, [
                'actor_label' => ['es' => 'Administrador', 'en' => 'Administrator'],
                'user_id'     => $userId,
                'action'      => ['es' => 'Novedad vinculada a cierre de caja.', 'en' => 'Incident linked to cash closing.'],
                'metadata'    => [
                    'cash_closing_id' => $cashClosingId,
                    'value_snapshot'  => $incident->total_value,
                ],
            ]);

            return $link;
        });
    }
}
