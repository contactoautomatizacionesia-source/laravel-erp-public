<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateIncidentsMenuAndPermissions extends Migration
{
    public function up()
    {
        // =========================================================
        // 1. PERMISOS
        // =========================================================
        $nextModuleId = (DB::table('permissions')->max('module_id') ?? 0) + 1;

        // Usar MAX(id)+N en lugar de insertGetId() porque la columna `id`
        // de `permissions` puede no tener AUTO_INCREMENT activo en todos los entornos.
        $nextId = (DB::table('permissions')->max('id') ?? 0) + 1;

        $parentPermissionId = $nextId++;  // Permiso raíz del módulo
        $listPermId         = $nextId++;  // Sub-módulo: Lista
        $settingsPermId     = $nextId++;  // Sub-módulo: Configuración

        DB::table('permissions')->insert([
            // Raíz
            [
                'id'          => $parentPermissionId,
                'module_id'   => $nextModuleId,
                'parent_id'   => null,
                'name'        => 'Incidents',
                'translation' => 'incidents::menu.incidents',
                'route'       => 'incidents.index',
                'type'        => 1,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            // Sub-módulos
            [
                'id'          => $listPermId,
                'module_id'   => $nextModuleId,
                'parent_id'   => $parentPermissionId,
                'name'        => 'Incident List',
                'translation' => 'incidents::menu.list',
                'route'       => 'incidents.index',
                'type'        => 2,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => $settingsPermId,
                'module_id'   => $nextModuleId,
                'parent_id'   => $parentPermissionId,
                'name'        => 'Incident Settings',
                'translation' => 'incidents::menu.settings',
                'route'       => 'incidents.settings',
                'type'        => 2,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            // Acciones granulares (type = 3)
            [
                'id'          => $nextId++,
                'module_id'   => $nextModuleId,
                'parent_id'   => $listPermId,
                'name'        => 'Incident View',
                'translation' => 'incidents::menu.view',
                'route'       => 'incidents.show',
                'type'        => 3,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => $nextId++,
                'module_id'   => $nextModuleId,
                'parent_id'   => $listPermId,
                'name'        => 'Incident Resolve',
                'translation' => 'incidents::menu.resolve',
                'route'       => 'incidents.resolve',
                'type'        => 3,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => $nextId++,
                'module_id'   => $nextModuleId,
                'parent_id'   => $listPermId,
                'name'        => 'Incident Void',
                'translation' => 'incidents::menu.void',
                'route'       => 'incidents.void',
                'type'        => 3,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => $nextId++,
                'module_id'   => $nextModuleId,
                'parent_id'   => $settingsPermId,
                'name'        => 'Incident Settings Save',
                'translation' => 'incidents::menu.settings_save',
                'route'       => 'incidents.settings.update',
                'type'        => 3,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        // =========================================================
        // 2. MENÚ LATERAL (backendmenus)
        // =========================================================

        // Mismo padre que InventoryCount: product.product_manage
        $globalParentId = DB::table('backendmenus')
            ->where('name', 'product.product_manage')
            ->value('id');

        if (! $globalParentId) {
            throw new \LogicException('backendmenus: parent "product.product_manage" not found. Cannot insert Incidents menu.');
        }

        $lastPosition = DB::table('backendmenus')
            ->where('parent_id', $globalParentId)
            ->max('position') ?? 0;

        // Menú padre: Novedades
        $parentMenuId = DB::table('backendmenus')->insertGetId([
            'name'       => 'incidents::menu.incidents',
            'icon'       => 'ti-alert-circle',
            'user_id'    => null,
            'parent_id'  => $globalParentId,
            'is_admin'   => 1,
            'is_seller'  => 0,
            'route'      => 'incidents.index',
            'position'   => $lastPosition + 1,
            'module'     => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Menú hijos
        DB::table('backendmenus')->insert([
            [
                'name'       => 'incidents::menu.list',
                'icon'       => 'ti-list',
                'user_id'    => null,
                'parent_id'  => $parentMenuId,
                'is_admin'   => 1,
                'is_seller'  => 0,
                'route'      => 'incidents.index',
                'position'   => 1,
                'module'     => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'incidents::menu.settings',
                'icon'       => 'ti-settings',
                'user_id'    => null,
                'parent_id'  => $parentMenuId,
                'is_admin'   => 1,
                'is_seller'  => 0,
                'route'      => 'incidents.settings',
                'position'   => 2,
                'module'     => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        $moduleId = DB::table('permissions')
            ->where('route', 'incidents.index')
            ->value('module_id');
        if ($moduleId) {
            DB::table('permissions')->where('module_id', $moduleId)->delete();
        }

        DB::table('backendmenus')->whereIn('name', [
            'incidents::menu.incidents',
            'incidents::menu.list',
            'incidents::menu.settings',
        ])->delete();
    }
}
