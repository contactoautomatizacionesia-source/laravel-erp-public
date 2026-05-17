<?php

namespace Modules\CostCenter\Actions;

use Illuminate\Support\Facades\DB;
use Modules\CostCenter\Entities\CostCenter;
use Modules\CostCenter\Exceptions\InsufficientStockException;
use Modules\CostCenter\Entities\CostCenterInventory;
use Modules\CostCenter\Repositories\CostCenterInventoryRepository;
use Modules\GeneralSetting\Entities\Catalogs\CostCenterMovementType;

class DeductCostCenterStock
{
    private static ?int $cartSaleMovementTypeId = null;

    public function __construct(
        private CostCenterInventoryRepository $inventoryRepo
    ) {}

    /**
     * Descuenta stock de un centro de costo por FIFO (lote más próximo a vencer primero).
     * Si la cantidad pedida supera un lote, consume tantos lotes como sea necesario.
     * Registra un movimiento en el Kardex por cada lote consumido.
     *
     * @param  int  $costCenterId
     * @param  int  $productSkuId
     * @param  int  $qty             Cantidad total a descontar
     * @param  int  $orderId         Para reference_id en el Kardex
     * @param  int  $createdBy
     * @return void
     *
     * @throws \RuntimeException si no hay suficiente stock en el centro para cubrir qty
     */
    public function execute(int $costCenterId, int $productSkuId, int $qty, int $orderId, int $createdBy): void
    {
        DB::transaction(function () use ($costCenterId, $productSkuId, $qty, $orderId, $createdBy) {
            $movementTypeId = $this->resolveMovementTypeId();

            // Lotes FIFO: primero los que vencen antes, nulls al final
            $lots = $this->inventoryRepo->getLocationLotsFifo('cost_center', $costCenterId, $productSkuId);

            $remaining = $qty;

            foreach ($lots as $lot) {
                if ($remaining <= 0) {
                    break;
                }

                $lotRecord = $this->inventoryRepo->getLocationLotWithLock(
                    'cost_center', $costCenterId, $productSkuId, $lot->lot_id
                );

                if (!$lotRecord || $lotRecord->qty <= 0) {
                    continue;
                }

                $consume = min($lotRecord->qty, $remaining);

                $this->inventoryRepo->deductLocationLotStock($lotRecord, $consume);

                CostCenterInventory::where('cost_center_id', $costCenterId)
                    ->where('product_sku_id', $productSkuId)
                    ->decrement('qty', $consume);

                $this->inventoryRepo->createMovement([
                    'movement_type_id' => $movementTypeId,
                    'source_type'      => 'cost_center',
                    'source_id'        => $costCenterId,
                    'destination_type' => 'exit',
                    'destination_id'   => null,
                    'product_sku_id'   => $productSkuId,
                    'lot_id'           => $lot->lot_id,
                    'qty'              => $consume,
                    'reason'           => null,
                    'reference_type'   => 'order',
                    'reference_id'     => $orderId,
                    'reference_code'   => null,
                    'created_by'       => $createdBy,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                $remaining -= $consume;
            }

            if ($remaining > 0) {
                throw new InsufficientStockException(
                    "Stock insuficiente en el centro de costo #{$costCenterId} para el SKU #{$productSkuId}. " .
                    "Se solicitaron {$qty} unidades pero faltaron {$remaining}. Orden #{$orderId}."
                );
            }
        });
    }

    private function resolveMovementTypeId(): ?int
    {
        if (self::$cartSaleMovementTypeId === null) {
            self::$cartSaleMovementTypeId = CostCenterMovementType::where('code', 'cart_sale')->value('id');
        }

        return self::$cartSaleMovementTypeId;
    }
}
