<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLotIdToCostCenterInventoryMovementsTable extends Migration
{
    public function up()
    {
        Schema::table('cost_center_inventory_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('lot_id')->nullable()->after('product_sku_id');
            $table->index('lot_id');
            $table->foreign('lot_id')
                ->references('id')->on('product_lots')
                ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::table('cost_center_inventory_movements', function (Blueprint $table) {
            $table->dropForeign(['lot_id']);
            $table->dropIndex(['lot_id']);
            $table->dropColumn('lot_id');
        });
    }
}
