<?php

namespace Modules\GeneralSetting\Repositories;

use Illuminate\Support\Facades\Cache;
use Modules\GeneralSetting\Entities\TimeZone;
use Modules\GeneralSetting\Entities\DateFormat;
use Modules\GeneralSetting\Entities\GeneralSetting;
use Modules\GeneralSetting\Entities\BusinessSetting;
use Modules\GeneralSetting\Entities\SmsGatewaySetting;

class GeneralSettingRepository
{
    public function all()
    {
        return GeneralSetting::first();
    }

    public function getVerificationNotificationAll()
    {
        return BusinessSetting::where('category_type', 'verification and notifications')->get();
    }

    public function getVendorConfigurationAll()
    {
        return BusinessSetting::where('category_type', 'vendor_configuration')->get();
    }

    public function getSmsGatewaysAll()
    {
        return BusinessSetting::where('category_type', 'sms_gateways')->get();
    }

    public function getLanguagesAll()
    {
        return BusinessSetting::where('category_type', 'sms_gateways')->get();
    }

    public function getDateFormatsAll()
    {
        return DateFormat::all();
    }

    public function getTimezonesAll()
    {
        return TimeZone::all();
    }

    public function getGeneralInfoDetails()
    {
        return GeneralSetting::first();
    }

    public function update(array $data)
    {

        return GeneralSetting::first()->update($data);
    }

    public function updateShopLink($shopLinkUrl)
    {
        return GeneralSetting::first()->update(['shop_link_banner'=>$shopLinkUrl]);
    }

    public function updateActivationStatus($data)
    {
        return BusinessSetting::where('id',$data['id'])->update([
            'status' => $data['status'],
        ]);
    }

    public function updateActivationSmsStatus($data)
    {


        if($data['action'] == 'other'){
            $this->handleOtherSmsGateway($data);
        } else {
            $this->handleStandardSmsGateways($data);
        }

        // Activar el gateway seleccionado
        BusinessSetting::where('id',$data['sms_gateway_id'])->update([
            'status' => 1,
        ]);

        return true;
    }

    /**
     * Maneja la configuración de gateways personalizados (tipo 'other').
     */
    private function handleOtherSmsGateway(array $data): void
    {
        $attributes = $this->extractSmsGatewayAttributes($data);

        // Buscamos el primero disponible, si no existe, preparamos uno nuevo
        $row_setting = SmsGatewaySetting::first() ?: new SmsGatewaySetting();

        // Llenamos los datos y guardamos (esto hace Update o Create automáticamente)
        $row_setting->fill($attributes);
        $row_setting->save();

        Cache::forget('sms_gateway_setting');
        
        $setting = SmsGatewaySetting::first();
        if ($setting) {
            $cacheData = collect($setting->toArray())
                ->except(['id', 'created_at', 'updated_at'])
                ->all();

            Cache::rememberForever('sms_gateway_setting', fn() => $cacheData);
        }
    }

    /**
     * Maneja la actualización de gateways estándar y archivos ENV.
     */
    private function handleStandardSmsGateways(array $data): void
    {
        // Desactivar todos los gateways
        foreach ($this->getSmsGatewaysAll() as $gateway) {
            $gateway->update(['status' => 0]);
        }

        // Actualizar variables de entorno
        if (isset($data['types']) && is_array($data['types'])) {
            foreach ($data['types'] as $type) {
                // Usamos el operador null coalescing para enviar un string vacío si no hay valor
                $value = $data[$type] ?? '';
                $this->overWriteEnvFile($type, $value);
            }
        }
    }

    /**
     * Centraliza el mapeo de campos para evitar duplicidad de código.
     */
    private function extractSmsGatewayAttributes(array $data): array
    {
        $fields = [
            'url', 'send_to_parameter_name', 'message_parameter_name', 'request_method'
        ];

        $attributes = [];
        foreach ($fields as $field) {
            $attributes[$field] = $data[$field] ?? null;
        }

        // Mapeo dinámico de los 10 parámetros (Key y Value)
        for ($i = 1; $i <= 10; $i++) {
            $attributes["parameter_{$i}_key"] = $data["parameter_{$i}_key"] ?? null;
            $attributes["parameter_{$i}_value"] = $data["parameter_{$i}_value"] ?? null;
        }

        return $attributes;
    }

    public function updateSmtpGatewayCredential($data)
    {
        $general_setting = $this->getGeneralInfoDetails();
        $general_setting->mail_protocol = $data['mail_gateway']; // Guarda 'sendgrid' en la DB
        $general_setting->save();

        foreach ($data['types'] as $type) {
            $this->overWriteEnvFile($type, $data[$type]);
        }

        if(@$data['QUEUE_CONNECTION']){
            $this->envUpdate('QUEUE_CONNECTION', $data['QUEUE_CONNECTION']);
        }

        return true;
    }

    public function overWriteEnvFile($type, $val)
    {
       $path = base_path('.env');
        if (!file_exists($path)) {
            return;
        }

        $val = '"' . trim($val) . '"';
        $content = file_get_contents($path);

        // Escapamos el tipo para que sea seguro en el patrón
        $escapedType = preg_quote($type, '/');
        
        // Regex para buscar la clave al inicio de una línea o después de un salto de línea
        // Soporta: CLAVE=valor, CLAVE="valor", CLAVE='valor'
        $pattern = "/^{$escapedType}=.*/m";
        $newLine = "{$type}={$val}";

        if (preg_match($pattern, $content)) {
            // Si existe, reemplazamos toda la línea
            $content = preg_replace($pattern, $newLine, $content);
        } else {
            // Si no existe, la añadimos al final
            $content .= PHP_EOL . $newLine;
        }

        file_put_contents($path, $content);
    }

    public static function envUpdate($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $content = file_get_contents($path);

            // Escapamos el valor si contiene espacios
            if (preg_match('/\s/', $value)) {
                $value = '"' . $value . '"';
            }

            // Buscamos la llave ignorando mayúsculas/minúsculas
            if (preg_match("/^{$key}=.*/m", $content)) {
                // Reemplaza la línea completa que empieza con la llave
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                // Si la llave no existe, la agrega al final asegurando un salto de línea
                $content .= PHP_EOL . "{$key}={$value}";
            }

            file_put_contents($path, $content);
        }
    }
    public function updateEmailFooterTemplate($data)
    {
        $general_setting = GeneralSetting::first()->update([
            'mail_footer' => $data['mail_footer']
        ]);
        return true;

    }

    public function HomepageSeoUpdate($data){
        return GeneralSetting::first()->update([
            'meta_site_title' => $data['meta_site_title'],
            'meta_tags' => $data['meta_tags'],
            'meta_description' => $data['meta_description']
        ]);
    }
}
