<?php

namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\NormalizesLegacyTranslations;
use Modules\Plans\Entities\FormSection;

class BenefitCategory extends Model
{
    use HasTranslations, NormalizesLegacyTranslations;

    protected $table = 'benefit_category';

    public $translatable = ['name', 'description'];

    protected $fillable = ['name', 'key', 'description', 'benefit_category_type_id'];

    public function type()
    {
        return $this->belongsTo(BenefitCategoryType::class, 'benefit_category_type_id');
    }

    public function formSections()
    {
        return $this->hasMany(FormSection::class, 'owner_key', 'key')->orderBy('section_order');
    }

    public function benefits()
    {
        return $this->hasMany(Benefit::class, 'benefit_category_id');
    }
}
