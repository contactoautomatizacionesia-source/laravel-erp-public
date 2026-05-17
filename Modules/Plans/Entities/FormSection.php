<?php
namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\NormalizesLegacyTranslations;

class FormSection extends Model
{
    use HasTranslations, NormalizesLegacyTranslations;

    protected $table = 'form_sections';

    public $translatable = ['section_label'];

    protected $fillable = [
        'owner_key',
        'section_label',
        'section_key',
        'section_order',
        'is_repeatable',
        'is_active',
    ];

    protected $casts = [
        'is_repeatable' => 'boolean',
        'is_active'     => 'boolean',
    ];

    public function fields()
    {
        return $this->hasMany(FormField::class, 'form_section_id')->where('is_active', true);
    }
}
