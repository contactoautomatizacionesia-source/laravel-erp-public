<?php
namespace Modules\Plans\Services;

use Modules\Plans\Repositories\BenefitRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class BenefitService
{
    protected $benefitRepository;

    public function __construct(BenefitRepository $benefitRepository)
    {
        $this->benefitRepository = $benefitRepository;
    }

    public function storeBenefit(array $data)
    {
        DB::beginTransaction();
        try {
            $data['is_cumulative'] = isset($data['is_cumulative']) ? true : false;
            $data['is_active']     = isset($data['is_active'])     ? true : false;
            $benefit = $this->benefitRepository->create([
                'code'                => $data['code'] ?? null,
                'title'               => $data['title'],
                'description'         => $data['description'] ?? null,
                'benefit_category_id' => $data['benefit_category_id'],
                'is_cumulative'       => $data['is_cumulative'],
                'is_active'           => $data['is_active'],
            ]);
            $this->saveAnswers($benefit, $data);
            DB::commit();

            return $benefit;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateBenefit($id, array $data)
    {
        DB::beginTransaction();
        try {
            $data['is_cumulative'] = isset($data['is_cumulative']) ? true : false;
            $data['is_active']     = isset($data['is_active'])     ? true : false;
            $benefit = $this->benefitRepository->findById($id);
            $this->benefitRepository->update($benefit, [
                'code'                => $data['code'] ?? null,
                'title'               => $data['title'],
                'description'         => $data['description'] ?? null,
                'benefit_category_id' => $data['benefit_category_id'],
                'is_cumulative'       => $data['is_cumulative'],
                'is_active'           => $data['is_active'],
            ]);
            $benefit->formAnswers()->delete();
            $this->saveAnswers($benefit, $data);
            DB::commit();
            return $benefit;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteBenefit($id)
    {
        $benefit = $this->benefitRepository->findById($id)->loadCount('planChildren');
        if ($benefit->plan_children_count > 0) {
            throw new Exception('No se puede eliminar: el beneficio está en uso en ' . $benefit->plan_children_count . ' subplan(es).');
        }
        return $this->benefitRepository->delete($benefit);
    }

    private function saveAnswers($benefit, array $data): void
    {
        if (empty($data['answers'])) return;

        foreach ($data['answers'] as $fieldId => $value) {
            if (is_array($value)) {
                $this->saveRepeatableAnswers($benefit, $fieldId, $value);
            } elseif ($value !== null && $value !== '') {
                $benefit->formAnswers()->create([
                    'form_field_id' => $fieldId,
                    'answer'        => $value,
                ]);
            }
        }
    }

    private function saveRepeatableAnswers($benefit, $fieldId, array $values): void
    {
        foreach ($values as $repeatIndex => $repeatValue) {
            if ($repeatValue === null || $repeatValue === '') {
                continue;
            }
            $benefit->formAnswers()->create([
                'form_field_id' => $fieldId,
                'answer'        => $repeatValue,
                'repeat_index'  => $repeatIndex,
            ]);
        }
    }
}
