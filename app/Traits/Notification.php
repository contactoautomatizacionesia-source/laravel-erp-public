<?php
namespace App\Traits;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\PushNotification;
use Illuminate\Support\Facades\DB;
use Modules\RolePermission\Entities\Permission;
use Modules\GeneralSetting\Entities\SmsTemplate;
use Modules\OrderManage\Entities\CustomerNotification;
use Modules\GeneralSetting\Entities\NotificationSetting;
use Modules\GeneralSetting\Entities\UserNotificationSetting;


trait Notification
{
    use SendMail, SendSMS;
    public $notificationUrl = "#";
    public $adminNotificationUrl = '#';
    public $routeCheck = '';
    public $typeId;
    public $relatable_type = null;
    public $relatable_id = null;
    public $order_on_notification = null;

    public function notificationSend($event, $userId,$delivery = 0, $notificationData = null, $notificationType = null)
    {
        try {
            // Si $event ya es un objeto (instancia de NotificationSetting), lo usamos directamente.
            // De lo contrario, realizamos la consulta original a la base de datos.
            if ($event instanceof NotificationSetting) {
                $notificationSetting = $event;
            } else {
                //getting notification setting according to event or delivery_process_id
                $notificationSetting = NotificationSetting::where('id', $event)->orWhere('delivery_process_id', $delivery)->first();
            }
            
            // if the notification exist take user notification setting for that notification setting
            if ($notificationSetting) {
                $user = User::find($userId);
                if($user){

                    // for registration
                    if ($notificationSetting->user_access_status == 0 && $notificationSetting->seller_access_status == 0 && $notificationSetting->admin_access_status == 1 && $notificationSetting->staff_access_status == 1) {
                        $this->checkNotificationSettingAndSend($user, $notificationSetting, $notificationData, $notificationType);
                    }
                }
                $userNotificationSetting = UserNotificationSetting::where('notification_setting_id', $notificationSetting->id)->where('user_id', $userId)->first();

                if ($userNotificationSetting) {
                    if($user && $user->role_id != 1){
                        $this->checkUserNotificationSettingAndSend($user, $notificationSetting, $userNotificationSetting);
                    }
                }
                if($user && $event == 'Sub Seller Created'){
                    $this->sendNotificationForSeller($notificationSetting, $user->sub_seller->user_id);
                }else{
                    // send notification to super admin
                    $this->sendNotificationToSuperAdmin($notificationSetting);
                    //send notificatuion permission wise
                    $this->sendNotificationToPermissionWise($notificationSetting);
                }
                if($event == 'New Order' && !$user){
                    $guestData = [
                        'email' => $this->order_on_notification->customer_email,
                        'first_name' => '',
                    ];
                    $this->sendNotificationMail((object) $guestData,$notificationSetting);
                }
            }
        } catch (\Exception $th) {

        }
    }

    public function sendNotificationMail($user, $notificationSetting)
    {
        $this->sendNotificationByMail($this->typeId, $user, $notificationSetting, $this->relatable_id, $this->relatable_type, @$this->order_on_notification->order_number);
    }

    public function createSystemNotification($user, $notificationSetting, $column = 'message', $notificationData = null, $notificationType = null)
    {
        $langs = getLanguageList();
        $adminNot = new CustomerNotification();

        if ($notificationSetting instanceof NotificationSetting) {
            // Obtenemos la traducción de fallback una sola vez fuera del bucle para optimizar el rendimiento.
            $fallbackTranslation = $notificationSetting->getTranslation($column, config('app.fallback_locale'));
            
            foreach($langs as $lang) {
                // Intentamos obtener la traducción específica sin activar el fallback automático de Spatie.
                $translation = $notificationSetting->getTranslationWithoutFallback($column, $lang->code);
                
                // Asignamos la traducción encontrada o, en su defecto, la de respaldo.
                $adminNot->setTranslation('title', $lang->code, $translation ?? $fallbackTranslation);
            }
        } else {
            // Fallback simplificado en caso de recibir un string plano o dato no estructurado.
            foreach($langs as $lang) {
                $adminNot->setTranslation('title', $lang->code, $notificationSetting);
            }
        }

        $adminNot->customer_id = $user->id;
        $adminNot->url = $this->notificationUrl;

        if ($notificationData && $notificationType) {
            $adminNot->notification_data = $notificationData; // Laravel lo convierte a JSON automáticamente por el cast que se agregó al modelo
            $adminNot->notification_type = $notificationType; // Almacena el slug: overstock_alert o low_stock_alert
        }
        $adminNot->save();

        // Ahora que tenemos $adminNot->id, actualizamos la URL si es de inventario
        if ($notificationData && $notificationType) {
            // Concatenamos el ID al final de la ruta para que el controlador lo reciba
           $adminNot->update(['url' => $this->notificationUrl . '/' . $adminNot->id]);
        }
    }
    public function createPushNotification($user, $notificationSetting)
    {
        $userNotificationSetting = UserNotificationSetting::where('notification_setting_id', $notificationSetting->id)->where('user_id', $user->id)->first();
        PushNotification::create([
            'title' => $notificationSetting->event,
            'user_id' => $user->id,
            'body' => $notificationSetting->message,
            'type' => $notificationSetting->event,
            'push_send_type' => 0,
            'user_notification_setting_id' => $userNotificationSetting->id,
        ]);
    }

    public function checkNotificationSettingAndSend($user, $notificationSetting, $notificationData = null, $notificationType = null)
    {

        if (Str::contains($notificationSetting->type, 'sms')) {
            if($user->phone != null){
                if ($notificationSetting->slug = 'new-order') {
                    $smsTemplete = SmsTemplate::where('type_id', 18)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-confirmation') {
                    $smsTemplete = SmsTemplate::where('type_id', 3)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-pending') {
                    $smsTemplete = SmsTemplate::where('type_id', 2)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-processing') {
                    $smsTemplete = SmsTemplate::where('type_id', 39)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-shipped') {
                    $smsTemplete = SmsTemplate::where('type_id', 40)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-recieved') {
                    $smsTemplete = SmsTemplate::where('type_id', 41)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-delivered') {
                    $smsTemplete = SmsTemplate::where('type_id', 6)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-declined') {
                    $smsTemplete = SmsTemplate::where('type_id', 4)->where('is_active', 1)->first();
                }

                $msg = $smsTemplete->value;
                $this->sendSMS($user->phone, $msg,$user->first_name,'','','');
            }
        }
        if (Str::contains($notificationSetting->type, 'email')) {

            if($user->email != null){
                $this->sendNotificationMail($user, $notificationSetting);
            }
        }
        if (Str::contains($notificationSetting->type, 'mobile')) {
            // Mobile push notification
            if ($user->role->type== "customer") {
                $this->createPushNotification($user, $notificationSetting);
            }
        }
        if (Str::contains($notificationSetting->type, 'system')) {
            // $noti = DB::table('notification_settings')->where('id',$notificationSetting->id)->first();
            $column = ($user->role_id == 1) ? 'admin_msg' : 'message';
            $this->createSystemNotification($user, $notificationSetting, $column, $notificationData, $notificationType);

        }
    }

    public function checkUserNotificationSettingAndSend($user, $notificationSetting, $userNotificationSetting)
    {
        if (Str::contains($notificationSetting->type, 'sms') && Str::contains($userNotificationSetting->type, 'sms')) {
            if($user->phone != null){
                if ($notificationSetting->slug = 'new-order') {
                    $smsTemplete = SmsTemplate::where('type_id', 18)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-confirmation') {
                    $smsTemplete = SmsTemplate::where('type_id', 3)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-pending') {
                    $smsTemplete = SmsTemplate::where('type_id', 2)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-processing') {
                    $smsTemplete = SmsTemplate::where('type_id', 39)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-shipped') {
                    $smsTemplete = SmsTemplate::where('type_id', 40)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-recieved') {
                    $smsTemplete = SmsTemplate::where('type_id', 41)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-delivered') {
                    $smsTemplete = SmsTemplate::where('type_id', 6)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-declined') {
                    $smsTemplete = SmsTemplate::where('type_id', 4)->where('is_active', 1)->first();
                }
                $msg = $smsTemplete->value;
                $this->sendSMS($user->phone, $msg,$user->first_name,'','','');
            }
        }

        if (Str::contains($notificationSetting->type, 'email') && Str::contains($userNotificationSetting->type, 'email')) {
            if($user->email != null){

                $this->sendNotificationMail($user, $notificationSetting);
            }
        }

        if (Str::contains($notificationSetting->type, 'mobile') && Str::contains($userNotificationSetting->type, 'mobile')) {
            // Mobile push notification
            if ($user->role->type== "customer") {
                $this->createPushNotification($user, $notificationSetting);
            }
        }

        if (Str::contains($notificationSetting->type, 'system') && Str::contains($userNotificationSetting->type, 'system')) {
            // $noti = DB::table('notification_settings')->where('id',$notificationSetting->id)->first();
            $column = ($user->role_id == 1) ? 'admin_msg' : 'message';
            $this->createSystemNotification($user, $notificationSetting, $column);
        }
    }

    public function isEnableEmail()
    {
        $email = app('business_settings')->where('type', 'email_verification')->where('status', 1)->first();
        if ($email)
            return true;
        return false;
    }

    public function isEnableSMS()
    {
        $email = app('business_settings')->where('type', 'sms_verification')->where('status', 1)->first();
        if ($email)
            return true;
        return false;
    }

    public function isEnableSystem()
    {
        $email = app('business_settings')->where('type', 'system_notification')->where('status', 1)->first();
        if ($email)
            return true;
        return false;
    }


    public function sendNotification($type, $email, $subject, $content, $number, $message)
    {

        if ($this->isEnableEmail()) {
            $this->sendMail($email, $subject, $content);
        }
        if ($this->isEnableSMS()) {
            $this->sendSMS($number, $message);
        }
        if ($this->isEnableSystem()) {
            $class = get_class($type);
            $explode = explode('\\', $class);
            if (end($explode) == 'Sale') {
                $url = 'sale.show';
            }
            if (end($explode) == 'PurchaseOrder') {
                $url = 'purchase_order.show';
            }
            if (end($explode) == 'Voucher') {
                $url = 'vouchers.show';
            }
            if (end($explode) == 'Payroll') {
                $url = 'staffs.show';
            }
            if (end($explode) == 'Staff') {
                $url = 'staffs.show';
            }
            if (end($explode) == 'ApplyLeave') {
                $url = 'staffs.show';
            }
            \Modules\Notification\Entities\Notification::create([
                'type' => end($explode),
                'data' => $message,
                'url' => $url,
                'notifiable_id' => $type->id,
                'notifiable_type' => $class,
            ]);
        }
        return true;
    }

    public function sendNotificationToSuperAdmin($notificationSetting){
        $superadmins = User::where('is_active', 1)->whereHas('role', function($query){
            return $query->where('type', 'superadmin');
        })->get();
        foreach($superadmins as $admin){
            $userNotificationSetting = UserNotificationSetting::where('notification_setting_id', $notificationSetting->id)->where('user_id', $admin->id)->first();
            $this->notificationUrl = $this->adminNotificationUrl;
            if($admin && $notificationSetting && $userNotificationSetting){
                $this->checkUserNotificationSettingAndSendForSuperAdmin($notificationSetting, $userNotificationSetting, $admin);
            }
        }
    }
    public function checkUserNotificationSettingAndSendForSuperAdmin($notificationSetting, $userNotificationSetting, $user){

        if (Str::contains($notificationSetting->type, 'sms') && Str::contains($userNotificationSetting->type, 'sms')) {
            if($user->phone != null){
                if ($notificationSetting->slug = 'new-order') {
                    $smsTemplete = SmsTemplate::where('type_id', 18)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-confirmation') {
                    $smsTemplete = SmsTemplate::where('type_id', 3)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-pending') {
                    $smsTemplete = SmsTemplate::where('type_id', 2)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-processing') {
                    $smsTemplete = SmsTemplate::where('type_id', 39)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-shipped') {
                    $smsTemplete = SmsTemplate::where('type_id', 40)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-recieved') {
                    $smsTemplete = SmsTemplate::where('type_id', 41)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-delivered') {
                    $smsTemplete = SmsTemplate::where('type_id', 6)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-declined') {
                    $smsTemplete = SmsTemplate::where('type_id', 4)->where('is_active', 1)->first();
                }
                $msg = $smsTemplete->value;
                $this->sendSMS($user->phone, $msg,$user->first_name,'','','');
            }
        }
        if (Str::contains($notificationSetting->type, 'email') && Str::contains($userNotificationSetting->type, 'email')) {
            if($user->email != null){
                $this->sendNotificationMail($user, $notificationSetting);
            }
        }
        if (Str::contains($notificationSetting->type, 'mobile') && Str::contains($userNotificationSetting->type, 'mobile')) {
            // Mobile push notification
            // $this->createPushNotification($user, $notificationSetting);
        }
        if (Str::contains($notificationSetting->type, 'system') && Str::contains($userNotificationSetting->type, 'system')) {
            // $noti = DB::table('notification_settings')->where('id',$notificationSetting->id)->first();
            $column = ($user->role_id == 1) ? 'admin_msg' : 'message';
            $this->createSystemNotification($user, $notificationSetting, $column);
        }
    }

    // for send Notification to permission wise
    public function sendNotificationToPermissionWise($notificationSetting){
        $users = User::where('is_active', 1)->whereHas('role', function($query){
            return $query->where('type', 'admin')->orWhere('type', 'staff');
        })->get();

        foreach($users as $user){
            $has_permission = false;
            $permission = Permission::where('route', $this->routeCheck)->first();
            if($permission){
                $access = DB::table('role_permission')->where('role_id', $user->role_id)->where('permission_id', $permission->id)->first();
                if($access){
                    $has_permission = true;
                }
            }
            if($has_permission){
                $userNotificationSetting = UserNotificationSetting::where('notification_setting_id', $notificationSetting->id)->where('user_id', $user->id)->first();
                $this->notificationUrl = $this->adminNotificationUrl;
                if($user && $notificationSetting && $userNotificationSetting){
                    $this->checkUserNotificationSettingAndSendForPermissionWise($notificationSetting, $userNotificationSetting, $user);
                }
            }
        }
    }

    public function checkUserNotificationSettingAndSendForPermissionWise($notificationSetting, $userNotificationSetting, $user){

        if (Str::contains($notificationSetting->type, 'sms') && Str::contains($userNotificationSetting->type, 'sms')) {
            if($user->phone != null){
                if ($notificationSetting->slug = 'new-order') {
                    $smsTemplete = SmsTemplate::where('type_id', 18)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-confirmation') {
                    $smsTemplete = SmsTemplate::where('type_id', 3)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-pending') {
                    $smsTemplete = SmsTemplate::where('type_id', 2)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-processing') {
                    $smsTemplete = SmsTemplate::where('type_id', 39)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-shipped') {
                    $smsTemplete = SmsTemplate::where('type_id', 40)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-recieved') {
                    $smsTemplete = SmsTemplate::where('type_id', 41)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-delivered') {
                    $smsTemplete = SmsTemplate::where('type_id', 6)->where('is_active', 1)->first();
                }
                elseif ($notificationSetting->slug = 'order-declined') {
                    $smsTemplete = SmsTemplate::where('type_id', 4)->where('is_active', 1)->first();
                }
                $msg = $smsTemplete->value;
                $this->sendSMS($user->phone, $msg,$user->first_name,'','','');
            }
        }
        if (Str::contains($notificationSetting->type, 'email') && Str::contains($userNotificationSetting->type, 'email')) {
            if($user->email != null){
                $this->sendNotificationMail($user, $notificationSetting);
            }
        }
        if (Str::contains($notificationSetting->type, 'mobile') && Str::contains($userNotificationSetting->type, 'mobile')) {
            // Mobile push notification
            if ($user->role->type== "customer") {
                $this->createPushNotification($user, $notificationSetting);
            }
        }
        if (Str::contains($notificationSetting->type, 'system') && Str::contains($userNotificationSetting->type, 'system')) {
            // $noti = DB::table('notification_settings')->where('id',$notificationSetting->id)->first();
            $column = ($user->role_id == 1) ? 'admin_msg' : 'message';
            $this->createSystemNotification($user, $notificationSetting, $column);
        }
    }

    // for send Notification to seller
    public function sendNotificationForSeller($notificationSetting, $seller_id){
        $user = User::find($seller_id);

        if($user){
            $has_permission = false;
            $permission = Permission::where('route', $this->routeCheck)->first();
            if($permission){
                $access = DB::table('role_permission')->where('role_id', $user->role_id)->where('permission_id', $permission->id)->first();
                if($access){
                    $has_permission = true;
                }
            }
            if($has_permission){
                $userNotificationSetting = UserNotificationSetting::where('notification_setting_id', $notificationSetting->id)->where('user_id', $user->id)->first();
                $this->notificationUrl = $this->adminNotificationUrl;
                if($user && $notificationSetting && $userNotificationSetting){
                    $this->checkUserNotificationSettingAndSendForPermissionWise($notificationSetting, $userNotificationSetting, $user);
                }
            }
        }
    }

    // En tu Trait Notification
    public function sendReferralInvitation($search, $replace, $notification, $targetEmail)
    {
        // Extraer nombre del destinatario del email
        $emailParts = explode('@', $targetEmail);
        $nameFromEmail = ucfirst($emailParts[0]);

        // Procesar traducciones de mensajes y admin_msg
        $messageTranslations = $notification->getTranslations('message');
        $adminMsgTranslations = $notification->getTranslations('admin_msg');

        foreach ($messageTranslations as $locale => $value) {
            $messageTranslations[$locale] = str_replace($search, $replace, $value);
        }
        foreach ($adminMsgTranslations as $locale => $value) {
            $adminMsgTranslations[$locale] = str_replace($search, $replace, $value);
        }

        $notification->setTranslations('message', $messageTranslations);
        $notification->setTranslations('admin_msg', $adminMsgTranslations);

        $receiver = (object)[
            'first_name' => $nameFromEmail,
            'email' => $targetEmail
        ];

        // Ejecutar el envío de correo
        return $this->sendNotificationByMail($this->typeId, $receiver, $notification, null, null, null);
    }
}
