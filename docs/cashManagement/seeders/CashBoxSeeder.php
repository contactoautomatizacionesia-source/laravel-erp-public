<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CashBoxSeeder extends Seeder
{
    public function run(): void
    {
        // Asumimos un Centro de Costo existente (ej. Principal Bogotá)
        $costCenter = DB::table('cost_centers')->first();

        if (!$costCenter) return;

        // 1. Crear Caja Principal de la Sucursal
        $principalId = Str::uuid()->toString();
        DB::table('cash_boxes')->updateOrInsert(
            ['code' => 'BOG-MAIN-01'],
            [
                'id'             => $principalId,
                'cost_center_id' => $costCenter->id,
                'parent_id'      => null,
                'name'           => 'Caja Principal Bogotá',
                'type'           => 'PRINCIPAL',
                'base_amount'    => 500000.00,
                'status'         => 'AVAILABLE',
                'created_at'     => now(),
            ]
        );

        // 2. Crear Cajas Auxiliares (Dependientes de la Principal)
        $auxBoxes = [
            ['code' => 'BOG-AUX-01', 'name' => 'Caja Auxiliar Punto 1', 'base' => 100000.00],
            ['code' => 'BOG-AUX-02', 'name' => 'Caja Auxiliar Punto 2', 'base' => 100000.00],
        ];

        foreach ($auxBoxes as $box) {
            DB::table('cash_boxes')->updateOrInsert(
                ['code' => $box['code']],
                [
                    'id'             => Str::uuid()->toString(),
                    'cost_center_id' => $costCenter->id,
                    'parent_id'      => $principalId,
                    'name'           => $box['name'],
                    'type'           => 'AUXILIARY',
                    'base_amount'    => $box['base'],
                    'alert_threshold'=> 5000000.00, // Alerta a los 5 millones
                    'status'         => 'AVAILABLE',
                    'created_at'     => now(),
                ]
            );
        }
    }
}