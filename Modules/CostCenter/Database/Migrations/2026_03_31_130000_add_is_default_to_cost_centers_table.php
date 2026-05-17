<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDefaultToCostCentersTable extends Migration
{
    public function up()
    {
        Schema::table('cost_centers', function (Blueprint $table) {
            $table->boolean('is_default')->default(0)->after('status');
        });
    }

    public function down()
    {
        Schema::table('cost_centers', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
}
