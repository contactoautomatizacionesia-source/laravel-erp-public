<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;

class CatMitigatingFactor extends Model
{
    protected $table = 'cat_mitigating_factors';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function appliedFactors()
    {
        return $this->hasMany(AppliedMitigatingFactor::class, 'mitigating_factor_id');
    }
}
