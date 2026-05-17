<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStockAndCountFailureOnNotificationSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = now();

        DB::table('notification_settings')->insert([
            // Registro: Alerta de Exceso de Stock
            [
                'event' => json_encode(['en' => 'Overstock Alert', 'es' => 'Alerta de exceso de stock']),
                'slug' => 'overstock_alert',
                'type' => 'system,email',
                'message' => json_encode([
                    'en' => 'Product {PRODUCT_NAME} has exceeded its maximum capacity. Current: {STOCK}',
                    'es' => 'El producto {PRODUCT_NAME} ha superado su capacidad máxima. Actual: {STOCK}'
                ]),
                'admin_msg' => json_encode([
                    'en' => 'Administrator: Excess stock for {PRODUCT_NAME}. Current: {STOCK}',
                    'es' => 'Administrador: Exceso de stock para {PRODUCT_NAME}. Actual: {STOCK}'
                ]),
                'user_access_status' => 0,
                'seller_access_status' => 0,
                'admin_access_status' => 1,
                'staff_access_status' => 1,
                'module' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Registro: Fallo en Conteo de Productos
            [
                'event' => json_encode(['en' => 'Product Counting Failure', 'es' => 'Fallo en Conteo de Productos']),
                'slug' => 'failed_product_count_report',
                'type' => 'email,system',
                'message' => json_encode([
                    'en' => 'Product counting error detected.',
                    'es' => 'Se ha detectado un error en el conteo de productos.'
                ]),
                'admin_msg' => json_encode([
                    'en' => 'Admin: Report of failures in daily product counting.',
                    'es' => 'Admin: Reporte de fallos en el conteo diario de productos.'
                ]),
                'user_access_status' => 0,
                'seller_access_status' => 0,
                'admin_access_status' => 1,
                'staff_access_status' => 1,
                'module' => null,
                'created_at' => $now,
                'updated_at' => $now,
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
        DB::table('notification_settings')
            ->whereIn('slug', ['overstock_alert', 'failed_product_count_report'])
            ->delete();
    }
}
