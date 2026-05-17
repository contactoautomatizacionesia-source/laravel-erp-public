<?php

namespace Modules\Plans\Pipeline\Benefits;

use Modules\Plans\Entities\Benefit;

interface BenefitCheckerInterface
{
    /**
     * Must match the benefit_category.key value exactly (e.g. 'DISCOUNT_ON_NEXT_PURCHASE').
     */
    public function categoryKey(): string;

    /**
     * Resolve the benefit data for the given user.
     * Must NEVER throw — catch internally and return an error result.
     *
     * @param Benefit $benefit  Benefit entity with category and formAnswers already loaded.
     * @param int     $userId   The entrepreneur being evaluated.
     * @param array   $context  Pre-computed user data snapshot.
     */
    public function resolve(
        Benefit $benefit,
        int     $userId,
        array   $context,
    ): BenefitResult;

    /**
     * Render a normalized card structure from already-resolved benefit data.
     * Receives the `data` array produced by resolve() and returns a presentation-ready shape:
     *
     * [
     *   'icon'     => string,        // Themify icon class (e.g. 'ti-tag')
     *   'title'    => string,        // Short human-readable title for the card header
     *   'summary'  => string,        // One-line description for the card body
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
     * @param  array $data  The `data` array from BenefitResult (may be empty on error).
     */
    public function render(array $data): array;
}
