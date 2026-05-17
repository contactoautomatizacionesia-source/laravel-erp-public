<?php

namespace Modules\CostCenter\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\CostCenter\Entities\CostCenter;
use Modules\CostCenter\Entities\CostCenterInventory;
use Modules\CostCenter\Entities\CostCenterInventoryMovement;
use Modules\CostCenter\Entities\CostCenterProductAlert;
use Modules\GeneralSetting\Entities\Catalogs\CostCenterMovementType;
use Modules\Seller\Entities\SellerProductSKU;
use Modules\CostCenter\Entities\CostCenterTransfer;
use Modules\CostCenter\Entities\CostCenterInventoryLot;
use Modules\CostCenter\Entities\CostCenterTransferDiscrepancy;
use Modules\CostCenter\Entities\CostCenterTransferItem;

class CostCenterInventoryRepository // NOSONAR
{
    // --- CONSULTAS GENERALES ---
    
    public function getActiveCostCenters()
    {
        return CostCenter::where('status', 1)->get();
    }

    public function getActiveMovementTypes()
    {
        return CostCenterMovementType::active()->orderBy('name')->get(['name', 'id']);
    }

    public function getDatatablesQuery()
    {
        return CostCenter::query()
            ->where('status', 1)
            ->addSelect([
                'product_count' => DB::table('cost_center_inventories as cci')
                    ->join('product_sku as ps', 'cci.product_sku_id', '=', 'ps.id')
                    ->join('products as p', 'ps.product_id', '=', 'p.id')
                    ->whereColumn('cci.cost_center_id', 'cost_centers.id')
                    ->where('ps.status', 1)
                    ->where('p.status', 1)
                    ->select(DB::raw('COUNT(DISTINCT ps.product_id)')),
                'total_quantity' => DB::table('cost_center_inventories as cci2')
                    ->whereColumn('cci2.cost_center_id', 'cost_centers.id')
                    ->select(DB::raw('COALESCE(SUM(cci2.qty), 0)')),
            ]);
    }

    public function findCenterById($id)
    {
        return CostCenter::findOrFail($id);
    }

    public function getCenterSkusWithStock($centerId)
    {
        $center = $this->findCenterById($centerId);
        $inventories = $center->inventories()
            ->with(['productSku.product.brand']) // Eager load product and brand
            ->where('qty', '>', 0) // CONDICION 1: Tiene Stock
            ->whereExists(function ($q) use ($centerId) {
                $q->select(DB::raw(1))
                    ->from('cost_center_inventory_lots as ccil')
                    ->where('ccil.location_type', 'cost_center')
                    ->where('ccil.location_id', $centerId)
                    ->where('ccil.qty', '>', 0)
                    ->whereColumn('ccil.product_sku_id', 'cost_center_inventories.product_sku_id');
            })
            ->whereHas('productSku', function ($q) {
                $q->where('status', 1)
                    ->whereHas('product', function ($q2) {
                        $q2->where('status', 1);
                    });
            })
            ->get();

        // Group by product_id to facilitate frontend rendering
        $grouped = $inventories->groupBy(function ($item) {
            return $item->productSku->product_id ?? 0;
        });

        $products = [];
        foreach ($grouped as $productId => $group) {
            $firstItem = $group->first();
            if ($firstItem && $firstItem->productSku && $firstItem->productSku->product) {
                $product = $firstItem->productSku->product;
                $products[] = [
                    'product_id' => $productId,
                    'product_name' => $product->getTranslation('product_name', app()->getLocale()),
                    'brand' => $product->brand ? $product->brand->getTranslation('name', app()->getLocale()) : '',
                    'model_number' => $product->model_number,
                    'expiry_date' => $product->expiry_date ? \Carbon\Carbon::parse($product->expiry_date)->format('Y-m-d') : null,
                    'thumbnail' => showImage($product->thumbnail_image_source),
                    'skus' => $group->map(function ($inv) {
                        return [
                            'inventory_id' => $inv->id,
                            'product_sku_id' => $inv->product_sku_id,
                            'sku' => $inv->productSku->sku ?? 'N/A',
                            'qty' => (int) ($inv->qty ?? 0),
                        ];
                    })->values()->toArray()
                ];
            }
        }

        return ['center' => $center, 'products' => $products, 'inventories' => $inventories];
    }

    public function getCenterProductsQuery($centerId)
    {
        return DB::table('cost_center_inventories as cci')
            ->join('product_sku as ps', 'cci.product_sku_id', '=', 'ps.id')
            ->join('products as p', 'ps.product_id', '=', 'p.id')
            ->leftJoin('brands as b', 'p.brand_id', '=', 'b.id')
            ->leftJoin('unit_types as ut', 'p.unit_type_id', '=', 'ut.id')
            ->leftJoin('cost_center_product_alerts as ccpa', function ($join) use ($centerId) {
                $join->on('ccpa.product_id', '=', 'p.id')
                    ->where('ccpa.cost_center_id', '=', $centerId);
            })
            ->where('cci.cost_center_id', $centerId)
            ->where('ps.status', 1)    // Validación de SKU Activo
            ->where('p.status', 1)     // Validación de Producto Padre Activo
            // 1. Agregamos todas las columnas no agregadas al GROUP BY
            ->groupBy(
                'p.id',
                'p.product_name',
                'p.thumbnail_image_source',
                'p.is_physical',
                'b.name',
                'ut.name'
            )
            ->select(
                'p.id as product_id',
                // 2. Quitamos el DB::raw y el ANY_VALUE
                'p.product_name',
                'p.thumbnail_image_source',
                'p.is_physical',
                DB::raw('SUM(cci.qty) as qty'),
                'b.name as brand',
                'ut.name as unit_type_json',
                DB::raw('COALESCE(MAX(ccpa.min_stock), 0) as min_stock'),
                DB::raw('COALESCE(MAX(ccpa.max_stock), 0) as max_stock')
            );
    }

    public function upsertProductAlert($centerId, $productId, $minStock, $maxStock)
    {
        return CostCenterProductAlert::updateOrCreate(
            ['cost_center_id' => $centerId, 'product_id' => $productId],
            ['min_stock' => $minStock, 'max_stock' => $maxStock]
        );
    }

    // --- BODEGA PRINCIPAL ---

    public function getMainWarehouseSkus($mainSellerId, $searchTerm = null)
    {
        $query = SellerProductSKU::where('user_id', $mainSellerId)
            ->where('status', 1) // SKU del Seller Activo
            ->where('product_stock', '>', 0) // CONDICION 1: Tiene stock en la bodega principal
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('cost_center_inventory_lots as ccil')
                    ->where('ccil.location_type', 'main')
                    ->whereNull('ccil.location_id')
                    ->where('ccil.qty', '>', 0)
                    ->whereColumn('ccil.product_sku_id', 'seller_product_s_k_us.product_sku_id');
            })
            ->with(['sku', 'product.product.brand'])
            ->whereHas('sku', function ($q) {
                $q->where('status', 1)
                    ->whereHas('product', function ($q2) {
                        $q2->where('status', 1);
                    });
            });

        if ($searchTerm) {
            $query->whereHas('sku', function ($q) use ($searchTerm) {
                $q->where('sku', 'like', "%{$searchTerm}%");
            });
        }

        $inventories = $query->get();

        $grouped = $inventories->groupBy(function ($item) {
            return $item->product->product_id ?? 0;
        });

        $products = [];
        foreach ($grouped as $productId => $group) {
            $firstItem = $group->first();
            if ($firstItem && $firstItem->product && $firstItem->product->product) {
                $mainProduct = $firstItem->product->product;
                $thumbnail = showImage($mainProduct->thumbnail_image_source);

                $products[] = [
                    'product_id' => $productId,
                    'product_name' => $mainProduct->getTranslation('product_name', app()->getLocale()),
                    'brand' => $mainProduct->brand ? $mainProduct->brand->getTranslation('name', app()->getLocale()) : '',
                    'model_number' => $mainProduct->model_number,
                    'expiry_date' => $mainProduct->expiry_date ? \Carbon\Carbon::parse($mainProduct->expiry_date)->format('Y-m-d') : null,
                    'thumbnail' => $thumbnail,
                    'skus' => $group->map(function ($inv) {
                        return [
                            'product_sku_id' => $inv->product_sku_id,
                            'sku' => $inv->sku->sku ?? 'N/A',
                            'qty' => $inv->product_stock
                        ];
                    })->values()->toArray()
                ];
            }
        }

        return collect($products);
    }

    public function getMainSkuWithLock($mainSellerId, $skuId)
    {
        return SellerProductSKU::with('sku')
            ->where('user_id', $mainSellerId)
            ->where('product_sku_id', $skuId)
            ->where('status', 1)
            ->lockForUpdate()
            ->first();
    }

    // --- INVENTARIO DE CENTROS DE COSTO ---

    public function getCenterInventoryWithLock($centerId, $skuId)
    {
        return CostCenterInventory::where('cost_center_id', $centerId)
            ->where('product_sku_id', $skuId)
            ->lockForUpdate()
            ->first();
    }

    public function addCenterStock($centerId, $skuId, $qty)
    {
        $inventory = CostCenterInventory::firstOrCreate(
            ['cost_center_id' => $centerId, 'product_sku_id' => $skuId],
            ['qty' => 0]
        );
        $inventory->increment('qty', $qty);
        return $inventory;
    }

    public function deductCenterStock($inventory, $qty)
    {
        return $inventory->decrement('qty', $qty);
    }

    // --- INVENTARIO POR LOTE (UBICACIONES) ---

    public function getLocationLotWithLock(string $locationType, $locationId, int $skuId, int $lotId)
    {
        return CostCenterInventoryLot::where('location_type', $locationType)
            ->where('location_id', $locationId)
            ->where('product_sku_id', $skuId)
            ->where('lot_id', $lotId)
            ->lockForUpdate()
            ->first();
    }

    public function addLocationLotStock(string $locationType, $locationId, int $skuId, int $lotId, $qty)
    {
        $inventoryLot = CostCenterInventoryLot::firstOrCreate(
            [
                'location_type' => $locationType,
                'location_id' => $locationId,
                'product_sku_id' => $skuId,
                'lot_id' => $lotId,
            ],
            ['qty' => 0]
        );

        $inventoryLot->increment('qty', $qty);
        return $inventoryLot;
    }

    public function deductLocationLotStock(CostCenterInventoryLot $inventoryLot, $qty)
    {
        return $inventoryLot->decrement('qty', $qty);
    }

    public function getLocationLots(string $locationType, $locationId, int $skuId)
    {
        return CostCenterInventoryLot::with('lot')
            ->where('location_type', $locationType)
            ->where('location_id', $locationId)
            ->where('product_sku_id', $skuId)
            ->where('qty', '>', 0)
            ->orderByRaw('CASE WHEN lot_id IS NULL THEN 1 ELSE 0 END')
            ->get();
    }

    public function getLocationLotsFifo(string $locationType, $locationId, int $skuId)
    {
        return CostCenterInventoryLot::with('lot')
            ->where('location_type', $locationType)
            ->where('location_id', $locationId)
            ->where('product_sku_id', $skuId)
            ->where('qty', '>', 0)
            ->join('product_lots as pl', 'cost_center_inventory_lots.lot_id', '=', 'pl.id')
            ->orderByRaw('CASE WHEN pl.expiration_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('pl.expiration_date', 'asc')
            ->select('cost_center_inventory_lots.*')
            ->get();
    }

    // --- MOVIMIENTOS (KARDEX) ---

    public function createMovement(array $data)
    {
        return CostCenterInventoryMovement::create($data);
    }

    public function createTransferHeader(array $data)
    {
        if (empty($data['reference_code'])) {
            $data['reference_code'] = $this->generateTransferReference();
        }
        return CostCenterTransfer::create($data);
    }

    private function generateTransferReference(): string
    {
        $attempts = 0;
        do {
            $code = 'TRA-' . random_int(10000000, 99999999);
            $attempts++;
        } while (CostCenterTransfer::where('reference_code', $code)->exists() && $attempts < 10);

        return $code;
    }

    // --- TRANSFERENCIAS Y RECEPCIÓN ---

    public function findTransferById($id)
    {
        return CostCenterTransfer::findOrFail($id);
    }

    public function getTransferItems($transferId)
    {
        return CostCenterTransferItem::where('transfer_id', $transferId)->get();
    }

    public function createTransferItem(array $data)
    {
        return CostCenterTransferItem::create($data);
    }

    public function updateTransferItem($id, array $data)
    {
        return CostCenterTransferItem::where('id', $id)->update($data);
    }

    public function createTransferDiscrepancy(array $data)
    {
        return CostCenterTransferDiscrepancy::create($data);
    }
}
