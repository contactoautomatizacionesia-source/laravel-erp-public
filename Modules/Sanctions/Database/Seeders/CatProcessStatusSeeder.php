<?php

namespace Modules\Sanctions\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
