<?php

namespace Modules\CashManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveOperatorRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'operator_role_ids'   => 'required|array|min:1',
            'operator_role_ids.*' => 'integer|exists:roles,id',
        ];
    }
}
