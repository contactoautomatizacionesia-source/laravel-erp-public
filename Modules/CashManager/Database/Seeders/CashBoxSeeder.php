<?php

namespace Modules\CashManager\Database\Seeders;

use App\Seeders\Contracts\StagingSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CashBoxSeeder extends Seeder implements StagingSeeder
{
    public function run(): void
    {
        $costCenter = DB::table('cost_centers')->first();

        if (!$costCenter) {
            return; // Sin CC no tiene sentido sembrar cajas
        }

        // Obtener o crear la caja PRINCIPAL
        $principal = DB::table('cash_boxes')->where('code', 'BOG-MAIN-01')->first();

        if (!$principal) {
            $principalId = Str::uuid()->toString();
            DB::table('cash_boxes')->insert([
                'id'             => $principalId,
                'cost_center_id' => $costCenter->id,
                'parent_id'      => null,
                'code'           => 'BOG-MAIN-01',
                'name'           => 'Caja Principal Bogotá',
                'type'           => 'PRINCIPAL',
                'base_amount'    => 500000.00,
                'status'         => 'AVAILABLE',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        } else {
            $principalId = $principal->id;
            // Solo actualizar campos no-sensibles, nunca el id
            DB::table('cash_boxes')->where('code', 'BOG-MAIN-01')->update([
                'name'        => 'Caja Principal Bogotá',
                'base_amount' => 500000.00,
                'updated_at'  => now(),
            ]);
        }

        // Cajas auxiliares
        $auxBoxes = [
            ['code' => 'BOG-AUX-01', 'name' => 'Caja Auxiliar Punto 1', 'base' => 100000.00],
            ['code' => 'BOG-AUX-02', 'name' => 'Caja Auxiliar Punto 2', 'base' => 100000.00],
        ];

        foreach ($auxBoxes as $box) {
            $existing = DB::table('cash_boxes')->where('code', $box['code'])->first();

            if (!$existing) {
                DB::table('cash_boxes')->insert([
                    'id'             => Str::uuid()->toString(),
                    'cost_center_id' => $costCenter->id,
                    'parent_id'      => $principalId,
                    'code'           => $box['code'],
                    'name'           => $box['name'],
                    'type'           => 'AUXILIARY',
                    'base_amount'    => $box['base'],
                    'status'         => 'AVAILABLE',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            } else {
                DB::table('cash_boxes')->where('code', $box['code'])->update([
                    'name'        => $box['name'],
                    'base_amount' => $box['base'],
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
