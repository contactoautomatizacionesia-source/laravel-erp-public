<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;

class CatComplaintSource extends Model
{
    protected $table = 'cat_complaint_sources';
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
        return $this->hasMany(Investigation::class, 'complaint_source_id');
    }
}
