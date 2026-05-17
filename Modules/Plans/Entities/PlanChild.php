<?php

namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\NormalizesLegacyTranslations;

class PlanChild extends Model
{
    use HasTranslations, NormalizesLegacyTranslations;
    protected $table = 'plan_child';
    protected $appends = [];
    public $translatable = ['title', 'description'];

    protected $fillable = [
        'plan_id',
        'title',
        'description',
        'level_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'level_order' => 'integer',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function rules()
    {
        return $this->belongsToMany(Rule::class, 'plan_rules', 'plan_child_id', 'rule_id')
                    ->withPivot('is_required')
                    ->withTimestamps();
    }

    public function benefits()
    {
        return $this->belongsToMany(Benefit::class, 'plan_benefits', 'plan_child_id', 'benefit_id')
                    ->withTimestamps();
    }
}
