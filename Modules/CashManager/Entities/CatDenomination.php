<?php

namespace Modules\CashManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CashManager\Entities\Traits\HasUuid;
use App\Models\Country; // Ajusta si tu modelo Country está en otro namespace
use Modules\Setup\Entities\Country as EntitiesCountry;

class CatDenomination extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'cat_denominations';

    protected $fillable = [
        'country_id',
        'type', // 'BILLETE' o 'MONEDA'
        'value',
        'image_url',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'value' => 'decimal:2'
    ];

    public function country()
    {
        return $this->belongsTo(EntitiesCountry::class);
    }
}
