<?php

return [
    'name' => 'Customer',

    /*
    |--------------------------------------------------------------------------
    | ProtecData — Firma Electrónica de Contratos
    |--------------------------------------------------------------------------
    |
    | enabled: Interruptor maestro. Poner en false para desactivar
    |          completamente el consumo del servicio ProtecData.
    |          Útil en entornos de QA/staging donde no se quiere incurrir
    |          en costos reales por contrato firmado.
    |
    |          false → registerCustomer() omite el proceso de firma
    |                  silenciosamente. El callback retorna 200 sin procesar.
    |          true  → flujo completo activo.
    |
    */
    'protecdata' => [
        // enabled NO viene aquí — se lee desde general_settings.protecdata_enabled (BD)
        // y se controla desde el panel admin en Configuración → Usuarios.
        'url'          => env('PROTECDATA_URL', ''),
        'username'     => env('PROTECDATA_USERNAME', ''),
        'password'     => env('PROTECDATA_PASSWORD', ''),
        'callback_url' => env('PROTECDATA_CALLBACK_URL', ''),
        'company_name' => env('PROTECDATA_COMPANY_NAME', ''),
        'notification' => env('PROTECDATA_NOTIFICATION', '4'), // 1=Email 2=SMS 3=WA 4=SMS+WA
    ],
];
