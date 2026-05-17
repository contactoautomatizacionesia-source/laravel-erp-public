<?php

namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\NormalizesLegacyTranslations;

class Plan extends Model
{
    use HasTranslations, NormalizesLegacyTranslations;

    protected $table = 'plan';
    protected $appends = [];
    public $translatable = ['title', 'description'];

    protected $fillable = [
        'title',
        'description',
        'scale_type',
        'order',
        'is_active',
        'is_life_title',
        'image',
        'styles',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'is_life_title'      => 'boolean',
        'order'              => 'integer',
        'styles'             => 'array',
    ];

    public function planChildren()
    {
        return $this->hasMany(PlanChild::class, 'plan_id')->orderBy('level_order');
    }
}
