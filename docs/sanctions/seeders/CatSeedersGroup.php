<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pobla las acciones concretas que Lifehuni puede ejecutar sobre el EUI.
 * Referencia: Manual del Empresario, secciones 2.18.1 a 2.18.6
 */
class CatActionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $actionTypes = [
            [
                'code'        => 'WRITTEN_NOTICE',
                'name'        => 'Llamado de Atención',
                'description' => 'Envío de comunicación escrita al EUI indicando la infracción cometida. '
                    . 'Puede incluir las cláusulas del manual infringidas, políticas o el Código de Ética. '
                    . 'Puede incluir suspensión parcial de beneficios como compra de productos.',
                'is_active'   => true,
            ],
            [
                'code'        => 'COMMITMENT_TRAINING',
                'name'        => 'Compromiso y Capacitación',
                'description' => 'El EUI debe asistir a la capacitación programada por Lifehuni para corregir '
                    . 'la infracción y recibir sugerencias basadas en el manual de negocio, '
                    . 'reglas de comportamiento y comité de ética.',
                'is_active'   => true,
            ],
            [
                'code'        => 'FREEZE_EARNINGS',
                'name'        => 'Congelamiento de Utilidades',
                'description' => 'Congelamiento de utilidades, beneficios, calificaciones, bonos, incentivos '
                    . 'y la actividad de representación, incluyendo invitaciones a seminarios, viajes y eventos.',
                'is_active'   => true,
            ],
            [
                'code'        => 'BLOCK_ORDERS',
                'name'        => 'Bloqueo de Pedidos',
                'description' => 'Bloqueo temporal del código del EUI para la realización de pedidos. '
                    . 'La duración será informada en la carta decisoria.',
                'is_active'   => true,
            ],
            [
                'code'        => 'SUSPEND_MULTILEVEL',
                'name'        => 'Suspensión de Actividad Multinivel',
                'description' => 'Suspensión de todos los privilegios del contrato de vinculación como EUI, '
                    . 'incluyendo la actividad de representar y participación en eventos de Lifehuni.',
                'is_active'   => true,
            ],
            [
                'code'        => 'BLOCK_QUALIFICATION',
                'name'        => 'Bloqueo de Calificación',
                'description' => 'Retención o devolución de calificaciones, utilidades, beneficios, bonos '
                    . 'y premios a discreción de Lifehuni, si hay necesidad de recompensar '
                    . 'al EUI involucrado, clientes o terceros vinculados.',
                'is_active'   => true,
            ],
            [
                'code'        => 'OFFENSE_REPARATION',
                'name'        => 'Reparación de la Infracción',
                'description' => 'Retención de utilidades, beneficios, bonos y premios para recompensar '
                    . 'al EUI involucrado, clientes o terceros vinculados que hayan sufrido perjuicio.',
                'is_active'   => true,
            ],
            [
                'code'        => 'TERMINATE_CONTRACT',
                'name'        => 'Terminación del Contrato',
                'description' => 'Finalización definitiva del contrato de vinculación como Empresario Universal '
                    . 'Independiente. El EUI deberá devolver todos los productos y servicios en su posesión, '
                    . 'cesar el uso de marcas registradas y dejar de identificarse como EUI.',
                'is_active'   => true,
            ],
        ];

        foreach ($actionTypes as $type) {
            DB::table('cat_action_types')->updateOrInsert(
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


/**
 * Pobla las fuentes o canales de denuncia que dan inicio a la investigación.
 * Referencia: Manual del Empresario, sección 2.17 (párrafo inicial)
 */
class CatComplaintSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            [
                'code'      => 'OWN_INITIATIVE',
                'name'      => 'Iniciativa Propia de Lifehuni',
                'is_active' => true,
            ],
            [
                'code'      => 'THIRD_PARTY',
                'name'      => 'Información de Terceros',
                'is_active' => true,
            ],
            [
                'code'      => 'NEWS',
                'name'      => 'Noticias o Medios Públicos',
                'is_active' => true,
            ],
            [
                'code'      => 'DATA_MESSAGE',
                'name'      => 'Mensaje de Datos (correo, WhatsApp, SMS)',
                'is_active' => true,
            ],
            [
                'code'      => 'OTHER',
                'name'      => 'Otro Medio',
                'is_active' => true,
            ],
        ];

        foreach ($sources as $source) {
            DB::table('cat_complaint_sources')->updateOrInsert(
                ['code' => $source['code']],
                array_merge($source, [
                    'id'         => Str::uuid()->toString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}


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


/**
 * Pobla los estados del proceso disciplinario.
 * Define el flujo de vida de una investigación.
 */
class CatProcessStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'code'      => 'OPEN',
                'name'      => 'Abierto',
                'is_active' => true,
            ],
            [
                'code'      => 'AWAITING_DEFENSE',
                'name'      => 'En Espera de Descargos',
                'is_active' => true,
            ],
            [
                'code'      => 'IN_RESOLUTION',
                'name'      => 'En Resolución',
                'is_active' => true,
            ],
            [
                'code'      => 'APPEALED',
                'name'      => 'Apelado ante Comité',
                'is_active' => true,
            ],
            [
                'code'      => 'CLOSED',
                'name'      => 'Cerrado',
                'is_active' => true,
            ],
        ];

        foreach ($statuses as $status) {
            DB::table('cat_process_statuses')->updateOrInsert(
                ['code' => $status['code']],
                array_merge($status, [
                    'id'         => Str::uuid()->toString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}