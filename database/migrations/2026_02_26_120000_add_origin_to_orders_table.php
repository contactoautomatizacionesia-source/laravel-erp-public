<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginToOrdersTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('orders', 'origin')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('origin')->nullable()->after('order_type');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('origin');
        });
    }
}
