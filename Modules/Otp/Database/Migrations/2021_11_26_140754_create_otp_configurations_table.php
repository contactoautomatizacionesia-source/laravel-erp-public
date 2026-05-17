<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\GeneralSetting\Entities\EmailTemplate;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\SmsTemplate;
use Modules\GeneralSetting\Entities\SmsTemplateType;
use Modules\Otp\Entities\OtpConfiguration;

class CreateOtpConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('otp_configurations')){
            Schema::create('otp_configurations', function (Blueprint $table) {
                $table->id();
                $table->string('key');
                $table->string('value')->nullable();
                $table->timestamps();
            });
            OtpConfiguration::create(['key'=>'code_validation_time','value' => 5]);
            OtpConfiguration::create(['key'=>'otp_type_registration','value' => 'email']);
            OtpConfiguration::create(['key'=>'otp_type_order','value' => 'email,sms']);
            OtpConfiguration::create(['key'=>'otp_activation_for_seller','value' => 1]);
            OtpConfiguration::create(['key'=>'otp_activation_for_customer','value' => 1]);
            OtpConfiguration::create(['key'=>'otp_activation_for_order','value' => 1]);
        }
        if(Schema::hasTable('email_template_types')){
            DB::statement("INSERT INTO `email_template_types` (`id`, `type`, `created_at`, `updated_at`) VALUES
            (35, 'registration_otp_email_template', NULL, '2021-01-20 12:40:47'),
            (36, 'order_otp_email_template', NULL, '2021-01-20 12:40:47')
            ");
        }

        if(Schema::hasTable('email_templates')){
            $emails = [
                ['type_id' => '35', 'subject' => 'Registration OTP', 'value' => '<div style="font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); text-align: center; background-color: rgb(152, 62, 81); padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0px;"><h1 style="margin: 20px 0px 10px; font-size: 36px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-weight: 500; line-height: 1.1; color: inherit;">Template</h1></div><div style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; padding: 20px;"><p style="color: rgb(85, 85, 85);">Hello {USER_FIRST_NAME}<br><br>An account has been created for you.</p><p style="color: rgb(85, 85, 85);">Please use the otp :</p><p style="color: rgb(85, 85, 85);">{ORDER_TRACKING_NUMBER}<br></p><hr style="box-sizing: content-box; margin-top: 20px; margin-bottom: 20px; border-top-color: rgb(238, 238, 238);"><p style="color: rgb(85, 85, 85);"><br></p><p style="color: rgb(85, 85, 85);">{EMAIL_SIGNATURE}</p><p style="color: rgb(85, 85, 85);"><br></p></div><div style="font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); text-align: center; background-color: rgb(152, 62, 81); padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0px;"><h1 style="margin: 20px 0px 10px; font-size: 36px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-weight: 500; line-height: 1.1; color: inherit;">Template</h1></div><div style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; padding: 20px;"></div>', 'is_active' => 1, 'relatable_type'=> NULL, 'relatable_id'=>NULL, 'reciepnt_type'=>'["customer"]', 'module'=>'Otp', 'created_at' => now()],
                ['type_id' =>'36', 'subject'=>'Order Confirmation OTP', 'value'=>'<div style="font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); text-align: center; background-color: rgb(152, 62, 81); padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0px;"><h1 style="margin: 20px 0px 10px; font-size: 36px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-weight: 500; line-height: 1.1; color: inherit;">Template</h1></div><div style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; padding: 20px;"><p style="color: rgb(85, 85, 85);">Hello {USER_FIRST_NAME}<br><br>An order has been created for you.</p><p style="color: rgb(85, 85, 85);">Please use the otp for confimation.</p><p style="color: rgb(85, 85, 85);">{ORDER_TRACKING_NUMBER}<br></p><hr style="box-sizing: content-box; margin-top: 20px; margin-bottom: 20px; border-top-color: rgb(238, 238, 238);"><p style="color: rgb(85, 85, 85);"><br></p><p style="color: rgb(85, 85, 85);">{EMAIL_SIGNATURE}</p><p style="color: rgb(85, 85, 85);"><br></p></div><div style="font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); text-align: center; background-color: rgb(152, 62, 81); padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0px;"><h1 style="margin: 20px 0px 10px; font-size: 36px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-weight: 500; line-height: 1.1; color: inherit;">Template</h1></div><div style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; padding: 20px;"></div>', 'is_active'=>1, 'relatable_type'=>NULL, 'relatable_id'=>NULL, 'reciepnt_type'=>'["customer"]', 'module'=>'Otp', 'created_at' => now()]
            ];
            DB::table('email_templates')->insert($emails);
        }
        if(Schema::hasTable('sms_template_types')){
            $sql1 = [
                ['id' => 35, 'type' => 'registration_templete', 'created_at' => now(), 'updated_at' => now()],
                ['id' => 36, 'type' => 'order_confirmation_templete', 'created_at' => now(), 'updated_at' => now()]
            ];
            DB::table('sms_template_types')->insert($sql1);
        }

        if(Schema::hasTable('sms_templates')){
            $sql = [
                ['type_id' => 35, 'subject' => 'Registration', 'value' => 'Your registration otp : ', 'is_active' => 1, 'relatable_type' => null, 'relatable_id' => null, 'reciepnt_type' => '["customer"]', 'created_at' => now(), 'updated_at' => now()],
                ['type_id' => 36, 'subject' => 'Order Confirmation', 'value' => 'Order confirmation otp : ', 'is_active' => 1, 'relatable_type' => null, 'relatable_id' => null, 'reciepnt_type' => '["customer"]', 'created_at' => now(), 'updated_at' => now()]
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
        Schema::dropIfExists('otp_configurations');
        $email_templates = EmailTemplate::whereIn('type_id',[35,36])->pluck('id');
        EmailTemplate::destroy($email_templates);
        EmailTemplateType::destroy([35,36]);
        $sms_templates = SmsTemplate::whereIn('type_id',[35,36])->pluck('id');
        SmsTemplate::destroy($sms_templates);
        SmsTemplateType::destroy([35,36]);
    }
}
