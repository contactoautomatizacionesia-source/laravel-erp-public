<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();

            // --- Paso 1 ---
            $table->unsignedBigInteger('document_type_id')->nullable();
            $table->string('document_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedBigInteger('birth_city_id')->nullable();
            $table->date('issue_date')->nullable();
            $table->unsignedBigInteger('issue_city_id')->nullable();
            $table->date('expiration_date')->nullable();

            // --- Paso 2 ---
            $table->string('whatsapp')->nullable();
            $table->string('phone_calls')->nullable();
            $table->string('phone_office')->nullable();
            $table->string('secondary_email')->nullable();
            
            $table->unsignedBigInteger('civil_status_id')->nullable();
            $table->unsignedBigInteger('economic_activity_id')->nullable();
            $table->unsignedBigInteger('profession_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('lead_source_id')->nullable();

            // --- Paso 3 ---
            $table->string('front_id_image')->nullable();
            $table->string('back_id_image')->nullable();

            // --- Paso 9 ---
            $table->string('code')->nullable();
            $table->date('registration_date')->nullable();
            $table->unsignedBigInteger('contract_type_id')->nullable();
            $table->unsignedBigInteger('representative_id')->nullable();

            $table->timestamps();
            
            // Relación con users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_profiles');
    }
}