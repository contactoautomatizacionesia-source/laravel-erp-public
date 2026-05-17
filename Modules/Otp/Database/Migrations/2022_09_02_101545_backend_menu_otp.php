<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\SidebarManager\Entities\Backendmenu;
use Modules\SidebarManager\Entities\BackendmenuUser;

class BackendMenuOtp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('backendmenus')){
            $sql = [
                ['is_admin' => 1, 'is_seller' => 0, 'icon' => 'ti-lock', 'parent_id' => 132,'position' => 12, 'module' => 'Otp', 'name' => 'otp.otp', 'route' => 'otp.configuration','children' => [
                    ['is_admin' => 1, 'is_seller' => 0, 'icon' => 'ti-lock', 'position' => 1, 'module' => 'Otp', 'name' => 'otp.OTP setting', 'route' => 'opt.configuration_update']
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasTable('backendmenus')){
            $backend_menus = Backendmenu::where('module', 'Otp')->pluck('id')->toArray();
            $backend_menu_users = BackendmenuUser::whereIn('backendmenu_id', $backend_menus)->pluck('id')->toArray();
            Backendmenu::destroy($backend_menus);
            BackendmenuUser::destroy($backend_menu_users);
        }
    }
}
