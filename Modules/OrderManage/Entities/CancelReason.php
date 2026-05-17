<?php

namespace Modules\OrderManage\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\NormalizesLegacyTranslations;

class CancelReason extends Model
{
    use HasFactory, HasTranslations, NormalizesLegacyTranslations;
    protected $table = "cancel_reasons";
    protected $guarded = ['id'];
    protected $appends = ['translateName','TranslateDescription'];
    public $translatable = ['name','description'];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    public function getTranslateNameAttribute(){
        return $this->attributes['name'];
    }
    public function getTranslateDescriptionAttribute(){
        return $this->attributes['description'];
    }
}
