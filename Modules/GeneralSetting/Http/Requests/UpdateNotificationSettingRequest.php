<?php

namespace Modules\GeneralSetting\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingRequest extends FormRequest
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
                'event.'. $code => 'required|max:87',
                'message.'. $code => 'required|max:256',
                'admin_msg.'. $code => 'required|max:256',
                'type' => 'required',
            ];
        }else{
            return [
                'event' => 'required|max:87',
                'message' => 'required|max:256',
                'admin_msg' => 'required|max:256',
                'type' => 'required',
            ];
        }
    }
    public function messages()
    {
        if (isModuleActive('FrontendMultiLang')) {
            return [
                'event.*.required' => __('validation.required', ['attribute' => __('hr.event')]),
                'event.max' => __('validation.max_amount', ['attribute' => __('common.characters'), 'max' => 87]),
                'message.*.required' => __('validation.required', ['attribute' => __('general_settings.message_for_particular_user')]),
                'message.max' => __('validation.max_amount', ['attribute' => __('common.characters'), 'max' => 256]),
                'admin_msg.*.required' => __('validation.required', ['attribute' => __('general_settings.message_for_admin_site')]),
                'admin_msg.max' => __('validation.max_amount', ['attribute' => __('common.characters'), 'max' => 256]),
                'type' => __('validation.required', ['attribute' => __('common.type')]),
            ];
        }else{
            return [
                'event.required' => __('validation.required', ['attribute' => __('hr.event')]),
                'event.max' => __('validation.max_amount', ['attribute' => __('common.characters'), 'max' => 87]),
                'message.required' => __('validation.required', ['attribute' => __('general_settings.message_for_particular_user')]),
                'message.max' => __('validation.max_amount', ['attribute' => __('common.characters'), 'max' => 256]),
                'admin_msg.required' => __('validation.required', ['attribute' => __('general_settings.message_for_admin_site')]),
                'admin_msg.max' => __('validation.max_amount', ['attribute' => __('common.characters'), 'max' => 256]),
                'type' => __('validation.required', ['attribute' => __('common.type')]),
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
