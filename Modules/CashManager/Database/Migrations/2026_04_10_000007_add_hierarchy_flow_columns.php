<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega soporte para el flujo jerárquico completo de caja:
 *
 * 1. cash_discrepancies
 *    - discrepancy_type_id  → system_catalogs (type='cash_discrepancy_type')
 *      Reemplaza el enum 'type' por un catálogo configurable.
 *    - notes                → texto libre (obligatorio cuando code='other')
 *
 * 2. cash_sessions
 *    - has_incidents        → flag booleano: cierre con novedad vs limpio
 *    - reviewer_notes       → notas que agrega el revisor (PRINCIPAL/VAULT) al aprobar
 *
 * 3. cash_transfers
 *    - destination_box_id   → ya existía, pero apuntaba a la misma caja.
 *      Se corrige en el servicio; la columna no necesita cambio de estructura.
 *      Documento aquí solo como referencia del bug original.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── cash_discrepancies ──────────────────────────────────────────────────
        Schema::table('cash_discrepancies', function (Blueprint $table) {
            // Nuevo: tipo como catálogo configurable (nullable para no romper registros existentes)
            $table->unsignedBigInteger('discrepancy_type_id')
                  ->nullable()
                  ->after('session_id')
                  ->comment('FK system_catalogs type=cash_discrepancy_type');

            $table->foreign('discrepancy_type_id')
                  ->references('id')
                  ->on('system_catalogs')
                  ->onDelete('restrict');

            // Nuevo: nota libre — obligatoria cuando discrepancy_type.code = 'other'
            $table->text('notes')->nullable()->after('justification');
        });

        // ── cash_sessions ───────────────────────────────────────────────────────
        Schema::table('cash_sessions', function (Blueprint $table) {
            // Flag: ¿el cierre fue aprobado con novedades?
            $table->boolean('has_incidents')
                  ->default(false)
                  ->after('status');

            // Notas del revisor al confirmar recepción (PRINCIPAL o VAULT)
            $table->text('reviewer_notes')
                  ->nullable()
                  ->after('has_incidents');
        });
    }

    public function down(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropColumn(['has_incidents', 'reviewer_notes']);
        });

        Schema::table('cash_discrepancies', function (Blueprint $table) {
            $table->dropForeign(['discrepancy_type_id']);
            $table->dropColumn(['discrepancy_type_id', 'notes']);
        });
    }
};
