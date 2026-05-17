<?php

namespace Modules\Sanctions\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pobla los tipos de sanción por nivel de reincidencia.
 * Referencia: Manual del Empresario, secciones 2.17.1 a 2.17.3 y 2.18.2
 */
class CatSanctionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $sanctionTypes = [
            // ------------------------------------------------------------------
            // Sanciones para FALTAS LEVES (sec. 2.17.1 y 2.18.2)
            // ------------------------------------------------------------------
            [
                'code'                => 'WRITTEN_WARNING',
                'name'                => 'Llamado de Atención Escrito',
                'description'         => 'Sanción escrita que puede incluir el envío de las cláusulas del manual '
                    . 'de reglas de comportamiento, la Política de Lifehuni o el Código de Ética del EUI, '
                    . 'incluyendo suspensión parcial de algunos beneficios como compra de productos.',
                'first_offense_text'  => 'Se emite llamado de atención escrito al EUI.',
                'second_offense_text' => 'Se emite llamado de atención escrito con copia al historial del EUI.',
                'third_offense_text'  => null,
                'is_active'           => true,
            ],

            // ------------------------------------------------------------------
            // Sanciones para FALTAS MODERADAS (sec. 2.17.2)
            // ------------------------------------------------------------------
            [
                'code'                => 'SUSPENSION_1_5_DAYS',
                'name'                => 'Suspensión Multinivel 1 a 5 Días',
                'description'         => 'Suspensión del servicio de multinivel por un período de 1 a 5 días. '
                    . 'Aplicable como primera sanción por falta moderada.',
                'first_offense_text'  => 'Suspensión del servicio de multinivel entre 1 y 5 días.',
                'second_offense_text' => null,
                'third_offense_text'  => null,
                'is_active'           => true,
            ],
            [
                'code'                => 'SUSPENSION_6_15_DAYS',
                'name'                => 'Suspensión Multinivel 6 a 15 Días',
                'description'         => 'Suspensión del servicio de multinivel por un período de 6 a 15 días. '
                    . 'Aplicable como segunda sanción por falta moderada.',
                'first_offense_text'  => null,
                'second_offense_text' => 'Suspensión del servicio de multinivel entre 6 y 15 días.',
                'third_offense_text'  => null,
                'is_active'           => true,
            ],
            [
                'code'                => 'SUSPENSION_16_30_DAYS',
                'name'                => 'Suspensión Multinivel 16 a 30 Días',
                'description'         => 'Suspensión del servicio de multinivel por un período de 16 a 30 días. '
                    . 'Aplicable como tercera sanción por falta moderada.',
                'first_offense_text'  => null,
                'second_offense_text' => null,
                'third_offense_text'  => 'Suspensión del servicio de multinivel entre 16 y 30 días.',
                'is_active'           => true,
            ],

            // ------------------------------------------------------------------
            // Sanciones para FALTAS GRAVES (sec. 2.17.3)
            // ------------------------------------------------------------------
            [
                'code'                => 'SUSPENSION_2_3_MONTHS',
                'name'                => 'Suspensión 2 a 3 Meses',
                'description'         => 'Suspensión del servicio de multinivel por un período de 2 a 3 meses. '
                    . 'Aplicable como primera sanción por falta grave.',
                'first_offense_text'  => 'Suspensión del servicio de multinivel entre 2 y 3 meses.',
                'second_offense_text' => null,
                'third_offense_text'  => null,
                'is_active'           => true,
            ],
            [
                'code'                => 'SUSPENSION_3_6_MONTHS',
                'name'                => 'Suspensión 3.5 a 6 Meses',
                'description'         => 'Suspensión del servicio de multinivel por un período de 3.5 a 6 meses. '
                    . 'Aplicable como segunda sanción por falta grave.',
                'first_offense_text'  => null,
                'second_offense_text' => 'Suspensión del servicio de multinivel entre 3.5 y 6 meses.',
                'third_offense_text'  => null,
                'is_active'           => true,
            ],
            [
                'code'                => 'CONTRACT_TERMINATION',
                'name'                => 'Terminación Unilateral del Contrato',
                'description'         => 'Terminación definitiva del contrato de vinculación como Empresario Universal '
                    . 'Independiente. El EUI pierde todos sus derechos sobre el acuerdo de registro, '
                    . 'incluyendo derechos de líneas derivadas, generación, calificaciones y títulos. '
                    . 'Su red ascenderá inmediatamente al EUI que era su representante.',
                'first_offense_text'  => null,
                'second_offense_text' => null,
                'third_offense_text'  => 'Terminación unilateral del contrato de vinculación como EUI.',
                'is_active'           => true,
            ],
        ];

        foreach ($sanctionTypes as $type) {
            DB::table('cat_sanction_types')->updateOrInsert(
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
