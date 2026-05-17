<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\RolePermission\Entities\Permission;

class AddPermissionSidebarForOtp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permission_sql = [
            ['id'  => 648, 'module_id' => 39, 'parent_id' => null, 'name' => 'OTP','module' => 'Otp', 'route' => 'otp.configuration', 'type' => 1 ],
            ['id'  => 649, 'module_id' => 39, 'parent_id' => 648, 'name' => 'Update','module' => 'Otp', 'route' => 'opt.configuration_update', 'type' => 2 ]

        ];

        try{
            DB::table('permissions')->insert($permission_sql);
        }catch(Exception $e){

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Permission::destroy([648,649]);
    }
}
