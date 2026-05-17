<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->string('shipping_guide')->nullable()->after('movement_type_id')->index();
        });

        Schema::table('cost_center_inventory_movements', function (Blueprint $table) {
            $table->string('shipping_guide')->nullable()->after('movement_type_id')->index();
        });
    }

    public function down()
    {
        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->dropColumn('shipping_guide');
        });

        Schema::table('cost_center_inventory_movements', function (Blueprint $table) {
            $table->dropColumn('shipping_guide');
        });
    }
};
