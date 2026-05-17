<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCycleLogsTable extends Migration
{
    public function up()
    {
        Schema::create('cycle_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cycle_id');
            $table->enum('phase', [
                'pre_validation',
                'consolidation',
                'pdf_generation',
                'block',
                'notification',
            ]);
            $table->enum('level', ['info', 'warning', 'error', 'success']);
            $table->text('message');
            $table->json('context')->nullable()->comment('Additional structured data');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Who triggered this log entry');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('cycle_id')->references('id')->on('cycles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cycle_logs');
    }
}
