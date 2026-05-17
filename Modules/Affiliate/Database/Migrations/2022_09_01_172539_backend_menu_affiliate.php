<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\SidebarManager\Entities\Backendmenu;
use Modules\SidebarManager\Entities\BackendmenuUser;

class BackendMenuAffiliate extends Migration
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
                ['is_admin' => 1, 'is_seller' => 0, 'parent_id' => 76, 'icon' => 'fas fa-money-bill','position' => 7, 'module' => 'Affiliate', 'name' => 'affiliate.Affiliate', 'route' => 'affiliate', 'children' => [
                    ['is_admin' => 1, 'is_seller' => 0, 'icon' => 'fas fa-money-bill', 'position' => 1, 'module' => 'Affiliate', 'name' => 'affiliate.My Affiliate', 'route' => 'affiliate.my_affiliate.index'],
                    ['is_admin' => 1, 'is_seller' => 0, 'icon' => 'fas fa-money-bill', 'position' => 2, 'module' => 'Affiliate', 'name' => 'affiliate.Pending Withdrawn', 'route' => 'affiliate.pending_withdraw'],
                    ['is_admin' => 1, 'is_seller' => 0, 'icon' => 'fas fa-money-bill', 'position' => 3, 'module' => 'Affiliate', 'name' => 'affiliate.Complete Withdrawn', 'route' => 'affiliate.complete_withdraw'],
                    ['is_admin' => 1, 'is_seller' => 0, 'icon' => 'fas fa-money-bill', 'position' => 4, 'module' => 'Affiliate', 'name' => 'affiliate.Affiliate setting', 'route' => 'affiliate.configurations.update'],
                    ['is_admin' => 1, 'is_seller' => 0, 'icon' => 'fas fa-money-bill', 'position' => 5, 'module' => 'Affiliate', 'name' => 'affiliate.Users', 'route' => 'affiliate.users.index'],
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
            $backend_menus = Backendmenu::where('module', 'Affiliate')->pluck('id')->toArray();
            $backend_menu_users = BackendmenuUser::whereIn('backendmenu_id', $backend_menus)->pluck('id')->toArray();
            Backendmenu::destroy($backend_menus);
            BackendmenuUser::destroy($backend_menu_users);
        }
    }
}
