<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Corregir cash_session_payments:
        // El diseño original FK-eaba a payment_methods (pasarelas externas).
        // La entidad correcta es system_catalogs (type='payment_form') que contiene
        // Efectivo, Tarjeta Crédito, Datáfono, Transferencia, etc.
        Schema::table('cash_session_payments', function (Blueprint $table) {
            // Eliminar FK y columna anterior
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');

            // Nueva columna apuntando a system_catalogs
            $table->unsignedBigInteger('payment_form_id')
                  ->after('session_id')
                  ->comment('ID de system_catalogs type=payment_form (Efectivo, Datáfono, etc.)');

            $table->foreign('payment_form_id')
                  ->references('id')
                  ->on('system_catalogs')
                  ->onDelete('restrict');
        });

        // Tabla de configuraciones paramétricas de caja
        // Almacena pares key→value por caja (base_amount, alert_threshold, operator_roles, etc.)
        Schema::create('cash_box_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('cash_box_id')
                  ->constrained('cash_boxes')
                  ->onDelete('cascade');
            $table->string('key', 60)
                  ->comment('Clave de configuración: operator_roles, allowed_payment_forms, etc.');
            $table->json('value')
                  ->comment('Valor serializado (JSON para arrays, string para escalares)');
            $table->timestamps();

            $table->unique(['cash_box_id', 'key'], 'uq_box_setting');
        });

        // Tabla de configuraciones globales del módulo (sin FK a caja)
        // Permite definir roles autorizados a operar cajas a nivel global
        Schema::create('cash_manager_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique()
                  ->comment('Clave global: operator_role_ids, manager_role_ids, etc.');
            $table->json('value')
                  ->comment('Valor JSON (array de IDs para listas de roles)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_manager_settings');
        Schema::dropIfExists('cash_box_settings');

        Schema::table('cash_session_payments', function (Blueprint $table) {
            $table->dropForeign(['payment_form_id']);
            $table->dropColumn('payment_form_id');

            // Nullable para permitir rollback con filas existentes sin datos previos.
            // La columna original era NOT NULL pero al revertir con datos no es posible
            // satisfacer la constraint sin truncar — aceptamos nullable en rollback.
            $table->unsignedBigInteger('payment_method_id')
                  ->nullable()
                  ->after('session_id');

            $table->foreign('payment_method_id')
                  ->references('id')
                  ->on('payment_methods')
                  ->onDelete('set null');
        });
    }
};
