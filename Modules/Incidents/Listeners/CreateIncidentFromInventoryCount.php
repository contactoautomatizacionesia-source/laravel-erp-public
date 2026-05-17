<?php

namespace Modules\Incidents\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\InventoryCount\Events\InventoryCountDifferenceDetected;
use Modules\Incidents\Services\IncidentCreationService;

class CreateIncidentFromInventoryCount
{
    public function __construct(protected IncidentCreationService $service) {}

    public function handle(InventoryCountDifferenceDetected $event): void
    {
        try {
            $count = $event->count->load(['details.product.skus']);

            foreach ($count->details as $detail) {
                // Detectar líneas con faltante: system_stock > physical_quantity
                $physical = $detail->physical_quantity ?? 0;
                $missing  = $detail->system_stock - $physical;

                if ($missing <= 0) {
                    continue;
                }

                $product = $detail->product;

                if (! $product) {
                    Log::warning('[Incidents] CreateIncidentFromInventoryCount: Producto no encontrado', [
                        'count_detail_id' => $detail->id,
                        'product_id'      => $detail->product_id,
                    ]);
                    continue;
                }

                // Tomar el primer SKU para el precio público
                $sku         = $product->skus->first();
                $publicPrice = (float) ($sku?->selling_price ?? 0);

                $this->service->createFromInventoryCount([
                    'countId'      => $count->id,
                    'costCenterId' => $count->cost_center_id,
                    'userId'       => $event->userId,
                    'productId'    => $detail->product_id,
                    'productName'  => $product->product_name ?? $product->name ?? 'Producto desconocido',
                    'publicPrice'  => $publicPrice,
                    'missingUnits' => $missing,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[Incidents] Error al crear novedad desde conteo: ' . $e->getMessage(), [
                'count_id'  => $event->count->id ?? null,
                'exception' => $e,
            ]);
        }
    }
}
