<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropCycleColumnsFromPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plan', function (Blueprint $table) {
            try {
                $table->dropForeign(['plan_cycle_type_id']);
            } catch (\Exception $e) {
                // Ignorar error si la llave foránea no existe
            }
            
            if (Schema::hasColumn('plan', 'plan_cycle_type_id')) {
                $table->dropColumn('plan_cycle_type_id');
            }
            if (Schema::hasColumn('plan', 'custom_days')) {
                $table->dropColumn('custom_days');
            }
        });

        Schema::dropIfExists('plan_cycle_type');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plan', function (Blueprint $table) {
            if (!Schema::hasColumn('plan', 'plan_cycle_type_id')) {
                $table->unsignedBigInteger('plan_cycle_type_id')->nullable();
            }
            if (!Schema::hasColumn('plan', 'custom_days')) {
                $table->integer('custom_days')->nullable();
            }
        });

        Schema::create('plan_cycle_type', function (Blueprint $table) {
            $table->id();
            $table->json('label')->comment('Nombre visible: Quincenal, Mensual, Personalizado');
            $table->string('key', 50)->unique()->comment('Clave interna: BIWEEKLY, MONTHLY, CUSTOM');
            $table->timestamps();
        });
    }
}
