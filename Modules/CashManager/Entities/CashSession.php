<?php

namespace Modules\CashManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\CashManager\Entities\Traits\HasUuid;

class CashSession extends Model
{
    use HasUuid;

    protected $table = 'cash_sessions';

    protected $fillable = [
        'assignment_id',
        'opened_at',
        'closed_at',
        'opening_base',
        'total_system_expected',
        'total_physical_counted',
        'discrepancy_amount',
        'status',           // 'OPEN' | 'PENDING_RECEIPT' | 'CLOSED'
        'has_incidents',
        'reviewer_notes',
    ];

    protected $casts = [
        'opened_at'              => 'datetime',
        'closed_at'              => 'datetime',
        'opening_base'           => 'decimal:2',
        'total_system_expected'  => 'decimal:2',
        'total_physical_counted' => 'decimal:2',
        'discrepancy_amount'     => 'decimal:2',
        'has_incidents'          => 'boolean',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────────

    public function assignment()
    {
        return $this->belongsTo(CashBoxAssignment::class, 'assignment_id');
    }

    public function denominations()
    {
        return $this->hasMany(CashSessionDenomination::class, 'session_id');
    }

    public function payments()
    {
        return $this->hasMany(CashSessionPayment::class, 'session_id');
    }

    public function discrepancies()
    {
        return $this->hasMany(CashDiscrepancy::class, 'session_id');
    }

    public function transfers()
    {
        return $this->hasMany(CashTransfer::class, 'origin_session_id');
    }

    // ─── Helpers de jerarquía ────────────────────────────────────────────────────

    /**
     * Retorna la caja a la que pertenece esta sesión (via assignment).
     */
    public function getBox(): ?CashBox
    {
        return $this->assignment?->box;
    }

    /**
     * Retorna la caja superior (parent) de la caja de esta sesión.
     */
    public function getParentBox(): ?CashBox
    {
        return $this->getBox()?->parentBox;
    }

    /**
     * Verifica si el usuario $userId es el responsable (asignación activa) de la caja superior.
     * Usado para validar quién puede confirmar recepción.
     */
    public function parentBoxIsAssignedTo(int $userId): bool
    {
        $parentBox = $this->getParentBox();

        if (!$parentBox) {
            return false;
        }

        return CashBoxAssignment::where('cash_box_id', $parentBox->id)
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->exists();
    }

    /**
     * Para una caja PRINCIPAL: indica si todas sus cajas AUXILIARY activas tienen sesión CLOSED.
     * Cuando es true, el responsable de la PRINCIPAL puede enviar el reporte al VAULT.
     */
    public function allChildSessionsClosed(): bool
    {
        $box = $this->getBox();

        if (!$box) {
            return false;
        }

        // Cajas hijas activas (OPEN o pendientes de recibir)
        $pendingChildren = CashBox::where('parent_id', $box->id)
            ->whereIn('status', ['OPEN', 'PENDING_RECEIPT'])
            ->exists();

        return !$pendingChildren;
    }
}
