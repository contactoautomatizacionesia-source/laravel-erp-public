<?php

namespace Modules\OrderManage\Enums;

enum NotificationTypeEnum: string
{
    case OverStock     = 'overstock_alert';
    case LowStock      = 'low_stock_alert';
    case EmptyStock    = 'empty_stock_alert';
    case UpdateProduct = 'update_product';

    public function labelKey(): string
    {
        return match($this) {
            self::OverStock     => 'product.max_stock',
            self::LowStock      => 'product.min_stock',
            self::EmptyStock    => 'product.empty_stock',
            self::UpdateProduct => 'product.update_product',
            default             => 'common.not_found',
        };
    }

    public function label(): string
    {
        return __($this->labelKey());
    }

    /**
     * Todos los valores.
     *
     * @return self[]
     */
    public static function notificationTypes(): array
    {
        return self::cases();
    }
}
