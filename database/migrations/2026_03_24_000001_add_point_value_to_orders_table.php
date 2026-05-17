<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPointValueToOrdersTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('orders', 'point_value')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            // Valor monetario congelado al momento de crear la orden:
            // orders.club_point * club_point_wallets.wallet_point vigente.
            // Nunca se recalcula — si el rate cambia después, este valor no varía.
            $table->decimal('point_value', 20, 4)->default(0)->after('total_points');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('point_value');
        });
    }
}
