<?php

namespace Modules\Plans\Pipeline\Rules;

use Modules\Plans\Exceptions\CheckerNotFoundException;

class RuleCheckerRegistry
{
    /** @param RuleCheckerInterface[] $checkers */
    public function __construct(
        private readonly array $checkers = [],
    ) {}

    /**
     * Resolve a checker by rule_category.key.
     *
     * @throws CheckerNotFoundException if no checker is registered for the given key.
     */
    public function for(string $categoryKey): RuleCheckerInterface
    {
        foreach ($this->checkers as $checker) {
            if ($checker->categoryKey() === $categoryKey) {
                return $checker;
            }
        }

        throw CheckerNotFoundException::forCategoryKey($categoryKey);
    }
}
