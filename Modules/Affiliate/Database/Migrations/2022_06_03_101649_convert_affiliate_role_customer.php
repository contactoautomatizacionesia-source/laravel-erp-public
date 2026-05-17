<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\GeneralSetting\Entities\UserNotificationSetting;
use Modules\RolePermission\Entities\Role;

class ConvertAffiliateRoleCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('roles')){
            $affiliate_role = Role::where('type','affiliate')->first();
            if($affiliate_role){
                $users = User::where('role_id', $affiliate_role->id)->get();
                foreach($users as $user){
                    $user->update([
                        'role_id' => 4
                    ]);
                    $notification_setting_check = UserNotificationSetting::where('user_id', $user->id)->get();
                    if($notification_setting_check->count() < 1){
                        (new UserNotificationSetting)->createForRegisterUser($user->id);
                    }
                    
                }
                $affiliate_role->delete();
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
        //
    }
}
