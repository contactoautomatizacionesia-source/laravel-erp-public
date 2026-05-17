<?php

namespace Modules\CashManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignCashBoxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cash_box_id' => 'required|uuid|exists:cash_boxes,id',
            'user_id'     => 'required|integer|exists:users,id',
        ];
    }
}
