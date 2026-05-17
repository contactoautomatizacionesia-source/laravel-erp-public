<?php

namespace Modules\CashManager\Database\Seeders;

use App\Seeders\Contracts\DeployableSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatDenominationSeeder extends Seeder implements DeployableSeeder
{
    private const COLOMBIA_NAME_PATTERN = '%Colombia%';

    public function run(): void
    {
        $country = DB::table('countries')
            ->where(function ($q) {
                $q->where('code', 'CO')->where('name', 'like', self::COLOMBIA_NAME_PATTERN);
            })
            ->orWhere(function ($q) {
                $q->where('code', 'COL')->where('name', 'like', self::COLOMBIA_NAME_PATTERN);
            })
            ->first();

        if (!$country) {
            $country = DB::table('countries')
                ->where('name', 'like', self::COLOMBIA_NAME_PATTERN)
                ->first();
        }

        if (!$country) {
            $this->command->error('País Colombia no encontrado. Ejecute primero el seeder de países.');
            return;
        }

        $denominations = [
            ['type' => 'BILLETE', 'value' => 100000.00, 'image' => '100k_front.png'],
            ['type' => 'BILLETE', 'value' => 50000.00,  'image' => '50k_front.png'],
            ['type' => 'BILLETE', 'value' => 20000.00,  'image' => '20k_front.png'],
            ['type' => 'BILLETE', 'value' => 10000.00,  'image' => '10k_front.png'],
            ['type' => 'BILLETE', 'value' => 5000.00,   'image' => '5k_front.png'],
            ['type' => 'BILLETE', 'value' => 2000.00,   'image' => '2k_front.png'],
            ['type' => 'MONEDA',  'value' => 1000.00,   'image' => '1000_coin.png'],
            ['type' => 'MONEDA',  'value' => 500.00,    'image' => '500_coin.png'],
            ['type' => 'MONEDA',  'value' => 200.00,    'image' => '200_coin.png'],
            ['type' => 'MONEDA',  'value' => 100.00,    'image' => '100_coin.png'],
            ['type' => 'MONEDA',  'value' => 50.00,     'image' => '50_coin.png'],
        ];

        foreach ($denominations as $den) {
            $existing = DB::table('cat_denominations')
                ->where('value', $den['value'])
                ->where('type', $den['type'])
                ->where('country_id', $country->id)
                ->first();

            if (!$existing) {
                DB::table('cat_denominations')->insert([
                    'id'         => Str::uuid()->toString(),
                    'country_id' => $country->id,
                    'type'       => $den['type'],
                    'value'      => $den['value'],
                    'image_url'  => $den['image'],
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Solo actualizar campos seguros, nunca el id (tiene FK en cash_session_denominations)
                DB::table('cat_denominations')
                    ->where('id', $existing->id)
                    ->update([
                        'image_url'  => $den['image'],
                        'is_active'  => true,
                        'updated_at' => now(),
                    ]);
            }
        }
    }
}
