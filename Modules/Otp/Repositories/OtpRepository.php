<?php

namespace Modules\Otp\Repositories;

use Modules\Otp\Entities\OtpConfiguration;

class OtpRepository
{
    public function update_configuration($request)
    {
        OtpConfiguration::where('key', 'code_validation_time')->first()->update(['value' => $request->code_validation_time]);
        OtpConfiguration::where('key', 'otp_activation_for_customer')->first()->update(['value' => $request->otp_activation_for_customer]);
        OtpConfiguration::where('key', 'otp_activation_for_order')->first()->update(['value' => $request->otp_activation_for_order]);
        OtpConfiguration::where('key', 'order_otp_on_verified_customer')->first()->update(['value' => $request->order_otp_on_verified_customer]);
        OtpConfiguration::where('key', 'otp_on_login')->first()->update(['value' => $request->otp_on_login]);
        OtpConfiguration::where('key', 'otp_on_password_reset')->first()->update(['value' => $request->otp_on_password_reset]);
        OtpConfiguration::where('key', 'order_cancel_limit_on_verified_customer')->first()->update(['value' => $request->order_cancel_limit_on_verified_customer]);
        if (isModuleActive('MultiVendor')) {
            OtpConfiguration::where('key', 'otp_activation_for_seller')->first()->update(['value' => $request->otp_activation_for_seller]);
        }
        $otp_type_order = "";
        if (isset($request->otp_type_order)) {
            foreach ($request->otp_type_order as $otp_type) {
                $otp_type_order .= $otp_type . ",";
            }
        }
        $otp_type_registration = "";
        if (isset($request->otp_type_registration)) {
            foreach ($request->otp_type_registration as $otp_type_reg) {
                $otp_type_registration .= $otp_type_reg . ",";
            }
        }
        OtpConfiguration::where('key', 'otp_type_order')->first()->update(['value' => $otp_type_order]);


        OtpConfiguration::where('key', 'otp_type_registration')->first()->update(['value' => $otp_type_registration]);
    }
}
