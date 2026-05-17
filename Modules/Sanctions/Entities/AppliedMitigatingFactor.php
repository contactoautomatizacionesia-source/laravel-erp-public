<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;

class AppliedMitigatingFactor extends Model
{
    protected $table = 'applied_mitigating_factors';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'investigation_id',
        'mitigating_factor_id',
        'justification',
    ];

    public function investigation()
    {
        return $this->belongsTo(Investigation::class, 'investigation_id');
    }

    public function mitigatingFactor()
    {
        return $this->belongsTo(CatMitigatingFactor::class, 'mitigating_factor_id');
    }
}
