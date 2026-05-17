<?php

namespace Modules\Customer\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\CustomerProfile;
use Modules\Customer\Entities\EntrepreneurPlanHistory;
use Modules\Plans\Entities\PlanChild;
use Modules\Plans\Services\PlanEvaluationResult;
use Modules\Plans\Services\PlanEvaluationService;

class EntrepreneurPlanService
{
    /**
     * Asigna un plan a un empresario.
     *
     * - Cierra el plan activo anterior (ended_at = now)
     * - Crea el nuevo registro en entrepreneur_plan_history
     * - Actualiza plan_child_id en customer_profiles (desnormalizado)
     * - Invalida el caché del árbol del empresario
     *
     * Todo dentro de una transacción atómica. // NOSONAR
     *
     * @param  int         $userId       Empresario al que se le asigna el plan
     * @param  int         $planChildId  ID del nuevo plan_child
     * @param  string|null $reason       Razón (usar constantes de EntrepreneurPlanHistory)
     * @param  int|null    $assignedBy   User que asigna (null = sistema)
     * @throws \InvalidArgumentException Si el plan no existe o el empresario no tiene perfil
     */
    public function assignPlan(int $userId, int $planChildId, ?string $reason = null, ?int $assignedBy = null): EntrepreneurPlanHistory
    {
        $plan = PlanChild::find($planChildId);
        if (! $plan) {
            throw new \InvalidArgumentException("plan_child_id={$planChildId} no existe.");
        }

        $profile = CustomerProfile::where('user_id', $userId)->first();
        if (! $profile) {
            throw new \InvalidArgumentException("El usuario user_id={$userId} no tiene customer_profile.");
        }

        return DB::transaction(function () use ($userId, $planChildId, $reason, $assignedBy, $profile) {
            // Cerrar plan activo anterior si existe
            EntrepreneurPlanHistory::where('user_id', $userId)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);

            // Crear nuevo registro en historial
            $history = EntrepreneurPlanHistory::create([
                'user_id'         => $userId,
                'plan_child_id'   => $planChildId,
                'assigned_by'     => $assignedBy ?? Auth::id(),
                'assigned_reason' => $reason ?? EntrepreneurPlanHistory::REASON_MANUAL,
                'started_at'      => now(),
                'ended_at'        => null,
            ]);

            // Actualizar campo desnormalizado en customer_profiles
            $profile->update(['plan_child_id' => $planChildId]);

            // Invalidar caché del árbol para que el nodo refleje el nuevo plan
            for ($d = 2; $d <= 5; $d++) {
                Cache::forget("network_tree_{$userId}_d{$d}");
            }
            Cache::forget("network_stats_{$userId}");

            return $history;
        });
    }

    /**
     * Retorna el plan activo actual del empresario con sus datos del plan_child.
     *
     * @param  int $userId
     * @return \Modules\Plans\Entities\PlanChild|null
     */
    public function getCurrentPlan(int $userId): ?PlanChild
    {
        $profile = CustomerProfile::where('user_id', $userId)
            ->with('planChild')
            ->first();

        return $profile?->planChild;
    }

    /**
     * Retorna el historial completo de planes del empresario, del más reciente al más antiguo.
     *
     * @param  int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    /**
     * Evaluates the entrepreneur's current performance, determines the qualifying plan,
     * and assigns it if it differs from their current plan.
     *
     * Calls PlanEvaluationService to iterate all active plans and find the lowest
     * PlanChild where all required rules pass. If the result differs from the
     * current assignment, calls assignPlan() with the appropriate reason.
     *
     * @param  int $userId
     * @return PlanEvaluationResult
     */
    public function runUpgrade(int $userId): PlanEvaluationResult
    {
        \Log::info("[runUpgrade] START userId={$userId}");

        $profile = \Modules\Customer\Entities\CustomerProfile::where('user_id', $userId)->first();
        if (!$profile) {
            \Log::warning("[runUpgrade] skipped — user_id={$userId} has no customer_profile");
            return new PlanEvaluationResult(null);
        }

        try {
            $evaluator  = app(PlanEvaluationService::class);
            $evalResult = $evaluator->evaluate($userId);

            \Log::info("[runUpgrade] evaluate() done", [
                'qualifiedPlanChildId' => $evalResult->qualifiedPlanChildId,
            ]);

            $currentPlan = $this->getCurrentPlan($userId);
            $currentId   = $currentPlan?->id;
            $newId       = $evalResult->qualifiedPlanChildId;

            \Log::info("[runUpgrade] plan comparison", [
                'currentId' => $currentId,
                'newId'     => $newId,
            ]);

            if ($newId !== null && $newId !== $currentId) {
                $reason = ($currentId === null || $newId > $currentId)
                    ? EntrepreneurPlanHistory::REASON_UPGRADE
                    : EntrepreneurPlanHistory::REASON_DOWNGRADE;

                \Log::info("[runUpgrade] assigning plan", [
                    'newId'  => $newId,
                    'reason' => $reason,
                ]);

                $this->assignPlan($userId, $newId, $reason, assignedBy: null);

                \Log::info("[runUpgrade] assignPlan() done");
            } else {
                \Log::info("[runUpgrade] no plan change needed");
            }

            \Log::info("[runUpgrade] END userId={$userId}");

            return $evalResult;
        } catch (\Throwable $e) {
            \Log::error("[runUpgrade] failed for user_id={$userId}: {$e->getMessage()}", [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);

            return new PlanEvaluationResult(null);
        }
    }

    public function getHistory(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return EntrepreneurPlanHistory::where('user_id', $userId)
            ->with(['planChild.plan', 'assignedBy:id,first_name,last_name'])
            ->orderBy('started_at', 'desc')
            ->get();
    }
}
