<?php

namespace Modules\CostCenter\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\GeneralSetting\Rules\CatalogExists;

class CostCenterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('cost_center') ?? $this->id;

        return [
            'code' => [
                'nullable',
                'string',
                Rule::unique('cost_centers', 'code')->ignore($id),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\sñÑáéíóúÁÉÍÓÚ]+$/u',
                Rule::unique('cost_centers', 'name')->ignore($id),
            ],
            'city_id'         => 'required|integer|exists:cities,id',
            'address'         => 'required|string|max:255',
            'pin_code'        => 'required|string|max:20',
            'phone'           => 'required|string|max:10|regex:/^[0-9]+$/',
            'brand_id'        => 'nullable|integer|exists:brands,id',
            'comment'         => 'nullable|string|max:1000',
            'status'          => 'required|boolean',
            'is_default'      => 'required|boolean',
            'payment_form_id' => [
                'nullable',
                'integer',
                new CatalogExists('payment_form')
            ],
        ];
    }
}
