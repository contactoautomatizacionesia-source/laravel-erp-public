<?php

namespace Modules\CycleClosure\Services;

use Modules\CycleClosure\Entities\CycleSetting;
use Modules\CycleClosure\Repositories\CycleSettingRepository;
use Modules\UserActivityLog\Traits\LogActivity;

class CycleSettingService
{
    public CycleSettingRepository $repo;

    public function __construct(CycleSettingRepository $repo)
    {
        $this->repo = $repo;
    }

    public function store(array $data): CycleSetting
    {
        $this->repo->supersedeCurrent();

        $setting = $this->repo->create([
            'period_type'       => $data['period_type'],
            'execution_day'     => $data['execution_day'] ?? null,
            'executor_user_id'  => $data['executor_user_id'],
            'approver_user_id'  => $data['approver_user_id'],
            'configured_by'     => auth()->id(),
            'is_active'         => true,
            'payload'           => $data,
        ]);

        LogActivity::successLog('CycleSetting saved and activated. ID: ' . $setting->id);

        return $setting;
    }

    public function getActive(): ?CycleSetting
    {
        return $this->repo->getActive();
    }

}
