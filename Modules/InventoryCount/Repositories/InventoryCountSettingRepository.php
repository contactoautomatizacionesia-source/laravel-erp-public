<?php

namespace Modules\InventoryCount\Repositories;

use Modules\InventoryCount\Entities\InventoryCountSetting;
use Modules\RolePermission\Entities\Role;
use App\Models\User;

class InventoryCountSettingRepository
{
    public function getAllWithCostCenter()
    {
        return InventoryCountSetting::with(['costCenter', 'countRole'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByCostCenter(int $costCenterId): ?InventoryCountSetting
    {
        return InventoryCountSetting::where('cost_center_id', $costCenterId)->first();
    }

    public function upsert(int $costCenterId, array $data): InventoryCountSetting
    {
        return InventoryCountSetting::updateOrCreate(
            ['cost_center_id' => $costCenterId],
            $data
        );
    }

    /**
     * Roles disponibles para asignar como encargados del conteo
     * (excluye superadmin, customer, affiliate)
     */
    public function getAvailableRoles()
    {
        return Role::whereNotIn('type', ['superadmin', 'customer', 'affiliate'])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Usuarios administradores disponibles para notificaciones
     */
    public function getAdminUsers()
    {
        return User::where('is_active', 1)
            ->whereHas('role', function ($q) {
                $q->whereIn('type', ['superadmin', 'admin']);
            })
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'username', 'email']);
    }
}
