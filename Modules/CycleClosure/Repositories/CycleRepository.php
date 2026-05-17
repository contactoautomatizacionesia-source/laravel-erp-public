<?php

namespace Modules\CycleClosure\Repositories;

use Modules\CycleClosure\Entities\Cycle;

class CycleRepository
{
    public function create(array $data): Cycle
    {
        return Cycle::create($data);
    }

    public function findById(int $id): ?Cycle
    {
        return Cycle::with(['executor', 'coApprover'])->find($id);
    }

    public function findPendingApproval(int $id): ?Cycle
    {
        return Cycle::where('id', $id)
            ->where('status', 'pending_approval')
            ->first();
    }

    public function updateStatus(Cycle $cycle, string $status, array $extra = []): Cycle
    {
        $cycle->update(array_merge(['status' => $status], $extra));
        return $cycle->fresh();
    }

    public function getAll()
    {
        return Cycle::with(['executor', 'coApprover'])->latest();
    }
}
