<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryEntryAuditsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_entry_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entry_id');
            $table->string('action', 30); // modified | deleted
            $table->text('notes');
            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->json('before_payload')->nullable();
            $table->json('after_payload')->nullable();
            $table->timestamps();

            $table->foreign('entry_id')
                ->references('id')->on('product_inventory_entries')
                ->onDelete('cascade');

            $table->foreign('responsible_id')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->index(['entry_id', 'action']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_entry_audits');
    }
}
