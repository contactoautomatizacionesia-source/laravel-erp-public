<?php

namespace Modules\Affiliate\Http\Requests;

use App\Traits\ValidationMessage;
use Illuminate\Foundation\Http\FormRequest;

class AffiliateConfigurationRequest extends FormRequest
{

    use ValidationMessage;

    public function rules()
    {
        return [
            'min_withdraw'=>'required',
            'balance_add_account_after_days'=>'required',
            'transfer_approval_need'=>'required',
            'admin_approval_need'=>'required',
            'referral_duration_type'=>'required',
            'referral_duration'=>'required_if:referral_duration_type,Fixed',
            'commission_type'=>'required',
            'amount_type'=>'required_if:commission_type,Product',
            'commission_amount'=>'required_if:commission_type,Product',
            'multi_category_commission_calculate'=>'required_if:commission_type,Category',
            'common_calculation_method'=>'required_if:commission_type,Category',
            'common_amount'=>'required_if:commission_type,Category',
        ];

    }

    public function authorize()
    {
        return true;
    }
}
