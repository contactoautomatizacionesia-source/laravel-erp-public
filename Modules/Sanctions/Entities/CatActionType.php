<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;

class CatActionType extends Model
{
    protected $table = 'cat_action_types';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function resolutions()
    {
        return $this->hasMany(SanctionResolution::class, 'action_type_id');
    }
}
