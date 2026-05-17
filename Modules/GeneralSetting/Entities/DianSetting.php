<?php

namespace Modules\GeneralSetting\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Brand; // Importamos el modelo de Marcas de Amazcart

class DianSetting extends Model
{
    protected $fillable = [
        'brand_id',
        'api_url',
        'api_user',
        'api_password',
        'api_token',
        'is_active',
        'connection_status',
        'last_response',
        'resolution_number',
        'resolution_date',
        'invoice_number_from',
        'invoice_number_to',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'api_password' => 'encrypted',
        'is_active' => 'boolean',
        'connection_status' => 'boolean',
        'resolution_date' => 'date',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }
}
