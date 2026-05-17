<?php

namespace Modules\Shipping\Http\Requests;

use CodeZero\UniqueTranslation\UniqueTranslationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarrierRequest extends FormRequest
{
    public function rules()
    {
        if (isModuleActive('FrontendMultiLang')) {
            $code = auth()->user()->lang_code;
            return [
                'name.'. $code  =>  ['required',UniqueTranslationRule::for('carriers', 'name')->where(function($q){
                    $seller_id = getParentSellerId();
                    return $q->where('created_by', $seller_id);
                })->ignore($this->id)],
                'tracking_url'=>'nullable',
                'logo'=>'nullable',
            ];
        }else{
            return [
                'name' =>  ['required',Rule::unique('carriers', 'name')->where(function($q){
                    $seller_id = getParentSellerId();
                    return $q->where('id', '!=', $this->id)->where('created_by', $seller_id);
                })],
                'tracking_url'=>'nullable',
                'logo'=>'nullable',
            ];
        }
    }
    public function messages()
    {
        if (isModuleActive('FrontendMultiLang')) {
            return [
                'name.*.required' => __('shipping.name_field_required'),
                'name.*.UniqueTranslationRule' => __('shipping.name_has_already_been_taken'),
            ];
        }else{
            return [
                'name.required' => __('shipping.name_field_required'),
                'name.unique' => __('shipping.name_has_already_been_taken'),
            ];
        }
    }
    public function authorize()
    {
        return true;
    }
}
