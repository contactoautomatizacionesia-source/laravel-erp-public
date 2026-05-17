<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder principal del módulo de sanciones.
 * Ejecuta todos los seeders de tablas paramétricas en orden.
 *
 * Uso:
 *   php artisan db:seed --class=SanctionsModuleSeeder
 */
class SanctionsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatOffenseTypeSeeder::class,
            CatSanctionTypeSeeder::class,
            CatActionTypeSeeder::class,
            CatComplaintSourceSeeder::class,
            CatMitigatingFactorSeeder::class,
            CatProcessStatusSeeder::class,
        ]);
    }
}