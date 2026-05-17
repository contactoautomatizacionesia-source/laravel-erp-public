<?php

namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\NormalizesLegacyTranslations;
use Modules\Plans\Traits\HasFormAnswers;

class Benefit extends Model
{
    use HasTranslations, NormalizesLegacyTranslations, HasFormAnswers;
    protected $table = 'benefit';
    protected $appends = [];
    public $translatable = ['title', 'description'];

    protected $fillable = [
        'code',
        'title',
        'description',
        'benefit_category_id',
        'is_cumulative',
        'is_active',
    ];

    protected $casts = [
        'is_cumulative' => 'boolean',
        'is_active'     => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(BenefitCategory::class, 'benefit_category_id');
    }

    public function planChildren()
    {
        return $this->belongsToMany(PlanChild::class, 'plan_benefits', 'benefit_id', 'plan_child_id')
                    ->withTimestamps();
    }
}
