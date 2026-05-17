<?php

namespace Modules\Incidents\Repositories;

use Modules\Incidents\Entities\IncidentSetting;

class IncidentSettingRepository
{
    public function getInstance(): IncidentSetting
    {
        return IncidentSetting::getInstance();
    }

    public function update(array $data, int $userId): IncidentSetting
    {
        $setting = $this->getInstance();
        $setting->update(array_merge($data, [
            'updated_by' => $userId,
            'updated_at' => now(),
        ]));
        return $setting->fresh();
    }
}
