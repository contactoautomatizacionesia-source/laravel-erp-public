<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->string('reference_code')->nullable()->unique()->after('shipping_guide');
        });

        Schema::table('cost_center_inventory_movements', function (Blueprint $table) {
            $table->string('reference_code')->nullable()->after('shipping_guide');
            $table->index('reference_code');
        });
    }

    public function down()
    {
        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->dropUnique(['reference_code']);
            $table->dropColumn('reference_code');
        });

        Schema::table('cost_center_inventory_movements', function (Blueprint $table) {
            $table->dropIndex(['reference_code']);
            $table->dropColumn('reference_code');
        });
    }
};
