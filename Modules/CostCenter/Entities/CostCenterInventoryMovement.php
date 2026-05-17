<?php

namespace Modules\CostCenter\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Entities\ProductSku;
use Modules\GeneralSetting\Entities\Catalogs\CostCenterMovementType;
use App\Models\User;
use Modules\InventoryEntry\Entities\ProductLot;

class CostCenterInventoryMovement extends Model
{
    use HasFactory;

    protected $table = 'cost_center_inventory_movements';
    protected $guarded = ['id'];

    protected $casts = [
        'qty' => 'decimal:2',
        'source_type' => 'string',
        'destination_type' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación: Tipo de movimiento (catálogo virtual)
     */
    public function movementType()
    {
        return $this->belongsTo(CostCenterMovementType::class, 'movement_type_id', 'id');
    }

    /**
     * Relación: Producto SKU movido
     */
    public function productSku()
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id', 'id');
    }

    /**
     * Relación: Lote asociado (si aplica)
     */
    public function lot()
    {
        return $this->belongsTo(ProductLot::class, 'lot_id', 'id');
    }

    /**
     * Relación: Centro de costo origen (si aplica)
     */
    public function sourceCostCenter()
    {
        return $this->belongsTo(CostCenter::class, 'source_id', 'id');
    }

    /**
     * Relación: Centro de costo destino (si aplica)
     */
    public function destinationCostCenter()
    {
        return $this->belongsTo(CostCenter::class, 'destination_id', 'id');
    }

    /**
     * Relación: Usuario que realizó el movimiento
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Scope: Filtrar por type de origen
     */
    public function scopeFromSource($query, $sourceType, $sourceId = null)
    {
        $query->where('source_type', $sourceType);
        if ($sourceId) {
            $query->where('source_id', $sourceId);
        }
        return $query;
    }

    /**
     * Scope: Filtrar por tipo de destino
     */
    public function scopeToDestination($query, $destinationType, $destinationId = null)
    {
        $query->where('destination_type', $destinationType);
        if ($destinationId) {
            $query->where('destination_id', $destinationId);
        }
        return $query;
    }

    /**
     * Scope: Movimientos de entrada a un centro
     */
    public function scopeIncomingToCenter($query, $centerId)
    {
        return $query->where('destination_type', 'cost_center')
                     ->where('destination_id', $centerId);
    }

    /**
     * Scope: Movimientos de salida de un centro
     */
    public function scopeOutgoingFromCenter($query, $centerId)
    {
        return $query->where('source_type', 'cost_center')
                     ->where('source_id', $centerId);
    }

    /**
     * Scope: Historial de un SKU
     */
    public function scopeForSku($query, $skuId)
    {
        return $query->where('product_sku_id', $skuId)
                     ->orderBy('created_at', 'desc');
    }

    public function transferHeader()
    {
        return $this->belongsTo(CostCenterTransfer::class, 'reference_id', 'id')->where('reference_type', 'transfer');
    }
}
