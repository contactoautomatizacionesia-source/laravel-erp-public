<?php

namespace Modules\GeneralSetting\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Modules\GeneralSetting\Entities\Catalogs\ContractType; // Usamos una clase hija para obtener la tabla

class CatalogExists implements Rule
{
    protected $type;
    protected $table;

    public function __construct($type)
    {
        $this->type = $type;
        $this->table = (new ContractType)->getTable(); 
    }

    public function passes($attribute, $value)
    {
        return DB::table($this->table)
            ->where('id', $value)
            ->where('type', $this->type)
            ->where('is_active', 1) 
            ->exists();
    }

    public function message()
    {
        return 'El valor seleccionado para :attribute no es válido o está inactivo.';
    }
}