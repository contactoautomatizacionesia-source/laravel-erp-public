<?php

namespace Modules\Plans\Exceptions;

class CheckerNotFoundException extends \RuntimeException
{
    public static function forCategoryKey(string $categoryKey): self
    {
        return new self("No checker registered for category key: {$categoryKey}");
    }
}
