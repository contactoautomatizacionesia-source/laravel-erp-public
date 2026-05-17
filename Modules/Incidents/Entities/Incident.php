<?php

namespace Modules\Incidents\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Incidents\Entities\Traits\HasUuid;

class Incident extends Model
{
    use HasUuid;

    protected $table = 'incidents';

    protected $guarded = ['id'];

    protected $casts = [
        'statement_reminder_sent' => 'boolean',
        'statement_expires_at'    => 'datetime',
        'statement_submitted_at'  => 'datetime',
        'resolved_at'             => 'datetime',
        'public_price_snapshot'   => 'decimal:2',
        'total_value'             => 'decimal:2',
        'missing_units'           => 'integer',
    ];

    // ─── Relaciones ───────────────────────────────────────────────────────────

    public function responsibleBranch()
    {
        return $this->belongsTo(\Modules\CostCenter\Entities\CostCenter::class, 'responsible_branch_id');
    }

    public function originBranch()
    {
        return $this->belongsTo(\Modules\CostCenter\Entities\CostCenter::class, 'origin_branch_id');
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function originUser()
    {
        return $this->belongsTo(User::class, 'origin_user_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function evidences()
    {
        return $this->hasMany(IncidentEvidence::class, 'incident_id')->orderBy('created_at');
    }

    public function auditLogs()
    {
        return $this->hasMany(IncidentAuditLog::class, 'incident_id')->orderBy('created_at');
    }

    public function cashClosingLink()
    {
        return $this->hasOne(CashClosingIncident::class, 'incident_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return ! in_array($this->status, ['closed', 'voided']);
    }

    public function isAwaitingStatement(): bool
    {
        return $this->status === 'awaiting_statement';
    }

    public function isUnderInvestigation(): bool
    {
        return $this->status === 'under_investigation';
    }

    public function statementDeadlineExpired(): bool
    {
        return $this->statement_expires_at && now()->isAfter($this->statement_expires_at);
    }
}
