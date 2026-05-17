<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddStockAndCountTemplateType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        // Registramos los nuevos tipos de plantilla para el motor de correos
        DB::table('email_template_types')->insert([
            [
                'type' => 'overstock_alert_template',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'type' => 'failed_product_count_template',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('email_template_types')
            ->whereIn('type', ['overstock_alert_template', 'failed_product_count_template'])
            ->delete();
    }
}
