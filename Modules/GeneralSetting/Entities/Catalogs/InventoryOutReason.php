<?php

namespace Modules\GeneralSetting\Entities\Catalogs;

class InventoryOutReason extends BaseCatalog
{
    public static function getCatalogType(): string
    {
        return 'inventory_out_reason';
    }

    public static function getUsageReferences(): array
    {
        return [
            'inventory_exit_requests' => 'exit_reason_id',
        ];
    }

    /**
     * Indica si este tipo de salida es del sistema y no puede editarse ni eliminarse.
     */
    public function isSystem(): bool
    {
        return (bool) ($this->meta['is_system'] ?? false);
    }

    /**
     * Código del tipo reservado para ventas del carrito.
     */
    public const CART_SALE_CODE = 'cart_sale';
}
