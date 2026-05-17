<?php

namespace Modules\InventoryExit\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\ProductSku;
use Modules\InventoryEntry\Entities\ProductLot;

class InventoryExitItem extends Model
{
    protected $table = 'inventory_exit_items';
    protected $guarded = ['id'];

    protected $casts = [
        'qty_requested' => 'decimal:2',
        'qty_approved'  => 'decimal:2',
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function exitRequest()
    {
        return $this->belongsTo(InventoryExitRequest::class, 'inventory_exit_request_id');
    }

    public function productSku()
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id');
    }

    public function lot()
    {
        return $this->belongsTo(ProductLot::class, 'lot_id');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Cantidad efectiva a descontar del inventario (la aprobada si existe, si no la solicitada).
     */
    public function effectiveQty(): float
    {
        return (float) ($this->qty_approved ?? $this->qty_requested);
    }
}
