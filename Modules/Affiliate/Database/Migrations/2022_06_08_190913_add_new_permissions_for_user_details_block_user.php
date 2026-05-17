<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\RolePermission\Entities\Permission;

class AddNewPermissionsForUserDetailsBlockUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('permissions')){
            $permission = [
                //users
                ['id' => 714, 'module_id' => 40, 'parent_id' => 657, 'module' => 'Affiliate', 'name' => 'Show Details', 'route' => 'affiliate.user.show', 'type' => 3],
                ['id' => 715, 'module_id' => 40, 'parent_id' => 657, 'module' => 'Affiliate', 'name' => 'Disable/Enable', 'route' => 'affiliate.users.disable_enable', 'type' => 3],
            ];
            try{
                DB::table('permissions')->insert($permission);
            }catch(Exception $e){
    
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable('permissions')){
            Permission::destroy([714,715]);
        }
    }
}
