<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHolidayAllowedToRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Agrega la columna 'holiday_allowed' a la tabla 'roles'
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('holiday_allowed')->nullable()->default(null)->after('module');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['holiday_allowed']);
        });
    }
}
