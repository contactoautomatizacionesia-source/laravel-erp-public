<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryCountAuditsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_count_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_count_id');
            $table->unsignedBigInteger('auditor_id')->comment('Admin que realiza la auditoría');
            $table->enum('status', ['pending', 'rejected', 'approved'])->default('pending');
            $table->text('notes')->comment('Obligatorio al auditar');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('inventory_count_id')->references('id')->on('inventory_counts')->onDelete('restrict');
            $table->foreign('auditor_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['inventory_count_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_count_audits');
    }
}
