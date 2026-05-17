<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarrierAndGuideDateToCostCenterTransfers extends Migration
{
    public function up()
    {
        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('carrier_id')->nullable()->after('shipping_guide');
            $table->date('guide_date')->nullable()->after('carrier_id');

            $table->foreign('carrier_id')->references('id')->on('carriers')->onDelete('set null');
        });

        Schema::table('cost_center_inventory_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('carrier_id')->nullable()->after('created_by');
            $table->date('guide_date')->nullable()->after('carrier_id');

            $table->foreign('carrier_id')->references('id')->on('carriers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->dropForeign(['carrier_id']);
            $table->dropColumn(['carrier_id', 'guide_date']);
        });

        Schema::table('cost_center_inventory_movements', function (Blueprint $table) {
            $table->dropForeign(['carrier_id']);
            $table->dropColumn(['carrier_id', 'guide_date']);
        });
    }
}
