<?php
namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\NormalizesLegacyTranslations;

class FormOption extends Model
{
    use HasTranslations, NormalizesLegacyTranslations;

    protected $table = 'form_options';

    public $translatable = ['option_label', 'help_text'];

    protected $fillable = [
        'option_label',
        'option_key',
        'help_text',
    ];
}
