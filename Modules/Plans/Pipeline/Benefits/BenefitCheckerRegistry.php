<?php

namespace Modules\Plans\Pipeline\Benefits;

use Modules\Plans\Exceptions\BenefitCheckerNotFoundException;

class BenefitCheckerRegistry
{
    /** @param BenefitCheckerInterface[] $checkers */
    public function __construct(
        private readonly array $checkers = [],
    ) {}

    /**
     * Resolve a checker by benefit_category.key.
     *
     * @throws BenefitCheckerNotFoundException if no checker is registered for the given key.
     */
    public function for(string $categoryKey): BenefitCheckerInterface
    {
        foreach ($this->checkers as $checker) {
            if ($checker->categoryKey() === $categoryKey) {
                return $checker;
            }
        }

        throw BenefitCheckerNotFoundException::forCategoryKey($categoryKey);
    }
}
