<?php

namespace Modules\NetworkTree\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class NetworkPath extends Model
{
    protected $table = 'network_paths';

    protected $fillable = [
        'entrepreneur_id',
        'ancestor_id',
        'depth',
    ];

    protected $casts = [
        'depth' => 'integer',
    ];

    public function entrepreneur()
    {
        return $this->belongsTo(User::class, 'entrepreneur_id');
    }

    public function ancestor()
    {
        return $this->belongsTo(User::class, 'ancestor_id');
    }
}
