<?php

namespace Modules\InventoryExit\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\InventoryExit\Entities\InventoryExitRequest;
use Modules\InventoryExit\Repositories\InventoryExitRepository;
use Modules\InventoryExit\Actions\ProcessExitApproval;
use Modules\InventoryExit\Actions\NotifyExitStatus;
use Modules\CostCenter\Repositories\CostCenterInventoryRepository;
use Modules\CostCenter\Entities\CostCenterInventoryLot;

class InventoryExitService
{
    public function __construct(
        public readonly InventoryExitRepository $repo,
        private ProcessExitApproval $processApproval,
        private NotifyExitStatus $notifier,
        private CostCenterInventoryRepository $inventoryRepo,
    ) {}

    // ---------------------------------------------------------------
    // Crear solicitud
    // ---------------------------------------------------------------

    /**
     * Crea la solicitud de salida con sus ítems y documentos soporte.
     * El inventario NO se toca en este paso.
     */
    public function createRequest(array $validated, Request $request): InventoryExitRequest
    {
        [$locationType, $locationId] = $this->resolveLocation($validated['location']);

        $exitRequest = $this->repo->create([
            'exit_reason_id'       => $validated['exit_reason_id'],
            'location_type'        => $locationType,
            'location_id'          => $locationId,
            'exit_date'            => $validated['exit_date'],
            'observation'          => $validated['observation'],
            'status'               => 'pending',
            'requested_by'         => Auth::id(),
            'requested_ip'         => $request->ip(),
            'requested_user_agent' => $request->userAgent(),
        ]);

        $this->repo->createItems($exitRequest->id, $validated['items']);

        if ($request->hasFile('documents')) {
            $this->repo->storeDocuments($exitRequest->id, $request->file('documents'), Auth::id());
        }

        $this->notifier->notifyAdmins($exitRequest->load('requestedBy', 'costCenter', 'exitReason'));

        return $exitRequest;
    }

    // ---------------------------------------------------------------
    // Aprobar / Rechazar
    // ---------------------------------------------------------------

    /**
     * Procesa la decisión del administrador.
     * Solo al aprobar se descuenta el inventario.
     */
    public function processDecision(InventoryExitRequest $exitRequest, array $validated, Request $request): void
    {
        $exitRequest->load('items.lot', 'items.productSku', 'requestedBy', 'costCenter');

        $updateData = [
            'status'              => $validated['status'],
            'approval_note'       => $validated['approval_note'],
            'approved_by'         => Auth::id(),
            'approved_at'         => now(),
            'approved_ip'         => $request->ip(),
            'approved_user_agent' => $request->userAgent(),
        ];

        if ($validated['status'] === 'approved') {
            // Aplicar qty_approved a los ítems si el admin las modificó
            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    $exitRequest->items
                        ->firstWhere('id', $itemData['id'])
                        ?->update(['qty_approved' => $itemData['qty_approved']]);
                }
                // Recargar ítems actualizados
                $exitRequest->load('items.lot', 'items.productSku');
            }

            $this->processApproval->execute($exitRequest);
        }

        $this->repo->updateStatus($exitRequest, $updateData);
        $this->notifier->notifyRequester($exitRequest->fresh('requestedBy'));
    }

    // ---------------------------------------------------------------
    // Catálogos para el frontend
    // ---------------------------------------------------------------

    public function getActiveReasons()
    {
        return $this->repo->getActiveReasons();
    }

    public function getCostCenters()
    {
        return $this->repo->getCostCenters();
    }

    /**
     * Convierte el valor del selector ("main" | "center-{id}") al par [type, id]
     * que se guarda en location_type + location_id, igual que cost_center_transfers.
     *
     * @return array{0: string, 1: int|null}
     */
    public function resolveLocation(string $locationValue): array
    {
        if ($locationValue === 'main') {
            return ['main', null];
        }

        return ['cost_center', (int) str_replace('center-', '', $locationValue)];
    }

    /**
     * SKUs con stock en una ubicación, con filtro opcional por término de búsqueda.
     * Devuelve una colección de CostCenterInventoryLot (distinct por product_sku_id).
     */
    public function searchSkusWithStock(string $locationType, ?int $locationId, string $search = '', int $limit = 15): \Illuminate\Support\Collection
    {
        $query = CostCenterInventoryLot::with('productSku.product')
            ->where('location_type', $locationType)
            ->where('location_id', $locationId)
            ->where('qty', '>', 0);

        if (strlen($search) >= 2) {
            $query->whereHas('productSku', function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('product_name', 'like', "%{$search}%"));
            });
        }

        return $query->select('product_sku_id')
            ->distinct()
            ->limit($limit)
            ->get();
    }

    /**
     * Lotes disponibles por ubicación y SKU — para el selector del formulario.
     */
    public function getLocationLots(string $locationType, ?int $locationId, int $skuId)
    {
        return $this->inventoryRepo->getLocationLots($locationType, $locationId, $skuId)
            ->map(fn($lot) => [
                'lot_id'          => $lot->lot_id,
                'lot_number'      => $lot->lot?->lot_number,
                'expiration_date' => $lot->lot?->expiration_date?->format('d/m/Y'),
                'qty'             => (float) $lot->qty,
            ]);
    }
}
