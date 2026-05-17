<?php

namespace Modules\Plans\Repositories;

use Modules\Plans\Entities\Benefit;
use Modules\Plans\Entities\BenefitCategory;

class BenefitRepository
{
    public function getBaseQuery()
    {
        return Benefit::with('category.type')
            ->withCount('planChildren')
            ->orderBy('created_at', 'desc');
    }

    public function findById($id, $relations = [])
    {
        return Benefit::with($relations)->findOrFail($id);
    }

    public function create(array $data)
    {
        return Benefit::create($data);
    }

    public function update(Benefit $benefit, array $data)
    {
        $benefit->update($data);
        return $benefit;
    }

    public function delete(Benefit $benefit)
    {
        return $benefit->delete();
    }

    public function getCategoriesWithSections()
    {
        return BenefitCategory::with(['type', 'formSections.fields'])->get();
    }

    public function getCategoryWithSections($categoryId)
    {
        return BenefitCategory::with(['formSections' => function ($q) {
            $q->where('is_active', true)->orderBy('section_order')->with(['fields' => function ($q2) {
                $q2->where('is_active', true);
            }]);
        }])->findOrFail($categoryId);
    }
}
