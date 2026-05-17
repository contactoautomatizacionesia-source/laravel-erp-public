@php
    use Modules\OrderManage\Enums\NotificationTypeEnum;
@endphp

@php
    $data = $alert->notification_data;

    $notificationType = NotificationTypeEnum::tryFrom($alert->notification_type);

    $notificationColor = match($notificationType) {
        NotificationTypeEnum::OverStock,
        NotificationTypeEnum::EmptyStock    => 'badge_2',
        NotificationTypeEnum::LowStock,
        NotificationTypeEnum::UpdateProduct => 'badge_5',
        default                             => 'badge_3',
    };

    $notificationLabel = $notificationType?->label() ?? __('common.not_found');

    $productName  = $data['product_name'] ?? 'N/A';

    $currentStock = $data['current_stock'] ?? 0;

    $limitStock = match($notificationType) {
        NotificationTypeEnum::OverStock => $data['max_stock'] ?? 0,
        NotificationTypeEnum::EmptyStock,
        NotificationTypeEnum::LowStock  => $data['min_stock'] ?? 0,
        default                         => null,
    };

    $stockClass = match($notificationType) {
        NotificationTypeEnum::OverStock => 'info',
        NotificationTypeEnum::EmptyStock,
        NotificationTypeEnum::LowStock  => 'danger',
        default                         => 'secondary',
    };

    $stockDetailsLabel = $notificationType == NotificationTypeEnum::OverStock
        ? $notificationType?->label()
        : NotificationTypeEnum::LowStock->label();

    $isUpdateProduct = $notificationType === NotificationTypeEnum::UpdateProduct;

    $changes = $isUpdateProduct ? ($data['changes'] ?? []) : [];

    $decodeValue = function ($value) {
        if (is_array($value)) {
            return implode(' / ', array_values($value));
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return implode(' / ', array_values($decoded));
            }
        }
        return $value;
    };
@endphp

<div class="inventory-alert-modal">
    <div class="modal-header">
        <h4 class="modal-title">
            @lang($isUpdateProduct ? 'product.update_alert' : 'product.stock_alert')
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i class="ti-close"></i>
        </button>
    </div>

    <div class="modal-body">
        <div class="form-card">
            
            <h3 class="">{{ $productName }}</h3>

            <div class="mb-3">
                <p class="meta-label">@lang('product.sku_variants')</p>
                @if(isset($data['skus']) && is_array($data['skus']) && count($data['skus']))
                    <div class="sku-badges">
                        @foreach($data['skus'] as $sku)
                            <span class="badge_5">{{ $sku }}</span>
                        @endforeach
                    </div>
                @else
                    <span class="text-muted">N/A</span>
                @endif
            </div>

            @if(!$isUpdateProduct)
                <div>
                    <p class="meta-label">@lang('product.stock_status')</p>
                    <div class="stock-metrics">
                        <div class="stock-box">
                            <small class="text-muted d-block mb-1">{{ __('product.current') }}</small>
                            <span class="stock-value text-{{ $stockClass }}">{{ $currentStock }}</span>
                        </div>
                        <div class="stock-box">
                            <small class="text-muted d-block mb-1">
                                {{ $stockDetailsLabel }}
                            </small>
                            <span class="stock-value">{{ $limitStock }}</span>
                        </div>
                    </div>
                </div>
            @else
                @if(isset($data['observation']))
                    <div class="mb-3">
                        <p class="meta-label">@lang('common.observations')</p>
                        <p class="text-muted mb-0">{{ $data['observation'] }}</p>
                    </div>
                @endif

                @if(count($changes))
                    <div>
                        <p class="meta-label">@lang('common.changes')</p>
                        <table class="changes-table w-100">
                            <thead>
                                <tr>
                                    <th>@lang('common.field')</th>
                                    <th class="text-right">@lang('common.old')</th>
                                    <th class="text-right">@lang('common.new')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($changes as $change)
                                    <tr>
                                        <td class="text-black">{{ $change['label'] }}</td>
                                        <td class="text-right text-muted">
                                            <s>{{ $decodeValue($change['old_value']) }}</s>
                                        </td>
                                        <td class="text-right text-success">
                                            {{ $decodeValue($change['new_value']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
