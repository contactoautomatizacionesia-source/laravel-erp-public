<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToCustomerNotificationsTable extends Migration
{
    public function up()
    {
        Schema::table('customer_notifications', function (Blueprint $blueprint) {
            // Campo JSON para almacenar: nombre, SKU, stock_actual, stock_limite, etc.
            $blueprint->json('notification_data')->nullable()->after('read_status');

            // Campo para identificar el tipo: 'min_stock', 'max_stock', 'out_of_stock'
            $blueprint->string('notification_type', 50)->nullable()->after('notification_data');

            // Índice para mejorar la velocidad de consulta en la nueva vista
            $blueprint->index('notification_type');
        });
    }

    public function down()
    {
        Schema::table('customer_notifications', function (Blueprint $blueprint) {
            $blueprint->dropIndex(['notification_type']);
            $blueprint->dropColumn(['notification_data', 'notification_type']);
        });
    }
}
