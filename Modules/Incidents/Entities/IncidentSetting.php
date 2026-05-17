<?php

namespace Modules\Incidents\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class IncidentSetting extends Model
{
    protected $table = 'incident_settings';

    // Solo updated_at, sin created_at
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'statement_deadline_hours'  => 'integer',
        'auto_escalate_on_deadline' => 'boolean',
        'send_email_notifications'  => 'boolean',
        'send_system_notifications' => 'boolean',
        'send_deadline_reminder'    => 'boolean',
        'reminder_hours_before'     => 'integer',
        'updated_at'                => 'datetime',
    ];

    /**
     * Obtiene el registro singleton de configuración.
     * Si no existe lo crea con valores por defecto.
     */
    public static function getInstance(): self
    {
        return static::first() ?? static::create([
            'statement_deadline_hours'  => 48,
            'auto_escalate_on_deadline' => true,
            'send_email_notifications'  => true,
            'send_system_notifications' => true,
            'send_deadline_reminder'    => true,
            'reminder_hours_before'     => 24,
            'price_reference'           => 'public_price',
            'updated_at'                => now(),
        ]);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
