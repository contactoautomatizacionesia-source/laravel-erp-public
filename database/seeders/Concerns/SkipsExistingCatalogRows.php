<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Trait para insertar filas en catálogos de forma idempotente.
 *
 * Verifica si cada fila ya existe usando las columnas de identidad indicadas.
 * Solo inserta si no existe — no actualiza filas existentes.
 * Para actualizar además de insertar, usar DB::table()->updateOrInsert() directamente.
 *
 * Uso:
 *   use Database\Seeders\Concerns\SkipsExistingCatalogRows;
 *
 *   $this->insertMissingRows('my_table', $rows, ['code']);
 */
trait SkipsExistingCatalogRows
{
    protected function insertMissingRows(string $table, array $rows, array $identityColumns = ['id']): void
    {
        foreach ($rows as $row) {
            $query = DB::table($table);

            foreach ($identityColumns as $column) {
                if (! array_key_exists($column, $row)) {
                    continue;
                }

                $query->orWhere($column, $row[$column]);
            }

            if ($query->exists()) {
                continue;
            }

            DB::table($table)->insert($row);
        }
    }
}
