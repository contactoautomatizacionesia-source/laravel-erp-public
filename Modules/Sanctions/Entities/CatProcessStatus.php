<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;

class CatProcessStatus extends Model
{
    protected $table = 'cat_process_statuses';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function investigations()
    {
        return $this->hasMany(Investigation::class, 'process_status_id');
    }
}
