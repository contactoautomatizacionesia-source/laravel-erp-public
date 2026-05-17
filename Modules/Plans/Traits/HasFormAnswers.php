<?php

namespace Modules\Plans\Traits;

use Modules\Plans\Entities\FormAnswer;

trait HasFormAnswers
{
    public function formAnswers()
    {
        return $this->morphMany(FormAnswer::class, 'formable');
    }
}
