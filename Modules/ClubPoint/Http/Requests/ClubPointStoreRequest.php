<?php

namespace Modules\ClubPoint\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClubPointStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'multiple'=>'required|numeric|min:2',
            'min'=>'required|numeric|min:1',
            'max'=>'required|numeric|min:1',
        ];
    }

    public function messages()
    {
        return [
            'multiple.required' => 'The multiple field is required',
            'multiple.numeric' => 'The multiple field must be numeric',
            'multiple.min' => 'minimum value be 2',
            'min.required' => 'The min field is required',
            'min.numeric' => 'The min field must be numeric',
            'min.min' => 'minimum value must be 1',
            'max.required' => 'The max field is required',
            'max.numeric' => 'The max field must be numeric',
            'max.min' => 'minimum value must be 1',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
