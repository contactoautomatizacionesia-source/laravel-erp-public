<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddStockAndProductNotificationToSmsTemplateTypesTable extends Migration
{
    private array $types = [
        'double_approval_template',
        'overstock_alert_template',
        'low_stock_template',
        'empty_stock_alert_template',
        'product_count_report_template',
        'failed_product_count_template',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $timestamp = now();

        $data = array_map(function ($type) use ($timestamp) {
            return [
                'type' => $type,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }, $this->types);

        DB::table('sms_template_types')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('sms_template_types')
            ->whereIn('type', $this->types)
            ->delete();
    }
}
