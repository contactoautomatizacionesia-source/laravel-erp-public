<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Enums\ModulesName;

class PendingApproval extends Model
{
    protected $table = 'pending_approvals';

    protected $fillable = [
        'hash',
        'module',
        'action_type',
        'new_data',
        'original_data',
        'requester_id',
        'assigned_approver_id',
        'status'
    ];

    // Casting automático de JSON a Array
    protected $casts = [
        'new_data'      => 'array',
        'original_data' => 'array',
        'status'        => 'integer',
    ];

    public function translatedModule(): string
    {
        $module = ModulesName::tryFrom($this->module);
        $key = 'double_approval.modules.' . ($module?->value ?? $this->module);
        $translated = __($key);
        return $translated !== $key ? $translated : ($module?->value ?? $this->module);
    }

    /**
     * Relación con el usuario que solicitó el cambio
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Relación con el staff asignado para aprobar
     */
    public function approver()
    {
        return $this->belongsTo(Staff::class, 'assigned_approver_id');
    }
}