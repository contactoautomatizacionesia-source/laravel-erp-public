<?php

namespace Modules\Plans\Exceptions;

class BenefitCheckerNotFoundException extends \RuntimeException
{
    public static function forCategoryKey(string $categoryKey): self
    {
        return new self("No benefit checker registered for category key: {$categoryKey}");
    }
}
