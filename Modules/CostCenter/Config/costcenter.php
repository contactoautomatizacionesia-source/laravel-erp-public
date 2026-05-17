<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Bodega Principal (Main Warehouse)
    |--------------------------------------------------------------------------
    |
    | Define la ubicación/referencia de la bodega principal del sistema.
    | Por defecto usa seller inhouse (user_id = 1), pero es configurable.
    |
    */

    'main_warehouse' => [
        // Identificador único del usuario seller que representa Bodega Principal
        'seller_id' => env('MAIN_WAREHOUSE_SELLER_ID', 1),
    ]
];
