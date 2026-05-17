<?php

namespace Modules\Plans\Repositories;

use Modules\Plans\Entities\Rule;
use Modules\Plans\Entities\RuleCategory;

class RuleRepository
{
    public function getBaseQuery()
    {
        return Rule::with('category.type')
            ->withCount('planChildren')
            ->orderBy('id', 'desc');
    }

    public function findById($id, $relations = [])
    {
        return Rule::with($relations)->findOrFail($id);
    }

    public function create(array $data)
    {
        return Rule::create($data);
    }

    public function update(Rule $rule, array $data)
    {
        $rule->update($data);
        return $rule;
    }

    public function delete(Rule $rule)
    {
        return $rule->delete();
    }

    public function getCategoriesWithSections()
    {
        return RuleCategory::with(['type', 'formSections.fields'])->get();
    }

    public function getCategoryWithSections($categoryId)
    {
        return RuleCategory::with(['formSections' => function ($q) {
            $q->where('is_active', true)->orderBy('section_order')->with(['fields' => function ($q2) {
                $q2->where('is_active', true);
            }]);
        }])->findOrFail($categoryId);
    }
}
