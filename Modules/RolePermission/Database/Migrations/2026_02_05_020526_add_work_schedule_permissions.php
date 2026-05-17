<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddWorkSchedulePermissions extends Migration
{
    public function up()
    {
        // 1. Obtener el nuevo module_id (último + 1)
        $lastModuleId = DB::table('permissions')->max('module_id');
        $newModuleId = $lastModuleId + 1;

        // 2. INSERTAR RUTA PRINCIPAL (Type 1)
        // Nota: route 'customer.restore' con parent_id NULL y type 1
        $mainPermissionId = DB::table('permissions')->max('id') + 1;

        DB::table('permissions')->insert([
            'id'          => $mainPermissionId,
            'module_id'   => $newModuleId,
            'parent_id'   => null, // Nivel raíz solicitado
            'name'        => 'Work Schedule Management',
            'translation' => 'permission.work_schedule_management',
            'route'       => 'role_work_schedule',
            'type'        => 1, // Type 1 para Menú Principal
            'status'      => 1,
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // 3. LISTADO DE SUB-PERMISOS (Type 2)
        $subPermissions = [
            ['name' => 'Role Work Schedule List', 'route' => 'role_work_schedule.index'],
            ['name' => 'Create Role Work Schedule', 'route' => 'role_work_schedule.store'],
            ['name' => 'Update Role Work Schedule', 'route' => 'role_work_schedule.update'],
            ['name' => 'Delete Role Work Schedule', 'route' => 'role_work_schedule.destroy'],
            ['name' => 'Assign Role Work Schedule', 'route' => 'role_work_schedule.assign'],
        ];

        foreach ($subPermissions as $item) {
            $nextId = DB::table('permissions')->max('id') + 1;

            DB::table('permissions')->insert([
                'id'          => $nextId,
                'module_id'   => $newModuleId,
                'parent_id'   => $mainPermissionId, // Cuelgan de la ruta principal creada arriba
                'name'        => $item['name'],
                'translation' => 'permission.' . str_replace('.', '_', $item['route']),
                'route'       => $item['route'],
                'type'        => 2, // Type 2 para Sub-módulo
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down()
    {
        // Eliminar por module_id para limpiar el grupo completamente
        $moduleId = DB::table('permissions')->where('route', 'customer.restore')->value('module_id');
        if ($moduleId) {
            DB::table('permissions')->where('module_id', $moduleId)->delete();
        }
    }
}
