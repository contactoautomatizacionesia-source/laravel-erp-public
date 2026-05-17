<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class DeleteDestroyPermissionFromParameterSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Eliminamos el permiso específico basado en su ruta única
        DB::table('permissions')->where('route', 'parameter_settings.destroy')->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Obtener el último ID para simular autoincremento
        $parameterSettingsId = DB::table('permissions')->max('id') + 1;
        // En caso de revertir la migración, restauramos el permiso con sus datos originales
        DB::table('permissions')->insert([
            'id' => $parameterSettingsId,
            'module_id' => 18,
            'parent_id' => 766,
            'name' => 'Delete',
            'translation' => 'permission.parameter_settings.delete',
            'route' => 'parameter_settings.destroy',
            'status' => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'type' => 3, // Tipo Acción
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
