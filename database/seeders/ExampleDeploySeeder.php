<?php

namespace Database\Seeders;

use App\Seeders\Contracts\DeployableSeeder;
use Database\Seeders\Concerns\SkipsExistingCatalogRows;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * EJEMPLO DE REFERENCIA — DeployableSeeder
 * ==========================================
 * Este archivo es documentación ejecutable. No inserta datos reales.
 * Ejecuta: php artisan seed:deploy --dry-run
 *          php artisan seed:staging --dry-run
 *
 * PROPÓSITO: DeployableSeeder
 *   Para catálogos de sistema que deben existir en TODOS los entornos.
 *   Se ejecuta con: php artisan seed:deploy
 *
 * REGLAS:
 *   - Implementar DeployableSeeder
 *   - Nunca truncate() / delete()
 *   - Usar updateOrInsert(['code' => ...], $row) como patrón principal
 *   - O usar el trait SkipsExistingCatalogRows para lotes grandes
 *   - Modificar este mismo archivo al agregar filas nuevas — no crear uno nuevo
 */
class ExampleDeploySeeder extends Seeder implements DeployableSeeder
{
    use SkipsExistingCatalogRows;

    public function run(): void
    {
        $this->command->info('[ExampleDeploySeeder] Seeder de ejemplo ejecutado correctamente.');
        $this->command->line('  Este seeder es solo de referencia y no inserta datos reales.');
        $this->command->line('  Patrón con updateOrInsert:');
        $this->command->line('    DB::table(\'my_catalog\')->updateOrInsert([\'code\' => \'val\'], $row);');
        $this->command->line('  Patrón con trait SkipsExistingCatalogRows:');
        $this->command->line('    $this->insertMissingRows(\'my_catalog\', $rows, [\'code\']);');

        /*
         * Ejemplo real — descomentar y adaptar:
         *
         * $rows = [
         *     ['code' => 'type_a', 'name' => 'Tipo A', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
         *     ['code' => 'type_b', 'name' => 'Tipo B', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
         * ];
         *
         * foreach ($rows as $row) {
         *     DB::table('my_catalog')->updateOrInsert(['code' => $row['code']], $row);
         * }
         *
         * // O con el trait (solo inserta, no actualiza):
         * $this->insertMissingRows('my_catalog', $rows, ['code']);
         */
    }
}
