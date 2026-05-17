<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;

class SanctionEnforcement extends Model
{
    protected $table = 'sanction_enforcements';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'resolution_id',
        'enforcement_type',
        'suspend_multilevel',
        'freeze_earnings',
        'block_orders',
        'block_qualification',
        'terminate_contract',
        'applied_at',
        'lifted_at',
    ];

    protected $casts = [
        'suspend_multilevel'  => 'boolean',
        'freeze_earnings'     => 'boolean',
        'block_orders'        => 'boolean',
        'block_qualification' => 'boolean',
        'terminate_contract'  => 'boolean',
        'applied_at'          => 'date',
        'lifted_at'           => 'date',
    ];

    public function resolution()
    {
        return $this->belongsTo(SanctionResolution::class, 'resolution_id');
    }
}
