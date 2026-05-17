<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Marketing\Entities\ReferralCode;
use Modules\Marketing\Entities\ReferralUse;
use Modules\Wallet\Entities\WalletBalance;
use App\Traits\Notification;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\NotificationSetting;

class ReferralController extends Controller
{
    use Notification;
    public function __construct()
    {
        $this->middleware('maintenance_mode');
    }

    public function referral()
    {
        $myCode = ReferralCode::where('user_id', auth()->user()->id)->first();

        if (auth()->user()->role->type != 'customer') {
            if (isset($myCode)) {
                $referList = ReferralUse::where('referral_code', $myCode->referral_code)->latest()->get();
                return view('backEnd.pages.customer_data.referral', compact('myCode', 'referList'));
            } else {
                return view('backEnd.pages.customer_data.referral');
            }
        } else {
            if (isset($myCode)) {
                $referList = ReferralUse::where('referral_code', $myCode->referral_code)->paginate(8);
                return view(theme('pages.profile.referral'), compact('myCode', 'referList'));
            } else {
                return view(theme('pages.profile.referral'));
            }
        }
    }

    public function referralUsed(Request $request)
    {
        $referral = ReferralUse::find($request->referral_id);
        $referral->update([
            'is_use' => 1
        ]);
        WalletBalance::create(['user_id' => auth()->user()->id, 'amount' => $referral->discount_amount, 'type' => 'Referral', 'status' => 1]);
        return response()->json([
            'amount' => $referral->discount_amount,
            'status' => true
        ]);
    }

    public function shareReferralCode(Request $request)
    {
        $request->validate([
            'method' => 'required|in:copy,whatsapp,email',
            'email' => 'required_if:method,email|email'
        ]);

        $user = auth()->user();
        $myCode = ReferralCode::where('user_id', $user->id)->first();

        if (!$myCode) {
            return response()->json(['error' => __('defaultTheme.referral_code_not_found')], 404);
        }

        $referralUrl = url('/register') . '?referral_code=' . $myCode->referral_code;

        // Buscar la configuración de la notificación
        $notification = NotificationSetting::where('slug', 'referral-invitation')->first();
        if (!$notification) return response()->json(['error' => __('defaultTheme.notification_template_not_found')], 404);

        // Definir variable a reemplazar
        $search = ['{CUSTOMER_NAME}'];
        $replace = [$user->full_name];

        // Procesamos el mensaje de la notificación
        $messageTranslations = $notification->getTranslations('message');
        $lang = $user->lang_code ?? 'es'; // Usar el idioma del usuario o default

        // El texto final para compartir (limpio de etiquetas HTML si las tuviera)
        $finalMessage = strip_tags(str_replace($search, $replace, $messageTranslations[$lang] ?? current($messageTranslations)));

        if ($request->method == 'email') {
            // Configurar datos de envío
            $type = EmailTemplateType::where('type', 'referral_invitation_template')->first();
            if (!$type) return response()->json(['error' => __('defaultTheme.email_template_type_not_found')], 404);

            $this->typeId = $type->id;
            $this->notificationUrl = $referralUrl;

           // Delegamos manejo al Trait
            $sent = $this->sendReferralInvitation($search, $replace, $notification, $request->email);

            if (!$sent) {
                return response()->json(['error' => __('defaultTheme.referral_invitation_failed_to_send')], 404);
            }

            return response()->json(['status' => true, 'message' => __('general_settings.Mail has been sent Successfully')]);
        }

        // Para WhatsApp y Copy, devolvemos el mensaje procesado
        return response()->json([
            'status' => true,
            'url' => $referralUrl,
            'message' => $finalMessage
        ]);
    }
}
