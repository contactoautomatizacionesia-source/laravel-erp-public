<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pobla los tipos de falta disciplinaria.
 * Referencia: Manual del Empresario, secciones 2.17.1 a 2.17.3
 */
class CatOffenseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $offenseTypes = [
            [
                'code'        => 'MINOR',
                'name'        => 'Falta Leve',
                'description' => 'Violación, vulneración o incumplimiento a las normas de la empresa '
                    . '(Manual del Empresario, circulares, resoluciones o políticas de Lifehuni) '
                    . 'que no cause ningún tipo de perjuicio para la empresa.',
                'level'       => 1,
                'is_active'   => true,
            ],
            [
                'code'        => 'MODERATE',
                'name'        => 'Falta Moderada',
                'description' => 'Infracción que causa perjuicio moderado a la empresa: afectación al buen nombre, '
                    . 'aspectos comerciales, problemas generados con el multinivel o '
                    . 'competencia desleal al interior de los EUI.',
                'level'       => 2,
                'is_active'   => true,
            ],
            [
                'code'        => 'SEVERE',
                'name'        => 'Falta Grave',
                'description' => 'Infracción que causa perjuicio grave a la empresa: afectación económica, '
                    . 'actos de competencia desleal con perjuicios económicos, utilización de '
                    . 'información empresarial sensible, o cualquier conducta que no se adecúe '
                    . 'a los presupuestos de falta leve o moderada.',
                'level'       => 3,
                'is_active'   => true,
            ],
        ];

        foreach ($offenseTypes as $type) {
            DB::table('cat_offense_types')->updateOrInsert(
                ['code' => $type['code']],
                array_merge($type, [
                    'id'         => Str::uuid()->toString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}