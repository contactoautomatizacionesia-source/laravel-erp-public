<?php

namespace App\Traits;

use PDF;
use App\Models\User;
use App\Jobs\SendmailJob;
use App\Mail\TestSmptMail;
use App\Mail\SendQueueMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\UserActivityLog\Traits\LogActivity;
use Modules\GeneralSetting\Entities\EmailTemplate;
use Modules\GeneralSetting\Entities\EmailTemplateType;

trait SendMail
{

    public function sendNotificationByMail($typeId, $user, $notificationSetting, $relatable_id = null, $relatable_type = null, $order_tracking_number = null)
    {
        $email_template = EmailTemplate::where('type_id', $typeId)->where('relatable_id', $relatable_id)->where('relatable_type', $relatable_type)->where('is_active', 1)->first();

        if (!$email_template) {
            return false;
        }

        try {
            $protocol = app('general_setting')->mail_protocol;

            $datas = $this->mailData($email_template, $user->first_name, $user->email, $order_tracking_number, $notificationSetting->message, null, $this->notificationUrl);

            if(in_array($protocol, ['smtp', 'sendgrid'])) {
                Mail::mailer($protocol)->to($user->email)->queue(new SendQueueMail($datas));
                return true;
            } elseif ($protocol == "sendmail") {
                $datas = $this->mailData($email_template, $user->first_name, $user->email, $order_tracking_number, $notificationSetting->message);
                $message = (string) view('emails.mail', $datas);

                if(config('queue.default') == 'sync'){
                    return $this->phpMailData($user->email, $email_template->subject, $message);
                }

                dispatch(new SendmailJob($user->email, $email_template->subject, $message));
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
        }

    }

    public function orderCancelMailSend($type_id, $order)
    {
        $email_template = EmailTemplate::where('type_id', $type_id)->where('is_active', 1)->first();

        if(!$email_template) {
            return false;
        }

        try {
            $protocol = app('general_setting')->mail_protocol;
            $datas = $this->mailData($email_template, $order->customer->first_name, $order->customer_email, $order->order_number);

            if (in_array($protocol, ['smtp', 'sendgrid'])) {
                Mail::mailer($protocol)->to($order->customer_email)->queue(new SendQueueMail($datas));
                return true;
            } elseif ($protocol == "sendmail") {
                $message = (string) view('emails.mail', $datas);
                if(config('queue.default') == 'sync'){
                    return $this->phpMailData($order->customer_email, $email_template->subject, $message);
                }

                dispatch(new SendmailJob($order->customer_email, $email_template->subject, $message));
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
        }
    }

    public function sendOtpByMail($user, $otp)
    {
        $email_template = EmailTemplate::where('type_id', 35)->where('is_active', 1)->first();
        $email = $user->customer_email;

        if (!$email_template) {
            return false;
        }

        try {
            $protocol = app('general_setting')->mail_protocol;

            if (in_array($protocol, ['smtp', 'sendgrid'])) {
                $datas = $this->otpMailData($email_template, $user->name, $email, $otp);
                // Se envia el protocolo especifico para el envio del correo
                Mail::mailer($protocol)->to($user->email)->queue(new SendQueueMail($datas));
                return true;
            }

            if ($protocol == "sendmail") {
                $datas = $this->otpMailData($email_template, $user->first_name, $user->email, $otp);
                $message = (string) view('emails.mail', $datas);
                if(config('queue.default') == 'sync'){
                    return $this->phpMailData($user->email, $email_template->subject, $message);
                }

                dispatch(new SendmailJob($user->email, $email_template->subject, $message));
                return true;
            }

            return false;

        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return false;
        }
    }
    public function sendLoginOtpByMail($user, $otp)
    {
        $email_template = EmailTemplate::where('type_id', 37)->where('is_active', 1)->first();

        if (!$email_template) {
            return false;
        }

        $datas = $this->otpMailData($email_template, $user->first_name, $user->email, $otp);

        // Delegamos la ejecución al nuevo método centralizado para enviar correos, que maneja SMTP, SendGrid y Sendmail de forma unificada
        return $this->executeMail($user->email, $email_template->subject, $datas);

    }

    public function sendPasswordResetOtpByMail($user, $otp)
    {
        $email_template = EmailTemplate::where('type_id', 38)->where('is_active', 1)->first();

        if (!$email_template) {
            return false;
        }

        $datas = $this->otpMailData($email_template, $user->first_name, $user->email, $otp);

        // Delegamos la ejecución al nuevo método centralizado para enviar correos, que maneja SMTP, SendGrid y Sendmail de forma unificada
        return $this->executeMail($user->email, $email_template->subject, $datas);
    }

    public function sendOtpByMailForSeller($user, $otp)
    {
        $email_template = EmailTemplate::where('type_id', 35)->where('is_active', 1)->first();

        if (!$email_template) {
            return false;
        }

        $datas = $this->otpMailData($email_template, $user->name, $user->email, $otp);

        // Delegamos la ejecución al método centralizado para enviar correos, que maneja SMTP, SendGrid y Sendmail de forma unificada
        return $this->executeMail($user->email, $email_template->subject, $datas);
    }

    public function sendOtpByMailForOrder($user, $otp)
    {
        $email_template = EmailTemplate::where('type_id', 36)->where('is_active', 1)->first();
        $email = $user->customer_email;

        if (!$email_template) {
            return false;
        }

        try {
            $protocol = app('general_setting')->mail_protocol;

            $datas = $this->mailData($email_template, $user->name, $email, $otp);

            // Bloque para protocolos basados en Mailer de Laravel (SMTP y SendGrid)
            if (in_array($protocol, ['smtp', 'sendgrid'])) {
                Mail::mailer($protocol)->to($user->email)->queue(new SendQueueMail($datas));
                return true;
            }

            // Bloque para Sendmail (Legacy)
            if ($protocol == "sendmail") {
                $message = (string) view('emails.mail', $datas);

                if (config('queue.default') == 'sync') {
                    return $this->phpMailData($email, $email_template->subject, $message);
                }

                dispatch(new SendmailJob($email, $email_template->subject, $message));
                return true;
            }

            return false;

        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return false;
        }
    }

    public function sendSupportTicketMail($user, $supportTicketMessage)
    {
        $email_template = EmailTemplate::where('type_id', 22)->where('is_active', 1)->first();
        if (!$email_template) {
            return false;
        }

        $datas = $this->mailData($email_template, $user->first_name, $user->email, $supportTicketMessage);

        // Delegamos la ejecución al método centralizado para enviar correos, que maneja SMTP, SendGrid y Sendmail de forma unificada
        return $this->executeMail($user->email, $email_template->subject, $datas);
    }

    public function sendVerificationMail($user, $supportTicketMessage)
    {
        $email_template = EmailTemplate::where('type_id', 23)->where('is_active', 1)->first();
        if (!$email_template) {
            return false;
        }

        $datas = $this->mailData($email_template, $user->first_name, $user->email, null, null, null, $supportTicketMessage);

        // Delegamos la ejecución al método centralizado para enviar correos, que maneja SMTP, SendGrid y Sendmail de forma unificada
        return $this->executeMail($user->email, $email_template->subject, $datas);
    }

    public function sendSellerVerificationMail($user, $supportTicketMessage)
    {
        $email_template = EmailTemplate::where('type_id', 39)->where('is_active', 1)->first();
        if (!$email_template) {
            return false;
        }

        $datas = $this->mailData($email_template, $user->first_name, $user->email, null, null, null, $supportTicketMessage);

        // Delegamos la ejecución al método centralizado para enviar correos, que maneja SMTP, SendGrid y Sendmail de forma unificada
        return $this->executeMail($user->email, $email_template->subject, $datas);
    }

    function sendMailWithTemplate($to, $array, $mailPath, $template)
    {
        try {
            $mail_protocol = app('general_setting')->mail_protocol;

            if (in_array($mail_protocol, ['smtp', 'sendgrid'])) {
                // Mail::to($to)->queue(new $mailPath($array));
                Mail::mailer($mail_protocol)->to($to)->queue(new $mailPath($array));
                return true;
            } elseif ($mail_protocol == "sendmail") {
                $message = (string) view($template, compact('array'));
                if(config('queue.default') == 'sync'){
                    return $this->phpMailData($to, $array['subject'], $message);
                }

                dispatch(new SendmailJob($to, $array['subject'], $message));
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
        }
    }

    public function sendMailTest($to, $subject, $body)
    {
        try {
            $protocol = app('general_setting')->mail_protocol;

            // Preparamos los datos base del correo de prueba
            $attribute = [
                'from'    => getMailFromByProtocol(), // Helper que elige el correo correcto según protocolo
                'subject' => ($protocol == 'sendgrid') ? 'Test Sendgrid Mail' : $subject,
                'content' => $body,
            ];

            if (in_array($protocol, ['smtp', 'sendgrid'])) {
                // Mail::to($to)->send(new TestSmptMail($attribute));
                Mail::mailer($protocol)->to($to)->send(new TestSmptMail($attribute));
                return true;
            } elseif ($protocol == "sendmail") {
                $datas = [
                    'from' => env('MAIL_FROM_ADDRESS'),
                    'subject' => $subject,
                    'body' => $body
                ];
                $message = (string) view("emails.mail", $datas);
                if(config('queue.default') == 'sync'){
                    return $this->phpMailData($to, $subject, $message);
                }

                dispatch(new SendmailJob($to, $subject, $message));
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return 'failed';
        }
    }

    function sendInvoiceMail($order_number, $order)
    {
        try {
            $email_template = EmailTemplate::where('type_id', 1)->where('is_active', 1)->first();
            if ($email_template && $email_template->is_active == 1) {
                $protocol = app('general_setting')->mail_protocol;

                if (in_array($protocol, ['smtp', 'sendgrid'])) {
                    $path = public_path('/invoice/order-'.$order->id.'.pdf');
                    $pdf = PDF::loadView(theme('pages.profile.order_pdf'), compact('order'))->save($path);

                    // Decodificar destinatarios una sola vez
                    $recipient_types = json_decode($email_template->reciepnt_type);

                    if (in_array("customer", $recipient_types)) {
                        if ($order->customer_id) {
                            $datas = $this->mailInvoiceData($email_template, $order->customer->first_name, $order->customer_email, $order);
                        } else {
                            $datas = $this->mailInvoiceData($email_template, $order->guest_info->billing_name, $order->guest_info->billing_email, $order);
                        }

                        $datas['attach'] = $path;
                        $target_email = $order->customer_id ? $order->customer_email : $order->guest_info->billing_email;
                        // Forzamos el mailer de acuerdo al protocolo y el remitente verificado en su config
                        Mail::mailer($protocol)->to($target_email)->queue(new SendQueueMail($datas));
                    }

                    if (in_array("admin", $recipient_types) || in_array("seller", $recipient_types)) {
                        foreach ($order->packages as $key => $package) {
                            if ($package->seller->email) {
                                $datas = $this->mailData($email_template, $package->seller->first_name, $package->seller->email, $package->package_code);
                                $datas['attach'] = $path;
                                Mail::mailer($protocol)->to($package->seller->email)->queue(new SendQueueMail($datas));
                            }
                        }
                    }

                    return true;
                } elseif ($protocol == "sendmail") {
                    $datas = $this->mailData($email_template, $order->customer->first_name, $order->customer_email, $order->order_number);
                    $message = (string) view('emails.mail', $datas);
                    if(config('queue.default') == 'sync'){
                        return $this->phpMailData($order->customer_email, $email_template->subject, $message);
                    }

                    dispatch(new SendmailJob($order->customer_email, $email_template->subject, $message));
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
        }
    }

    function sendOrderRefundInfoUpdateMail($order, $type_id)
    {
        try {
            $email_template = EmailTemplate::where('type_id', $type_id)->where('is_active', 1)->first();
            if ($email_template && $email_template->is_active == 1) {
                $protocol = app('general_setting')->mail_protocol;

                // Decodificar destinatarios una sola vez
                $recipient_types = json_decode($email_template->reciepnt_type);

                if (in_array($protocol, ['smtp', 'sendgrid'])) {
                    if (in_array("customer", $recipient_types)) {
                        if ($order->customer_id) {
                            $datas = $this->mailData($email_template, $order->customer->first_name, $order->customer_email, $order->order_number);
                        } else {
                            $datas = $this->mailData($email_template, $order->guest_info->billing_name, $order->guest_info->billing_email, $order->order_number);
                        }

                        $target_email = $order->customer_id ? $order->customer_email : $order->guest_info->billing_email;
                        // Forzamos el mailer de acuedo al protocolo y el remitente verificado en su config
                        Mail::mailer($protocol)->to($target_email)->queue(new SendQueueMail($datas));
                    }
                    if (in_array("seller", $recipient_types)) {
                        foreach ($order->packages as $key => $package) {
                            if ($package->seller->email) {
                                $datas = $this->mailData($email_template, $package->seller->first_name, $package->seller->email, $package->package_code);
                                Mail::mailer($protocol)->to($package->seller->email)->queue(new SendQueueMail($datas));
                            }
                        }
                    }
                    return true;
                } elseif ($protocol == "sendmail") {
                    if (in_array("customer", $recipient_types)) {
                        if ($order->customer_id) {
                            $datas = $this->mailData($email_template, $order->customer->first_name, $order->customer_email, $order->order_number);
                        } else {
                            $datas = $this->mailData($email_template, $order->guest_info->billing_name, $order->guest_info->billing_email, $order->order_number);
                        }

                        $target_email = $order->customer_id ? $order->customer_email : $order->guest_info->billing_email;
                        $message = (string) view('emails.mail', $datas);
                        if(config('queue.default') == 'sync'){
                            $this->phpMailData($target_email, $email_template->subject, $message);
                        }else{
                            dispatch(new SendmailJob($target_email, $email_template->subject, $message));
                        }
                    }
                    if (in_array("seller", $recipient_types)) {
                        foreach ($order->packages as $key => $package) {
                            if ($package->seller->email) {
                                $datas = $this->mailData($email_template, $package->seller->first_name, $package->seller->email, $package->package_code);
                                $message = (string) view('emails.mail', $datas);
                                if(config('queue.default') == 'sync'){
                                    $this->phpMailData($package->seller->email, $email_template->subject, $message);
                                }else{
                                    dispatch(new SendmailJob($package->seller->email, $email_template->subject, $message));
                                }
                            }
                        }
                    }
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
        }
    }

    function sendOrderRefundorDeliveryProcessMail($order, $relatable_type, $relatable_id)
    {
        try {
            $email_template = EmailTemplate::where('relatable_type', $relatable_type)->where('relatable_id', $relatable_id)->first();
            if ($email_template && $email_template->is_active == 1) {
                $protocol = app('general_setting')->mail_protocol;

                // Decodificar destinatarios una sola vez
                $recipient_types = json_decode($email_template->reciepnt_type);

                if (in_array($protocol, ['smtp', 'sendgrid'])) {
                    if (in_array("customer", $recipient_types)) {
                        if ($order->customer_id) {
                            $datas = $this->mailData($email_template, $order->customer->first_name, $order->customer_email, $order->order_number);
                        } else {
                            $datas = $this->mailData($email_template, $order->guest_info->billing_name, $order->guest_info->billing_email, $order->order_number);
                        }

                        $target_email = $order->customer_id ? $order->customer_email : $order->guest_info->billing_email;
                        // Forzamos el mailer de acuedo al protocolo y el remitente verificado en su config
                        Mail::mailer($protocol)->to($target_email)->queue(new SendQueueMail($datas));
                    }
                    if (in_array("seller", $recipient_types)) {
                        foreach ($order->packages as $key => $package) {
                            if ($package->seller->email) {
                                $datas = $this->mailData($email_template, $package->seller->first_name, $package->seller->email, $package->package_code);
                                Mail::mailer($protocol)->to($package->seller->email)->queue(new SendQueueMail($datas));
                            }
                        }
                    }
                    return true;
                } elseif ($protocol == "sendmail") {
                    if (in_array("customer", $recipient_types)) {
                        if ($order->customer_id) {
                            $datas = $this->mailData($email_template, $order->customer->first_name, $order->customer_email, $order->order_number);
                        } else {
                            $datas = $this->mailData($email_template, $order->guest_info->billing_name, $order->guest_info->billing_email, $order->order_number);
                        }

                        $target_email = $order->customer_id ? $order->customer_email : $order->guest_info->billing_email;
                        $message = (string) view('emails.mail', $datas);
                        if(config('queue.default') == 'sync'){
                            $this->phpMailData($target_email, $email_template->subject, $message);
                        }else{
                            dispatch(new SendmailJob($target_email, $email_template->subject, $message));
                        }

                    }
                    if (in_array("seller", $recipient_types)) {
                        foreach ($order->packages as $key => $package) {
                            if ($package->seller->email) {
                                $datas = $this->mailData($email_template, $package->seller->first_name, $package->seller->email, $package->package_code);
                                $message = (string) view('emails.mail', $datas);
                                if(config('queue.default') == 'sync'){
                                    $this->phpMailData($package->seller->email, $email_template->subject, $message);
                                }else{
                                    dispatch(new SendmailJob($package->seller->email, $email_template->subject, $message));
                                }
                            }
                        }
                    }
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
        }
    }
    function sendGiftCardSecretCodeMail($order, $to_mail, $gift_card, $secret_code)
    {
        try {
            $email_template = EmailTemplate::where('type_id', 15)->where('is_active', 1)->first();
            if ($email_template && $email_template->is_active == 1) {
                $protocol = app('general_setting')->mail_protocol;

                // Decodificar destinatarios una sola vez
                $recipient_types = json_decode($email_template->reciepnt_type);

                if (in_array($protocol, ['smtp', 'sendgrid'])) {
                    if (in_array("customer", $recipient_types)) {
                        if ($order->customer_id) {
                            $datas = $this->mailDataGiftCard($email_template, $order->customer->first_name, $to_mail, $order->order_number, $secret_code, $gift_card->name);
                        } else {
                            $datas = $this->mailDataGiftCard($email_template, $order->guest_info->shipping_name, $to_mail, $order->order_number, $secret_code, $gift_card->name);
                        }

                        // Forzamos el mailer de acuedo al protocolo y el remitente verificado en su config
                        Mail::mailer('sendgrid')->to($to_mail)->queue(new SendQueueMail($datas));
                    }
                    return true;
                } elseif ($protocol == "sendmail") {
                    if (in_array("customer", $recipient_types)) {
                        if ($order->customer_id) {
                            $datas = $this->mailDataGiftCard($email_template, $order->customer->first_name, $to_mail, $order->order_number, $secret_code, $gift_card->name);
                        } else {
                            $datas = $this->mailData($email_template, $order->guest_info->shipping_name, $to_mail, $order->order_number, $secret_code, $gift_card->name);
                        }

                        $message = (string) view('emails.mail', $datas);
                        if(config('queue.default') == 'sync'){
                            return $this->phpMailData($to_mail, $email_template->subject, $message);
                        }else{
                            dispatch(new SendmailJob($to_mail, $email_template->subject, $message));
                            return true;
                        }
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
        }
    }
    // send digital file
    function sendDigitalFileMail($to_mail, $download_link, $data = null)
    {
        try {
            $email_template = EmailTemplate::where('type_id', 43)->where('is_active', 1)->first();

            if($email_template){
                if(@$data['customer_id']){
                    $customer = User::find($data['customer_id']);
                    $customer_name = $customer->first_name;
                }else{
                    $customer_name = '';
                }

                $link = "<a href='" . $download_link . "'>" . __('common.click_here_to_download') . "</a>";
                $datas = $this->mailData($email_template, $customer_name, $to_mail, null, null, null, null, $link);
                $datas['title'] = $email_template->subject;

                $protocol = app('general_setting')->mail_protocol;

                if (in_array($protocol, ['smtp', 'sendgrid'])) {
                    // Forzamos el mailer de acuerdo al protocolo y el remitente verificado en su config
                    Mail::mailer($protocol)->to($to_mail)->queue(new SendQueueMail($datas));
                    return true;
                }elseif($protocol == "sendmail"){
                    if(config('queue.default') == 'sync'){
                        return $this->phpMailData($to_mail, $datas["title"], $datas["body"]);
                    }else{
                        dispatch(new SendmailJob($to_mail, $datas["title"], $datas["body"]));
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
        }
    }

    // send newsletter email verify mail
    public function sendNewsletterVerifyMail($data){
        try {
            // Generación del link de verificación
            $base_url = url('/subscription/email-verify');
            $verify_link = $base_url . '?email='.$data->email.'&verify_code='.$data->verify_code;
            $verify_link = "<a href='" . $verify_link . "'>" . __('common.click_here') . "</a>";
            $email_template = EmailTemplate::where('type_id', 42)->where('is_active', 1)->first();
            if (!$email_template) {
                return false;
            }

            $datas = $this->mailData($email_template, '', $data->email, '', null, null,$verify_link);

            // Delegamos la ejecución al método centralizado para enviar correos, que maneja SMTP, SendGrid y Sendmail de forma unificada
            return $this->executeMail($data->email, $datas['title'], $datas);
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
        }
    }

    public function mailData($email_template, $to_name, $to_mail, $order_tracking_number, $custom_message = null, $RESET_URL = null,$VERIFICATION_LINK = null, $DIGITAL_FILE_LINK = null)
    {
        $datas["email"] = app('general_setting')->email;
        $datas["title"] = $email_template->subject;
        $datas['from'] = getMailFromByProtocol();
        $datas["body"] = $email_template->value;
        $datas["body"] = str_replace("{SECRET_CODE}", $order_tracking_number, $datas["body"]);
        $datas["body"] = str_replace("{USER_FIRST_NAME}", $to_name, $datas["body"]);
        $datas["body"] = str_replace("{USER_EMAIL}", $to_mail, $datas["body"]);
        $datas["body"] = str_replace("{EMAIL_SIGNATURE}", app('general_setting')->mail_signature, $datas["body"]);
        $datas["body"] = str_replace("{ORDER_TRACKING_NUMBER}", $order_tracking_number, $datas["body"]);
        $datas["body"] = str_replace("{EMAIL_FOOTER}", $email_template->footer, $datas["body"]);
        $datas["body"] = str_replace("{WEBSITE_NAME}", app('general_setting')->site_title, $datas["body"]);
        $datas["body"] = str_replace("{CUSTOM_MESSAGE}", $custom_message, $datas["body"]);
        $datas["body"] = str_replace("{RESET_LINK}", $RESET_URL, $datas["body"]);
        // $datas["body"] = str_replace("{RESET_URL}", $RESET_URL, $datas["body"]);
        $datas["body"] = str_replace("{VERIFICATION_LINK}", $VERIFICATION_LINK, $datas["body"]);
        $datas["body"] = str_replace("{DIGITAL_FILE_LINK}", $DIGITAL_FILE_LINK, $datas["body"]);
        return $datas;
    }

    public function otpMailData($email_template,$to_name,$to_mail,$otp)
    {

        $datas["email"] = app('general_setting')->email;
        $datas["title"] = $email_template->subject;
        $datas['from'] = getMailFromByProtocol();
        $datas["body"] = $email_template->value;
        $datas["body"] = str_replace("{USER_FIRST_NAME}", $to_name, $datas["body"]);
        $datas["body"] = str_replace("{USER_EMAIL}", $to_mail, $datas["body"]);
        $datas["body"] = str_replace("{OTP}", $otp, $datas["body"]);
        $datas["body"] = str_replace("{EMAIL_SIGNATURE}", app('general_setting')->mail_signature, $datas["body"]);
        return $datas;
    }

    public function mailInvoiceData($email_template, $to_name, $to_mail, $order)
    {
        $datas["email"] = app('general_setting')->email;
        $datas["title"] = $email_template->subject;
        $datas["body"] = $email_template->value;
        $datas["body"] = str_replace("{USER_FIRST_NAME}", $to_name, $datas["body"]);
        $datas["body"] = str_replace("{USER_EMAIL}", $to_mail, $datas["body"]);
        $datas["body"] = str_replace("{EMAIL_SIGNATURE}", app('general_setting')->mail_signature, $datas["body"]);
        $datas["body"] = str_replace("{ORDER_TRACKING_NUMBER}", $order->order_number, $datas["body"]);
        $datas["body"] = str_replace("{EMAIL_FOOTER}", $email_template->footer, $datas["body"]);
        $datas["body"] = str_replace("{WEBSITE_NAME}", app('general_setting')->site_title, $datas["body"]);
        $datas["body"] = str_replace("{RECIEVER_EMAIL}", @$order->shipping_address->email, $datas["body"]);
        $datas["body"] = str_replace("{RECIEVER_PHONE}", @$order->shipping_address->phone, $datas["body"]);
        $datas["body"] = str_replace("{RECIEVER_ADDRESS}", @$order->shipping_address->address, $datas["body"]);
        $datas["body"] = str_replace("{RECIEVER_CITY}", @$order->shipping_address->city->name, $datas["body"]);
        $datas["body"] = str_replace("{RECIEVER_STATE}", @$order->shipping_address->state->name, $datas["body"]);
        $datas["body"] = str_replace("{RECIEVER_COUNTRY}", @$order->shipping_address->country->name, $datas["body"]);
        $datas["inv_details"] = (string) view(theme('pages.profile.order_pdf'), compact('order'));
        return $datas;
    }

    public function phpMailData($to, $subject, $message)
    {
        try {
            $headers = "From:  ". config('mail.sender.name') ." <". config('mail.sender.email') .">"  . " \r\n";
            $headers .= "Reply-To: " . app('general_setting')->email . " \r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html; charset=UTF-8" . "\r\n";
            return mail($to, $subject, $message, $headers);
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return false;
        }
    }

    public function mailDataGiftCard($email_template, $to_name, $to_mail, $order_tracking_number, $secret_code,$gift_card_name)
    {
        $datas["email"] = app('general_setting')->email;
        $datas["title"] = $email_template->subject;
        $datas["body"] = $email_template->value;
        $datas["body"] = str_replace("{USER_FIRST_NAME}", $to_name, $datas["body"]);
        $datas["body"] = str_replace("{USER_EMAIL}", $to_mail, $datas["body"]);
        $datas["body"] = str_replace("{EMAIL_SIGNATURE}", app('general_setting')->mail_signature, $datas["body"]);
        $datas["body"] = str_replace("{ORDER_TRACKING_NUMBER}", $order_tracking_number, $datas["body"]);
        $datas["body"] = str_replace("{EMAIL_FOOTER}", $email_template->footer, $datas["body"]);
        $datas["body"] = str_replace("{WEBSITE_NAME}", app('general_setting')->site_title, $datas["body"]);
        $datas["body"] = str_replace("{SECRET_CODE}", $secret_code, $datas["body"]);
        $datas["body"] = str_replace("{GIFT_CARD_NAME}", $gift_card_name, $datas["body"]);
        return $datas;
    }

    public function phpMailDataGiftCard($to, $subject, $message)
    {
        try {
            $headers = "From:  ". config('mail.sender.name') ." <". config('mail.sender.email') .">"  . " \r\n";
            $headers .= "Reply-To: " . app('general_setting')->email . " \r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html; charset=UTF-8" . "\r\n";
            return mail($to, $subject, $message, $headers);
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return false;
        }
    }
    public function phpMailDigitalfile($to, $subject, $message)
    {
        try {
            $headers = "From:  ". config('mail.sender.name') ." <". config('mail.sender.email') .">"  . " \r\n";
            $headers .= "Reply-To: " . app('general_setting')->email . " \r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html; charset=UTF-8" . "\r\n";
            return mail($to, $subject, $message, $headers);
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return false;
        }
    }

    public function userActivationMailSend($type, $user)
    {
        $emailType = EmailTemplateType::where('type',$type)->first();

        if(!$emailType){
            return false;
        }

        $email_template = EmailTemplate::where('type_id', $emailType->id)->where('is_active', 1)->first();

        if (!$email_template) {
            return false;
        }

        $datas = $this->activationMailData($email_template, $user);

        // Delegamos la ejecución al método centralizado para enviar correos, que maneja SMTP, SendGrid y Sendmail de forma unificada
        return $this->executeMail($user->email, $email_template->subject, $datas);
    }


    public function newUserRegistradEmailSend($type, $user)
    {
        $admin = User::find(1);
        if(!$admin) {
            return false;
        }

        $emailType = EmailTemplateType::where('type',$type)->first();
        if(!$emailType) {
            return false;
        }

        $email_template = EmailTemplate::where('type_id', $emailType->id)->where('is_active', 1)->first();
        if (!$email_template) {
            return false;
        }

        $datas = $this->registrationMailData($email_template, $user);

        // Delegamos la ejecución al método centralizado para enviar correos, que maneja SMTP, SendGrid y Sendmail de forma unificada
        // Al usar return, devolvemos el estado del envío (true/false) que genera executeMail.
        return $this->executeMail($admin->email, $email_template->subject, $datas);
    }

    public function activationMailData($email_template,$user){
        $datas["email"] = app('general_setting')->email;
        $datas["title"] = $email_template->subject;
        $datas['from'] = getMailFromByProtocol();
        $datas["body"] = $email_template->value;
        $datas["body"] = str_replace("{USER_FIRST_NAME}", $user->name, $datas["body"]);
        $datas["body"] = str_replace("{APP_NAME}", config('app.name'), $datas["body"]);
        $datas["body"] = str_replace("{EMAIL_SIGNATURE}", app('general_setting')->mail_signature, $datas["body"]);
        $datas["body"] = str_replace("{VERIFICATION_LINK}", url('/login'), $datas["body"]);
        return $datas;
    }


    public function registrationMailData($email_template,$user)
    {
        $datas["email"] = app('general_setting')->email;
        $datas["title"] = $email_template->subject;
        $datas['from'] = getMailFromByProtocol();
        $datas["body"] = $email_template->value;
        $datas["body"] = str_replace("{CUSTOMER_NAME}", $user->name, $datas["body"]);
        $datas["body"] = str_replace("{CUSTOMER_EMAIL}", $user->email, $datas["body"]);
        $datas["body"] = str_replace("{APP_NAME}", config('app.name'), $datas["body"]);
        $datas["body"] = str_replace("{EMAIL_SIGNATURE}", app('general_setting')->mail_signature, $datas["body"]);
        return $datas;
    }

    private function executeMail($to, $subject, $datas)
    {
        try {
            $protocol = app('general_setting')->mail_protocol;

            if (in_array($protocol, ['smtp', 'sendgrid'])) {
                Mail::mailer($protocol)->to($to)->queue(new SendQueueMail($datas));
                return true;
            }

            if ($protocol == "sendmail") {
                $message = (string) view('emails.mail', $datas);
                if (config('queue.default') == 'sync') {
                    return $this->phpMailData($to, $subject, $message);
                }
                dispatch(new SendmailJob($to, $subject, $message));
                return true;
            }

            return false;
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return false;
        }
    }
}
