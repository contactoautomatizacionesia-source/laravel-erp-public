<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CreateSanctionsStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // =========================================================
        // 0. HABILITAR EL MÓDULO Y FORZAR RESINCRONIZACIÓN DEL SIDEBAR
        // =========================================================
        Artisan::call('module:enable', ['module' => 'Sanctions']);

        // Fuerza que todos los usuarios admin reconstruyan su sidebar en el
        // próximo login, garantizando que los nuevos ítems aparezcan correctamente.
        $adminRoleIds = DB::table('roles')->whereIn('type', ['superadmin', 'admin'])->pluck('id');
        $adminUserIds = DB::table('users')->whereIn('role_id', $adminRoleIds)->pluck('id');
        DB::table('backendmenu_users')->whereIn('user_id', $adminUserIds)->delete();


        // =========================================================
        // 1. LÓGICA PARA LA TABLA 'permissions' (Permisos)
        // =========================================================

        $nextModuleId = (DB::table('permissions')->max('module_id') ?? 0) + 1;

        // NOTA: Usamos MAX(id)+N en lugar de insertGetId() porque la columna `id`
        // de `permissions` puede no tener AUTO_INCREMENT activo en todos los entornos.
        $nextId = (DB::table('permissions')->max('id') ?? 0) + 1;

        $parentPermissionId = $nextId++;  // id del permiso raíz

        DB::table('permissions')->insert([
            // A. Permiso Padre
            ['id' => $parentPermissionId, 'module_id' => $nextModuleId, 'parent_id' => null,                'name' => 'Sanctions', 'translation' => 'permission.sanctions', 'route' => 'sanctions.index',          'type' => 1, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            // B. Sub-módulos
            ['id' => $nextId++,           'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId, 'name' => 'Casos Activos',      'translation' => 'sanctions.active_cases',  'route' => 'sanctions.index',          'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $nextId++,           'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId, 'name' => 'Historial de Fallos', 'translation' => 'sanctions.history',       'route' => 'sanctions.history.index',  'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $nextId++,           'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId, 'name' => 'Configuración',       'translation' => 'sanctions.settings',      'route' => 'sanctions.settings.index', 'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);


        // =========================================================
        // 2. LÓGICA PARA LA TABLA 'backendmenus' (Menú Lateral)
        // =========================================================

        // Buscamos el ID de la categoría "Gestión de Usuarios" dinámicamente.
        $globalParentId = DB::table('backendmenus')
            ->where('name', 'common.user_manages')
            ->value('id') ?? 2;

        // A. Menú Padre (sin route — actúa como desplegable, no como link)
        $parentMenuId = DB::table('backendmenus')->insertGetId([
            'name'        => 'sanctions.sanctions',
            'icon'        => 'ti-shield',
            'user_id'     => null,
            'parent_id'   => $globalParentId,
            'is_admin'    => 1,
            'is_seller'   => 0,
            'route'       => null,
            'position'    => 3,
            'module'      => '',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // B. Sub-Menús
        DB::table('backendmenus')->insert([
            [
                'name'        => 'sanctions.active_cases',
                'icon'        => 'ti-alert-circle',
                'user_id'     => null,
                'parent_id'   => $parentMenuId,
                'is_admin'    => 1,
                'is_seller'   => 0,
                'route'       => 'sanctions.index',
                'position'    => 1,
                'module'      => '',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'sanctions.history',
                'icon'        => 'ti-history',
                'user_id'     => null,
                'parent_id'   => $parentMenuId,
                'is_admin'    => 1,
                'is_seller'   => 0,
                'route'       => 'sanctions.history.index',
                'position'    => 2,
                'module'      => '',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'sanctions.settings',
                'icon'        => 'ti-settings',
                'user_id'     => null,
                'parent_id'   => $parentMenuId,
                'is_admin'    => 1,
                'is_seller'   => 0,
                'route'       => 'sanctions.settings.index',
                'position'    => 3,
                'module'      => '',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Limpiamos los permisos
        DB::table('permissions')->whereIn('route', [
            'sanctions.index',
            'sanctions.history.index',
            'sanctions.settings.index',
        ])->delete();

        // Limpiamos los menús usando los nombres
        DB::table('backendmenus')->whereIn('name', [
            'sanctions.sanctions',
            'sanctions.active_cases',
            'sanctions.history',
            'sanctions.settings',
        ])->delete();

        Artisan::call('module:disable', ['module' => 'Sanctions']);
    }
}
