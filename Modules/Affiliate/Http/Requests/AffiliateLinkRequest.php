<?php

namespace Modules\Affiliate\Http\Requests;

use App\Traits\ValidationMessage;
use Illuminate\Foundation\Http\FormRequest;

class AffiliateLinkRequest extends FormRequest
{
    use ValidationMessage;

    public function rules()
    {
        return [
            'user_name'=>'nullable',
            'url'=>'required',
            'affiliate_link'=>'nullable|unique:affiliate_links,affiliate_link',
        ];

    }

    public function authorize()
    {
        return true;
    }
}
