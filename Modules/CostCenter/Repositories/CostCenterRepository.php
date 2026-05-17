<?php

namespace Modules\CostCenter\Repositories;

use Modules\CostCenter\Entities\CostCenter;
use App\Models\User;

class CostCenterRepository
{
    public function getActiveQuery()
    {
        return CostCenter::with(['city', 'brand', 'paymentForm'])
            ->orderBy('id', 'desc');
    }

    public function getDeletedQuery()
    {
        return CostCenter::onlyTrashed()
            ->with(['city', 'brand', 'paymentForm'])
            ->orderBy('deleted_at', 'desc');
    }

    public function findById($id, $relations = [])
    {
        return CostCenter::with($relations)->findOrFail($id);
    }

    public function findTrashedById($id)
    {
        return CostCenter::onlyTrashed()->findOrFail($id);
    }

    public function create(array $data)
    {
        $costCenter = CostCenter::create($data);
        if (isset($data['user_ids'])) {
            User::whereIn('id', $data['user_ids'])->update(['cost_center_id' => $costCenter->id]);
        }
        return $costCenter;
    }

    public function update(CostCenter $costCenter, array $data)
    {
        $costCenter->update($data);
        if (isset($data['user_ids'])) {
            $newUserIds = $data['user_ids'] ?? [];

            // Desvincular solo los usuarios que ya no están en la lista
            User::where('cost_center_id', $costCenter->id)
                ->whereNotIn('id', $newUserIds)
                ->update(['cost_center_id' => null]);

            // Vincular solo los usuarios de la nueva lista (sin tocar los que ya lo están)
            if (!empty($newUserIds)) {
                User::whereIn('id', $newUserIds)->update(['cost_center_id' => $costCenter->id]);
            }
        }
        return $costCenter;
    }

    public function delete(CostCenter $costCenter)
    {
        return $costCenter->delete();
    }

    public function restore($id)
    {
        $costCenter = $this->findTrashedById($id);
        $costCenter->restore();
        return $costCenter;
    }

    public function clearDefaultFlag(?int $exceptId = null): void
    {
        CostCenter::withTrashed()
            ->when($exceptId, function ($query) use ($exceptId) {
                $query->where('id', '!=', $exceptId);
            })
            ->where('is_default', 1)
            ->update(['is_default' => 0]);
    }

    public function activeDefaultExists(?int $exceptId = null): bool
    {
        return CostCenter::query()
            ->where('status', 1)
            ->where('is_default', 1)
            ->when($exceptId, function ($query) use ($exceptId) {
                $query->where('id', '!=', $exceptId);
            })
            ->exists();
    }
}
