<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReplaceCostCenterIdWithLocationInExitRequests extends Migration
{
    public function up()
    {
        Schema::table('inventory_exit_requests', function (Blueprint $table) {
            $table->dropForeign(['cost_center_id']);
            $table->dropIndex(['cost_center_id']);
            $table->dropColumn('cost_center_id');

            $table->enum('location_type', ['main', 'cost_center'])->default('main')->after('exit_reason_id');
            $table->unsignedBigInteger('location_id')->nullable()->after('location_type');

            $table->index(['location_type', 'location_id']);
        });
    }

    public function down()
    {
        Schema::table('inventory_exit_requests', function (Blueprint $table) {
            $table->dropIndex(['location_type', 'location_id']);
            $table->dropColumn(['location_type', 'location_id']);

            $table->unsignedBigInteger('cost_center_id')->after('exit_reason_id');
            $table->foreign('cost_center_id')->references('id')->on('cost_centers');
            $table->index('cost_center_id');
        });
    }
}
