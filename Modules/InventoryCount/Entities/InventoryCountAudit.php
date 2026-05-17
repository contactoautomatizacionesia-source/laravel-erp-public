<?php

namespace Modules\InventoryCount\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class InventoryCountAudit extends Model
{
    protected $table = 'inventory_count_audits';

    protected $fillable = [
        'inventory_count_id',
        'auditor_id',
        'status',
        'notes',
        'created_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_by = Auth::id();
        });
    }

    public function inventoryCount()
    {
        return $this->belongsTo(InventoryCount::class, 'inventory_count_id');
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }
}
