<?php

namespace Modules\CycleClosure\Pipeline;

use Modules\CycleClosure\Entities\Cycle;

interface CycleStepInterface
{
    /**
     * Human-readable name shown in logs.
     */
    public function name(): string;

    /**
     * Phase key used in cycle_logs.phase — maps to cycleclosure::messages.phase_{key}.
     */
    public function phase(): string;

    /**
     * Execute the step. Must NEVER throw — catch internally and return warn().
     */
    public function run(Cycle $cycle): StepResult;
}
