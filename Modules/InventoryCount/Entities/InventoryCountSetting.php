<?php

namespace Modules\InventoryCount\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\CostCenter\Entities\CostCenter;
use Modules\RolePermission\Entities\Role;
use App\Models\User;

class InventoryCountSetting extends Model
{
    protected $table = 'inventory_count_settings';

    protected $fillable = [
        'cost_center_id',
        'count_role_id',
        'max_attempts',
        'allow_history_view',
        'notify_user_ids',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'notify_user_ids'    => 'array',
        'max_attempts'       => 'integer',
        'allow_history_view' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
        });
        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id')->withTrashed();
    }

    public function countRole()
    {
        return $this->belongsTo(Role::class, 'count_role_id');
    }

    public function notifyUsers()
    {
        return User::whereIn('id', $this->notify_user_ids ?? [])->get();
    }

    public function hasLimit(): bool
    {
        return $this->max_attempts > 0;
    }
}
