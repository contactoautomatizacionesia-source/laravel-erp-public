<?php

namespace Modules\GeneralSetting\Entities\Catalogs;

class CostCenterMovementType extends BaseCatalog
{
    /**
     * Retorna el tipo de catálogo
     */
    public static function getCatalogType(): string
    {
        return 'cost_center_movement_type';
    }

    /**
     * Define dónde se usan estos registros para proteger eliminaciones
     */
    public static function getUsageReferences(): array
    {
        return [
            'cost_center_inventory_movements' => 'movement_type_id'
        ];
    }
}
