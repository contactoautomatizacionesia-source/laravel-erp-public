<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // key: PERSONAL_SALES → POINTS_PER_CYCLE
        DB::table('rule_category')
            ->where('key', 'PERSONAL_SALES')
            ->update([
                'key'         => 'POINTS_PER_CYCLE',
                'name'        => json_encode([
                    'es' => 'Puntos por Ciclo',
                    'en' => 'Cycle Points',
                ]),
                'description' => json_encode([
                    'es' => 'Define la cantidad de puntos requeridos por ciclo y la fuente de red desde la que deben provenir',
                    'en' => 'Defines the required points per cycle and the network source they must come from',
                ]),
                'updated_at'  => now(),
            ]);

        DB::table('form_sections')
            ->where('owner_key', 'PERSONAL_SALES')
            ->update([
                'owner_key'  => 'POINTS_PER_CYCLE',
                'updated_at' => now(),
            ]);

        // key: MAINTENANCE → RULE_GROUPING
        DB::table('rule_category')
            ->where('key', 'MAINTENANCE')
            ->update([
                'key'         => 'RULE_GROUPING',
                'name'        => json_encode([
                    'es' => 'Agrupación de Reglas',
                    'en' => 'Rule Grouping',
                ]),
                'description' => json_encode([
                    'es' => 'Agrupa múltiples reglas bajo una condición lógica AND u OR, permitiendo construir requisitos compuestos de forma jerárquica',
                    'en' => 'Groups multiple rules under an AND or OR logical condition, allowing hierarchical composite requirements to be built',
                ]),
                'updated_at'  => now(),
            ]);
    }

    public function down(): void
    {
        // key: POINTS_PER_CYCLE → PERSONAL_SALES
        DB::table('rule_category')
            ->where('key', 'POINTS_PER_CYCLE')
            ->update([
                'key'         => 'PERSONAL_SALES',
                'name'        => json_encode([
                    'es' => 'Ventas Personales por Ciclos',
                    'en' => 'Personal Sales by Cycles',
                ]),
                'description' => json_encode([
                    'es' => 'Valida que las ventas personales lleguen a un total usando combinaciones válidas de ciclos',
                    'en' => 'Validates that personal sales reach a total using valid cycle combinations',
                ]),
                'updated_at'  => now(),
            ]);

        DB::table('form_sections')
            ->where('owner_key', 'POINTS_PER_CYCLE')
            ->update([
                'owner_key'  => 'PERSONAL_SALES',
                'updated_at' => now(),
            ]);

        // key: RULE_GROUPING → MAINTENANCE
        DB::table('rule_category')
            ->where('key', 'RULE_GROUPING')
            ->update([
                'key'         => 'MAINTENANCE',
                'name'        => json_encode([
                    'es' => 'Mantenimiento del Plan',
                    'en' => 'Plan Maintenance',
                ]),
                'description' => json_encode([
                    'es' => 'Condición compuesta: el empresario debe cumplir un conjunto de reglas unidas por AND u OR',
                    'en' => 'Compound condition: the entrepreneur must meet a set of rules joined by AND or OR',
                ]),
                'updated_at'  => now(),
            ]);
    }
};
