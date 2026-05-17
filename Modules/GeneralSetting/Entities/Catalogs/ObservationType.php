<?php

namespace Modules\GeneralSetting\Entities\Catalogs;

class ObservationType extends BaseCatalog
{
    public static function getCatalogType(): string { return 'inventory_observation_type'; }

    public static function getUsageReferences(): array
    {
        return [
            'inventory_count_details' => 'observation_type_id',
        ];
    }
}
