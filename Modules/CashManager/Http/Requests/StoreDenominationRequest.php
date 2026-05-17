<?php

namespace Modules\CashManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDenominationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id' => 'required|integer|exists:countries,id',
            'type'       => 'required|in:BILLETE,MONEDA',
            'value'      => [
                'required',
                'numeric',
                'min:1',
                // Impedir duplicados: misma combinación país+valor
                \Illuminate\Validation\Rule::unique('cat_denominations')
                    ->where(fn ($q) => $q
                        ->where('country_id', $this->country_id)
                        ->whereNull('deleted_at')
                    ),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'value.unique' => __('cashmanager::cash_manager.denomination_duplicate'),
        ];
    }
}
