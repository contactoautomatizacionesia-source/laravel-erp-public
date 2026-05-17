<?php

namespace Modules\Plans\Database\Seeders;

use App\Seeders\Contracts\DeployableSeeder;
use Illuminate\Database\Seeder;
use Database\Seeders\Concerns\SkipsExistingCatalogRows;

class FormOptionsCatalogSeeder extends Seeder implements DeployableSeeder
{
    use SkipsExistingCatalogRows;

    public function run(): void
    {
        $this->insertMissingRows('form_options', [
            [
                'id'           => 3,
                'option_label' => json_encode(['en' => 'Personal points',  'es' => 'Puntos personales']),
                'option_key'   => 'PERSONAL_POINTS',
                'help_text'    => json_encode(['en' => 'Personal points',  'es' => 'Puntos personales']),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'id'           => 4,
                'option_label' => json_encode(['en' => 'Life Network',     'es' => 'Red Life']),
                'option_key'   => 'LIFE_NETWORK',
                'help_text'    => json_encode(['en' => 'Life Network',     'es' => 'Red Life']),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'id'           => 5,
                'option_label' => json_encode(['en' => 'No Life Network',  'es' => 'Red No Life']),
                'option_key'   => 'NO_LIFE_NETWORK',
                'help_text'    => json_encode(['en' => 'No Life Network',  'es' => 'Red No Life']),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'id'           => 6,
                'option_label' => json_encode(['en' => '1st generation',   'es' => '1ª generación']),
                'option_key'   => 'FIRST_GENERATION',
                'help_text'    => json_encode(['en' => '1st generation',   'es' => '1ª generación']),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'id'           => 7,
                'option_label' => json_encode(['en' => '2nd generation',   'es' => '2ª generación']),
                'option_key'   => 'SECOND_GENERATION',
                'help_text'    => json_encode(['en' => '2nd generation',   'es' => '2ª generación']),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'id'           => 8,
                'option_label' => json_encode(['en' => '3rd generation',   'es' => '3ª generación']),
                'option_key'   => 'THIRD_GENERATION',
                'help_text'    => json_encode(['en' => '3rd generation',   'es' => '3ª generación']),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'id'           => 9,
                'option_label' => json_encode(['en' => '4th generation',   'es' => '4ª generación']),
                'option_key'   => 'FOURTH_GENERATION',
                'help_text'    => json_encode(['en' => '4th generation',   'es' => '4ª generación']),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'id'           => 10,
                'option_label' => json_encode(['en' => '5th generation',   'es' => '5ª generación']),
                'option_key'   => 'FIFTH_GENERATION',
                'help_text'    => json_encode(['en' => '5th generation',   'es' => '5ª generación']),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'id'           => 11,
                'option_label' => json_encode(['en' => '6th generation',   'es' => '6ª generación']),
                'option_key'   => 'SIX_GENERATION',
                'help_text'    => json_encode(['en' => '6th generation',   'es' => '6ª generación']),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'id'           => 12,
                'option_label' => json_encode(['en' => 'Cycle 1',          'es' => 'Ciclo 1']),
                'option_key'   => 'CYCLE_1',
                'help_text'    => json_encode(['en' => 'First cycle of the plan.',  'es' => 'Primer ciclo del plan.']),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'id'           => 13,
                'option_label' => json_encode(['en' => 'Cycle 2',          'es' => 'Ciclo 2']),
                'option_key'   => 'CYCLE_2',
                'help_text'    => json_encode(['en' => 'Second cycle of the plan.', 'es' => 'Segundo ciclo del plan.']),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ], ['id', 'option_key']);
    }
}
