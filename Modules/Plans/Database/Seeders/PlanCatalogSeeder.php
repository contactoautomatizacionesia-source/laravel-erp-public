<?php

namespace Modules\Plans\Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Concerns\SkipsExistingCatalogRows;

class PlanCatalogSeeder extends Seeder
{
    use SkipsExistingCatalogRows;

    public function run(): void
    {
        // =========================================================
        // SEED: Escalas de plan
        // =========================================================
        $this->insertMissingRows('plan_scale', [
            ['id' => 1, 'label' => json_encode(['es' => 'Diario',   'en' => 'Daily']),   'key' => 'DAILY',   'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'label' => json_encode(['es' => 'Semanal',  'en' => 'Weekly']),  'key' => 'WEEKLY',  'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'label' => json_encode(['es' => 'Mensual',  'en' => 'Monthly']), 'key' => 'MONTHLY', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'label' => json_encode(['es' => 'Ciclo',    'en' => 'Cycle']),   'key' => 'CYCLE',   'created_at' => now(), 'updated_at' => now()],
        ], ['id', 'key']);

        // =========================================================
        // SEED: Tipos de ciclo de plan
        // =========================================================
        $this->insertMissingRows('plan_cycle_type', [
            ['id' => 1, 'label' => json_encode(['es' => 'Quincenal',    'en' => 'Bi-weekly']),    'key' => 'BIWEEKLY', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'label' => json_encode(['es' => 'Mensual',      'en' => 'Monthly']),      'key' => 'MONTHLY',  'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'label' => json_encode(['es' => 'Personalizado','en' => 'Custom']),       'key' => 'CUSTOM',   'created_at' => now(), 'updated_at' => now()],
        ], ['id', 'key']);
    }
}
