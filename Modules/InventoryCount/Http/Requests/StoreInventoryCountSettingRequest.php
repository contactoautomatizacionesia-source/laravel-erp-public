<?php

namespace Modules\InventoryCount\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryCountSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cost_center_id'     => 'required|exists:cost_centers,id',
            'count_role_id'      => 'required|exists:roles,id',
            'max_attempts'       => 'required|integer|min:0|max:255',
            'allow_history_view' => 'nullable|boolean',
            'notify_user_ids'    => [
                'array',
                function ($attr, $value, $fail) { // NOSONAR
                    if ((int) $this->max_attempts > 0 && empty($value)) {
                        $fail(__('inventorycount::messages.notify_required_when_limited'));
                    }
                },
            ],
            'notify_user_ids.*'  => 'exists:users,id',
        ];
    }
}
