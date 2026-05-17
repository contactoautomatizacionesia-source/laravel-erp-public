<?php

namespace Modules\GeneralSetting\Services;

use Illuminate\Support\Collection;
use Modules\GeneralSetting\Entities\ParameterSetting;
use Modules\GeneralSetting\Repositories\ParameterSettingRepository;

class ParameterSettingService
{
    protected $repository;

    public function __construct(ParameterSettingRepository $repository) {
        $this->repository = $repository;
    }

    public function getAll(): Collection {
        return $this->repository->getAll();
    }

    public function create(array $data) : ParameterSetting {
        return $this->repository->create($data);
    }

    public function findById(int $id): ParameterSetting {
        return $this->repository->findById($id);
    }

    public function store(array $data): ParameterSetting {
        return $this->repository->create($data);
    }

    public function update(array $data, int $id): ParameterSetting {
        return $this->repository->update($data, $id);
    }

    public function destroy(int $id): bool {
        return $this->repository->delete($id);
    }
}
