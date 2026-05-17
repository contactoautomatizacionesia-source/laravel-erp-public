<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Otp\Entities\OtpConfiguration;

class AddVerifiedFieldToOtpConfigurationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('otp_configurations')){
            $row = OtpConfiguration::where('key', 'order_otp_on_verified_customer')->first();
            if(!$row){
                OtpConfiguration::create([
                    'key' => 'order_otp_on_verified_customer',
                    'value' => 0
                ]);
            }
            $row = OtpConfiguration::where('key', 'otp_on_login')->first();
            if(!$row){
                OtpConfiguration::create([
                    'key' => 'otp_on_login',
                    'value' => 0
                ]);
            }
            $row = OtpConfiguration::where('key', 'otp_on_password_reset')->first();
            if(!$row){
                OtpConfiguration::create([
                    'key' => 'otp_on_password_reset',
                    'value' => 0
                ]);
            }
            $row = OtpConfiguration::where('key', 'order_cancel_limit_on_verified_customer')->first();
            if(!$row){
                OtpConfiguration::create([
                    'key' => 'order_cancel_limit_on_verified_customer',
                    'value' => 5
                ]);
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
}
