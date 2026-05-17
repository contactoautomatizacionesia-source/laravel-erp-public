<?php

namespace Modules\GeneralSetting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ParameterSetting extends Model
{
    protected $fillable = [
        'parameter_name',
        'slug',
        'is_active',
        'min_value',
        'max_value',
        'value_limit',
        'monetary_value',
        'json_value',
        'staff_id',
        'created_by',
        'updated_by'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
