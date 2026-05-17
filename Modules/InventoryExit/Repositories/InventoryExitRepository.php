<?php

namespace Modules\InventoryExit\Repositories;

use Modules\InventoryExit\Entities\InventoryExitRequest;
use Modules\InventoryExit\Entities\InventoryExitItem;
use Modules\InventoryExit\Entities\InventoryExitDocument;
use Modules\GeneralSetting\Entities\Catalogs\InventoryOutReason;
use Modules\CostCenter\Entities\CostCenter;
use Illuminate\Support\Facades\Storage;

class InventoryExitRepository
{
    // ---------------------------------------------------------------
    // Solicitudes
    // ---------------------------------------------------------------

    public function create(array $data): InventoryExitRequest
    {
        return InventoryExitRequest::create($data);
    }

    public function findOrFail(int $id): InventoryExitRequest
    {
        return InventoryExitRequest::with([
            'exitReason',
            'costCenter',
            'requestedBy',
            'approvedBy',
            'items.productSku.product',
            'items.lot',
            'documents',
        ])->findOrFail($id);
    }

    public function updateStatus(InventoryExitRequest $request, array $data): void
    {
        $request->update($data);
    }

    // ---------------------------------------------------------------
    // Ítems
    // ---------------------------------------------------------------

    public function createItems(int $requestId, array $items): void
    {
        $rows = array_map(fn($item) => array_merge($item, [
            'inventory_exit_request_id' => $requestId,
            'created_at' => now(),
            'updated_at' => now(),
        ]), $items);

        InventoryExitItem::insert($rows);
    }

    // ---------------------------------------------------------------
    // Documentos
    // ---------------------------------------------------------------

    public function storeDocuments(int $requestId, array $files, int $userId): void
    {
        foreach ($files as $file) {
            $path = $file->store('inventory-exit/documents', 'public');

            InventoryExitDocument::create([
                'inventory_exit_request_id' => $requestId,
                'file_path'   => $path,
                'file_name'   => $file->getClientOriginalName(),
                'mime_type'   => $file->getMimeType(),
                'uploaded_by' => $userId,
            ]);
        }
    }

    // ---------------------------------------------------------------
    // Catálogos
    // ---------------------------------------------------------------

    public function getActiveReasons()
    {
        return InventoryOutReason::active()
            ->where('code', '!=', InventoryOutReason::CART_SALE_CODE)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code'])
            ->map(fn($r) => ['id' => $r->id, 'name' => $r->name]);
    }

    public function getCostCenters()
    {
        return CostCenter::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    // ---------------------------------------------------------------
    // DataTable base query
    // ---------------------------------------------------------------

    public function getDataTableQuery()
    {
        return InventoryExitRequest::with([
            'exitReason',
            'costCenter',
            'requestedBy',
            'approvedBy',
            'items.productSku.product',
            'items.lot',
        ])->select('inventory_exit_requests.*');
    }
}
