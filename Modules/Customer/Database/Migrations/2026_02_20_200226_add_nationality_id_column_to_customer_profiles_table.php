<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNationalityIdColumnToCustomerProfilesTable extends Migration
{
    public function up()
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            // Lo agregamos en el "Paso 2" junto a los otros catálogos
            $table->unsignedBigInteger('nationality_id')->nullable()->after('expiration_date');
        });
    }

    public function down()
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->dropColumn('nationality_id');
        });
    }
}
