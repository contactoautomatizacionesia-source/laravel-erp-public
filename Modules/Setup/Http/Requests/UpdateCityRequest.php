<?php

namespace Modules\Setup\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCityRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('state') && ! $this->has('state_id')) {
            $this->merge(['state_id' => $this->input('state')]);
        }
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                Rule::unique('cities', 'name')
                    ->where('state_id', $this->input('state_id'))
                    ->ignore($this->id),
            ],
            'state_id' => [
                'required',
                'exists:states,id',
            ],
            'status' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function authorize()
    {
        return true;
    }
}
