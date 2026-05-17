<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\GeneralSetting\Entities\EmailTemplate;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\SmsTemplate;
use Modules\GeneralSetting\Entities\SmsTemplateType;

class AddTemplateForUserLoginOtp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('email_template_types')){
            DB::statement("INSERT INTO `email_template_types` (`id`, `type`, `created_at`, `updated_at`) VALUES
                (37, 'login_otp_email_template', NULL, '2021-01-20 12:40:47'),
                (38, 'password_reset_otp_email_template', NULL, '2021-01-20 12:40:47')
            ");
        }

        if(Schema::hasTable('email_templates')){
            $emails = [
                ['type_id' => '37', 'subject' => 'Login OTP', 'value' => '<div style="font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); text-align: center; background-color: rgb(152, 62, 81); padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0px;"><h1 style="margin: 20px 0px 10px; font-size: 36px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-weight: 500; line-height: 1.1; color: inherit;">Template</h1></div><div style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; padding: 20px;"><p style="color: rgb(85, 85, 85);">Hello {USER_FIRST_NAME}<br><br>You Are Attempt To Login.</p><p style="color: rgb(85, 85, 85);">Please use the otp :</p><p style="color: rgb(85, 85, 85);">{ORDER_TRACKING_NUMBER}<br></p><hr style="box-sizing: content-box; margin-top: 20px; margin-bottom: 20px; border-top-color: rgb(238, 238, 238);"><p style="color: rgb(85, 85, 85);"><br></p><p style="color: rgb(85, 85, 85);">{EMAIL_SIGNATURE}</p><p style="color: rgb(85, 85, 85);"><br></p></div><div style="font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); text-align: center; background-color: rgb(152, 62, 81); padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0px;"><h1 style="margin: 20px 0px 10px; font-size: 36px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-weight: 500; line-height: 1.1; color: inherit;">Template</h1></div><div style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; padding: 20px;"></div>', 'is_active' => 1, 'relatable_type'=> NULL, 'relatable_id'=>NULL, 'reciepnt_type'=>'["customer"]', 'module'=>'Otp', 'created_at' => now()],
                ['type_id' => '38', 'subject' => 'Password Reset OTP', 'value' => '<div style="font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); text-align: center; background-color: rgb(152, 62, 81); padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0px;"><h1 style="margin: 20px 0px 10px; font-size: 36px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-weight: 500; line-height: 1.1; color: inherit;">Template</h1></div><div style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; padding: 20px;"><p style="color: rgb(85, 85, 85);">Hello {USER_FIRST_NAME}<br><br>You Are Attempt To Reset Password.</p><p style="color: rgb(85, 85, 85);">Please use the otp :</p><p style="color: rgb(85, 85, 85);">{ORDER_TRACKING_NUMBER}<br></p><hr style="box-sizing: content-box; margin-top: 20px; margin-bottom: 20px; border-top-color: rgb(238, 238, 238);"><p style="color: rgb(85, 85, 85);"><br></p><p style="color: rgb(85, 85, 85);">{EMAIL_SIGNATURE}</p><p style="color: rgb(85, 85, 85);"><br></p></div><div style="font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); text-align: center; background-color: rgb(152, 62, 81); padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0px;"><h1 style="margin: 20px 0px 10px; font-size: 36px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-weight: 500; line-height: 1.1; color: inherit;">Template</h1></div><div style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; padding: 20px;"></div>', 'is_active' => 1, 'relatable_type'=> NULL, 'relatable_id'=>NULL, 'reciepnt_type'=>'["customer"]', 'module'=>'Otp', 'created_at' => now()],
            ];
            DB::table('email_templates')->insert($emails);
        }

        if(Schema::hasTable('sms_template_types')){
            $sql1 = [
                ['id' => 37, 'type' => 'login_otp_templete', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 38, 'type' => 'Password_reset_otp_templete', 'created_at' => now(), 'updated_at' => now()]
            ];
            DB::table('sms_template_types')->insert($sql1);
        }

        if(Schema::hasTable('sms_templates')){
            $sql = [
                ['type_id' => 37, 'subject' => 'User Login', 'value' => 'user login otp is : ', 'is_active' => 1, 'relatable_type' => null, 'relatable_id' => null, 'reciepnt_type' => '["customer"]', 'created_at' => now(), 'updated_at' => now()],
                ['type_id' => 38, 'subject' => 'Password Reset', 'value' => 'password reset otp is : ', 'is_active' => 1, 'relatable_type' => null, 'relatable_id' => null, 'reciepnt_type' => '["customer"]', 'created_at' => now(), 'updated_at' => now()]
            ];
            DB::table('sms_templates')->insert($sql);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $email_templates = EmailTemplate::whereIn('type_id',[37,38])->pluck('id');
        EmailTemplate::destroy($email_templates);
        EmailTemplateType::destroy([37,38]);
        $sms_templates = SmsTemplate::whereIn('type_id',[37,38])->pluck('id');
        SmsTemplate::destroy($sms_templates);
        SmsTemplateType::destroy([37,38]);
    }
}
