<?php

namespace Modules\OrderManage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelResonRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (isModuleActive('FrontendMultiLang')) {
            $code = auth()->user()->lang_code;
            return [
                'name.'. $code => "required|max:255|unique_translation:cancel_reasons,name,{$this->id}",
                'description.'. $code => 'required'
            ];
        }else{
            return [
                'name' => 'required|max:255|unique:cancel_reasons,name,'.$this->id,
                'description' => 'required'
            ];
        }
        
    }
    public function messages()
    {
        if (isModuleActive('FrontendMultiLang')) {
            return [
                'name.*.required'           => __('order.validation_name_required'),
                'name.*.unique_translation'  => __('order.validation_name_unique'),
                'description.*.required'    => __('order.validation_description_required'),
            ];
        } else {
            return [
                'name.required'        => __('order.validation_name_required'),
                'name.unique'          => __('order.validation_name_unique'),
                'description.required' => __('order.validation_description_required'),
            ];
        }
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
