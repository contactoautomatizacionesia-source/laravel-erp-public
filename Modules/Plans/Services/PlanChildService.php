<?php
namespace Modules\Plans\Services;

use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\CustomerProfile;
use Modules\Customer\Entities\EntrepreneurPlanHistory;
use Modules\Plans\Exceptions\PlanChildDeletionException;
use Modules\Plans\Repositories\PlanChildRepository;

class PlanChildService
{
    protected $planChildRepository;

    public function __construct(PlanChildRepository $planChildRepository)
    {
        $this->planChildRepository = $planChildRepository;
    }

    public function store(array $data)
    {
        $planId = $data['plan_id'];
        $count  = $this->planChildRepository->countByPlan($planId);
        $final  = min(max(1, (int) $data['level_order']), $count + 1);

        $this->planChildRepository->incrementOrderFrom($planId, $final);

        $data['level_order'] = $final;
        $data['is_active']   = isset($data['is_active']) ? true : false;

        return $this->planChildRepository->create($data);
    }

    public function update($id, array $data)
    {
        $planId = $data['plan_id'];
        $child  = $this->planChildRepository->findById($id);
        $count  = $this->planChildRepository->countByPlan($planId);
        $old    = $child->level_order;
        $final  = min(max(1, (int) $data['level_order']), $count);

        if ($final !== $old) {
            if ($final > $old) {
                $this->planChildRepository->shiftBetween($planId, $old + 1, $final, -1);
            } else {
                $this->planChildRepository->shiftBetween($planId, $final, $old - 1, +1);
            }
        }

        $data['level_order'] = $final;
        $data['is_active']   = isset($data['is_active']) ? true : false;

        return $this->planChildRepository->update($child, $data);
    }

    public function delete($id)
    {
        $child = $this->planChildRepository->findById($id);
        $activeAssignmentsCount = CustomerProfile::where('plan_child_id', $child->id)->count();
        if ($activeAssignmentsCount > 0) {
            throw PlanChildDeletionException::assignedToEntrepreneurs($activeAssignmentsCount);
        }

        $historyCount = EntrepreneurPlanHistory::where('plan_child_id', $child->id)->count();
        if ($historyCount > 0) {
            throw PlanChildDeletionException::hasHistory($historyCount);
        }

        return DB::transaction(function () use ($child) {
            $deleted = $this->planChildRepository->delete($child);

            if ($deleted) {
                $this->planChildRepository->decrementOrderAfter($child->plan_id, $child->level_order);
            }

            return $deleted;
        });
    }

    public function reorder(array $ids): void
    {
        $this->planChildRepository->reorder($ids);
    }

    public function assignRules($id, array $rules)
    {
        $child    = $this->planChildRepository->findById($id);
        $syncData = [];
        foreach ($rules as $ruleEntry) {
            $syncData[$ruleEntry['rule_id']] = ['is_required' => !empty($ruleEntry['is_required'])];
        }
        $child->rules()->sync($syncData);
        return $child;
    }

    public function assignBenefits($id, array $benefitIds)
    {
        $child = $this->planChildRepository->findById($id);
        $child->benefits()->sync($benefitIds);
        return $child;
    }
}
