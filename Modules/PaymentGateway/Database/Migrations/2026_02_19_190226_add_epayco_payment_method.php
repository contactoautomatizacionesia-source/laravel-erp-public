<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\PaymentGateway\Entities\PaymentMethod;

class AddEpaycoPaymentMethod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        PaymentMethod::firstOrCreate(
            ['slug' => 'ePayco'],
            [
                "method" => "EPAYCO",
                "slug" => "ePayco",
                "type" => "System",
                "active_status" => 1,
                "module_status" => 1,
                "logo" => 'payment_gateway/epayco.png',
                "created_by" => 1,
                "updated_by" => 1
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        PaymentMethod::where('slug', 'ePayco')->delete();
    }
}
