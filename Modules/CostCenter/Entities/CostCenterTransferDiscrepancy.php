<?php

namespace Modules\CostCenter\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\GeneralSetting\Entities\Catalogs\Novelty;

class CostCenterTransferDiscrepancy extends Model
{
    protected $table = 'cost_center_transfer_discrepancies';
    protected $guarded = ['id'];

    public function transferItem()
    {
        return $this->belongsTo(CostCenterTransferItem::class, 'transfer_item_id');
    }

    // Aquí implementamos la relación con system_catalogs que sugeriste
    public function novelty()
    {
        return $this->belongsTo(Novelty::class, 'novelty_id');
    }
}
