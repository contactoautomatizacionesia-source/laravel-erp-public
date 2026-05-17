<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ProcessNotification extends Model
{
    protected $table = 'process_notifications';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'investigation_id',
        'sent_by_id',
        'type',
        'channel',
        'content',
        'sent_at',
        'receipt_confirmed',
    ];

    protected $casts = [
        'sent_at'           => 'date',
        'receipt_confirmed' => 'boolean',
    ];

    public function investigation()
    {
        return $this->belongsTo(Investigation::class, 'investigation_id');
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by_id');
    }
}
