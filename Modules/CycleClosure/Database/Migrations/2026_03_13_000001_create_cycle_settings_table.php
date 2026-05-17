<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCycleSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('cycle_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('period_type', ['daily', 'monthly', 'annual']);
            $table->tinyInteger('execution_day')->unsigned()->nullable()->comment('1-31, only for monthly period');
            $table->unsignedBigInteger('executor_user_id')->nullable()->comment('Designated executor — notified when cron pre-validation fails; must be Admin or SuperAdmin');
            $table->unsignedBigInteger('approver_user_id')->comment('Co-approver for cycle execution — must be Contador (role 27)');
            $table->unsignedBigInteger('configured_by');
            $table->boolean('is_active')->default(false)->comment('True = current active config; false = superseded');
            $table->json('payload')->comment('Exact snapshot of the configuration at save time');
            $table->timestamps();

            $table->foreign('executor_user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approver_user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('configured_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cycle_settings');
    }
}
