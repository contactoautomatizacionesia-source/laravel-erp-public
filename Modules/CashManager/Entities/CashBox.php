<?php

namespace Modules\CashManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\CashManager\Entities\Traits\HasUuid;
use Modules\CostCenter\Entities\CostCenter;

class CashBox extends Model
{
    use HasUuid;

    protected $table = 'cash_boxes';

    protected $fillable = [
        'cost_center_id',
        'parent_id',
        'code',
        'name',
        'type', // 'VAULT', 'PRINCIPAL', 'AUXILIARY'
        'base_amount',
        'alert_threshold',
        'status' // 'AVAILABLE', 'OPEN', 'MAINTENANCE', 'INACTIVE'
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'alert_threshold' => 'decimal:2'
    ];

    // --- Relaciones ---
    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function parentBox()
    {
        return $this->belongsTo(CashBox::class, 'parent_id');
    }

    public function childBoxes()
    {
        return $this->hasMany(CashBox::class, 'parent_id');
    }

    public function assignments()
    {
        return $this->hasMany(CashBoxAssignment::class);
    }
}
