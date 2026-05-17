<?php

namespace Modules\CycleClosure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\CycleClosure\Constants\CycleRoles;

class StoreCycleSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_type'      => ['required', 'in:daily,monthly,annual'],
            'execution_day'    => ['nullable', 'required_if:period_type,monthly', 'integer', 'min:1', 'max:31'],
            'executor_user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn($q) => $q->whereIn('role_id', CycleRoles::EXECUTOR_ROLES)->where('is_active', 1)),
                'different:' . auth()->id(),
            ],
            'approver_user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn($q) => $q->where('role_id', CycleRoles::CONTADOR)->where('is_active', 1)),
                'different:' . auth()->id(),
                'different:executor_user_id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'period_type.required'          => __('cycleclosure::messages.period_type_required'),
            'period_type.in'                => __('cycleclosure::messages.period_type_invalid'),
            'execution_day.required_if'     => __('cycleclosure::messages.execution_day_required'),
            'execution_day.min'             => __('cycleclosure::messages.execution_day_range'),
            'execution_day.max'             => __('cycleclosure::messages.execution_day_range'),
            'executor_user_id.required'     => __('cycleclosure::messages.executor_required'),
            'executor_user_id.exists'       => __('cycleclosure::messages.executor_not_found'),
            'executor_user_id.different'    => __('cycleclosure::messages.executor_same_user'),
            'approver_user_id.required'     => __('cycleclosure::messages.approver_required'),
            'approver_user_id.exists'       => __('cycleclosure::messages.approver_not_found'),
            'approver_user_id.different'    => __('cycleclosure::messages.approver_same_user'),
        ];
    }
}
