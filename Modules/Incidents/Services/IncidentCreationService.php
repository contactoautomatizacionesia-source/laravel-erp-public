<?php

namespace Modules\Incidents\Services;

use Illuminate\Support\Facades\DB;
use Modules\Incidents\Entities\Incident;
use Modules\Incidents\Repositories\IncidentAuditLogRepository;
use Modules\Incidents\Repositories\IncidentRepository;
use Modules\Incidents\Repositories\IncidentSettingRepository;

class IncidentCreationService
{
    public function __construct(
        protected IncidentRepository        $repo,
        protected IncidentSettingRepository $settingRepo,
        protected IncidentAuditLogRepository $auditRepo
    ) {}

    /**
     * Crea una novedad a partir de una transferencia con faltante.
     * Llamado desde el Listener CreateIncidentFromTransfer.
     *
     * @param array $payload {
     *   transferId: int,
     *   originBranchId: int,
     *   originUserId: int,
     *   destinationBranchId: int,
     *   destinationUserId: int,
     *   productId: int,
     *   productName: string,
     *   publicPrice: float,
     *   missingUnits: int,
     * }
     */
    public function createFromTransfer(array $payload): Incident
    {
        $sourceItemId = $payload['transferItemId'] ?? null;

        if ($this->repo->hasActiveForSource('cost_center_transfer', $payload['transferId'], $payload['productId'], $sourceItemId)) {
            // Ya existe una novedad activa para este ítem de transferencia
            $query = $this->repo->getBaseQuery()
                ->where('source_type', 'cost_center_transfer')
                ->where('source_id', $payload['transferId'])
                ->where('product_id', $payload['productId'])
                ->whereNotIn('status', ['closed', 'voided']);

            if ($sourceItemId !== null) {
                $query->where('source_item_id', $sourceItemId);
            }

            return $query->first();
        }

        return DB::transaction(function () use ($payload, $sourceItemId) {
            $setting = $this->settingRepo->getInstance();
            $code    = $this->repo->generateSequentialCode();

            // Crear la novedad (Incident) con la información de la transferencia y el producto faltante
            $incident = $this->repo->create([
                'sequential_code'          => $code,
                'incident_type'            => 'transfer',
                'status'                   => 'awaiting_statement',
                'source_type'              => 'cost_center_transfer',
                'source_id'                => $payload['transferId'],
                'source_item_id'           => $sourceItemId,
                'product_id'               => $payload['productId'],
                'product_name_snapshot'    => $payload['productName'],
                'public_price_snapshot'    => $payload['publicPrice'],
                'missing_units'            => $payload['missingUnits'],
                'responsible_branch_id'    => $payload['destinationBranchId'],
                'responsible_user_id'      => $payload['destinationUserId'],
                'origin_branch_id'         => $payload['originBranchId'],
                'origin_user_id'           => $payload['originUserId'],
                'statement_deadline_hours' => $setting->statement_deadline_hours,
                'statement_expires_at'     => now()->addHours($setting->statement_deadline_hours),
            ]);

            $this->auditRepo->log($incident->id, [
                'actor_label' => ['es' => 'Sistema', 'en' => 'System'],
                'user_id'     => null,
                'action'      => [
                    'es' => 'Novedad generada automáticamente por diferencia en transferencia.',
                    'en' => 'Incident automatically created due to transfer discrepancy.',
                ],
                'new_status'  => 'awaiting_statement',
                'metadata'    => [
                    'source'      => 'transfer',
                    'transfer_id' => $payload['transferId'],
                ],
            ]);

            return $incident;
        });
    }

    /**
     * Crea una novedad a partir de un conteo de inventario con diferencia.
     * Llamado desde el Listener CreateIncidentFromInventoryCount.
     * Se invoca una vez por cada línea de producto con diferencia.
     *
     * @param array $payload {
     *   countId: int,
     *   costCenterId: int,
     *   userId: int,
     *   productId: int,
     *   productName: string,
     *   publicPrice: float,
     *   missingUnits: int,
     * }
     */
    public function createFromInventoryCount(array $payload): Incident
    {
        if ($this->repo->hasActiveForSource('inventory_count', $payload['countId'], $payload['productId'])) {
            // Ya existe una novedad activa para este producto en este conteo
            return $this->repo->getBaseQuery()
                ->where('source_type', 'inventory_count')
                ->where('source_id', $payload['countId'])
                ->where('product_id', $payload['productId'])
                ->whereNotIn('status', ['closed', 'voided'])
                ->first();
        }

        return DB::transaction(function () use ($payload) {
            $setting = $this->settingRepo->getInstance();
            $code    = $this->repo->generateSequentialCode();

            // Crear la novedad (Incident) con la información del conteo de inventario y el producto faltante
            $incident = $this->repo->create([
                'sequential_code'          => $code,
                'incident_type'            => 'inventory_count',
                'status'                   => 'pending',
                'source_type'              => 'inventory_count',
                'source_id'                => $payload['countId'],
                'product_id'               => $payload['productId'],
                'product_name_snapshot'    => $payload['productName'],
                'public_price_snapshot'    => $payload['publicPrice'],
                'missing_units'            => $payload['missingUnits'],
                'responsible_branch_id'    => $payload['costCenterId'],
                'responsible_user_id'      => $payload['userId'],
                'statement_deadline_hours' => $setting->statement_deadline_hours,
            ]);

            $this->auditRepo->log($incident->id, [
                'actor_label' => ['es' => 'Sistema', 'en' => 'System'],
                'user_id'     => null,
                'action'      => [
                    'es' => 'Novedad generada automáticamente por diferencia en conteo de inventario.',
                    'en' => 'Incident automatically created due to inventory count discrepancy.',
                ],
                'new_status'  => 'pending',
                'metadata'    => [
                    'source'   => 'inventory_count',
                    'count_id' => $payload['countId'],
                ],
            ]);

            return $incident;
        });
    }
}
