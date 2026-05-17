<?php

namespace Modules\Otp\Http\Controllers;

use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Otp\Repositories\OtpRepository;
use Modules\UserActivityLog\Traits\LogActivity;

class OtpController extends Controller
{
    public function configuration()
    {

        return view('otp::configuration');
    }

    public function configuration_update(Request $request)
    {

        try{
            if($request->otp_activation_for_seller == 1  && !isset($request->otp_type_registration)){
                Toastr::error(__('otp.please_select_otp_type'), __('common.error'));
                return back();
            }
            if($request->otp_activation_for_order == 1  && !isset($request->otp_type_order)){
                Toastr::error(__('otp.please_select_otp_type'), __('common.error'));
                return back();
            }
            (new OtpRepository())->update_configuration($request);
            Toastr::success(__('common.operation_done_successfully'), __('common.success'));
            return back();
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return redirect()->back();
        }
    }

}
