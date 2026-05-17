<?php

namespace Modules\CostCenter\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use App\Services\DoubleApprovalService;
use Modules\CostCenter\Events\TransferDiscrepancyCreated;
use Modules\GeneralSetting\Entities\ParameterSetting;
use Modules\CostCenter\Entities\CostCenterInventory;
use Modules\CostCenter\Entities\CostCenterProductAlert;
use Modules\CostCenter\Repositories\CostCenterInventoryRepository;
use Modules\CostCenter\Exceptions\InventoryTransferException; // <-- 1. Importamos la nueva excepción
use Modules\Shipping\Entities\Carrier;
use Exception;

class CostCenterInventoryService // NOSONAR
{
    protected $repo;

    public function __construct(CostCenterInventoryRepository $repo)
    {
        $this->repo = $repo;
    }

    protected function getMainWarehouseSellerId()
    {
        return config('costcenter.main_warehouse.seller_id', 1);
    }

    protected function normalizeTransferMeta(array $transferMeta): array
    {
        return [
            'movement_type_id' => $transferMeta['movement_type_id'] ?? null,
            'reason'           => $transferMeta['reason'] ?? null,
            'created_by'       => $transferMeta['created_by'] ?? null,
            'dispatched_by'    => $transferMeta['dispatched_by'] ?? null,
            'received_by'      => $transferMeta['received_by'] ?? null,
            'shipping_guide'   => $transferMeta['shipping_guide'] ?? null,
            'carrier_id'       => $transferMeta['carrier_id'] ?? null,
            'guide_date'       => $transferMeta['guide_date'] ?? null,
        ];
    }

    public function getCarriers()
    {
        return Carrier::where('status', 1)->get(['id', 'name']);
    }

    public function getMainWarehouseSkus($searchTerm = null)
    {
        return $this->repo->getMainWarehouseSkus($this->getMainWarehouseSellerId(), $searchTerm);
    }

    public function getTransferFormData()
    {
        return [
            'costCenters' => $this->repo->getActiveCostCenters(),
            'movementTypes' => $this->repo->getActiveMovementTypes()
        ];
    }

    public function getDatatablesQuery()
    {
        return $this->repo->getDatatablesQuery();
    }

    public function getCenterSkus($centerId)
    {
        return $this->repo->getCenterSkusWithStock($centerId);
    }

    public function getLocationLots(string $locationType, $locationId, int $skuId)
    {
        return $this->repo->getLocationLots($locationType, $locationId, $skuId);
    }

    public function getCenterProductsQuery($centerId)
    {
        return $this->repo->getCenterProductsQuery($centerId);
    }

    public function updateProductAlert($centerId, $productId, $minStock, $maxStock)
    {
        return $this->repo->upsertProductAlert($centerId, $productId, $minStock, $maxStock);
    }

    public function transferFromMainToCenter($centerId, $items, array $transferMeta = [])
    {
        $meta = $this->normalizeTransferMeta($transferMeta);

        try {
            return DB::transaction(function () use ($centerId, $items, $meta) {
                $center = $this->repo->findCenterById($centerId);
                $processedProducts = [];

                // Crear Cabecera usando el método universal
                $headerData = $this->prepareHeaderData(null, $centerId, 'main', 'cost_center', $items, $meta);
                $transferHeader = $this->repo->createTransferHeader($headerData);

                foreach ($items as $item) {
                    // Procesar salida de Bodega Principal
                    $product = $this->processMainToCenterItem($centerId, $transferHeader, $item, $meta);
                    
                    if ($product) {
                        $processedProducts[$product->id] = $product;
                    }
                }

                // Alerta de Stock Bajo para Bodega Principal
                $this->checkStockAlerts(
                    ['type' => 'main', 'id' => null, 'name' => 'Bodega Principal'],
                    $processedProducts,
                    $meta['created_by'] ?? auth()->id());

                Log::info("Transferencia exitosa: Main -> {$center->name} (Header ID: {$transferHeader->id})");

                return [
                    'success' => true,
                    'message' => __('costcenter::messages.transfer_successful', ['from' => __('costcenter::main_warehouse.name'), 'to' => $center->name]),
                    'transfer_id' => $transferHeader->id
                ];
            });
        } catch (Exception $e) {
            Log::error("Error transferencia: " . $e->getMessage());
            return ['success' => false, 'message' => __('costcenter::messages.error') . ': ' . $e->getMessage()];
        }
    }

    public function returnFromCenterToMain($centerId, $items, array $transferMeta = [])
    {
        $meta = $this->normalizeTransferMeta($transferMeta);

        try {
            return DB::transaction(function () use ($centerId, $items, $meta) {
                $center = $this->repo->findCenterById($centerId);
                $processedProducts = []; // Para evaluación de alertas

                // CREAR LA CABECERA EN ESTADO "DISPATCHED"
                $transferHeader = $this->repo->createTransferHeader(
                    $this->prepareHeaderData($centerId, null, 'cost_center', 'main', $items, $meta)
                );

                foreach ($items as $item) {
                    $product = $this->processTransferItem($center, $centerId, $transferHeader, $item, $meta);
                    
                    if ($product) {
                        // Guardamos el objeto producto para evaluarlo al final de la transacción
                        $processedProducts[$product->id] = $product;
                    }
                }

                // LLAMADA A LA SUBFUNCIÓN REUTILIZABLE (Evaluación de alertas en el Centro de Costo)
                $this->checkStockAlerts(
                    ['type' => 'cost_center', 'id' => $centerId, 'name' => $center->name],
                    $processedProducts,
                    $meta['created_by'] ?? auth()->id()
                );

                Log::info("Devolución despachada: {$center->name} -> Main (Header ID: {$transferHeader->id})");

                return [
                    'success'     => true,
                    'message'     => 'Devolución despachada correctamente. La mercancía está en tránsito hacia bodega principal.',
                    'transfer_id' => $transferHeader->id
                ];
            });
        } catch (Exception $e) {
            Log::error("Error devolución: " . $e->getMessage());
            return ['success' => false, 'message' => __('costcenter::messages.error') . ': ' . $e->getMessage()];
        }
    }

    /**
     * Prepara los datos estructurados para la creación de la cabecera de transferencia.
     * Extrae la lógica de cálculos (sum, count) del flujo principal.
     */
    private function prepareHeaderData($sourceId, $destinationId, string $sourceType, string $destinationType, array $items, array $meta): array
    {
        return [
            'movement_type_id' => $meta['movement_type_id'],
            'shipping_guide'   => $meta['shipping_guide'],
            'carrier_id'       => $meta['carrier_id'],
            'guide_date'       => $meta['guide_date'],
            'source_type'      => $sourceType,
            'source_id'        => $sourceId,
            'destination_type' => $destinationType,
            'destination_id'   => $destinationId,
            'total_products'   => count($items),
            'total_qty'        => collect($items)->sum('qty'),
            'reason'           => $meta['reason'],
            'dispatched_by'    => $meta['dispatched_by'],
            'received_by'      => $meta['received_by'],
            'created_by'       => $meta['created_by'] ?? auth()->id(),
            'status'           => 'dispatched',
            'dispatched_at'    => now(),
        ];
    }

    /**
     * Procesa la validación, descuento y registro de cada ítem.
     * (Reduce la complejidad cognitiva del bucle principal)
     */
    private function processTransferItem($center, $centerId, $transferHeader, array $item, array $meta)
    {
        $skuId = $item['id'];
        $lotId = $item['lot_id'] ?? null;
        $qty   = $item['qty'];

        $centerInventory = $this->repo->getCenterInventoryWithLock($centerId, $skuId);
        $centerLot       = $this->repo->getLocationLotWithLock('cost_center', $centerId, $skuId, $lotId);

        // Validar que haya stock en el centro de costo de origen
        if (!$centerInventory || $centerInventory->qty < $qty || !$centerLot || $centerLot->qty < $qty) {
            throw new InventoryTransferException(__('costcenter::messages.insufficient_stock_in_center', ['center' => $center->name, 'available' => $centerInventory->qty ?? 0, 'requested' => $qty]));
        }

        // Validación de SKU en bodega principal
        $this->validateMainWarehouseSku($skuId);

        // Descuentos y Registros
        $this->repo->deductCenterStock($centerInventory, $qty);
        $this->repo->deductLocationLotStock($centerLot, $qty);
        // REGISTRAMOS EL ÍTEM EN TRÁNSITO
        $this->repo->createTransferItem([
            'transfer_id'    => $transferHeader->id,
            'product_sku_id' => $skuId,
            'lot_id'         => $lotId,
            'dispatched_qty' => $qty,
        ]);

        $this->registerKardexMovement(
            ['source_type' => 'cost_center', 'source_id' => $centerId, 'destination_id' => null],
            ['sku_id' => $skuId, 'lot_id' => $lotId, 'qty' => $qty],
            $transferHeader,
            $meta
        );

        // Retorno del producto para alertas
        if (!$centerInventory->relationLoaded('productSku')) {
            $centerInventory->load('productSku.product');
        }

        return $centerInventory->productSku->product ?? null;
    }

    /**
     * Procesa la salida de bodega principal por cada ítem.
     */
    private function processMainToCenterItem($centerId, $transferHeader, array $item, array $meta)
    {
        $skuId = $item['id'];
        $lotId = $item['lot_id'] ?? null;
        $qty   = $item['qty'];

        $mainSku = $this->repo->getMainSkuWithLock($this->getMainWarehouseSellerId(), $skuId);
        $mainLot = $this->repo->getLocationLotWithLock('main', null, $skuId, $lotId);

        // Validación.
        $this->validateMainWarehouseStock($mainSku, $mainLot, $skuId, $qty);

        // Descuentos (Salida de Bodega Principal)
        $this->repo->deductLocationLotStock($mainLot, $qty);
        $mainSku->decrement('product_stock', $qty);
        $mainSku->sku->decrement('product_stock', $qty);

        // Registro de ítem y Kardex
        $this->repo->createTransferItem([
            'transfer_id'    => $transferHeader->id,
            'product_sku_id' => $skuId,
            'lot_id'         => $lotId,
            'dispatched_qty' => $qty,
        ]);

        // Reutilizamos el registrador de Kardex
        $this->registerKardexMovement(
            ['source_type' => 'main', 'source_id' => null, 'destination_id' => $centerId],
            ['sku_id' => $skuId, 'lot_id' => $lotId, 'qty' => $qty],
            $transferHeader,
            $meta
        );

        return $mainSku->sku->product ?? null;
    }

    /**
    * Encapsula la creación del movimiento de Kardex.
    * Registra el movimiento de Kardex agrupando parámetros para cumplir con SonarQube.
    *
    * @param array $locations [source_type, source_id, destination_id]
    * @param array $productData [sku_id, lot_id, qty]
    * @param object $header Registro de la cabecera de transferencia
    * @param array $meta Metadatos adicionales (reason, created_by, etc.)
    */
    private function registerKardexMovement(array $locations, array $productData, $transferHeader, array $meta)
    {
        // MOVIMIENTO DE KARDEX: SOLO SALIDA (Destino temporal: transit)
        $this->repo->createMovement([
            'movement_type_id' => $meta['movement_type_id'],
            'source_type'      => $locations['source_type'],
            'source_id'        => $locations['source_id'],
            'destination_type' => 'transit', // Queda en tránsito
            'destination_id'   => $locations['destination_id'],  // Bodega principal no tiene ID
            'product_sku_id'   => $productData['sku_id'],
            'lot_id'           => $productData['lot_id'],
            'qty'              => $productData['qty'],
            'reason'           => $meta['reason'],
            'reference_type'   => 'transfer_dispatch',
            'reference_id'     => $transferHeader->id,
            'reference_code'   => $transferHeader->reference_code ?? null,
            'created_by'       => $meta['created_by'] ?? auth()->id(),
        ]);
    }

    private function validateMainWarehouseSku($skuId): void
    {
        $mainSku = $this->repo->getMainSkuWithLock($this->getMainWarehouseSellerId(), $skuId);
        
        if (!$mainSku) {
            throw new InventoryTransferException(__('costcenter::messages.sku_not_found_for_return', ['warehouse' => __('costcenter::main_warehouse.name')]));
        }

        if (!$mainSku->sku) {
            throw new InventoryTransferException("SKU ID {$skuId} no tiene registro en product_sku.");
        }
    }

    public function transferBetweenCenters($fromCenterId, $toCenterId, $items, array $transferMeta = [])
    {
        $meta = $this->normalizeTransferMeta($transferMeta);

        try {
            return DB::transaction(function () use ($fromCenterId, $toCenterId, $items, $meta) {
                $fromCenter = $this->repo->findCenterById($fromCenterId);
                $toCenter = $this->repo->findCenterById($toCenterId);
                $processedProducts = [];
                // CREAR LA CABECERA EN ESTADO "DISPATCHED"
                $headerData = $this->prepareHeaderData($fromCenterId, $toCenterId, 'cost_center', 'cost_center', $items, $meta);
                $transferHeader = $this->repo->createTransferHeader($headerData);

                foreach ($items as $item) {
                    $skuId = $item['id'];
                    $lotId = $item['lot_id'] ?? null;
                    $qty = $item['qty'];

                    $fromInventory = $this->repo->getCenterInventoryWithLock($fromCenterId, $skuId);
                    $fromLot = $this->repo->getLocationLotWithLock('cost_center', $fromCenterId, $skuId, $lotId);

                    // Validar que haya stock en el centro de origen
                    if (!$fromInventory || $fromInventory->qty < $qty || !$fromLot || $fromLot->qty < $qty) {
                        throw new InventoryTransferException(__('costcenter::messages.insufficient_stock_in_center', ['center' => $fromCenter->name, 'available' => $fromInventory->qty ?? 0, 'requested' => $qty]));
                    }

                    // 1. SOLO DESCONTAMOS DEL ORIGEN
                    $this->repo->deductCenterStock($fromInventory, $qty);
                    $this->repo->deductLocationLotStock($fromLot, $qty);

                    // 2. REGISTRAMOS EL ÍTEM EN TRÁNSITO
                    $this->repo->createTransferItem([
                        'transfer_id'    => $transferHeader->id,
                        'product_sku_id' => $skuId,
                        'lot_id'         => $lotId,
                        'dispatched_qty' => $qty,
                    ]);

                    // 3. Reutilización: Registro de Kardex Dinámico (Source: CenterId, Destination: toCenterId)
                    // MOVIMIENTO DE KARDEX: SOLO SALIDA (Destino temporal: transit)
                    $this->registerKardexMovement(
                        ['source_type' => 'cost_center', 'source_id' => $fromCenterId, 'destination_id' => $toCenterId],
                        ['sku_id' => $skuId, 'lot_id' => $lotId, 'qty' => $qty],
                        $transferHeader,
                        $meta
                    );

                    // Carga de relación para alertas
                    if (!$fromInventory->relationLoaded('productSku')) {
                        $fromInventory->load('productSku.product');
                    }

                    $product = $fromInventory->productSku->product ?? null;
                    if ($product) {
                        $processedProducts[$product->id] = $product;
                    }

                }

                $this->checkStockAlerts(
                    ['type' => 'cost_center', 'id' => $fromCenterId, 'name' => $fromCenter->name],
                    $processedProducts,
                    $meta['created_by'] ?? auth()->id()
                );

                Log::info("Transferencia despachada: {$fromCenter->name} -> {$toCenter->name} (Header ID: {$transferHeader->id})");

                return [
                    'success'     => true,
                    'message'     => "Transferencia despachada correctamente. La mercancía está en tránsito hacia {$toCenter->name}.",
                    'transfer_id' => $transferHeader->id
                ];
            });
        } catch (Exception $e) {
            Log::error("Error transferencia entre centros: " . $e->getMessage());
            return ['success' => false, 'message' => __('costcenter::messages.error') . ': ' . $e->getMessage()];
        }
    }

    /**
     * Evalúa los niveles de stock en un Centro de Costo específico y dispara notificaciones.
     * Reutilizable para cualquier flujo que descuente stock de un punto de venta.
     */
    private function checkStockAlerts(array $locationData, array $products, int $userId): void
    {
        $approvalParam = ParameterSetting::where('slug', 'product-stock')->first();
        
        // Si el parámetro de alertas no está activo, retornamos temprano (SonarQube: Clean Code)
        if (!$approvalParam || !$approvalParam->is_active || empty($products)) {
            return;
        }

        foreach ($products as $product) {
            // 1. Obtener límites y stock actual según el tipo de origen
            $stockStatus = $this->getStockStatusByLocation($locationData, $product);

            // 2. Si no hay límites definidos o el stock es suficiente, saltar
            if (!$stockStatus || $stockStatus['current'] >= $stockStatus['min']) {
                continue;
            }

            // 3. Disparar notificación
            $this->dispatchLowStockNotification($product, $stockStatus, $locationData, $userId);
        }
    }

    /**
     * Determina los límites y el stock actual basado en la ubicación.
     */
    private function getStockStatusByLocation(array $location, $product): ?array
    {
        if ($location['type'] === 'main') {
            return [
                'min'     => (float) $product->min_stock,
                'max'     => (float) $product->max_stock,
                'current' => (float) ($product->product_type == 1
                            ? $product->skus->first()->product_stock
                            : $product->skus->sum('product_stock'))
            ];
        }

        // Caso: Centro de Costo
        $alert = CostCenterProductAlert::where('cost_center_id', $location['id'])
            ->where('product_id', $product->id)
            ->first();

        if (!$alert) { return null; }

        $currentInCenter = CostCenterInventory::where('cost_center_id', $location['id'])
            ->whereHas('productSku', function($q) use ($product) {
                $q->where('product_id', $product->id);
            })->sum('qty');

        return [
            'min'     => (float) $alert->min_stock,
            'max'     => (float) $alert->max_stock,
            'current' => (float) $currentInCenter
        ];
    }

    /**
     * Encapsula el envío de la notificación (SonarQube: SRP)
     */
    private function dispatchLowStockNotification($product, array $status, array $location, int $userId): void
    {
        $locationName = $location['name'] ?? 'Bodega Principal';
        
        $notificationData = [
            'product_id'    => $product->id,
            'product_name'  => $product->product_name,
            'skus'          => $product->skus->pluck('sku')->toArray(),
            'current_stock' => $status['current'],
            'min_stock'     => $status['min'],
            'max_stock'     => $status['max'],
            'observation'   => "Alerta de stock bajo generada en {$locationName}.",
            'updated_by'    => $userId,
        ];

        app(DoubleApprovalService::class)->sendStockAlertNotification(
            'low_stock_alert',
            $notificationData
        );
    }

    /**
     * Valida el stock y la existencia de los SKUs en la bodega principal
     * para reducir la complejidad cognitiva.
     */
    private function validateMainWarehouseStock($mainSku, $mainLot, $skuId, $qty)
    {
        if (!$mainSku) {
            throw new InventoryTransferException(__('costcenter::messages.sku_not_found_in_main_warehouse') . " (SKU ID: {$skuId})");
        }
        if (!$mainLot || $mainLot->qty < $qty) {
            throw new InventoryTransferException(__('costcenter::messages.insufficient_stock') . " SKU ID {$skuId}.");
        }
        if ($mainSku->product_stock < $qty) {
            throw new InventoryTransferException(__('costcenter::messages.insufficient_stock') . " SKU ID {$skuId}.");
        }
        if (!$mainSku->sku) {
            throw new InventoryTransferException("SKU ID {$skuId} no tiene registro en product_sku.");
        }
    }

    /**
     * Procesa la recepción de una transferencia en el destino.
     */
    public function receiveTransfer($transferId, array $payload, $userId = null)
    {
        return DB::transaction(function () use ($transferId, $payload, $userId) {
            $transfer = $this->repo->findTransferById($transferId);

            if ($transfer->status !== 'dispatched') {
                throw new InventoryTransferException("Esta transferencia ya fue recibida o no se encuentra en tránsito.");
            }

            $hasDiscrepancies = false;
            $items = $this->repo->getTransferItems($transferId);
            $receivedItemsData = collect($payload['items'])->keyBy('transfer_item_id');

            foreach ($items as $item) {
                $data = $receivedItemsData->get($item->id);
                if (!$data) {
                    continue;
                }

                // Delegamos el procesamiento individual a un método privado
                $discrepancyCreated = $this->processReceivedItem($transfer, $item, $data, $userId);

                if ($discrepancyCreated) {
                    $hasDiscrepancies = true;
                }
            }

            // MARCAMOS LA TRANSFERENCIA COMO CERRADA
            $transfer->update([
                'status'          => $hasDiscrepancies ? 'received_with_discrepancies' : 'received',
                'received_at'     => now(),
                'reception_notes' => $payload['reception_notes'] ?? null,
                'received_by'     => $userId ?? auth()->id(),
            ]);

            return [
                'success' => true,
                'message' => 'Inventario recibido y procesado correctamente.',
                'has_discrepancies' => $hasDiscrepancies
            ];
        });
    }

    /**
     * Procesa un ítem individual de la transferencia y determina si hubo novedad.
     * Retorna TRUE si se registró una discrepancia.
     */
    private function processReceivedItem($transfer, $item, array $data, $userId = null): bool
    {
        $receivedQty = (float) $data['received_qty'];

        if ($receivedQty > $item->dispatched_qty) {
            throw new InventoryTransferException("No puedes recibir una cantidad mayor a la despachada en el SKU ID {$item->product_sku_id}.");
        }

        // 1. Actualizamos la cantidad recibida
        $this->repo->updateTransferItem($item->id, ['received_qty' => $receivedQty]);

        // 2. Ingresamos stock y movimiento de Kardex si se recibió al menos 1 unidad
        if ($receivedQty > 0) {
            $this->addReceivedStock($transfer, $item, $receivedQty);
            $this->recordReceptionMovement($transfer, $item, $receivedQty, $userId);
        }

        // 3. Registramos la novedad si hay faltante
        if ($receivedQty < $item->dispatched_qty) {
            $this->recordDiscrepancy($item, $data, $receivedQty);
            return true;
        }

        return false;
    }

    /**
     * Agrega el stock recibido al destino correspondiente (Main o Centro de Costo).
     */
    private function addReceivedStock($transfer, $item, float $receivedQty): void
    {
        if ($transfer->destination_type === 'cost_center') {
            $this->repo->addCenterStock($transfer->destination_id, $item->product_sku_id, $receivedQty);
            $this->repo->addLocationLotStock('cost_center', $transfer->destination_id, $item->product_sku_id, $item->lot_id, $receivedQty);
        } elseif ($transfer->destination_type === 'main') {
            $mainSku = $this->repo->getMainSkuWithLock($this->getMainWarehouseSellerId(), $item->product_sku_id);
            $mainSku->increment('product_stock', $receivedQty);
            $mainSku->sku->increment('product_stock', $receivedQty);
            $this->repo->addLocationLotStock('main', null, $item->product_sku_id, $item->lot_id, $receivedQty);
        }
    }

    /**
     * Crea el movimiento de entrada en el Kardex para el ítem recibido.
     */
    private function recordReceptionMovement($transfer, $item, float $receivedQty, $userId = null): void
    {
        $this->repo->createMovement([
            'movement_type_id' => $transfer->movement_type_id,
            'source_type'      => 'transit',
            'source_id'        => $transfer->source_id,
            'destination_type' => $transfer->destination_type,
            'destination_id'   => $transfer->destination_id,
            'product_sku_id'   => $item->product_sku_id,
            'lot_id'           => $item->lot_id,
            'qty'              => $receivedQty,
            'reason'           => 'Recepción de transferencia ' . $transfer->reference_code,
            'reference_type'   => 'transfer_receipt',
            'reference_id'     => $transfer->id,
            'reference_code'   => $transfer->reference_code,
            'created_by'       => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Guarda el registro de discrepancia (novedad) justificada con su evidencia.
     */
    private function recordDiscrepancy($item, array $data, float $receivedQty): void
    {
        $diffQty = $item->dispatched_qty - $receivedQty;

        $this->repo->createTransferDiscrepancy([
            'transfer_item_id' => $item->id,
            'novelty_id'       => $data['novelty_id'],
            'difference_qty'   => $diffQty,
            'description'      => $data['description'] ?? null,
            'evidence_path'    => $data['evidence_path'] ?? null,
        ]);

        // Disparar evento para que el módulo Incidents cree la novedad formal
        try {
            $transfer    = $item->transfer;
            $dispatchedBy = $transfer->dispatched_by;

            if ($dispatchedBy === null) {
                Log::warning('[CostCenter] TransferDiscrepancyCreated no disparado: dispatched_by es nulo en la transferencia.', [
                    'transfer_id' => $transfer->id,
                    'item_id'     => $item->id,
                ]);
                return;
            }

            Event::dispatch(new TransferDiscrepancyCreated(
                transfer:       $transfer,
                item:           $item,
                discrepancyQty: $diffQty,
                dispatchedBy:   $dispatchedBy,
            ));
        } catch (\Throwable $e) {
            Log::error('[CostCenter] Error al disparar TransferDiscrepancyCreated: ' . $e->getMessage());
        }
    }
}
