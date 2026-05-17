<?php

namespace Modules\Affiliate\Http\Requests;

use App\Traits\ValidationMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Session;

class BalanceTransferRequest extends FormRequest
{

    use ValidationMessage;


    public function rules()
    {
        return [
            'user_id'=>'required',
            'transfer_amount'=>'required',
            'payment_type'=>'required',
        ];

    }

    public function authorize()
    {
        return true;
    }
}
