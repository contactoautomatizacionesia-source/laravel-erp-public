<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneCodesToCustomerProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            // Indicativo para Whatsapp
            $table->unsignedBigInteger('whatsapp_country_code_id')
                  ->nullable()
                  ->after('whatsapp')
                  ->comment('ID del catálogo country_phone_code');

            // Indicativo para Llamadas
            $table->unsignedBigInteger('phone_calls_code_id')
                  ->nullable()
                  ->after('phone_calls')
                  ->comment('ID del catálogo country_phone_code');

            // Indicativo para Oficina
            $table->unsignedBigInteger('phone_office_code_id')
                  ->nullable()
                  ->after('phone_office')
                  ->comment('ID del catálogo country_phone_code');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_country_code_id',
                'phone_calls_code_id',
                'phone_office_code_id'
            ]);
        });
    }
}
