<?php

namespace Modules\Plans\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PlansDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call([
            PlanCatalogSeeder::class,
            FormOptionsCatalogSeeder::class,
            RulesCatalogSeeder::class,
            BenefitsCatalogSeeder::class,
        ]);
    }
}
