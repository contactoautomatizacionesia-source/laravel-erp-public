<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGenderIdColumnToCustomerProfilesTable extends Migration
{
    public function up()
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            // Lo agregamos en el "Paso 2" junto a los otros catálogos
            $table->unsignedBigInteger('gender_id')->nullable()->after('lead_source_id');
        });
    }

    public function down()
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->dropColumn('gender_id');
        });
    }
}
