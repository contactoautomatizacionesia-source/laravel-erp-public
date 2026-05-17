<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddCustomNotificationSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        $notifications = [
            [
                'event' => json_encode(['en' => 'Low Stock Alert', 'es' => 'Alerta de Stock Bajo']),
                'slug' => 'low_stock_alert',
                'type' => 'system,email', 
                'message' => json_encode(['en' => 'Product {PRODUCT_NAME} is running low on stock. Current: {STOCK}', 'es' => 'El producto {PRODUCT_NAME} tiene stock bajo. Actual: {STOCK}']),
                'admin_msg' => json_encode(['en' => 'Admin: Low stock for {PRODUCT_NAME}', 'es' => 'Admin: Stock bajo para {PRODUCT_NAME}']),
                'user_access_status' => 0, 
                'seller_access_status' => 0,
                'admin_access_status' => 1, 
                'staff_access_status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],            
            [
                'event' => json_encode(['en' => 'Cash Register Opened', 'es' => 'Apertura de Caja']),
                'slug' => 'cash_register_open',
                'type' => 'system,email',
                'message' => json_encode(['en' => 'Cash register opened by {USER} with amount {AMOUNT}', 'es' => 'Caja abierta por {USER} con monto {AMOUNT}']),
                'admin_msg' => json_encode(['en' => 'Admin: Register opened', 'es' => 'Admin: Caja abierta']),
                'user_access_status' => 0,
                'seller_access_status' => 0,
                'admin_access_status' => 1,
                'staff_access_status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event' => json_encode(['en' => 'Point Value Changed', 'es' => 'Cambio Valor de Puntos']),
                'slug' => 'point_value_change',
                'type' => 'system,email',
                'message' => json_encode(['en' => 'Point value updated to {NEW_VALUE}', 'es' => 'El valor del punto cambió a {NEW_VALUE}']),
                'admin_msg' => json_encode(['en' => 'Admin: Point value changed', 'es' => 'Admin: Valor de punto cambiado']),
                'user_access_status' => 1, 
                'seller_access_status' => 0,
                'admin_access_status' => 1,
                'staff_access_status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'event' => json_encode(['en' => 'Product Count Report', 'es' => 'Reporte Conteo Productos']),
                'slug' => 'product_count_report',
                'type' => 'system,email',
                'message' => json_encode(['en' => 'Total Active Products: {COUNT}', 'es' => 'Total Productos Activos: {COUNT}']),
                'admin_msg' => json_encode(['en' => 'Admin: Product count report', 'es' => 'Admin: Reporte conteo de productos']),
                'user_access_status' => 0,
                'seller_access_status' => 0,
                'admin_access_status' => 1,
                'staff_access_status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('notification_settings')->insert($notifications);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $slugs = ['low_stock_alert', 'cash_register_open', 'point_value_change', 'product_count_report'];
        DB::table('notification_settings')->whereIn('slug', $slugs)->delete();
    }
}
