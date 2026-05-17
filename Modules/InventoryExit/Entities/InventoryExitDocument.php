<?php

namespace Modules\InventoryExit\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class InventoryExitDocument extends Model
{
    protected $table = 'inventory_exit_documents';
    protected $guarded = ['id'];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function exitRequest()
    {
        return $this->belongsTo(InventoryExitRequest::class, 'inventory_exit_request_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
