<?php

namespace Modules\GeneralSetting\Entities\Catalogs;

class CashDiscrepancyType extends BaseCatalog
{
    public static function getCatalogType(): string
    {
        return 'cash_discrepancy_type';
    }

    public static function getUsageReferences(): array
    {
        return [
            'cash_discrepancies' => 'discrepancy_type_id',
        ];
    }

    // Código especial: cuando el tipo es "other", las notas son obligatorias
    public const OTHER_CODE = 'other';
}
