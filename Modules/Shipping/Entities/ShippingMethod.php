<?php

namespace Modules\Shipping\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Spatie\Translatable\HasTranslations;

class ShippingMethod extends Model
{
    use HasFactory , HasTranslations;
    protected $guarded = ['id'];
    protected $casts = ['cost' => 'double','is_active' => 'integer','is_approved' => 'integer','carrier_id' => 'integer','minimum_shopping' => 'double','is_active' => "integer",'is_approved' => "integer",'request_by_user' => "integer"];
    public $translatable = ['method_name'];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

    }

    public function request_user()
    {
        return $this->belongsTo(User::class,'request_by_user','id')->withDefault();
    }

    public function methodUse(){
        return $this->hasMany(ProductShipping::class,'shipping_method_id', 'id');
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class,'carrier_id','id')->withDefault();
    }

    public function getShipmentTimeLabelAttribute()
    {
        $value = $this->attributes['shipment_time'] ?? null;
        if (!$value || !is_string($value)) {
            return $value;
        }

        $unitTranslations = [
            'days' => __('common.days'),
            'day' => __('common.day'),
            'hrs' => __('common.hrs'),
            'hours' => __('common.hours'),
        ];

        $parts = preg_split('/\s+/', trim($value), 2);
        if (count($parts) < 2) {
            return $value;
        }

        $range = $parts[0];
        $unit = strtolower($parts[1]);

        return $range . ' ' . ($unitTranslations[$unit] ?? $parts[1]);
    }
}
