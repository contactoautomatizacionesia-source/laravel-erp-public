<?php

namespace Modules\GeneralSetting\Entities\Catalogs;

class EconomicActivity extends BaseCatalog
{
    public static function getCatalogType(): string { return 'economic_activity'; }

    public function getDisplayNameAttribute()
    {
        return "{$this->code} - {$this->name}";
    }
}