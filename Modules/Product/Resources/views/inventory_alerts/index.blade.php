@extends('backEnd.master')

@section('mainContent')
<link rel="stylesheet" href="{{ asset('public/css/inventory-alerts.css') }}">

@php
    use Modules\OrderManage\Enums\NotificationTypeEnum;
@endphp

<x-admin.section class="ign-customer-list">
    <div class="row">
        <div class="col-12">
            <div class="box_header common_table_header ">
                <div class="main-title">
                    <h3 class="mb-0">@lang('product.inventory_alerts')</h3>
                </div>
            </div>
            <x-admin.table-container>
                <table class="table table-hover display text-center Crm_table_active3 inventory-alerts-table">
                    <thead>
                        <tr>
                            <th class="min-w-170">@lang('common.date')</th>
                            <th>@lang('common.name')</th>
                            <th>@lang('product.sku_variants')</th>
                            <th>@lang('product.alert_type')</th>
                            <th>@lang('product.stock_details')</th>
                            <th>@lang('common.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alerts as $alert)
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

                                $stockDetailsLabel = $notificationType == NotificationTypeEnum::OverStock
                                    ? $notificationType?->label()
                                    : NotificationTypeEnum::LowStock->label();

                                $notificationLabel = $notificationType?->label() ?? __('common.not_found');
                            @endphp
                            <tr id="notification_row_{{ $alert->id }}" class="{{ $alert->read_status == 0 ? 'unread-row' : 'read-row' }}">
                                <td>
                                    <span class="cell-date">{{ dateConvert($alert->created_at) }}</span>
                                </td>
                                <td class="cell-name">
                                    <span class="product-name">{{ $data['product_name'] ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @if(isset($data['skus']) && is_array($data['skus']))
                                        <div class="sku-wrap">
                                            @foreach($data['skus'] as $sku)
                                                <span class="badge_5">{{ $sku }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">{{ $data['skus'] ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $notificationColor }}">
                                        {{ $notificationLabel }}

                                    </span>
                                </td>
                                <td>
                                    <div class="stock-card">
                                        <div class="stock-row">
                                            <span class="stock-label">@lang('product.current')</span>
                                            <span class="stock-value {{ $notificationType === NotificationTypeEnum::OverStock ? 'current-high' : 'current-low' }}">
                                                {{ $data['current_stock'] ?? 0 }}
                                            </span>
                                        </div>
                                        <div class="stock-row">
                                            <span class="stock-label">
                                                {{ $stockDetailsLabel }}
                                            </span>
                                            <span class="stock-value">
                                                {{ $notificationType === NotificationTypeEnum::OverStock ? ($data['max_stock'] ?? 0) : ($data['min_stock'] ?? 0) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <button
                                        id="btn_alert_{{ $alert->id }}"
                                        class="btn-toolkit btn-primary btn-icon btn-sm {{ $alert->read_status == 0 ? 'bg-warning' : '' }} "
                                        onclick="loadAlertModal({{ $alert->id }})"
                                    >
                                        <i class="ti-eye"></i> @lang('common.view')
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-admin.table-container>
        </div>
    </div>
</x-admin.section>

<div class="modal fade admin-query" id="alertDetailModal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" id="modal_content_loader">
            {{-- Aquí se cargará el HTML desde el back --}}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function loadAlertModal(id) {
        let url = "{{ route('product.inventory_alerts.show_modal', ':id') }}";
        url = url.replace(':id', id);
        $('#pre-loader').removeClass('d-none')

        $.ajax({
            url: url,
            type: "GET",
            success: function(html) {
                // Cargar el contenido en el modal y mostrarlo
                $('#modal_content_loader').html(html);
                $('#alertDetailModal').modal('show');

                // --- ACTUALIZACIÓN VISUAL INMEDIATA ---
                let row = $('#notification_row_' + id);
                let btn = $('#btn_alert_' + id);

                if(row.hasClass('unread-row')) {
                    // Cambiar el estilo de la fila
                    row.removeClass('unread-row').addClass('read-row');

                    // Cambiar el botón de Amarillo a Verde
                    btn.removeClass('bg-warning').addClass('bg-success');
                }
            },
            error: function() {
                toastr.error("{{ __('common.error_message') }}");
            },
            complete: function(){
                $('#pre-loader').addClass('d-none')
            }
        });
    }

    $(document).ready(function() {
        // Si vienes desde la notificación con ID en la URL
        @if(isset($selectedAlert))
            loadAlertModal({{ $selectedAlert->id }});
        @endif
    });
</script>
@endpush
