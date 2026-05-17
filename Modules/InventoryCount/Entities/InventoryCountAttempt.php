<?php

namespace Modules\InventoryCount\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class InventoryCountAttempt extends Model
{
    protected $table = 'inventory_count_attempts';

    public $timestamps = false;

    protected $fillable = [
        'inventory_count_id',
        'user_id',
        'attempt_number',
        'result',
        'device_info',
        'attempted_at',
    ];

    protected $casts = [
        'device_info'  => 'array',
        'attempted_at' => 'datetime',
    ];

    public function inventoryCount()
    {
        return $this->belongsTo(InventoryCount::class, 'inventory_count_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
