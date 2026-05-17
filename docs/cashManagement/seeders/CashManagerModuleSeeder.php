<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CashManagerModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatDenominationSeeder::class,
            CashBoxSeeder::class,
        ]);
    }
}