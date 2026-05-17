<?php

namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\NormalizesLegacyTranslations;
use Modules\Plans\Traits\HasFormAnswers;

class Rule extends Model
{
    use HasTranslations, NormalizesLegacyTranslations, HasFormAnswers;
    protected $table = 'rule';

    protected $appends = [];
    public $translatable = ['title', 'description'];

    protected $fillable = [
        'code',
        'title',
        'description',
        'rule_category_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(RuleCategory::class, 'rule_category_id');
    }

    public function dependencies()
    {
        return $this->hasMany(RuleDependency::class, 'parent_rule_id')->orderBy('order_index');
    }

    public function planChildren()
    {
        return $this->belongsToMany(PlanChild::class, 'plan_rules', 'rule_id', 'plan_child_id')
                    ->withPivot('is_required')
                    ->withTimestamps();
    }

    public function isMaintenance(): bool
    {
        return $this->category && $this->category->key === 'RULE_GROUPING';
    }
}
