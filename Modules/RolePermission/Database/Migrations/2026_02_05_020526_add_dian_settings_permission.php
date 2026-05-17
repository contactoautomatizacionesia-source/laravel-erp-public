<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDianSettingsPermission extends Migration
{
    public function up()
    {
        // Consultar dinámicamente el Parent ID y Module ID
        $parentPermission = DB::table('permissions')
            ->where('route', 'generalsetting.index')
            ->first();

        if ($parentPermission) {
            $parentId = $parentPermission->id;
            $moduleId = $parentPermission->module_id;

            // Obtener el siguiente ID manual (simulando autoincremento)
            $nextId = DB::table('permissions')->max('id') + 1;

            // Insertar el nuevo permiso de tipo Acción (Type 3)
            DB::table('permissions')->insert([
                'id'          => $nextId,
                'module_id'   => $moduleId,
                'parent_id'   => $parentId,
                'name'        => 'Dian Settings',
                'translation' => 'permission.dian_settings',
                'route'       => 'dian_settings.index',
                'type'        => 3, // Tipo 3 para acciones/botones
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
        DB::table('permissions')
            ->where('route', 'dian_settings.index')
            ->delete();
    }
}