<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsManualAssignmentToCustomerProfilesTable extends Migration
{
    public function up()
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->boolean('is_manual_assignment')->default(false)->after('plan_child_id')
                  ->comment('true = plan asignado manualmente por administrador; false = asignado por flujo automático (compra, evaluación, etc.)');
        });
    }

    public function down()
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->dropColumn('is_manual_assignment');
        });
    }
}
