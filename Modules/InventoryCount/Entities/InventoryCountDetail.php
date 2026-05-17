<?php

namespace Modules\InventoryCount\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\GeneralSetting\Entities\Catalogs\ObservationType;
use Modules\Product\Entities\Product;

class InventoryCountDetail extends Model
{
    protected $table = 'inventory_count_details';

    protected $fillable = [
        'inventory_count_id',
        'product_id',
        'system_stock',
        'physical_quantity',
        'observation_type_id',
        'is_draft',
    ];

    protected $casts = [
        'system_stock'      => 'integer',
        'physical_quantity' => 'integer',
        'is_draft'          => 'boolean',
    ];

    public function inventoryCount()
    {
        return $this->belongsTo(InventoryCount::class, 'inventory_count_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function observationType()
    {
        return $this->belongsTo(ObservationType::class, 'observation_type_id');
    }

    public function getDifferenceAttribute(): int
    {
        return ($this->physical_quantity ?? 0) - $this->system_stock;
    }

    public function hasDifference(): bool
    {
        return $this->physical_quantity !== null && $this->physical_quantity !== $this->system_stock;
    }
}
