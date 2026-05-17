<?php

namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\NormalizesLegacyTranslations;

class BenefitCategoryType extends Model
{
    use HasTranslations, NormalizesLegacyTranslations;

    protected $table = 'benefit_category_type';

    public $translatable = ['label'];

    protected $fillable = ['label', 'key'];

    public function categories()
    {
        return $this->hasMany(BenefitCategory::class, 'benefit_category_type_id');
    }
}
