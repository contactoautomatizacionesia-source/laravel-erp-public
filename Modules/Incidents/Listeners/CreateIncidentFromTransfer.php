<?php

namespace Modules\Incidents\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\CostCenter\Events\TransferDiscrepancyCreated;
use Modules\Incidents\Services\IncidentCreationService;

class CreateIncidentFromTransfer
{
    public function __construct(protected IncidentCreationService $service) {}

    public function handle(TransferDiscrepancyCreated $event): void
    {
        try {
            $item     = $event->item;
            $transfer = $event->transfer;

            // Resolver producto y precio desde el SKU del item
            $sku = $item->productSku()->with('product')->first();

            if (! $sku || ! $sku->product) {
                Log::warning('[Incidents] CreateIncidentFromTransfer: SKU o producto no encontrado', [
                    'transfer_item_id' => $item->id,
                ]);
                return;
            }

            // Crear la novedad (Incident) a partir de la transferencia con faltante
            $this->service->createFromTransfer([
                'transferId'          => $transfer->id,
                'transferItemId'      => $item->id,
                'originBranchId'      => $transfer->source_id,
                'originUserId'        => $event->dispatchedBy,
                'destinationBranchId' => $transfer->destination_id,
                'destinationUserId'   => $transfer->received_by,
                'productId'           => $sku->product_id,
                'productName'         => $sku->product->product_name ?? $sku->product->name ?? 'Producto desconocido',
                'publicPrice'         => (float) ($sku->selling_price ?? 0),
                'missingUnits'        => (int) $event->discrepancyQty,
            ]);
        } catch (\Throwable $e) {
            Log::error('[Incidents] Error al crear novedad desde transferencia: ' . $e->getMessage(), [
                'transfer_id' => $event->transfer->id ?? null,
                'exception'   => $e,
            ]);
        }
    }
}
