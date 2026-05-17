<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliateConfigurationsTable extends Migration
{

    public function up()
    {
        Schema::create('affiliate_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('value')->nullable();
            $table->timestamps();
        });

        $config_data = [

            [
                'key' => 'min_withdraw',
                'value' => 10 //currency unit
            ],
            [
                'key' => 'balance_add_account_after_days',
                'value' => 10 //in days
            ],

            [
                'key' => 'transfer_approval_need',
                'value' => 1
            ],

            [
                'key' => 'admin_approval_need',
                'value' => 0
            ],

            [
                'key' => 'referral_duration_type',
                'value' => 'Fixed' //Fixed,Lifetime
            ],

            [
                'key' => 'referral_duration',
                'value' => 180 //in days
            ],

            [
                'key' => 'commission_type',
                'value' => "Product"
            ],

            [
                'key' => 'amount_type',
                'value' => "Percentage"//Percentage,Flat
            ],
            [
                'key' => 'commission_amount',
                'value' => 10
            ],

            [
                'key' => 'common_amount',
                'value' => 5
            ],
            [
                'key' => 'common_calculation_method',
                'value' => 'Percentage'
            ],
            [
                'key' => 'multi_category_commission_calculate',
                'value' => 'Maximum'
            ],

        ];
        DB::table('affiliate_configurations')->insert($config_data);

    }

    public function down()
    {
        Schema::dropIfExists('affiliate_configurations');
    }
}
