<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Actualiza el ENUM field_type en rule_form_fields y benefit_form_fields:
 *  - Renombra 'money' → 'currency'
 *  - Añade 'multiselect'
 */
class UpdateFieldTypeEnumOnFormFields extends Migration
{
    private array $tables = ['rule_form_fields', 'benefit_form_fields'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            // 1. Primero ampliar el ENUM incluyendo 'money' + los nuevos valores
            //    (debe incluir 'money' todavía para no invalidar filas existentes)
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `field_type`
                ENUM('number', 'select', 'boolean', 'text', 'money', 'currency', 'multiselect')
                NOT NULL DEFAULT 'text'");

            // 2. Ahora que 'currency' es válido, migrar datos
            DB::table($table)->where('field_type', 'money')->update(['field_type' => 'currency']);

            // 3. Quitar 'money' del ENUM (ya no quedan filas con ese valor)
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `field_type`
                ENUM('number', 'select', 'boolean', 'text', 'currency', 'multiselect')
                NOT NULL DEFAULT 'text'");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            // 1. Ampliar el ENUM para que 'money' sea válido de nuevo
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `field_type`
                ENUM('number', 'select', 'boolean', 'text', 'money', 'currency', 'multiselect')
                NOT NULL DEFAULT 'text'");

            // 2. Revertir datos
            DB::table($table)->where('field_type', 'currency')->update(['field_type' => 'money']);
            DB::table($table)->where('field_type', 'multiselect')->update(['field_type' => 'text']);

            // 3. Dejar solo los valores originales
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `field_type`
                ENUM('number', 'select', 'boolean', 'text', 'money')
                NOT NULL DEFAULT 'text'");
        }
    }
}
