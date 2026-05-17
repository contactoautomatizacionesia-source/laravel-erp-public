<?php

namespace Modules\Setup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Setup\Rules\FlagDimensions;

class StoreCountryRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => [
                'required',
                'regex:/^[\pL\s]+$/u',
                Rule::unique('countries', 'name')->where(function ($query) {
                    $value = $this->input('name');
                    $query->whereRaw('LOWER(name) = ?', [strtolower($value)]);
                }),
            ],
            'code' => [
                'required',
                'regex:/^[A-Za-z0-9]+$/',
                Rule::unique('countries', 'code')->where(function ($query) {
                    $value = $this->input('code');
                    if ($value !== null) {
                        $query->whereRaw('LOWER(code) = ?', [strtolower($value)]);
                    }
                }),
            ],
            'phonecode' => [
                'required',
                'regex:/^\+?[0-9]+$/',
            ],
            'flag' => [
                'nullable',
                'mimes:jpg,png',
                'max:200',
                new FlagDimensions(),
            ],
            'status' => [
                'required',
                'boolean',
            ],
            'is_default' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function authorize()
    {
        return true;
    }
}
