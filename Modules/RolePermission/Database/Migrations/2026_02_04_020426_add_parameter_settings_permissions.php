<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddParameterSettingsPermissions extends Migration
{
    public function up()
    {        
        // General Settings (Sub-módulo) es ID: 330, module_id: 18, parent_id: 329
        // Obtener dinámicamente el module_id y parent_id desde 'system_settings'
        $systemSetting = DB::table('permissions')
            ->where('route', 'system_settings')
            ->first();

        if (!$systemSetting) {
            // Fallback preventivo si la ruta exacta cambia
            $systemSetting = DB::table('permissions')->where('id', 329)->first();
        }

        $moduleId = $systemSetting->module_id; 
        $parentId = $systemSetting->id;

        // Obtener el último ID para simular autoincremento
        $parameterSettingsId = DB::table('permissions')->max('id') + 1;

        // Insertar Sub-módulo: Parameter Settings (Type 2)
        DB::table('permissions')->insert([
            'id'         => $parameterSettingsId,
            'module_id'  => $moduleId,
            'parent_id'  => $parentId,
            'name'       => 'Parameter Settings',
            'translation' => 'permission.parameter_settings',
            'route'      => 'parameter_settings.index',
            'type'       => 2, // 2 para sub-módulo
            'status'     => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insertar Acciones: Update y Delete (Type 3)

        // INSERTAR ACCIÓN: Update
        // Calculamos el nuevo MAX ID después de la inserción anterior
        $currentMaxId = DB::table('permissions')->max('id');
        $updateActionId = $currentMaxId + 1;

        DB::table('permissions')->insert([
            'id'         => $updateActionId,
            'module_id'  => $moduleId,
            'parent_id'  => $parameterSettingsId,
            'name'       => 'Parameter Update',
            'translation' => 'permission.parameter_settings.update',
            'route'      => 'parameter_settings.update',
            'type'       => 3,
            'status'     => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. INSERTAR ACCIÓN: Delete
        $finalMaxId = DB::table('permissions')->max('id');
        DB::table('permissions')->insert([
            'id'         => $finalMaxId + 1,
            'module_id'  => $moduleId,
            'parent_id'  => $parameterSettingsId,
            'name'       => 'Delete',
            'translation' => 'permission.parameter_settings.delete',
            'route'      => 'parameter_settings.destroy',
            'type'       => 3,
            'status'     => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('permissions')
            ->where('route', 'like', 'parameter_settings.%')
            ->delete();
    }
}