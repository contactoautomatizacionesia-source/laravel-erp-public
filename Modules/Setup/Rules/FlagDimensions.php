<?php

namespace Modules\Setup\Rules;

use Illuminate\Contracts\Validation\Rule;

class FlagDimensions implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (!is_object($value) || !method_exists($value, 'getRealPath')) {
            return false;
        }

        $imagePath = $value->getRealPath();
        $size = @getimagesize($imagePath);

        if ($size === false) {
            return false;
        }

        return (int) $size[0] === 61 && (int) $size[1] === 36;
    }

    public function message(): string
    {
        return 'La bandera debe tener dimensiones de 61x36 píxeles.';
    }
}
