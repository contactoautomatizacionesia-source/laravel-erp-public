<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceItemIdToIncidentsTable extends Migration
{
    public function up()
    {
        Schema::table('incidents', function (Blueprint $table) {
            // Referencia al ítem específico dentro del documento fuente
            // (ej: transfer_item_id cuando source_type = 'cost_center_transfer')
            $table->unsignedBigInteger('source_item_id')->nullable()->after('source_id');

            $table->index(['source_type', 'source_id', 'source_item_id'], 'idx_incidents_source_item');
        });
    }

    public function down()
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropIndex('idx_incidents_source_item');
            $table->dropColumn('source_item_id');
        });
    }
}
