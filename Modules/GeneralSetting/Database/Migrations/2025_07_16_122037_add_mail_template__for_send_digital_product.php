<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\GeneralSetting\Entities\EmailTemplate;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\NotificationSetting;

class AddMailTemplateForSendDigitalProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $hasType = EmailTemplateType::where('id',43)->first();
        if($hasType) {

            $hasTemplate = EmailTemplate::where('type_id',$hasType->id)->first();
            if($hasTemplate){
                $template = [
                    "type_id" => $hasType->id,
                    "subject" => 'Send Digital File',
                    "value" => $this->template(),


                ];
                $hasTemplate->update($template);
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

    }

    public function template(){
        return '<div style="font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); text-align: center; background-color: rgb(152, 62, 81); padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0px;"><h1 style="margin: 20px 0px 10px; font-size: 36px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-weight: 500; line-height: 1.1; color: inherit;">Template</h1></div><div style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; padding: 20px;"><p style="color: rgb(85, 85, 85);">Hello,<br><br>You are receiving this email because of get access digital link. Arabic</p><p style="color: rgb(85, 85, 85);">File access link is :</p><p style="color: rgb(85, 85, 85);">{DIGITAL_FILE_LINK}<br></p><hr style="box-sizing: content-box; margin-top: 20px; margin-bottom: 20px; border-top-color: rgb(238, 238, 238);"><p style="color: rgb(85, 85, 85);"><br></p><p style="color: rgb(85, 85, 85);">{EMAIL_SIGNATURE}</p><p style="color: rgb(85, 85, 85);"><br></p></div><div style="font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); text-align: center; background-color: rgb(152, 62, 81); padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0px;"><h1 style="margin: 20px 0px 10px; font-size: 36px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-weight: 500; line-height: 1.1; color: inherit;">Template</h1></div><div style="color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; padding: 20px;"></div>';
    }
}
