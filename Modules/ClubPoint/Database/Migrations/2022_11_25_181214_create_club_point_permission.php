<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\SidebarManager\Entities\Backendmenu;
use Modules\RolePermission\Entities\Permission;

class CreateClubPointPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        {
            $permission = [
                ['id' => 737, 'module_id' => 51, 'parent_id' => null, 'module'=>'ClubPoint', 'name' => 'Club Point', 'route' => 'clubpoint', 'type' => 1],
                ['id' => 738, 'module_id' => 51, 'parent_id' => 737, 'module'=>'ClubPoint', 'name' => 'Set Product Point', 'route' => 'clubpoint.set-product-point', 'type' => 2],
                ['id' => 739, 'module_id' => 51, 'parent_id' => 737, 'module'=>'ClubPoint', 'name' => 'User Product Point', 'route' => 'clubpoint.user-product-point', 'type' => 2]
            ];
            try{
                DB::table('permissions')->insert($permission);
            }catch(Exception $e){
            }
            if(Schema::hasTable('backendmenus')){
                $sql = [
                    ['parent_id' => 53, 'is_admin' => 1,'is_seller' => 0, 'icon' =>'ti-eye', 'module'=>'ClubPoint','name' => 'clubpoint.club_point', 'route' => 'clubpoint', 'position' => 2, 'children'=>[
                        ['is_admin' => 1,'is_seller' => 0, 'icon' =>null, 'module'=>'ClubPoint','name' => 'clubpoint.set_product_point', 'route' => 'clubpoint.set-product-point', 'position' => 3],//Submenu
                        ['is_admin' => 1,'is_seller' => 0, 'icon' =>null, 'module'=>'ClubPoint','name' => 'clubpoint.user_product_point', 'route' => 'clubpoint.user-product-point', 'position' => 3],//Submenu
                    ]],
                ]; 
                foreach($sql as $menu){
                    $children = null;
                    if(array_key_exists('children',$menu)){
                        $children = $menu['children'];
                        unset( $menu['children']);
                    }
                    $parent = Backendmenu::create($menu);
                    if($children){
                        foreach($children as $menu){
                            $sub_children = null;
                            if(array_key_exists('children',$menu)){
                                $sub_children = $menu['children'];
                                unset( $menu['children']);
                            }
                            $menu['parent_id'] = $parent->id;
                            $parent_children = Backendmenu::create($menu);
                            if($sub_children){
                                foreach($sub_children as $menu){
                                    $subsubmenu['parent_id'] = $parent_children->id;
                                    Backendmenu::create($subsubmenu);
                                }
                            }
                        }
                    }
                }
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
        Permission::destroy([737,738,739]);
        Backendmenu::destroy([228,229.230]);
    }
}
