<?php

namespace Modules\InventoryExit\Actions;

use Illuminate\Support\Facades\DB;
use Modules\InventoryExit\Entities\InventoryExitRequest;
use Modules\CostCenter\Repositories\CostCenterInventoryRepository;
use Modules\CostCenter\Entities\CostCenterInventory;
use Modules\CostCenter\Entities\CostCenterInventoryMovement;
use Modules\GeneralSetting\Entities\Catalogs\CostCenterMovementType;
use Modules\Product\Entities\ProductSku;
use Modules\Seller\Entities\SellerProductSKU;
use Modules\GeneralSetting\Entities\Catalogs\InventoryOutReason;
use Modules\InventoryExit\Exceptions\InsufficientLotStockException;

class ProcessExitApproval
{
    public function __construct(
        private CostCenterInventoryRepository $inventoryRepo
    ) {}

    /**
     * Ejecuta el descuento de inventario y registra el movimiento en el Kardex.
     * Solo debe llamarse cuando la solicitud pasa a estado 'approved'.
     *
     * @throws RuntimeException si no hay stock suficiente en algún lote
     */
    public function execute(InventoryExitRequest $exitRequest): void
    {
        DB::transaction(function () use ($exitRequest) {
            $locationType = $exitRequest->location_type;
            $locationId   = $exitRequest->location_id;
            $isMain       = $locationType === 'main';

            // Obtener movement_type_id del motivo de salida desde system_catalogs
            $movementTypeId = $this->resolveMovementTypeId();

            foreach ($exitRequest->items as $item) {
                $qty    = $item->effectiveQty();
                $skuId  = $item->product_sku_id;
                $lotId  = $item->lot_id;

                // 1. Validar y descontar del lote en la ubicación
                $lotRecord = $this->inventoryRepo->getLocationLotWithLock($locationType, $locationId, $skuId, $lotId);

                if (!$lotRecord || $lotRecord->qty < $qty) {
                    throw new InsufficientLotStockException(
                        $item->lot?->lot_number ?? "ID {$lotId}",
                        (float) ($lotRecord?->qty ?? 0),
                        (float) $qty,
                    );
                }

                $this->inventoryRepo->deductLocationLotStock($lotRecord, $qty);

                // 2. Descontar del inventario agregado del centro de costo / bodega principal
                if ($isMain) {
                    ProductSku::where('id', $skuId)->decrement('product_stock', $qty);
                    SellerProductSKU::where('product_sku_id', $skuId)
                        ->where('user_id', config('costcenter.main_warehouse.seller_id'))
                        ->decrement('product_stock', $qty);
                } else {
                    CostCenterInventory::where('cost_center_id', $locationId)
                        ->where('product_sku_id', $skuId)
                        ->decrement('qty', $qty);
                }

                // 3. Registrar movimiento en el Kardex (salida del sistema)
                $this->inventoryRepo->createMovement([
                    'movement_type_id'  => $movementTypeId,
                    'source_type'       => $locationType,
                    'source_id'         => $locationId,
                    'destination_type'  => 'exit',
                    'destination_id'    => null,
                    'product_sku_id'    => $skuId,
                    'lot_id'            => $lotId,
                    'qty'               => $qty,
                    'reason'            => $exitRequest->observation,
                    'reference_type'    => 'exit_request',
                    'reference_id'      => $exitRequest->id,
                    'created_by'        => $exitRequest->approved_by,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        });
    }

    /**
     * Obtiene el movement_type_id del catálogo para salidas de inventario.
     */
    private function resolveMovementTypeId(): ?int
    {
        $type = CostCenterMovementType::where(
            'code', 'adjustment_out'
        )->first();

        return $type?->id;
    }
}
