<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateIncidentSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('incident_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('statement_deadline_hours')->default(48);
            $table->boolean('auto_escalate_on_deadline')->default(true);
            $table->boolean('send_email_notifications')->default(true);
            $table->boolean('send_system_notifications')->default(true);
            $table->boolean('send_deadline_reminder')->default(true);
            $table->unsignedInteger('reminder_hours_before')->default(24);
            $table->enum('price_reference', ['public_price', 'cost_price', 'transfer_price'])->default('public_price');
            $table->timestamp('updated_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });

        // Seed singleton row
        DB::table('incident_settings')->insert([
            'statement_deadline_hours'  => 48,
            'auto_escalate_on_deadline' => true,
            'send_email_notifications'  => true,
            'send_system_notifications' => true,
            'send_deadline_reminder'    => true,
            'reminder_hours_before'     => 24,
            'price_reference'           => 'public_price',
            'updated_at'                => now(),
            'updated_by'                => null,
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('incident_settings');
    }
}
