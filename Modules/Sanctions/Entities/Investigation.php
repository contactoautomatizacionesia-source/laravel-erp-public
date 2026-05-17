<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Investigation extends Model
{
    use SoftDeletes;

    protected $table = 'investigations';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'eui_id',
        'instructor_id',
        'offense_type_id',
        'complaint_source_id',
        'process_status_id',
        'facts_description',
        'origin_detail',
        'opened_at',
        'closed_at',
        'offense_count',
        'is_active',
    ];

    protected $casts = [
        'opened_at'     => 'date',
        'closed_at'     => 'date',
        'offense_count' => 'integer',
        'is_active'     => 'boolean',
    ];

    public function eui()
    {
        return $this->belongsTo(User::class, 'eui_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function offenseType()
    {
        return $this->belongsTo(CatOffenseType::class, 'offense_type_id');
    }

    public function complaintSource()
    {
        return $this->belongsTo(CatComplaintSource::class, 'complaint_source_id');
    }

    public function processStatus()
    {
        return $this->belongsTo(CatProcessStatus::class, 'process_status_id');
    }

    public function evidences()
    {
        return $this->hasMany(InvestigationEvidence::class, 'investigation_id');
    }

    public function notifications()
    {
        return $this->hasMany(ProcessNotification::class, 'investigation_id');
    }

    public function defenses()
    {
        return $this->hasMany(EuiDefense::class, 'investigation_id');
    }

    public function resolution()
    {
        return $this->hasOne(SanctionResolution::class, 'investigation_id');
    }

    public function appliedMitigatingFactors()
    {
        return $this->hasMany(AppliedMitigatingFactor::class, 'investigation_id');
    }

    public function committeeReview()
    {
        return $this->hasOne(CommitteeReview::class, 'investigation_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(EuiStatusLog::class, 'investigation_id');
    }
}
