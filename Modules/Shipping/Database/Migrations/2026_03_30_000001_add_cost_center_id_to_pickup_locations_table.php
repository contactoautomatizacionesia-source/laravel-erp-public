<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCostCenterIdToPickupLocationsTable extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_center_id')->nullable()->after('id');
            $table->foreign('cost_center_id')->references('id')->on('cost_centers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pickup_locations', function (Blueprint $table) {
            $table->dropForeign(['cost_center_id']);
            $table->dropColumn('cost_center_id');
        });
    }
}
