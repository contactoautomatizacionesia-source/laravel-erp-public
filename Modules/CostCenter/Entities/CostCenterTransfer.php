<?php

namespace Modules\CostCenter\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Modules\GeneralSetting\Entities\Catalogs\CostCenterMovementType;
use Modules\Shipping\Entities\Carrier;

class CostCenterTransfer extends Model
{
    protected $table = 'cost_center_transfers';
    protected $guarded = ['id'];

    public function movementType()
    {
        return $this->belongsTo(CostCenterMovementType::class, 'movement_type_id', 'id');
    }

    public function sourceCostCenter()
    {
        return $this->belongsTo(CostCenter::class, 'source_id', 'id');
    }

    public function destinationCostCenter()
    {
        return $this->belongsTo(CostCenter::class, 'destination_id', 'id');
    }

    public function dispatchedBy()
    {
        return $this->belongsTo(User::class, 'dispatched_by', 'id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class, 'carrier_id', 'id');
    }

    // Relación con el detalle (Kardex)
    public function movements()
    {
        return $this->hasMany(CostCenterInventoryMovement::class, 'reference_id', 'id')
            ->where('reference_type', 'transfer');
    }
    /**
     * Items específicos de la transferencia
     */
    public function items()
    {
        return $this->hasMany(CostCenterTransferItem::class, 'transfer_id', 'id');
    }

    /**
     * Accesos rápidos a las discrepancias a través de los items
     */
    public function discrepancies()
    {
        return $this->hasManyThrough(
            CostCenterTransferDiscrepancy::class,
            CostCenterTransferItem::class,
            'transfer_id', // Foreign key on items table
            'transfer_item_id', // Foreign key on discrepancies table
            'id', // Local key on transfers table
            'id' // Local key on items table
        );
    }
}
