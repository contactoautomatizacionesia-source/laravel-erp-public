<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeAuditLogLabelsJson extends Migration
{
    public function up()
    {
        // Convertir los valores existentes a JSON usando ALTER TABLE con DEFAULT expression.
        // Un ALTER TABLE que modifica el tipo de columna no dispara triggers DML (INSERT/UPDATE/DELETE),
        // por lo que es la única forma segura de migrar datos en una tabla con triggers inmutables.
        //
        // Estrategia:
        // 1. Añadir columnas temporales tipo JSON.
        // 2. Poblarlas con ALTER TABLE ... SET DEFAULT / UPDATE via ALTER (opera en DDL, no dispara triggers).
        //    En MySQL no hay UPDATE sin trigger; usamos ALTER TABLE MODIFY con expresiones generadas,
        //    o bien borramos y recreamos los triggers durante la ventana de migración.
        // 3. Opción más compatible: DROP triggers → UPDATE datos → re-CREATE triggers → MODIFY columnas.

        // Eliminar triggers temporalmente para poder migrar los datos
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_incident_audit_log_update');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_incident_audit_log_delete');

        // Convertir actor_label: strings planos → {"es":"...","en":"..."}
        DB::unprepared("
            UPDATE incident_audit_logs
            SET actor_label = JSON_OBJECT('es', actor_label, 'en', actor_label)
            WHERE actor_label IS NOT NULL
              AND JSON_VALID(actor_label) = 0
        ");

        // Convertir action: strings planos → {"es":"...","en":"..."}
        DB::unprepared("
            UPDATE incident_audit_logs
            SET action = JSON_OBJECT('es', action, 'en', action)
            WHERE action IS NOT NULL
              AND JSON_VALID(action) = 0
        ");

        // Cambiar tipo de columna a JSON
        Schema::table('incident_audit_logs', function (Blueprint $table) {
            $table->json('actor_label')->change();
            $table->json('action')->change();
        });

        // Restaurar los triggers de inmutabilidad
        DB::unprepared("
            CREATE TRIGGER prevent_incident_audit_log_update
            BEFORE UPDATE ON incident_audit_logs
            FOR EACH ROW
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'incident_audit_logs rows are immutable and cannot be updated'
        ");

        DB::unprepared("
            CREATE TRIGGER prevent_incident_audit_log_delete
            BEFORE DELETE ON incident_audit_logs
            FOR EACH ROW
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'incident_audit_logs rows are immutable and cannot be deleted'
        ");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_incident_audit_log_update');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_incident_audit_log_delete');

        // Revertir JSON → string tomando el valor 'es' como representación plana
        DB::unprepared("
            UPDATE incident_audit_logs
            SET actor_label = JSON_UNQUOTE(JSON_EXTRACT(actor_label, '$.es'))
            WHERE JSON_VALID(actor_label) = 1
        ");

        DB::unprepared("
            UPDATE incident_audit_logs
            SET action = JSON_UNQUOTE(JSON_EXTRACT(action, '$.es'))
            WHERE JSON_VALID(action) = 1
        ");

        Schema::table('incident_audit_logs', function (Blueprint $table) {
            $table->string('actor_label', 100)->change();
            $table->string('action', 255)->change();
        });

        DB::unprepared("
            CREATE TRIGGER prevent_incident_audit_log_update
            BEFORE UPDATE ON incident_audit_logs
            FOR EACH ROW
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'incident_audit_logs rows are immutable and cannot be updated'
        ");

        DB::unprepared("
            CREATE TRIGGER prevent_incident_audit_log_delete
            BEFORE DELETE ON incident_audit_logs
            FOR EACH ROW
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'incident_audit_logs rows are immutable and cannot be deleted'
        ");
    }
}
