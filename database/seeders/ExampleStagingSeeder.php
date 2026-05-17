<?php

namespace Database\Seeders;

use App\Seeders\Contracts\StagingSeeder;
use Illuminate\Database\Seeder;

/**
 * EJEMPLO DE REFERENCIA — StagingSeeder
 * =======================================
 * Este archivo es documentación ejecutable. No inserta datos reales.
 * Ejecuta: php artisan seed:staging --dry-run
 *
 * PROPÓSITO: StagingSeeder
 *   Para datos de prueba / fixtures que solo tienen sentido en develop o local.
 *   Se ejecuta con: php artisan seed:staging (NUNCA llega a producción)
 *
 * REGLAS:
 *   - Implementar StagingSeeder (no DeployableSeeder)
 *   - Nunca truncate() / delete()
 *   - Usar updateOrInsert() o firstOrCreate() — idempotente igual que DeployableSeeder
 *   - seed:staging ejecuta primero los DeployableSeeder, luego los StagingSeeder
 *     así que los catálogos reales ya están disponibles cuando este corre
 */
class ExampleStagingSeeder extends Seeder implements StagingSeeder
{
    public function run(): void
    {
        $this->command->info('[ExampleStagingSeeder] Seeder de ejemplo ejecutado correctamente.');
        $this->command->line('  Este seeder es solo de referencia y no inserta datos reales.');
        $this->command->line('  Úsalo para usuarios de prueba, fixtures de demo, configuraciones locales.');
        $this->command->warn('  NUNCA implementes DeployableSeeder aquí — esto no llega a producción.');

        /*
         * Ejemplo real — descomentar y adaptar:
         *
         * DB::table('users')->updateOrInsert(
         *     ['email' => 'tester@example.local'],
         *     [
         *         'name'       => 'Usuario Tester',
         *         'password'   => bcrypt('password'),
         *         'role_id'    => 2,
         *         'created_at' => now(),
         *         'updated_at' => now(),
         *     ]
         * );
         */
    }
}
