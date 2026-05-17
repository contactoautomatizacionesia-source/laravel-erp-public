<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Product\Database\Seeders\BrandTableSeeder;
use Modules\Product\Database\Seeders\CategorySeedTableSeeder;
use Modules\Product\Database\Seeders\ProductDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * SEEDER PARA DESARROLLO LOCAL
     * * REGLAS PARA DESARROLLADORES:
     * 1. Este seeder es para desarrollo local, no debe contener datos de producción.
     * 2. Solo agregar seeders que inserten DATOS DE PRUEBA.
     * 3. Todos los seeders llamados aquí DEBEN ser IDEMPOTENTES (usar updateOrCreate).
     *
     * @return void
     */
    public function run()
    {
        $this->call(CategorySeedTableSeeder::class);
        $this->call(BrandTableSeeder::class);
        $this->call(ProductDatabaseSeeder::class);
    }
}
