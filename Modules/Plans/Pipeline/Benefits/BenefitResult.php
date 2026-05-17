<?php

namespace Modules\Plans\Pipeline\Benefits;

/**
 * Immutable value object returned by every BenefitChecker.
 *
 * Unlike RuleResult (which has pass/fail semantics), BenefitResult always
 * "resolves" — it carries whatever runtime data the checker extracted from
 * the benefit's form answers and the user's snapshot context.
 *
 * The `data` array shape is checker-specific and is documented in each
 * concrete BenefitChecker.
 */
class BenefitResult
{
    public function __construct(
        public readonly int    $benefitId,
        public readonly string $categoryKey,
        public readonly string $label,
        public readonly bool   $resolved,
        public readonly array  $data  = [],
        public readonly string $error = '',
    ) {}

    public static function make(
        int    $benefitId,
        string $categoryKey,
        string $label,
        array  $data = [],
    ): self {
        return new self(
            benefitId:   $benefitId,
            categoryKey: $categoryKey,
            label:       $label,
            resolved:    true,
            data:        $data,
        );
    }

    public static function error(
        int    $benefitId,
        string $categoryKey,
        string $label,
        string $error,
    ): self {
        return new self(
            benefitId:   $benefitId,
            categoryKey: $categoryKey,
            label:       $label,
            resolved:    false,
            data:        [],
            error:       $error,
        );
    }
}
