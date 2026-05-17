<?php

namespace Modules\CostCenter\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\ProductSku;
use Modules\InventoryEntry\Entities\ProductLot;

class CostCenterTransferItem extends Model
{
    protected $table = 'cost_center_transfer_items';
    protected $guarded = ['id'];

    public function transfer()
    {
        return $this->belongsTo(CostCenterTransfer::class, 'transfer_id');
    }

    public function productSku()
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id');
    }

    public function lot()
    {
        return $this->belongsTo(ProductLot::class, 'lot_id');
    }

    public function discrepancies()
    {
        return $this->hasMany(CostCenterTransferDiscrepancy::class, 'transfer_item_id');
    }
}
