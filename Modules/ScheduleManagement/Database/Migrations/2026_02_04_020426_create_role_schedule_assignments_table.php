<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasTable('role_schedule_assignments')) {
            return;
        }

        Schema::create('role_schedule_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('schedule_id');

            // Relaciones con integridad referencial
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('schedule_id')->references('id')->on('role_work_schedules')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_schedule_assignments');
    }
};