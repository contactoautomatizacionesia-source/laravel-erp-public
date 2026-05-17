<?php

namespace Modules\Sanctions\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
