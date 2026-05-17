<?php

namespace Modules\Setup\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Setup\Entities\City;
use Modules\Setup\Entities\Country;
use Modules\Setup\Entities\State;

class CoverageDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            Country::whereRaw('LOWER(code) != ?', ['co'])->update([
                'status' => 0,
            ]);

            Country::whereRaw('LOWER(code) = ?', ['co'])->update([
                'is_default' => true,
            ]);

            State::whereHas('country', function ($query) {
                $query->where('status', 0);
            })->update([
                'status' => 0,
            ]);

            City::whereHas('state', function ($query) {
                $query->where('status', 0);
            })->update([
                'status' => 0,
            ]);
        });

        info('CoverageDefaultsSeeder: done');
    }
}
