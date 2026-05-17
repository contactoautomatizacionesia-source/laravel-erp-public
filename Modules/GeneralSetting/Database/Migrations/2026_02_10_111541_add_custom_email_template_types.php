<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddCustomEmailTemplateTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();
        $types = [
            // 1. Stock Bajo
            ['type' => 'low_stock_template', 'created_at' => $now, 'updated_at' => $now],
            // 2. Apertura de Caja
            ['type' => 'cash_register_open_template', 'created_at' => $now, 'updated_at' => $now],
            // 3. Cambio de Puntos
            ['type' => 'point_change_template', 'created_at' => $now, 'updated_at' => $now],
            // 4. Reporte Productos
            ['type' => 'product_count_report_template', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('email_template_types')->insert($types);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $types = [
            'low_stock_template',
            'cash_register_open_template',
            'point_change_template',
            'product_count_report_template',
        ];
        DB::table('email_template_types')->whereIn('type', $types)->delete();
    }
}
