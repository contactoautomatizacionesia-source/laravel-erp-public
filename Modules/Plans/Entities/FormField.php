<?php
namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Traits\NormalizesLegacyTranslations;

class FormField extends Model
{
    use HasTranslations, NormalizesLegacyTranslations;

    protected $table = 'form_fields';

    public $translatable = ['field_label', 'help_text'];

    protected $fillable = [
        'form_section_id',
        'field_label',
        'field_key',
        'field_type',
        'is_required',
        'help_text',
        'validation_rules',
        'is_active',
    ];

    protected $casts = [
        'is_required'      => 'boolean',
        'is_active'        => 'boolean',
        'validation_rules' => 'array',
    ];

    public function section()
    {
        return $this->belongsTo(FormSection::class, 'form_section_id');
    }
}
