<?php

namespace Modules\InventoryEntry\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\InventoryEntry\Entities\ProductLot;
use Modules\InventoryEntry\Entities\InventoryEntry;
use Modules\Product\Entities\ProductSku;
use Modules\Seller\Entities\SellerProductSKU;
use Modules\CostCenter\Entities\CostCenterInventoryLot;
use Modules\CostCenter\Entities\CostCenterInventoryMovement;
use App\Services\DoubleApprovalService;
use Modules\GeneralSetting\Entities\ParameterSetting;
use Modules\InventoryEntry\Entities\InventoryEntryAudit;
use Modules\InventoryEntry\Exceptions\InventoryEntryTransferUsedException;
use Modules\InventoryEntry\Exceptions\InventoryEntryNotInMainWarehouseException;
use Modules\InventoryEntry\Exceptions\InventoryEntryInsufficientStockException;
use Modules\InventoryEntry\Exceptions\InventoryEntryDataConsistencyException;

class InventoryEntryService
{
    /**
     * Crea uno o varios ingresos de inventario en una sola transacción.
     * Cada item en $entries puede tener su propio lote (firstOrCreate por lot_number).
     *
     * @param array $entries  Array de ítems validados (entries.*)
     * @return InventoryEntry[]
     */
    public function createMany(array $entries): array
    {
        return DB::transaction(function () use ($entries) {
            $userId  = Auth::id();
            $created = [];
            $processedProducts = [];

            $skuIds = array_column($entries, 'product_sku_id');
            $skusById = ProductSku::with('product.skus')
                ->findMany($skuIds)
                ->keyBy('id');

            foreach ($entries as $data) {
                // 1. Crear o recuperar el lote por lot_number
                $lot = ProductLot::firstOrCreate(
                    ['lot_number' => $data['lot_number']],
                    [
                        'manufacture_date' => $data['manufacture_date'] ?? null,
                        'expiration_date'  => $data['expiration_date'] ?? null,
                        'created_by'       => $userId,
                    ]
                );

                // 2. Registrar el ingreso en el historial
                $entry = InventoryEntry::create([
                    'lot_id'             => $lot->id,
                    'product_sku_id'     => $data['product_sku_id'],
                    'quantity'           => $data['quantity'],
                    'unit_cost'          => $data['unit_cost'] ?? null,
                    'warehouse_location' => $data['warehouse_location'] ?? __('inventoryentry::inventory.location_default'),
                    'supplier'           => $data['supplier'] ?? null,
                    'notes'              => $data['notes'] ?? null,
                    'created_by'         => $userId,
                ]);

                // 3. Sumar stock en product_sku (bodega principal administrativa)
                ProductSku::where('id', $data['product_sku_id'])
                    ->increment('product_stock', $data['quantity']);

                // 4. Sumar stock en seller_product_s_k_us (bodega principal — user_id=1)
                SellerProductSKU::where('product_sku_id', $data['product_sku_id'])
                    ->where('user_id', 1)
                    ->increment('product_stock', $data['quantity']);

                CostCenterInventoryLot::firstOrCreate(
                    [
                        'location_type' => 'main',
                        'location_id' => null,
                        'product_sku_id' => $data['product_sku_id'],
                        'lot_id' => $lot->id,
                    ],
                    ['qty' => 0]
                )->increment('qty', $data['quantity']);

                // 5. Identificar el producto para la alerta posterior
                $sku = $skusById->get($data['product_sku_id']);
                if ($sku && $sku->product) {
                    $processedProducts[$sku->product->id] = $sku->product;
                }

                $created[] = $entry;
            }

            // 6. Llamada a la subfunción para manejo de alertas de stock
            $this->evaluateAndSendStockAlerts($processedProducts, $userId, $entries[0]['notes']);

            return $created;
        });
    }

    /**
     * Evalúa los niveles de stock finales y dispara las notificaciones necesarias.
     *
     * @param array $productIds Lista de IDs de productos afectados
     * @param int $userId ID del usuario que realiza la acción
     * @return void
     */
    private function evaluateAndSendStockAlerts(array $products, int $userId, ?string $notes = null): void
    {
        $approvalParam = ParameterSetting::where('slug', 'product-stock')->first();

        if (!$approvalParam || !$approvalParam->is_active || empty($products)) {
            return;
        }

        foreach ($products as $product) {
            // 2. OBTENER EL STOCK TOTAL: Sumamos el stock de todos sus SKUs
            $totalStock = $product->skus->sum('product_stock');

            // Determinación del tipo de alerta
            $notificationSlug = $this->determineNotificationSlug($product, $totalStock);

            if ($notificationSlug) {
                $notificationData = [
                    'product_id'    => $product->id,
                    'product_name'  => $product->product_name,
                    'skus'          => $product->skus->pluck('sku')->toArray(),
                    'current_stock' => $totalStock,
                    'min_stock'     => $product->min_stock,
                    'max_stock'     => $product->max_stock,
                    'observation'   => $notes ?? '',
                    'updated_by'    => $userId,
                ];

                app(DoubleApprovalService::class)->sendStockAlertNotification(
                    $notificationSlug,
                    $notificationData
                );
            }
        }
    }

    /**
     * Lógica de negocio para determinar el slug de notificación.
     */
    private function determineNotificationSlug($product, $totalStock): ?string
    {
        if ($product->max_stock > 0 && $totalStock > $product->max_stock) {
            return 'overstock_alert';
        }

        if ($totalStock < $product->min_stock) {
            return 'low_stock_alert';
        }

        return null;
    }

    /**
     * Retorna el estado badge de un lote para la vista.
     * Retorna array con [key, label, badge_class]
     */
    public function getLotStatusBadge(ProductLot $lot): array
    {
        $status = $lot->status;

        $map = [
            'vigente'    => ['key' => 'vigente',    'label' => __('inventoryentry::inventory.status_valid'),    'class' => 'badge_1'],
            'por_vencer' => ['key' => 'por_vencer', 'label' => __('inventoryentry::inventory.status_expiring'), 'class' => 'badge_3'],
            'vencido'    => ['key' => 'vencido',    'label' => __('inventoryentry::inventory.status_expired'),  'class' => 'badge_2'],
        ];

        return $map[$status] ?? $map['vigente'];
    }

    /**
     * Valida si un ingreso puede ser modificado/eliminado.
     */
    public function ensureCanMutate(InventoryEntry $entry): void
    {
        $hasTransfers = CostCenterInventoryMovement::where('reference_type', 'transfer')
            ->where('product_sku_id', $entry->product_sku_id)
            ->where('lot_id', $entry->lot_id)
            ->exists();

        if ($hasTransfers) {
            throw new InventoryEntryTransferUsedException(__('inventoryentry::inventory.cannot_edit_transfer_used'));
        }

        $mainLot = CostCenterInventoryLot::where('location_type', 'main')
            ->whereNull('location_id')
            ->where('product_sku_id', $entry->product_sku_id)
            ->where('lot_id', $entry->lot_id)
            ->first();

        if (!$mainLot || $mainLot->qty < $entry->quantity) {
            throw new InventoryEntryNotInMainWarehouseException(__('inventoryentry::inventory.cannot_edit_not_in_main'));
        }

        $centerHasStock = CostCenterInventoryLot::where('location_type', 'cost_center')
            ->where('product_sku_id', $entry->product_sku_id)
            ->where('lot_id', $entry->lot_id)
            ->where('qty', '>', 0)
            ->exists();

        if ($centerHasStock) {
            throw new InventoryEntryNotInMainWarehouseException(__('inventoryentry::inventory.cannot_edit_not_in_main'));
        }
    }

    public function updateEntry(InventoryEntry $entry, array $data, string $auditNotes, array $meta = []): InventoryEntry
    {
        return DB::transaction(function () use ($entry, $data, $auditNotes, $meta) {
            $this->ensureCanMutate($entry);

            $beforePayload = $this->buildPayload($entry);

            $newQty = (float) $data['quantity'];
            $oldQty = (float) $entry->quantity;
            $delta = $newQty - $oldQty;

            $mainLot = CostCenterInventoryLot::where('location_type', 'main')
                ->whereNull('location_id')
                ->where('product_sku_id', $entry->product_sku_id)
                ->where('lot_id', $entry->lot_id)
                ->lockForUpdate()
                ->first();

            $sku = ProductSku::where('id', $entry->product_sku_id)->lockForUpdate()->first();
            $sellerSku = SellerProductSKU::where('product_sku_id', $entry->product_sku_id)
                ->where('user_id', 1)
                ->lockForUpdate()
                ->first();

            if ($delta < 0) {
                $reduceBy = abs($delta);
                if (!$mainLot || $mainLot->qty < $reduceBy || !$sku || $sku->product_stock < $reduceBy || !$sellerSku || $sellerSku->product_stock < $reduceBy) {
                    throw new InventoryEntryInsufficientStockException(__('inventoryentry::inventory.insufficient_stock_for_update'));
                }
                $mainLot->decrement('qty', $reduceBy);
                $sku->decrement('product_stock', $reduceBy);
                $sellerSku->decrement('product_stock', $reduceBy);
            } elseif ($delta > 0) {
                if (!$mainLot || !$sku || !$sellerSku) {
                    throw new InventoryEntryDataConsistencyException(__('inventoryentry::inventory.no_stock_data_for_update'));
                }
                $mainLot?->increment('qty', $delta);
                $sku?->increment('product_stock', $delta);
                $sellerSku?->increment('product_stock', $delta);
            }

            $entry->update([
                'quantity' => $newQty,
                'unit_cost' => $data['unit_cost'] ?? null,
                'supplier' => $data['supplier'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $entry->lot?->update([
                'manufacture_date' => $data['manufacture_date'] ?? null,
                'expiration_date' => $data['expiration_date'] ?? null,
            ]);

            $entry->refresh();
            $entry->load('lot');

            InventoryEntryAudit::create([
                'entry_id' => $entry->id,
                'action' => 'modified',
                'notes' => $auditNotes,
                'responsible_id' => $meta['responsible_id'] ?? null,
                'ip_address' => $meta['ip_address'] ?? null,
                'user_agent' => $meta['user_agent'] ?? null,
                'before_payload' => $beforePayload,
                'after_payload' => $this->buildPayload($entry),
            ]);

            return $entry;
        });
    }

    public function deleteEntry(InventoryEntry $entry, string $auditNotes, array $meta = []): void
    {
        DB::transaction(function () use ($entry, $auditNotes, $meta) {
            $this->ensureCanMutate($entry);

            $beforePayload = $this->buildPayload($entry);
            $qty = (float) $entry->quantity;

            $mainLot = CostCenterInventoryLot::where('location_type', 'main')
                ->whereNull('location_id')
                ->where('product_sku_id', $entry->product_sku_id)
                ->where('lot_id', $entry->lot_id)
                ->lockForUpdate()
                ->first();

            $sku = ProductSku::where('id', $entry->product_sku_id)->lockForUpdate()->first();
            $sellerSku = SellerProductSKU::where('product_sku_id', $entry->product_sku_id)
                ->where('user_id', 1)
                ->lockForUpdate()
                ->first();

            if (!$mainLot || $mainLot->qty < $qty || !$sku || $sku->product_stock < $qty || !$sellerSku || $sellerSku->product_stock < $qty) {
                throw new InventoryEntryInsufficientStockException(__('inventoryentry::inventory.insufficient_stock_for_delete'));
            }

            $mainLot->decrement('qty', $qty);
            $sku->decrement('product_stock', $qty);
            $sellerSku->decrement('product_stock', $qty);

            $entry->delete();

            InventoryEntryAudit::create([
                'entry_id' => $entry->id,
                'action' => 'deleted',
                'notes' => $auditNotes,
                'responsible_id' => $meta['responsible_id'] ?? null,
                'ip_address' => $meta['ip_address'] ?? null,
                'user_agent' => $meta['user_agent'] ?? null,
                'before_payload' => $beforePayload,
                'after_payload' => null,
            ]);
        });
    }

    private function buildPayload(InventoryEntry $entry): array
    {
        $lot = $entry->lot;

        return [
            'entry' => [
                'id' => $entry->id,
                'lot_id' => $entry->lot_id,
                'product_sku_id' => $entry->product_sku_id,
                'quantity' => $entry->quantity,
                'unit_cost' => $entry->unit_cost,
                'warehouse_location' => $entry->warehouse_location,
                'supplier' => $entry->supplier,
                'notes' => $entry->notes,
                'created_by' => $entry->created_by,
                'created_at' => optional($entry->created_at)->toDateTimeString(),
                'updated_at' => optional($entry->updated_at)->toDateTimeString(),
                'deleted_at' => optional($entry->deleted_at)->toDateTimeString(),
            ],
            'lot' => [
                'id' => $lot?->id,
                'lot_number' => $lot?->lot_number,
                'manufacture_date' => $lot?->manufacture_date?->format('Y-m-d'),
                'expiration_date' => $lot?->expiration_date?->format('Y-m-d'),
            ],
        ];
    }
}
