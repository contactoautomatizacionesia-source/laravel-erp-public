<?php

namespace Modules\Plans\Helpers;

use Illuminate\Support\Facades\Auth;
use Modules\Plans\Entities\Plan;
use Modules\Plans\Entities\PlanChild;
use Modules\Customer\Entities\CustomerProfile;
use Modules\Plans\Pipeline\Rules\RuleCheckerRegistry;
use Modules\Plans\Services\BenefitEvaluationService;
use Modules\Plans\Services\UserSnapshotBuilder;

class PlanContextHelper
{
    /**
     * Resolve the PlanChild for the given context and attach benefit + progression data.
     *
     * Priority:
     *   1. $userId provided      → load current PlanChild from CustomerProfile
     *   2. $planChildId provided → load that specific PlanChild directly
     *   3. $planId provided      → load first active PlanChild of that plan
     *   4. fallback              → authenticated user's CustomerProfile
     *
     * Note: when resolved via $planChildId or $planId without a $userId, benefit
     * evaluation and rule checking run without user context (points = null, rules
     * cannot be evaluated against a real snapshot).
     *
     * @param  int|null  $userId
     * @param  int|null  $planId
     * @param  int|null  $planChildId
     * @return array|null  Returns null when no context can be resolved.
     */
    public static function resolve(?int $userId = null, ?int $planId = null, ?int $planChildId = null): ?array
    {
        // --- 1. Resolve current PlanChild ---
        $planChild = null;
        $resolvedUserId = $userId;

        if ($userId !== null) {
            $profile = CustomerProfile::with(['planChild.plan'])
                ->where('user_id', $userId)
                ->first();

            $planChild = $profile?->planChild;
        } elseif ($planChildId !== null) {
            $planChild = PlanChild::with(['plan'])->find($planChildId);
        } elseif ($planId !== null) {
            $plan      = Plan::with(['planChildren' => fn ($q) => $q->where('is_active', true)])->find($planId);
            $planChild = $plan?->planChildren->first();
        } else {
            $authUserId = Auth::id();
            if ($authUserId) {
                $resolvedUserId = $authUserId;
                $profile = CustomerProfile::with(['planChild.plan'])
                    ->where('user_id', $authUserId)
                    ->first();

                $planChild = $profile?->planChild;
            }
        }

        if (! $planChild) {
            return null;
        }

        // Ensure the parent plan (with all its children) is loaded
        $planChild->loadMissing(['plan.planChildren']);

        $plan         = $planChild->plan;
        $allChildren  = $plan->planChildren->where('is_active', true)->sortBy('level_order')->values();
        $childCount   = $allChildren->count();

        // --- 2. Display name ---
        $displayName = self::buildDisplayName($plan, $planChild, $childCount);

        // --- 3. Next plan ---
        $nextPlan = Plan::where('order', '>', $plan->order)
            ->where('is_active', true)
            ->orderBy('order')
            ->first();

        // --- 4. Next PlanChild (within the same plan, by level_order) ---
        $nextPlanChild = $allChildren
            ->first(fn ($c) => $c->level_order > $planChild->level_order);

        // If there is no next child within the same plan, take the first child of the next plan
        if (! $nextPlanChild && $nextPlan) {
            $nextPlan->loadMissing('planChildren');
            $nextPlanChild = $nextPlan->planChildren
                ->where('is_active', true)
                ->sortBy('level_order')
                ->first() ?? null;
        }

        // --- 5. Display name for next_plan_child ---
        $nextDisplayName = null;
        if ($nextPlanChild) {
            $nextPlanChild->loadMissing('plan.planChildren');
            $nextChildPlan       = $nextPlanChild->plan;
            $nextChildPlanCount  = $nextChildPlan->planChildren->where('is_active', true)->count();
            $nextDisplayName     = self::buildDisplayName($nextChildPlan, $nextPlanChild, $nextChildPlanCount);
        }

        // --- 6. Benefits for current plan child ---
        $currentBenefits = self::resolveBenefitData($planChild->id, $resolvedUserId);

        // --- 7. Benefits for next plan child (for next-level discount preview) ---
        $nextBenefits = $nextPlanChild
            ? self::resolveBenefitData($nextPlanChild->id, $resolvedUserId)
            : null;

        // --- 8. Current plan child rules (requirements to maintain current level) ---
        $currentPlanChildRules = self::resolveNextPlanChildRules($planChild->id, $resolvedUserId);

        // --- 9. Next plan child rules (requirements to reach next level) ---
        $nextPlanChildRules = $nextPlanChild
            ? self::resolveNextPlanChildRules($nextPlanChild->id, $resolvedUserId)
            : null;

        // --- 10. Progression data toward next plan child ---
        $progression = self::resolveProgression($resolvedUserId, $nextPlanChild);

        // --- 11. Explicit discount data ---
        $discountData = self::extractDiscountData($currentBenefits, $nextBenefits);

        return [
            'current_plan_child' => [
                'id'          => $planChild->id,
                'level_order' => $planChild->level_order,
                'title'       => $planChild->title,
            ],
            'current_plan' => [
                'id'                 => $plan->id,
                'order'              => $plan->order,
                'title'              => $plan->title,
                'styles'             => $plan->styles,
                'scale_type'         => $plan->scale_type,
                'is_life_title'      => (bool) $plan->is_life_title,
                'network_type_label' => self::resolveNetworkTypeLabel((bool) $plan->is_life_title),
            ],
            'display_name' => $displayName,
            'next_plan'    => $nextPlan ? [
                'id'                 => $nextPlan->id,
                'order'              => $nextPlan->order,
                'title'              => $nextPlan->title,
                'styles'             => $nextPlan->styles,
                'scale_type'         => $nextPlan->scale_type,
                'is_life_title'      => (bool) $nextPlan->is_life_title,
                'network_type_label' => self::resolveNetworkTypeLabel((bool) $nextPlan->is_life_title),
            ] : null,
            'next_plan_child' => $nextPlanChild ? [
                'id'           => $nextPlanChild->id,
                'level_order'  => $nextPlanChild->level_order,
                'title'        => $nextPlanChild->title,
                'plan_id'      => $nextPlanChild->plan_id,
                'display_name' => $nextDisplayName,
            ] : null,

            // ── Benefit & progression data (items requested in dashboard) ──────────

            /**
             * The user's current total points (personal + children, as used for rule evaluation).
             */
            'current_points' => $progression['current_points'],

            /**
             * The minimum points required to qualify for the next plan child.
             * Extracted from the first POINTS_THRESHOLD or POINTS_RANGE rule on the next PlanChild.
             * Null when no next PlanChild or no points rule found.
             */
            'next_plan_target_points' => $progression['target_points'],

            /**
             * Numeric progress value toward the next plan child.
             * Calculated as: (current_points / target_points) * 100, capped at 100.
             * Null when target_points is unavailable.
             */
            'progress_to_next_plan' => $progression['progress'],

            /**
             * Current explicit purchase discount percentage/amount from the current plan.
             * Taken from the DISCOUNT_ON_NEXT_PURCHASE benefit of the current plan child.
             * Null if not assigned.
             */
            'current_explicit_discount' => $discountData['current'],

            /**
             * Explicit discount percentage/amount the user would gain upon reaching the next plan child.
             * Taken from the DISCOUNT_ON_NEXT_PURCHASE benefit of the next plan child.
             * Null if not assigned or no next plan child.
             */
            'next_level_discount' => $discountData['next'],

            /**
             * Resolved benefits of the current plan child.
             * Each entry: { benefit_id, category_key, label, resolved, data, card, error }
             */
            'current_plan_benefits' => $currentBenefits,

            /**
             * Resolved benefits of the next plan child (preview of what the user would gain).
             * Each entry: { benefit_id, category_key, label, resolved, data, card, error }
             * Null when there is no next plan child or no user context.
             */
            'next_plan_benefits' => $nextBenefits,

            /**
             * Rules/requirements the user must currently meet to maintain their active level.
             * Each entry: { rule_id, category_key, label, is_required, passed, data, card }
             */
            'current_plan_requirements' => $currentPlanChildRules,

            /**
             * Rules/requirements the user must meet to reach the next plan child.
             * Each entry: { rule_id, category_key, label, is_required, passed, data, card }
             * Null when there is no next plan child.
             */
            'next_plan_requirements' => $nextPlanChildRules,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve and serialize the benefits of a given PlanChild.
     * Returns null when $userId is unknown (e.g. resolved by planId without a user).
     *
     * @return array[]|null
     */
    private static function resolveBenefitData(int $planChildId, ?int $userId): ?array
    {
        if ($userId === null) {
            return null;
        }

        /** @var BenefitEvaluationService $service */
        $service = app(BenefitEvaluationService::class);
        $result  = $service->resolveForPlanChild($planChildId, $userId);

        return $result->toArray();
    }

    /**
     * Build progression data toward the next plan child.
     *
     * @param  int|null   $userId
     * @param  PlanChild|null $nextPlanChild
     * @return array{ current_points: float|null, target_points: float|null, progress: float|null }
     */
    private static function resolveProgression(?int $userId, ?PlanChild $nextPlanChild): array
    {
        $currentPoints = null;
        $targetPoints  = null;
        $progress      = null;

        if ($userId !== null) {
            /** @var UserSnapshotBuilder $snapshotBuilder */
            $snapshotBuilder = app(UserSnapshotBuilder::class);
            $context         = $snapshotBuilder->build($userId);
            $currentPoints   = (float) ($context['total_points'] ?? 0);
        }

        if ($nextPlanChild) {
            $targetPoints = self::extractTargetPointsFromRules($nextPlanChild->id);
        }

        if ($currentPoints !== null && $targetPoints !== null && $targetPoints > 0) {
            $progress = min(100.0, round(($currentPoints / $targetPoints) * 100, 2));
        }

        return [
            'current_points' => $currentPoints,
            'target_points'  => $targetPoints,
            'progress'       => $progress,
        ];
    }

    /**
     * Extract the minimum points threshold from the next PlanChild's rules.
     * Looks for POINTS_THRESHOLD (MIN_POINTS) or POINTS_RANGE (MIN_POINTS) rules.
     */
    private static function extractTargetPointsFromRules(int $planChildId): ?float
    {
        $planChild = PlanChild::with([
            'rules.category',
            'rules.formAnswers.field',
        ])->find($planChildId);

        if (! $planChild) {
            return null;
        }

        foreach ($planChild->rules as $rule) {
            $key = $rule->category->key ?? '';
            if ($key === 'POINTS_THRESHOLD' || $key === 'POINTS_RANGE') {
                $answers   = $rule->formAnswers
                    ->whereNull('repeat_index')
                    ->keyBy(fn($a) => $a->field->field_key)
                    ->map(fn($a) => $a->answer)
                    ->all();

                $minPoints = isset($answers['MIN_POINTS']) && $answers['MIN_POINTS'] !== null
                    ? (float) $answers['MIN_POINTS']
                    : null;

                if ($minPoints !== null) {
                    return $minPoints;
                }
            }
        }

        return null;
    }

    /**
     * Extract explicit discount data from resolved benefit arrays.
     *
     * @param  array[]|null $currentBenefits
     * @param  array[]|null $nextBenefits
     * @return array{ current: array|null, next: array|null }
     */
    private static function extractDiscountData(?array $currentBenefits, ?array $nextBenefits): array
    {
        return [
            'current' => self::findDiscountEntry($currentBenefits),
            'next'    => self::findDiscountEntry($nextBenefits),
        ];
    }

    /**
     * Find the first DISCOUNT_ON_NEXT_PURCHASE entry in a resolved benefits array.
     *
     * @param  array[]|null $benefits
     * @return array|null   { discount_quantity, discount_type, discount_type_label }
     */
    private static function findDiscountEntry(?array $benefits): ?array
    {
        if (! $benefits) {
            return null;
        }

        foreach ($benefits as $entry) {
            if (($entry['category_key'] ?? '') === 'DISCOUNT_ON_NEXT_PURCHASE' && $entry['resolved']) {
                return $entry['data'] ?? null;
            }
        }

        return null;
    }

    /**
     * Return the rules of the next PlanChild evaluated against the user context,
     * including checker data and a normalized card for display.
     *
     * @return array[]|null  Each entry: { rule_id, category_key, label, is_required, passed, data, card }
     */
    private static function resolveNextPlanChildRules(int $planChildId, ?int $userId): ?array
    {
        $planChild = PlanChild::with([
            'rules.category',
            'rules.formAnswers.field',
        ])->find($planChildId);

        if (! $planChild) {
            return null;
        }

        $context  = $userId !== null
            ? app(UserSnapshotBuilder::class)->build($userId)
            : [];

        $registry = app(RuleCheckerRegistry::class);

        return $planChild->rules
            ->map(function ($rule) use ($context, $registry) {
                $categoryKey = $rule->category->key ?? null;
                $isRequired  = (bool) ($rule->pivot->is_required ?? true);

                try {
                    $checker = $registry->for($categoryKey);
                    $result  = $checker->check($rule, 0, $context, $isRequired);
                    $card    = $checker->render($result->context, $result->passed);

                    return [
                        'rule_id'      => $rule->id,
                        'category_key' => $categoryKey,
                        'label'        => $rule->title,
                        'is_required'  => $isRequired,
                        'passed'       => $result->passed,
                        'data'         => $result->context,
                        'card'         => $card,
                    ];
                } catch (\Throwable) {
                    return [
                        'rule_id'      => $rule->id,
                        'category_key' => $categoryKey,
                        'label'        => $rule->title,
                        'is_required'  => $isRequired,
                        'passed'       => false,
                        'data'         => [],
                        'card'         => null,
                    ];
                }
            })
            ->values()
            ->all();
    }

    /**
     * Build the display name following the business rules:
     *  - Single child  → "PlanName"
     *  - Multiple children → "PlanName LevelOrder"
     */
    private static function buildDisplayName(Plan $plan, PlanChild $planChild, int $childCount): string
    {
        $planTitle = $plan->title; // uses current locale via HasTranslations

        if ($childCount <= 1) {
            return $planTitle;
        }

        return $planTitle . ' > ' . $planChild->level_order;
    }

    private static function resolveNetworkTypeLabel(bool $isLifeTitle): string
    {
        return $isLifeTitle ? __('common.red_life') : __('common.red_no_life');
    }
}
