<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Migrations\Migration;
use Modules\RolePermission\Entities\Permission;

class CreateAffiliateModulePermissionTable extends Migration
{

    public function up()
    {
        /*
             |--------------------------------------------------------------------------
             |Affiliate Module Permission
             |--------------------------------------------------------------------------
        */
        $permission = [
            ['id' => 650, 'module_id' => 40, 'parent_id' => null, 'module' => 'Affiliate', 'name' => 'Affiliate', 'route' => 'affiliate', 'type' => 1],
            ['id' => 651, 'module_id' => 40, 'parent_id' => 650, 'module' => 'Affiliate', 'name' => 'My Affiliate', 'route' => 'affiliate.my_affiliate.index', 'type' => 2],

            //pending_withdraw
            ['id' => 652, 'module_id' => 40, 'parent_id' => 650, 'module' => 'Affiliate', 'name' => 'Withdrawn', 'route' => 'affiliate.pending_withdraw', 'type' => 2],
            ['id' => 653, 'module_id' => 40, 'parent_id' => 652, 'module' => 'Affiliate', 'name' => 'Pending List', 'route' => 'affiliate.pending_withdraw', 'type' => 3],
            ['id' => 654, 'module_id' => 40, 'parent_id' => 652, 'module' => 'Affiliate', 'name' => 'Complete List', 'route' => 'affiliate.complete_withdraw', 'type' => 3],
            ['id' => 655, 'module_id' => 40, 'parent_id' => 652, 'module' => 'Affiliate', 'name' => 'Confirm', 'route' => 'affiliate.confirm_withdraw', 'type' => 3],

            //configs
            ['id' => 656, 'module_id' => 40, 'parent_id' => 650, 'module' => 'Affiliate', 'name' => 'Configurations', 'route' => 'affiliate.configurations.update', 'type' => 2],

            //users
            ['id' => 657, 'module_id' => 40, 'parent_id' => 650, 'module' => 'Affiliate', 'name' => 'Users', 'route' => 'affiliate.users.index', 'type' => 2],
            ['id' => 658, 'module_id' => 40, 'parent_id' => 657, 'module' => 'Affiliate', 'name' => 'List', 'route' => 'affiliate.users.index', 'type' => 3],
            ['id' => 659, 'module_id' => 40, 'parent_id' => 657, 'module' => 'Affiliate', 'name' => 'Approved', 'route' => 'affiliate.users.approved', 'type' => 3],

        ];

        try{
            DB::table('permissions')->insert($permission);
        }catch(Exception $e){

        }

    }

    public function down()
    {
        Permission::destroy([650,659]);
    }
}
