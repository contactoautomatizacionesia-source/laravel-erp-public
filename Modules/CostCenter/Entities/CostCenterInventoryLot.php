<?php

namespace Modules\CostCenter\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\ProductSku;
use Modules\InventoryEntry\Entities\ProductLot;

class CostCenterInventoryLot extends Model
{
    protected $table = 'cost_center_inventory_lots';
    protected $guarded = ['id'];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function productSku()
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id');
    }

    public function lot()
    {
        return $this->belongsTo(ProductLot::class, 'lot_id');
    }
}
