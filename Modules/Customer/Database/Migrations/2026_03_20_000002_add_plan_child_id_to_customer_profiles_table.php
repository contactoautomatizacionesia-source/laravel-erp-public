<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlanChildIdToCustomerProfilesTable extends Migration
{
    public function up(): void
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            // Plan activo actual del empresario — desnormalizado para consultas rápidas.
            // La fuente de verdad del historial está en entrepreneur_plan_history.
            $table->unsignedBigInteger('plan_child_id')
                ->nullable()
                ->after('representative_id')
                ->comment('Plan activo actual del empresario (FK a plan_child)');

            $table->foreign('plan_child_id')
                ->references('id')->on('plan_child')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->dropForeign(['plan_child_id']);
            $table->dropColumn('plan_child_id');
        });
    }
}
