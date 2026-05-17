<?php

namespace Modules\CycleClosure\Repositories;

use Modules\CycleClosure\Entities\CycleSetting;

class CycleSettingRepository
{
    public function getActive(): ?CycleSetting
    {
        return CycleSetting::active()->latest()->first();
    }

    public function create(array $data): CycleSetting
    {
        return CycleSetting::create($data);
    }

    public function supersedeCurrent(): void
    {
        CycleSetting::active()->update(['is_active' => false]);
    }

    public function findById(int $id): ?CycleSetting
    {
        return CycleSetting::find($id);
    }

    public function allForTable()
    {
        return CycleSetting::with(['approver', 'executor', 'configurator'])
            ->latest()
            ->get();
    }
}
