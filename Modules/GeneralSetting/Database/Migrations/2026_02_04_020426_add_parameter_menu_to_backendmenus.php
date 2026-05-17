<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddParameterMenuToBackendmenus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Buscamos el ID del padre exacto usando su ruta única
        $parent = DB::table('backendmenus')
                    ->where('route', 'system_settings')
                    ->first();

        $parentId = $parent ? $parent->id : null;

        DB::table('backendmenus')->insert([
            [
                'name'       => 'general_settings.parameterization',
                'route'      => 'parameter_settings.index',
                'parent_id'  => $parentId,
                'icon'       => '',
                'position'   => 56,
                'is_admin'   => 1,              // Visible para Admin
                'is_seller'  => 0,              // Oculto para Vendedores
                'module'     => 'GeneralSetting', // Vital para que el sistema modular lo reconozca
                'created_at' => now(),
                'updated_at' => now(),
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
        // Para borrar, usamos la ruta única del hijo
        DB::table('backendmenus')
            ->where('route', 'parameter_settings.index')
            ->delete();
    }
}
