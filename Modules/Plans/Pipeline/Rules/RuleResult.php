<?php

namespace Modules\Plans\Pipeline\Rules;

class RuleResult
{
    public function __construct(
        public readonly bool   $passed,
        public readonly int    $ruleId,
        public readonly string $categoryKey,
        public readonly string $label,
        public readonly string $detail,
        public readonly array  $context    = [],
        public readonly bool   $isRequired = true,
    ) {}

    public static function pass(
        int    $ruleId,
        string $categoryKey,
        string $label,
        string $detail,
        array  $context    = [],
        bool   $isRequired = true,
    ): self {
        return new self(true, $ruleId, $categoryKey, $label, $detail, $context, $isRequired);
    }

    public static function fail(
        int    $ruleId,
        string $categoryKey,
        string $label,
        string $detail,
        array  $context    = [],
        bool   $isRequired = true,
    ): self {
        return new self(false, $ruleId, $categoryKey, $label, $detail, $context, $isRequired);
    }
}
