<?php

namespace Modules\GeneralSetting\Repositories;

use Illuminate\Support\Collection;
use Modules\GeneralSetting\Entities\ParameterSetting;

class ParameterSettingRepository
{
    public function getAll(): Collection {
        return ParameterSetting::all();
    }

    public function findById(int $id): ParameterSetting {
        return ParameterSetting::findOrFail($id);
    }

    public function create(array $data): ParameterSetting {
        return ParameterSetting::create($data);
    }

    public function update(array $data, int $id): ParameterSetting {
        $record = ParameterSetting::findOrFail($id);
        // La auditoría (updated_by) se llena sola gracias al boot del modelo
        $record->update($data);
        return $record;
    }

    public function delete(int $id): bool {
        return ParameterSetting::findOrFail($id)->delete();
    }
}
