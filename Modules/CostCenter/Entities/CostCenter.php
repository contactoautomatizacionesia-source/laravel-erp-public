<?php

namespace Modules\CostCenter\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Modules\Setup\Entities\City;
use Modules\Product\Entities\Brand;
use App\Models\User;

use Modules\GeneralSetting\Entities\Catalogs\PaymentForm;

class CostCenter extends Model
{
    use SoftDeletes;

    protected $table = 'cost_centers';

    protected $fillable = [
        'code',
        'name',
        'city_id',
        'address',
        'pin_code',
        'phone',
        'brand_id',
        'payment_form_id',
        'comment',
        'status',
        'is_default',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id'        => 'integer',
        'code'      => 'string',
        'name'      => 'string',
        'city_id'   => 'integer',
        'brand_id'  => 'integer',
        'payment_form_id' => 'integer',
        'pin_code'        => 'string',
        'status'          => 'integer',
        'is_default'      => 'integer',
        'created_by'=> 'integer',
        'updated_by'=> 'integer',
    ];

    protected $dates = ['deleted_at'];

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

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function paymentForm()
    {
        return $this->belongsTo(PaymentForm::class, 'payment_form_id');
    }
    public function users()
    {
        return $this->hasMany(User::class, 'cost_center_id');
    }

    /**
     * Relación: Inventarios del centro de costo
     */
    public function inventories()
    {
        return $this->hasMany(CostCenterInventory::class, 'cost_center_id', 'id');
    }

    /**
     * Relación: Movimientos de inventario (como origen)
     */
    public function outgoingMovements()
    {
        return $this->hasMany(CostCenterInventoryMovement::class, 'source_id', 'id')
                    ->where('source_type', 'cost_center');
    }

    /**
     * Relación: Movimientos de inventario (como destino)
     */
    public function incomingMovements()
    {
        return $this->hasMany(CostCenterInventoryMovement::class, 'destination_id', 'id')
                    ->where('destination_type', 'cost_center');
    }

    /**
     * Obtener inventario disponible de un SKU
     */
    public function getSkuInventory($skuId)
    {
        return $this->inventories()
                    ->where('product_sku_id', $skuId)
                    ->first();
    }

    /**
     * Obtener balance de un SKU
     */
    public function getSkuBalance($skuId)
    {
        return $this->inventories()
                    ->where('product_sku_id', $skuId)
                    ->value('qty') ?? 0;
    }
}
