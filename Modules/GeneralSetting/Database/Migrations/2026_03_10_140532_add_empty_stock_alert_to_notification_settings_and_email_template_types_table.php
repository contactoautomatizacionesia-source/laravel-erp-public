<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddEmptyStockAlertToNotificationSettingsAndEmailTemplateTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $currentTimeStamp = now();

        DB::table('notification_settings')->insert([
            'event'   => json_encode(['en' => 'Empty Stock Alert', 'es' => 'Alerta de stock vacío']),
            'slug'    => 'empty_stock_alert',
            'type'    => 'system,email',
            'message' => json_encode([
                'en' => 'Product {PRODUCT_NAME} has run out of stock.',
                'es' => 'El producto {PRODUCT_NAME} se ha quedado sin stock.'
            ]),
            'admin_msg' => json_encode([
                'en' => 'Administrator: Empty stock for {PRODUCT_NAME}.',
                'es' => 'Administrador: Stock vacío para {PRODUCT_NAME}.'
            ]),
            'user_access_status'   => 0,
            'seller_access_status' => 0,
            'admin_access_status'  => 1,
            'staff_access_status'  => 1,
            'module'               => null,
            'created_at'           => $currentTimeStamp,
            'updated_at'           => $currentTimeStamp,
        ]);

        DB::table('email_template_types')->insert([
            'type'       => 'empty_stock_alert_template',
            'module'     => null,
            'created_at' => $currentTimeStamp,
            'updated_at' => $currentTimeStamp
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notification_settings')
            ->where('slug', 'empty_stock_alert')
            ->delete();

        DB::table('email_template_types')
            ->where('type', 'empty_stock_alert_template')
            ->delete();
    }
}
