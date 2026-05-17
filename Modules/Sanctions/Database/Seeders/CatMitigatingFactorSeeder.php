<?php

namespace Modules\Sanctions\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pobla los factores atenuantes que pueden reducir la sanción aplicada al EUI.
 * Referencia: Manual del Empresario, sección 2.17.5
 */
class CatMitigatingFactorSeeder extends Seeder
{
    public function run(): void
    {
        $factors = [
            [
                'code'        => 'ACCEPTS_OFFENSE',
                'description' => 'El EUI acepta la falta cometida, evitando desgaste en la investigación.',
                'is_active'   => true,
            ],
            [
                'code'        => 'COOPERATES',
                'description' => 'El EUI colabora activamente con la investigación por lo sucedido.',
                'is_active'   => true,
            ],
            [
                'code'        => 'PREVENTED_DAMAGE',
                'description' => 'El EUI realizó actos, actuaciones o procedimientos tendientes a evitar '
                    . 'la generación de perjuicios a la empresa.',
                'is_active'   => true,
            ],
            [
                'code'        => 'NO_MALICE',
                'description' => 'El EUI no cometió la falta con dolo, imprudencia o violación de reglamentos.',
                'is_active'   => true,
            ],
        ];

        foreach ($factors as $factor) {
            DB::table('cat_mitigating_factors')->updateOrInsert(
                ['code' => $factor['code']],
                array_merge($factor, [
                    'id'         => Str::uuid()->toString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
