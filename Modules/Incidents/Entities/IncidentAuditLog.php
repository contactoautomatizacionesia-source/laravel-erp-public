<?php

namespace Modules\Incidents\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Incidents\Entities\Traits\HasTranslatableLabel;

class IncidentAuditLog extends Model
{
    use HasTranslatableLabel;

    protected $table = 'incident_audit_logs';

    // Sin updated_at — tabla de solo inserción protegida por trigger MySQL
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'actor_label' => 'array',
        'action'      => 'array',
        'metadata'    => 'array',
        'created_at'  => 'datetime',
    ];

    /**
     * Devuelve el actor_label traducido al locale activo.
     */
    public function getActorLabelTranslatedAttribute(): string
    {
        return $this->translateLabel($this->actor_label);
    }

    /**
     * Devuelve el action traducido al locale activo.
     */
    public function getActionTranslatedAttribute(): string
    {
        return $this->translateLabel($this->action);
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class, 'incident_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
