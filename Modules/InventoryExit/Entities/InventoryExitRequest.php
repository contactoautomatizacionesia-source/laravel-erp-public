<?php

namespace Modules\InventoryExit\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\GeneralSetting\Entities\Catalogs\InventoryOutReason;
use Modules\CostCenter\Entities\CostCenter;
use App\Models\User;

class InventoryExitRequest extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_exit_requests';
    protected $guarded = ['id'];

    protected $casts = [
        'exit_date'   => 'date',
        'approved_at' => 'datetime',
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function exitReason()
    {
        return $this->belongsTo(InventoryOutReason::class, 'exit_reason_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'location_id');
    }

    /**
     * Nombre legible de la ubicación origen (bodega principal o nombre del CC).
     */
    public function locationLabel(): string
    {
        if ($this->location_type === 'main') {
            return __('costcenter::main_warehouse.name');
        }
        return $this->costCenter?->name ?? "CC #{$this->location_id}";
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(InventoryExitItem::class, 'inventory_exit_request_id');
    }

    public function documents()
    {
        return $this->hasMany(InventoryExitDocument::class, 'inventory_exit_request_id');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Impedir eliminación si tiene documentos soporte (documentos oficiales).
     * Llamar antes de soft-delete cuando se requiera.
     */
    public function hasOfficialDocuments(): bool
    {
        return $this->documents()->exists();
    }
}
