<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatDenominationSeeder extends Seeder
{
    public function run(): void
    {
        // Buscamos el ID del país (asumiendo que 'Colombia' ya existe)
        $country = DB::table('countries')->where('iso_code', 'COL')->first();

        if (!$country) {
            $this->command->error('País COL no encontrado. Por favor ejecute primero los seeders de países.');
            return;
        }

        $denominations = [
            // Billetes
            ['type' => 'BILLETE', 'value' => 100000.00, 'image' => '100k_front.png'],
            ['type' => 'BILLETE', 'value' => 50000.00,  'image' => '50k_front.png'],
            ['type' => 'BILLETE', 'value' => 20000.00,  'image' => '20k_front.png'],
            ['type' => 'BILLETE', 'value' => 10000.00,  'image' => '10k_front.png'],
            ['type' => 'BILLETE', 'value' => 5000.00,   'image' => '5k_front.png'],
            ['type' => 'BILLETE', 'value' => 2000.00,   'image' => '2k_front.png'],
            // Monedas
            ['type' => 'MONEDA',  'value' => 1000.00,   'image' => '1000_coin.png'],
            ['type' => 'MONEDA',  'value' => 500.00,    'image' => '500_coin.png'],
            ['type' => 'MONEDA',  'value' => 200.00,    'image' => '200_coin.png'],
            ['type' => 'MONEDA',  'value' => 100.00,    'image' => '100_coin.png'],
            ['type' => 'MONEDA',  'value' => 50.00,     'image' => '50_coin.png'],
        ];

        foreach ($denominations as $item) {
            DB::table('cat_denominations')->updateOrInsert(
                ['country_id' => $country->id, 'value' => $item['value']],
                [
                    'id'         => Str::uuid()->toString(),
                    'type'       => $item['type'],
                    'image_url'  => $item['image'],
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}