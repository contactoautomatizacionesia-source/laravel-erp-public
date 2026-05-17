<?php

namespace Modules\CycleClosure\Pipeline;

class StepResult
{
    public function __construct(
        public readonly bool   $passed,
        public readonly string $label,
        public readonly string $detail,
        public readonly array  $context = [],
    ) {}

    public static function ok(string $label, string $detail, array $context = []): self
    {
        return new self(true, $label, $detail, $context);
    }

    public static function warn(string $label, string $detail, array $context = []): self
    {
        return new self(false, $label, $detail, $context);
    }
}
