<?php

namespace Modules\CycleClosure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveCycleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'confirm' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm.required' => __('cycleclosure::messages.confirm_required'),
            'confirm.accepted' => __('cycleclosure::messages.confirm_required'),
        ];
    }
}
