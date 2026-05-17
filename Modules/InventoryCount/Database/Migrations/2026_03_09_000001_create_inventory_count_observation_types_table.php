<?php

use Illuminate\Database\Migrations\Migration;
use Modules\GeneralSetting\Entities\Catalogs\ObservationType;

class CreateInventoryCountObservationTypesTable extends Migration
{
    public function up()
    {
        $types = [
            ['es' => 'Dañado',        'en' => 'Damaged'],
            ['es' => 'Sobrante',      'en' => 'Surplus'],
            ['es' => 'Extraviado',    'en' => 'Missing'],
            ['es' => 'Vencido',       'en' => 'Expired'],
            ['es' => 'En mal estado', 'en' => 'In poor condition'],
        ];

        foreach ($types as $i => $name) {
            ObservationType::firstOrCreate(
                ['name->es' => $name['es']],
                [
                    'name'       => $name,
                    'sort_order' => $i + 1,
                    'is_active'  => true,
                ]
            );
        }
    }

    public function down()
    {
        ObservationType::whereIn('name->es', ['Dañado', 'Sobrante', 'Extraviado', 'Vencido', 'En mal estado'])->delete();
    }
}
