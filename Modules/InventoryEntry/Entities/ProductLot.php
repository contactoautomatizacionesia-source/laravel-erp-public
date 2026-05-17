<?php

namespace Modules\InventoryEntry\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ProductLot extends Model
{
    protected $table = 'product_lots';

    protected $guarded = ['id'];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiration_date'  => 'date',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function entries()
    {
        return $this->hasMany(InventoryEntry::class, 'lot_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ─── Estado calculado ─────────────────────────────────────────

    /**
     * Retorna el estado del lote basado en la fecha de vencimiento.
     * vigente | por_vencer (≤30 días) | vencido
     */
    public function getStatusAttribute(): string
    {
        if (!$this->expiration_date) {
            return 'vigente';
        }

        $expiration = Carbon::parse($this->expiration_date);

        if ($expiration->isPast()) {
            return 'vencido';
        }

        return $expiration->diffInDays(Carbon::today()) <= 30 ? 'por_vencer' : 'vigente';
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiration_date')
              ->orWhere('expiration_date', '>', Carbon::today());
        });
    }

    public function scopeExpiring($query)
    {
        return $query->whereBetween('expiration_date', [
            Carbon::today(),
            Carbon::today()->addDays(30),
        ]);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<', Carbon::today());
    }
}
