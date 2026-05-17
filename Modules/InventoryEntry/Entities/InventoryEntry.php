<?php

namespace Modules\InventoryEntry\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryEntry extends Model
{
    use SoftDeletes;

    protected $table = 'product_inventory_entries';

    protected $guarded = ['id'];

    protected $casts = [
        'quantity'  => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function lot()
    {
        return $this->belongsTo(ProductLot::class, 'lot_id');
    }

    public function productSku()
    {
        return $this->belongsTo(\Modules\Product\Entities\ProductSku::class, 'product_sku_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function audits()
    {
        return $this->hasMany(InventoryEntryAudit::class, 'entry_id');
    }

    public function latestAudit()
    {
        return $this->hasOne(InventoryEntryAudit::class, 'entry_id')->latestOfMany();
    }

    public function latestDeletedAudit()
    {
        return $this->hasOne(InventoryEntryAudit::class, 'entry_id')
            ->where('action', 'deleted')
            ->latestOfMany();
    }

    public function latestModifiedAudit()
    {
        return $this->hasOne(InventoryEntryAudit::class, 'entry_id')
            ->where('action', 'modified')
            ->latestOfMany();
    }
}
