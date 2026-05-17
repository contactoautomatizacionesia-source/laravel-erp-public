<?php

namespace Modules\Plans\Pipeline\Rules;

use Modules\Plans\Entities\Rule;

interface RuleCheckerInterface
{
    /**
     * Must match the rule_category.key value exactly (e.g. 'POINTS_THRESHOLD').
     */
    public function categoryKey(): string;

    /**
     * Evaluate the rule for the given user.
     * Must NEVER throw — catch internally and return a failing result.
     *
     * @param Rule  $rule       Rule entity with category and formAnswers already loaded.
     * @param int   $userId     The entrepreneur being evaluated.
     * @param array $context    Pre-computed user data snapshot.
     * @param bool  $isRequired Whether the rule is required for plan qualification.
     */
    public function check(
        Rule  $rule,
        int   $userId,
        array $context,
        bool  $isRequired = true,
    ): RuleResult;

    /**
     * Render a normalized card structure from already-evaluated rule data.
     * Receives the `context` array from RuleResult and the pass/fail outcome.
     *
     * [
     *   'icon'     => string,        // Themify icon class (e.g. 'ti-crown')
     *   'title'    => string,        // Short human-readable title for the card header
     *   'summary'  => string,        // One-line description adapted to pass/fail state
     *   'details'  => [              // Key→value pairs for a detail list (never empty)
     *       ['label' => string, 'value' => string],
     *       ...
     *   ],
     *   'progress' => null | [       // Optional progress bar data
     *       'current' => float,
     *       'target'  => float,
     *       'percent' => float,      // 0–100
     *   ],
     * ]
     *
     * @param  array $data    The `context` array from RuleResult (may be empty on error).
     * @param  bool  $passed  Whether the rule evaluation passed.
     */
    public function render(array $data, bool $passed): array;
}
