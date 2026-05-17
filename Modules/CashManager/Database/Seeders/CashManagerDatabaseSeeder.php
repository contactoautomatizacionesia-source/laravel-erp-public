<?php

namespace Modules\CashManager\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class CashManagerDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguard();
        $this->call(CatDenominationSeeder::class);
        $this->call(CashBoxSeeder::class);

    }
}