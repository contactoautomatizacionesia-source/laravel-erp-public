<?php

namespace Modules\CostCenter\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Entities\ProductSku;

class CostCenterInventory extends Model
{
    use HasFactory;

    protected $table = 'cost_center_inventories';
    protected $guarded = ['id'];

    protected $casts = [
        'qty' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Centro de Costo (belongsTo)
     */
    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id', 'id');
    }

    /**
     * Relación: Producto SKU (belongsTo)
     */
    public function productSku()
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id', 'id');
    }

    /**
     * Relación: Movimientos de inventario de este ítem
     */
    public function movements()
    {
        return $this->hasMany(CostCenterInventoryMovement::class, 'product_sku_id', 'product_sku_id')
                    ->where('destination_type', 'cost_center')
                    ->where('destination_id', $this->cost_center_id);
    }

    /**
     * Scope: Buscar inventario por centro y SKU
     */
    public function scopeByCenter($query, $centerId)
    {
        return $query->where('cost_center_id', $centerId);
    }

    /**
     * Scope: Buscar inventario por SKU
     */
    public function scopeBySku($query, $skuId)
    {
        return $query->where('product_sku_id', $skuId);
    }

    /**
     * Scope: Solo con disponibilidad
     */
    public function scopeWithStock($query)
    {
        return $query->where('qty', '>', 0);
    }
}
