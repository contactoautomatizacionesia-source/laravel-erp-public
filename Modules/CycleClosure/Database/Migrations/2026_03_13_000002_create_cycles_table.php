<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCyclesTable extends Migration
{
    public function up()
    {
        Schema::create('cycles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('period_label', 7)->comment('Format: YYYY-MM e.g. 2026-03');
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedBigInteger('executor_id');
            $table->unsignedBigInteger('co_approver_id')->nullable();
            $table->enum('status', [
                'running',
                'needs_review',
                'pending_approval',
                'closed',
                'cancelled',
            ])->default('running');
            $table->json('pipeline_detail')->nullable()->comment('Pipeline step results detail');
            $table->timestamp('executed_at')->nullable();
            $table->timestamp('executor_approved_at')->nullable()->comment('Set when executor manually approves a needs_review cycle');
            $table->timestamp('approved_at')->nullable()->comment('Set when co-approver approves the cycle');
            $table->decimal('total_sales', 18, 2)->nullable();
            $table->string('act_path')->nullable()->comment('Path to the generated PDF act');
            $table->timestamps();

            $table->foreign('executor_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('co_approver_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cycles');
    }
}
