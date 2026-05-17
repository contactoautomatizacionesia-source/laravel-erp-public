<?php

namespace Modules\InventoryEntry\Entities;

use Illuminate\Database\Eloquent\Model;

class InventoryEntryAudit extends Model
{
    protected $table = 'inventory_entry_audits';

    protected $guarded = ['id'];

    protected $casts = [
        'before_payload' => 'array',
        'after_payload' => 'array',
    ];

    public function entry()
    {
        return $this->belongsTo(InventoryEntry::class, 'entry_id');
    }

    public function responsible()
    {
        return $this->belongsTo(\App\Models\User::class, 'responsible_id');
    }
}
