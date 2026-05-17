<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('rule_category')
            ->where('key', 'LIFE_TITLE_COUNT')
            ->update([
                'description' => json_encode([
                    'es' => 'Valida que existan N empresarios cuyo plan padre esté marcado como título Life debajo de los empresarios del título seleccionado',
                    'en' => 'Validates that N entrepreneurs whose parent plan is marked as a Life title exist beneath entrepreneurs with the selected title',
                ]),
                'updated_at' => now(),
            ]);

        DB::table('form_fields')
            ->where('field_key', 'BENEATH_PLAN')
            ->whereIn('form_section_id', function ($query) {
                $query->select('id')
                    ->from('form_sections')
                    ->where('owner_key', 'LIFE_TITLE_COUNT');
            })
            ->update([
                'help_text' => json_encode([
                    'es' => 'Los empresarios Life se cuentan debajo de los empresarios del título seleccionado.',
                    'en' => 'Life entrepreneurs are counted beneath entrepreneurs with the selected title.',
                ]),
                'updated_at' => now(),
            ]);

        DB::table('form_fields')
            ->where('field_key', 'MIN_COUNT')
            ->whereIn('form_section_id', function ($query) {
                $query->select('id')
                    ->from('form_sections')
                    ->where('owner_key', 'LIFE_TITLE_COUNT');
            })
            ->update([
                'help_text' => json_encode([
                    'es' => 'Total de empresarios cuyo plan padre esté marcado como título Life requeridos en toda la red bajo los empresarios del título seleccionado.',
                    'en' => 'Total entrepreneurs whose parent plan is marked as a Life title required across the entire network beneath entrepreneurs with the selected title.',
                ]),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('rule_category')
            ->where('key', 'LIFE_TITLE_COUNT')
            ->update([
                'description' => json_encode([
                    'es' => 'Valida que existan N empresarios con título Life debajo de los Platinos de la red',
                    'en' => 'Validates that N Life-titled entrepreneurs exist beneath the Platinos in the network',
                ]),
                'updated_at' => now(),
            ]);

        DB::table('form_fields')
            ->where('field_key', 'BENEATH_PLAN')
            ->whereIn('form_section_id', function ($query) {
                $query->select('id')
                    ->from('form_sections')
                    ->where('owner_key', 'LIFE_TITLE_COUNT');
            })
            ->update([
                'help_text' => json_encode([
                    'es' => 'Los empresarios Life se cuentan debajo de los empresarios de este titulo.',
                    'en' => 'Life entrepreneurs are counted beneath entrepreneurs of this title.',
                ]),
                'updated_at' => now(),
            ]);

        DB::table('form_fields')
            ->where('field_key', 'MIN_COUNT')
            ->whereIn('form_section_id', function ($query) {
                $query->select('id')
                    ->from('form_sections')
                    ->where('owner_key', 'LIFE_TITLE_COUNT');
            })
            ->update([
                'help_text' => json_encode([
                    'es' => 'Total de empresarios con título Life requeridos en toda la red bajo los Platinos.',
                    'en' => 'Total Life-titled entrepreneurs required across the entire network beneath Platinos.',
                ]),
                'updated_at' => now(),
            ]);
    }
};
