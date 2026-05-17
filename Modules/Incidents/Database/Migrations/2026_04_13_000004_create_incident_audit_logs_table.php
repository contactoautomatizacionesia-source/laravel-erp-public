<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateIncidentAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('incident_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('incident_id');
            $table->foreign('incident_id')->references('id')->on('incidents')->onDelete('restrict');

            $table->string('actor_label', 100);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('action', 255);
            $table->string('previous_status', 50)->nullable();
            $table->string('new_status', 50)->nullable();
            $table->json('metadata')->nullable();

            // Solo created_at — tabla de solo inserción
            $table->timestamp('created_at')->useCurrent();

            $table->index('incident_id', 'idx_incident_audit_logs_incident');
            $table->index('created_at', 'idx_incident_audit_logs_created_at');
        });

        // Triggers MySQL que bloquean UPDATE y DELETE — tabla inmutable
        DB::unprepared("
            CREATE TRIGGER prevent_incident_audit_log_update
            BEFORE UPDATE ON incident_audit_logs
            FOR EACH ROW
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'incident_audit_logs rows are immutable and cannot be updated';
        ");

        DB::unprepared("
            CREATE TRIGGER prevent_incident_audit_log_delete
            BEFORE DELETE ON incident_audit_logs
            FOR EACH ROW
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'incident_audit_logs rows are immutable and cannot be deleted';
        ");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_incident_audit_log_update');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_incident_audit_log_delete');
        Schema::dropIfExists('incident_audit_logs');
    }
}
