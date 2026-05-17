<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;

class CatOffenseType extends Model
{
    protected $table = 'cat_offense_types';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'name',
        'description',
        'level',
        'is_active',
    ];

    protected $casts = [
        'level'     => 'integer',
        'is_active' => 'boolean',
    ];

    public function investigations()
    {
        return $this->hasMany(Investigation::class, 'offense_type_id');
    }
}
