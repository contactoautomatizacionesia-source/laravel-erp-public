<?php

namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;

class FormAnswer extends Model
{
    protected $table = 'form_answers';

    protected $fillable = [
        'formable_type',
        'formable_id',
        'form_field_id',
        'answer',
        'repeat_index',
    ];

    protected $casts = [
        'repeat_index' => 'integer',
    ];

    public function formable()
    {
        return $this->morphTo();
    }

    public function field()
    {
        return $this->belongsTo(FormField::class, 'form_field_id');
    }
}
