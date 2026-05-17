<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;

class CatSanctionType extends Model
{
    protected $table = 'cat_sanction_types';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'name',
        'description',
        'first_offense_text',
        'second_offense_text',
        'third_offense_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function resolutions()
    {
        return $this->hasMany(SanctionResolution::class, 'sanction_type_id');
    }
}
