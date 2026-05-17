<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CommitteeReview extends Model
{
    protected $table = 'committee_reviews';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'investigation_id',
        'requested_by_id',
        'review_status',
        'requested_at',
        'decided_at',
        'decision',
        'decision_description',
    ];

    protected $casts = [
        'requested_at' => 'date',
        'decided_at'   => 'date',
    ];

    public function investigation()
    {
        return $this->belongsTo(Investigation::class, 'investigation_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }
}
