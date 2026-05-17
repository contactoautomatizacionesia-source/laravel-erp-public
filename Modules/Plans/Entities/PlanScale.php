<?php

namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\NormalizesLegacyTranslations;

class PlanScale extends Model
{
    use HasTranslations, NormalizesLegacyTranslations;

    protected $table = 'plan_scale';

    public $translatable = ['label'];

    protected $fillable = ['label', 'key'];

    public function plans()
    {
        return $this->hasMany(Plan::class, 'plan_scale_id');
    }
}
