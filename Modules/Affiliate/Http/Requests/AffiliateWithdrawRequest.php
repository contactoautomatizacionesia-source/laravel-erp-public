<?php

namespace Modules\Affiliate\Http\Requests;

use App\Traits\ValidationMessage;
use Illuminate\Foundation\Http\FormRequest;

class AffiliateWithdrawRequest extends FormRequest
{
    use ValidationMessage;

    public function rules()
    {
        return [
            'user_id'=>'required',
            'withdraw_amount'=>'required',
            'payment_type'=>'required',
        ];

    }

    public function authorize()
    {
        return true;
    }
}
