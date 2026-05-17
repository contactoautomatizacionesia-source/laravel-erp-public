<?php
namespace Modules\Plans\Services;

use Modules\Plans\Repositories\RuleRepository;
use Modules\Plans\Entities\RuleDependency;
use Illuminate\Support\Facades\DB;
use Exception;

class RuleService
{
    protected $ruleRepository;

    public function __construct(RuleRepository $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    public function storeRule(array $data)
    {
        DB::beginTransaction();
        try {
            $data['is_active'] = isset($data['is_active']) ? true : false;
            $rule = $this->ruleRepository->create([
                'code'             => $data['code'],
                'title'            => $data['title'],
                'description'      => $data['description'] ?? null,
                'rule_category_id' => $data['rule_category_id'],
                'is_active'        => $data['is_active'],
            ]);
            $this->saveAnswers($rule, $data);
            $this->saveDependencies($rule, $data);
            DB::commit();
            return $rule;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateRule($id, array $data)
    {
        DB::beginTransaction();
        try {
            $data['is_active'] = isset($data['is_active']) ? true : false;
            $rule = $this->ruleRepository->findById($id);
            $this->ruleRepository->update($rule, [
                'code'             => $data['code'],
                'title'            => $data['title'],
                'description'      => $data['description'] ?? null,
                'rule_category_id' => $data['rule_category_id'],
                'is_active'        => $data['is_active'],
            ]);
            $rule->formAnswers()->delete();
            $rule->dependencies()->delete();
            $this->saveAnswers($rule, $data);
            $this->saveDependencies($rule, $data);
            DB::commit();
            return $rule;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteRule($id)
    {
        $rule = $this->ruleRepository->findById($id)->loadCount('planChildren');
        if ($rule->plan_children_count > 0) {
            throw new Exception('No se puede eliminar: la regla está en uso en ' . $rule->plan_children_count . ' subplan(es).');
        }
        return $this->ruleRepository->delete($rule);
    }

    private function saveAnswers($rule, array $data)
    {
        if (empty($data['answers'])) return;
        foreach ($data['answers'] as $fieldId => $value) {
            if (is_array($value)) {
                foreach ($value as $repeatIndex => $repeatValue) {
                    if ($repeatValue === null || $repeatValue === '') {
                        continue;
                    }
                    $rule->formAnswers()->create([
                        'form_field_id' => $fieldId,
                        'answer'        => $repeatValue,
                        'repeat_index'  => $repeatIndex,
                    ]);
                }
            } else {
                if ($value === null || $value === '') {
                    continue;
                }
                $rule->formAnswers()->create([
                    'form_field_id' => $fieldId,
                    'answer'        => $value,
                    'repeat_index'  => null,
                ]);
            }
        }
    }

    private function saveDependencies($rule, array $data)
    {
        if (empty($data['dependencies'])) return;
        foreach ($data['dependencies'] as $index => $dep) {
            if (empty($dep['child_rule_id'])) continue;
            if ($dep['child_rule_id'] == $rule->id) {
                throw new Exception('Una regla no puede depender de sí misma.');
            }
            RuleDependency::create([
                'parent_rule_id' => $rule->id,
                'child_rule_id'  => $dep['child_rule_id'],
                'operator'       => $dep['operator'] ?? 'AND',
                'order_index'    => $index,
            ]);
        }
    }
}
