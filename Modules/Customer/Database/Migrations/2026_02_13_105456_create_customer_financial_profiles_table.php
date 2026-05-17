<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerFinancialProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('customer_financial_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();

            // --- Paso 4 ---
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('bank_account_type_id')->nullable();
            $table->string('account_number')->nullable();

            // --- Paso 5 ---
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('work_address')->nullable();
            $table->boolean('public_resources')->default(0);
            $table->boolean('marital_society')->default(0);
            $table->boolean('is_pep')->default(0);
            $table->boolean('pep_family')->default(0);

            // --- Paso 6 ---
            $table->decimal('monthly_income', 15, 2)->nullable();
            $table->decimal('monthly_expenses', 15, 2)->nullable();
            $table->decimal('other_income', 15, 2)->nullable();
            $table->text('other_income_desc')->nullable();
            $table->decimal('total_assets', 15, 2)->nullable();
            $table->decimal('total_liabilities', 15, 2)->nullable();
            $table->decimal('total_equity', 15, 2)->nullable();

            // --- Paso 7 ---
            $table->boolean('ops_foreign_currency')->default(0);
            $table->text('ops_foreign_desc')->nullable();
            $table->boolean('has_foreign_accounts')->default(0);
            $table->text('foreign_bank')->nullable();
            $table->text('foreign_account_number')->nullable();
            $table->text('foreign_currency')->nullable();
            $table->unsignedBigInteger('foreign_country_id')->nullable();
            $table->unsignedBigInteger('foreign_city_id')->nullable();

            // --- Paso 8 ---
            $table->boolean('iva_responsibility')->default(0);
            $table->boolean('rent_retention_agent')->default(0);
            $table->boolean('ica_retention_agent')->default(0);
            $table->boolean('sales_tax_responsible')->default(0);
            $table->boolean('grand_contributor')->default(0);
            $table->boolean('self_withholder')->default(0);
            $table->boolean('source_retention')->default(0);
            $table->text('retention_reason')->nullable();
            $table->boolean('ica_tax')->default(0);
            $table->decimal('ica_rate', 8, 2)->nullable();
            $table->unsignedBigInteger('declaration_city_id')->nullable();
            $table->string('declaration_pdffile')->nullable();
            $table->boolean('has_rut')->default(0);
            $table->string('rut_file')->nullable();

            $table->timestamps();

            // Relación con users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_financial_profiles');
    }
}