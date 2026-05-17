<?php

namespace Modules\Setup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Setup\Rules\FlagDimensions;

class UpdateCountryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_default' => $this->boolean('is_default'),
        ]);
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                'regex:/^[\pL\s]+$/u',
                Rule::unique('countries', 'name')
                    ->ignore($this->id)
                    ->where(function ($query) {
                        $value = $this->input('name');
                        $query->whereRaw('LOWER(name) = ?', [strtolower($value)]);
                    }),
            ],
            'code' => [
                'required',
                'regex:/^[A-Za-z0-9]+$/',
                Rule::unique('countries', 'code')
                    ->ignore($this->id)
                    ->where(function ($query) {
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
