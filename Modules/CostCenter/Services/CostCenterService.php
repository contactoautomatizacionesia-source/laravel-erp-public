<?php

namespace Modules\CostCenter\Services;

use Illuminate\Support\Facades\DB;
use Modules\CostCenter\Actions\SyncPickupLocation;
use Modules\CostCenter\Entities\CostCenter;
use Modules\CostCenter\Exceptions\CannotDeleteWithInventoryException;
use Modules\CostCenter\Exceptions\DefaultCostCenterException;
use Modules\CostCenter\Repositories\CostCenterRepository;

class CostCenterService
{
    protected $repository;

    protected $syncPickupLocation;

    public function __construct(CostCenterRepository $repository, SyncPickupLocation $syncPickupLocation)
    {
        $this->repository = $repository;
        $this->syncPickupLocation = $syncPickupLocation;
    }

    public function store(array $data)
    {
        $data = $this->normalizeFlags($data);

        if (empty($data['code'])) {
            $data['code'] = $this->generateCode();
        }

        $this->ensureDefaultCanBeAssigned($data);

        return DB::transaction(function () use ($data) {
            if (!empty($data['is_default'])) {
                $this->repository->clearDefaultFlag();
            }

            $costCenter = $this->repository->create($data);
            $this->syncPickupLocation->execute($costCenter);

            return $costCenter;
        });
    }

    private function generateCode()
    {
        $last = CostCenter::withTrashed()
            ->where('code', 'LIKE', 'CC%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$last) {
            return 'CC0001';
        }

        $lastCode = $last->code;

        if (preg_match('/CC(\d+)/', $lastCode, $matches)) {
            $number = (int) $matches[1];
        } else {
            $number = CostCenter::withTrashed()->max('id') ?? 1;
        }

        $newNumber = $number + 1;

        return 'CC' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function update($id, array $data)
    {
        $costCenter = $this->repository->findById($id);
        $data = $this->normalizeFlags($data);

        if ($this->isTryingToDeactivateDefault($costCenter, $data)) {
            throw new DefaultCostCenterException(__('cost_center.cannot_inactivate_default_cost_center'));
        }

        if ($this->isTryingToUnsetCurrentDefault($costCenter, $data)) {
            throw new DefaultCostCenterException(__('cost_center.cannot_unset_default_cost_center'));
        }

        $this->ensureDefaultCanBeAssigned($data, $costCenter);

        if (
            isset($data['status'])
            && $data['status'] == 0
            && $costCenter->status != 0
            && $costCenter->inventories()->where('qty', '>', 0)->exists()
        ) {
            throw new CannotDeleteWithInventoryException(__('costcenter::messages.cannot_inactivate_with_inventory'));
        }

        return DB::transaction(function () use ($costCenter, $data) {
            if (!empty($data['is_default'])) {
                $this->repository->clearDefaultFlag($costCenter->id);
            }

            $updatedCostCenter = $this->repository->update($costCenter, $data);
            $this->syncPickupLocation->execute($updatedCostCenter);

            return $updatedCostCenter;
        });
    }

    public function delete($id)
    {
        $costCenter = $this->repository->findById($id);

        if ((int) $costCenter->is_default === 1) {
            throw new DefaultCostCenterException(__('cost_center.cannot_delete_default_cost_center'));
        }

        if ($costCenter->inventories()->where('qty', '>', 0)->exists()) {
            throw new CannotDeleteWithInventoryException(__('costcenter::messages.cannot_delete_with_inventory'));
        }

        return DB::transaction(function () use ($costCenter) {
            $this->repository->update($costCenter, ['status' => 0]);
            $this->syncPickupLocation->execute($costCenter->fresh());

            return $this->repository->delete($costCenter);
        });
    }

    public function restore($id)
    {
        $costCenter = $this->repository->restore($id);

        if ((int) $costCenter->is_default === 1) {
            $shouldClearDefault = (int) $costCenter->status !== 1
                || $this->repository->activeDefaultExists($costCenter->id);

            if ($shouldClearDefault) {
                $costCenter = $this->repository->update($costCenter, ['is_default' => 0]);
            }
        }

        return $costCenter;
    }

    public function setDefault(int $id, bool $isDefault): CostCenter
    {
        $costCenter = $this->repository->findById($id);

        if (!$isDefault && (int) $costCenter->is_default === 1) {
            throw new DefaultCostCenterException(__('cost_center.cannot_unset_default_cost_center'));
        }

        if (!$isDefault) {
            return $costCenter;
        }

        if ((int) $costCenter->status !== 1) {
            throw new DefaultCostCenterException(__('cost_center.default_cost_center_must_be_active'));
        }

        return DB::transaction(function () use ($costCenter) {
            $this->repository->clearDefaultFlag($costCenter->id);

            return $this->repository->update($costCenter, ['is_default' => 1]);
        });
    }

    private function normalizeFlags(array $data): array
    {
        foreach (['status', 'is_default'] as $flag) {
            if (array_key_exists($flag, $data)) {
                $data[$flag] = (int) ((bool) $data[$flag]);
            }
        }

        return $data;
    }

    private function ensureDefaultCanBeAssigned(array $data, ?CostCenter $costCenter = null): void
    {
        $status = array_key_exists('status', $data)
            ? (int) $data['status']
            : (int) ($costCenter->status ?? 1);

        $isDefault = array_key_exists('is_default', $data)
            ? (int) $data['is_default']
            : (int) ($costCenter->is_default ?? 0);

        if ($isDefault === 1 && $status !== 1) {
            throw new DefaultCostCenterException(__('cost_center.default_cost_center_must_be_active'));
        }
    }

    private function isTryingToDeactivateDefault(CostCenter $costCenter, array $data): bool
    {
        return isset($data['status'])
            && (int) $data['status'] === 0
            && (int) $costCenter->status === 1
            && (int) $costCenter->is_default === 1;
    }

    private function isTryingToUnsetCurrentDefault(CostCenter $costCenter, array $data): bool
    {
        return array_key_exists('is_default', $data)
            && (int) $data['is_default'] === 0
            && (int) $costCenter->is_default === 1;
    }
}
