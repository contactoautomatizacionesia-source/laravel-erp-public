<?php

namespace Modules\GeneralSetting\Repositories;
use Modules\GeneralSetting\Entities\NotificationSetting;
use Modules\GeneralSetting\Exceptions\DuplicateNotificationSettingException;
use Modules\OrderManage\Entities\CustomerNotification;

class NotificationSettingRepository
{
    public function all()
    {
        return NotificationSetting::all();
    }
    public function single($id)
    {
        return NotificationSetting::findOrFail($id);
    }

    public function update($request)
    {
        $notificationSetting = NotificationSetting::findOrFail($request->id);

        $notificationtype = "";
        if (!empty($request->type)) {
            $types = (array) $request->type;
            sort($types);
            $notificationtype = implode(',', $types) . ',';
        }

        $notificationSetting->fixLegacyTranslations();

        $currentLocale = app()->getLocale();

        $isMultiLang = isModuleActive('FrontendMultiLang');

        $eventVal    = $isMultiLang ? ($request->event[$currentLocale]     ?? null) : $request->event;
        $messageVal  = $isMultiLang ? ($request->message[$currentLocale]   ?? null) : $request->message;
        $adminMsgVal = $isMultiLang ? ($request->admin_msg[$currentLocale] ?? null) : $request->admin_msg;

        // Verificar duplicado solo en el idioma actual
        $duplicate = NotificationSetting::where('id', '!=', $request->id)
            ->where('type', $notificationtype)
            ->get()
            ->first(function ($record) use ($eventVal, $messageVal, $adminMsgVal, $currentLocale) {
                return $record->getTranslation('event',     $currentLocale) === $eventVal
                    && $record->getTranslation('message',   $currentLocale) === $messageVal
                    && $record->getTranslation('admin_msg', $currentLocale) === $adminMsgVal;
            });

        if ($duplicate) {
            throw new DuplicateNotificationSettingException();
        }

        $notificationSetting->type = $notificationtype;

        if($isMultiLang){
            $notificationSetting->event     = $request->event;
            $notificationSetting->message   = $request->message;
            $notificationSetting->admin_msg = $request->admin_msg;
        } else {
            $notificationSetting->setTranslation('event',     $currentLocale, $request->event);
            $notificationSetting->setTranslation('message',   $currentLocale, $request->message);
            $notificationSetting->setTranslation('admin_msg', $currentLocale, $request->admin_msg);
        }

        $notificationSetting->save();

        return true;
    }

    public function userNotifications($user_id)
    {
        return CustomerNotification::with('order')->where('customer_id',$user_id)->latest()->paginate(10);
    }
}
