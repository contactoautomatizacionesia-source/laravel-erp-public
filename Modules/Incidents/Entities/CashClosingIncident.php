<?php

namespace Modules\Incidents\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CashClosingIncident extends Model
{
    protected $table = 'cash_closing_incidents';

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'value_snapshot' => 'decimal:2',
        'included_at'    => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class, 'incident_id');
    }

    public function includedBy()
    {
        return $this->belongsTo(User::class, 'included_by');
    }
}
