<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddScheduleMenuToBackendmenus extends Migration
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
                    ->where('route', 'human_resource')
                    ->first();
        
        $parentId = $parent ? $parent->id : null;

        DB::table('backendmenus')->insert([
            [
                'name'       => 'hr.schedule',
                'route'      => 'role_work_schedule.index',
                'parent_id'  => $parentId,
                'icon'       => '',
                'position'   => 20,
                'is_admin'   => 1,
                'is_seller'  => 0,
                'module'     => 'RoleWorkSchedule',
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
            ->where('route', 'role_work_schedule.index')
            ->delete();
    }
}