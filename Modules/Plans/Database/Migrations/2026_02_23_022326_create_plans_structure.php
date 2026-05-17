<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreatePlansStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
            ['id' => $parentPermissionId, 'module_id' => $nextModuleId, 'parent_id' => null,               'name' => 'Plans or Titles', 'translation' => 'permission.plans_or_titles', 'route' => 'plans_or_titles.index', 'type' => 1, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            // B. Sub-módulos (sin acciones CRUD propias por ahora)
            ['id' => $nextId++,           'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId,'name' => 'Plans',           'translation' => 'plans_or_titles.plans',    'route' => 'plans.index',           'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $nextId++,           'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId,'name' => 'Rules',           'translation' => 'plans_or_titles.rules',    'route' => 'rules.index',           'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $nextId++,           'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId,'name' => 'Benefits',        'translation' => 'plans_or_titles.benefits', 'route' => 'benefits.index',        'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);


        // =========================================================
        // 2. LÓGICA PARA LA TABLA 'backendmenus' (Menú Lateral)
        // =========================================================

        // Buscamos el ID del padre global dinámicamente. 
        // NOTA: Cambia 'nombre_del_modulo_padre' por el nombre real de tu menú 76 (ej. 'marketing', 'crm', etc.)
        $globalParentId = DB::table('backendmenus')
            ->where('name', 'common.promotional') // <-- Ajustar este valor
            ->value('id') ?? 76;

        // A. Menú Padre
        $parentMenuId = DB::table('backendmenus')->insertGetId([
            'name'        => 'plans_or_titles.plans_or_titles',
            'icon'        => 'ti-crown',
            'user_id'     => null,
            'parent_id'   => $globalParentId, // <--- Dinámico y seguro
            'is_admin'    => 1,
            'is_seller'   => 0,
            'route'       => 'plans_or_titles.index',
            'position'    => 1,
            'module'      => '',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // B. Sub-Menús (Insertamos en bloque sin forzar el 'id')
        DB::table('backendmenus')->insert([
            [
                'name'        => 'plans_or_titles.plans',
                'icon'        => 'ti-layers',
                'user_id'     => null,
                'parent_id'   => $parentMenuId, // <--- Atado al ID real del menú padre
                'is_admin'    => 1,
                'is_seller'   => 0,
                'route'       => 'plans.index',
                'position'    => 1,
                'module'      => '',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'plans_or_titles.rules',
                'icon'        => 'ti-ruler-pencil',
                'user_id'     => null,
                'parent_id'   => $parentMenuId,
                'is_admin'    => 1,
                'is_seller'   => 0,
                'route'       => 'plans.rules.index',
                'position'    => 2,
                'module'      => '',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'plans_or_titles.benefits',
                'icon'        => 'ti-gift',
                'user_id'     => null,
                'parent_id'   => $parentMenuId,
                'is_admin'    => 1,
                'is_seller'   => 0,
                'route'       => 'plans.benefits.index',
                'position'    => 3,
                'module'      => '',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
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
            'plans_or_titles.index',
            'plans.index',
            'rules.index',
            'benefits.index'
        ])->delete();

        // Limpiamos los menús usando los nombres
        DB::table('backendmenus')->whereIn('name', [
            'plans_or_titles.plans_or_titles',
            'plans_or_titles.plans',
            'plans_or_titles.rules',
            'plans_or_titles.benefits'
        ])->delete();
    }
}
