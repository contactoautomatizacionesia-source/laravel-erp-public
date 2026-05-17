<?php

namespace Modules\GeneralSetting\Entities\Catalogs;

use Illuminate\Database\Eloquent\Builder;

class CountryPhoneCode extends BaseCatalog
{
    public static function getCatalogType(): string { return 'country_phone_code'; }
}