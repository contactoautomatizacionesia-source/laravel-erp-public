<?php
namespace Modules\ScheduleManagement\Services;

use Illuminate\Support\Collection;
use Modules\ScheduleManagement\Entities\RoleWorkSchedule;
use Modules\ScheduleManagement\Repositories\RoleWorkScheduleRepository;

class RoleWorkScheduleService
{
    protected $roleWorkScheduleRepository;

    public function __construct(RoleWorkScheduleRepository  $roleWorkScheduleRepository)
    {
        $this->roleWorkScheduleRepository = $roleWorkScheduleRepository;
    }

    public function getAll(): Collection
    {
        return $this->roleWorkScheduleRepository->getAll();
    }

    public function getById(int $id): RoleWorkSchedule
    {
        return $this->roleWorkScheduleRepository->getById($id);
    }

    public function create(array $data)
    {
        return $this->roleWorkScheduleRepository->create($data);
    }

    public function update(array $data, int $id): bool
    {
        return $this->roleWorkScheduleRepository->update($data, $id);
    }

    public function destroy(int $id): bool
    {
        return $this->roleWorkScheduleRepository->destroy($id);
    }

    public function getAllSchedulesWithAssignment(int $roleId)
    {
        return $this->roleWorkScheduleRepository->getAllSchedulesWithAssignment($roleId);
    }

    public function assignRole(int $roleId, int $scheduleId)
    {
        return $this->roleWorkScheduleRepository->assignRole($roleId, $scheduleId);
    }

    public function unassignScheduleFromRole(int $roleId, string $dayType)
    {
        return $this->roleWorkScheduleRepository->unassignScheduleFromRole($roleId, $dayType);
    }

    public function isRoleAllowedNow(int $roleId): bool
    {
        return $this->roleWorkScheduleRepository->isRoleAllowedNow($roleId);
    }
}
