<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateDoubleApprovalStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // LÓGICA PARA LA TABLA 'permissions'
        $nextPermissionId = DB::table('permissions')->max('id') + 1;
        $nextModuleId = DB::table('permissions')->max('module_id') + 1;

        DB::table('permissions')->insert([
            'id'          => $nextPermissionId,
            'module_id'   => $nextModuleId,
            'parent_id'   => null,
            'name'        => 'Double Approval',
            'translation' => 'permission.double_approval',
            'route'       => 'double_approval.index',
            'type'        => 1,
            'status'      => 1,
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);


        // LÓGICA PARA LA TABLA 'backendmenus' (Menú Lateral)
        $nextMenuId = DB::table('backendmenus')->max('id') + 1;

        DB::table('backendmenus')->insert([
            'id'          => $nextMenuId,
            'name'        => 'common.double_approval',
            'icon'        => 'ti-check-box', // Icono seleccionado para validación
            'user_id'     => null,
            'parent_id'   => 53, // Ubicado bajo el padre especificado
            'is_admin'    => 1,
            'is_seller'   => 0,
            'route'       => 'double_approval.index',
            'position'    => 2,
            'module'      => '',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')->where('route', 'double_approval.index')->delete();
        DB::table('backendmenus')->where('route', 'double_approval.index')->delete();
    }
}