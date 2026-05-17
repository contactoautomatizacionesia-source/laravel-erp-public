<?php

namespace Modules\Sanctions\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

/**
 * Seeder principal del módulo de sanciones.
 * Ejecuta todos los seeders de tablas paramétricas en orden.
 *
 * Uso:
 *   php artisan db:seed --class=Modules\\Sanctions\\Database\\Seeders\\SanctionsDatabaseSeeder
 */
class SanctionsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();

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
