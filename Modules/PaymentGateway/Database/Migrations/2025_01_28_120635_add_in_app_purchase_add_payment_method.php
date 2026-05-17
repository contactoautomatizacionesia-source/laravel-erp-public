<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\PaymentGateway\Entities\PaymentMethod;

class AddInAppPurchaseAddPaymentMethod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $hasOne = PaymentMethod::where('slug','in-app-purchase')->first();
        if(!$hasOne)
        {
            PaymentMethod::create([
                "method" => "In app purchase",
                "slug" => "in-app-purchase",
                "type" => "system",
                "active_stauts" => 0,
                "module_status" => 1,
                "logo" => 'payment_gateway/in-app-purchase.jpg',
                "created_by" => 1,
                "updated_by" => 1
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $hasOne = PaymentMethod::where('slug','in-app-purchase')->first();
        if($hasOne)
        {
            $hasOne->delete();
        }
    }
}
