<?php

namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\NormalizesLegacyTranslations;

class RuleCategoryType extends Model
{
    use HasTranslations, NormalizesLegacyTranslations;

    protected $table = 'rule_category_type';

    public $translatable = ['label'];

    protected $fillable = ['label', 'key'];

    public function categories()
    {
        return $this->hasMany(RuleCategory::class, 'rule_category_type_id');
    }
}
