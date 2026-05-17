<?php

namespace Modules\InventoryCount\Repositories;

use Modules\InventoryCount\Entities\InventoryCountAudit;

class InventoryCountAuditRepository
{
    public function getDatatablesQuery()
    {
        return InventoryCountAudit::with([
            'inventoryCount.costCenter',
            'inventoryCount.user',
            'auditor',
        ])->orderByDesc('created_at');
    }

    public function findById(int $id): InventoryCountAudit
    {
        return InventoryCountAudit::with([
            'inventoryCount.costCenter',
            'inventoryCount.user',
            'inventoryCount.details.product',
            'inventoryCount.details.observationType',
            'inventoryCount.attempts',
            'auditor',
        ])->findOrFail($id);
    }

    public function create(array $data): InventoryCountAudit
    {
        return InventoryCountAudit::create($data);
    }

    public function countIncorrectByUser(int $userId): int
    {
        return InventoryCountAudit::whereHas('inventoryCount', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('status', 'rejected')->count();
    }
}
