<?php

namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\NormalizesLegacyTranslations;
use Modules\Plans\Entities\FormSection;

class RuleCategory extends Model
{
    use HasTranslations, NormalizesLegacyTranslations;

    protected $table = 'rule_category';

    public $translatable = ['name', 'description'];

    protected $fillable = ['name', 'key', 'description', 'rule_category_type_id'];

    public function type()
    {
        return $this->belongsTo(RuleCategoryType::class, 'rule_category_type_id');
    }

    public function formSections()
    {
        return $this->hasMany(FormSection::class, 'owner_key', 'key')->orderBy('section_order');
    }

    public function rules()
    {
        return $this->hasMany(Rule::class, 'rule_category_id');
    }
}
