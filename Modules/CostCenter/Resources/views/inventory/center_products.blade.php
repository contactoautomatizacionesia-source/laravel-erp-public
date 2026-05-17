@extends('backEnd.master')
@section('styles')
    <link rel="stylesheet" href="{{asset(asset_path('modules/product/css/product_index.css'))}}">
@endsection
@section('mainContent')
<x-admin.section class="ign-customer-list">
    <div class="row">
        <div class="col-md-12 mb-10">

            <div class="box_header_right">
                <div class="pos_tab_btn justify-content-end">
                    <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                        @if(permissionCheck('inventory_count.settings.store'))
                        <li class="nav-item">
                            <a class="nav-link action" href="{{ route('cost_centers.inventory.manage', ['center_id' => $centerId]) }}" ><i class="fas fa-exchange-alt"></i> {{ __('costcenter::inventory.transfer') }}</a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="row justify-content-between align-items-start mx-0">
                <div class="main-title d-flex justify-content-start align-items-center">
                    <x-backEnd.back-button :text="false" />
                    <h3 class="mb-0">{{ __('costcenter::inventory.inventory')}}</h3>
                </div>
                <div class="col-md-4 form-card mt-md-0 mt-2 bg-primary-50">
                    <div class="info-item d-flex align-items-start">
                        <div class="info-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="">
                            <span class="primary_input_label mb-0">
                                {{ __('cost_center.cost_center') }}
                            </span>
                            <p class="mb-0 font-weight-bold fs-20">
                                {{ $center->name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <x-admin.table-container>
                <table class="table" id="centerProductTable">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">{{ __('common.sl') }}</th>
                            <th scope="col" class="text-center">{{ __('common.image') }}</th>
                            <th scope="col" class="text-center">{{ __('common.name') }}</th>
                            <th scope="col" class="text-center">{{ __('product.brand') }}</th>
                            <th scope="col" class="text-center">{{ __('product.product_type') }}</th>
                            <th scope="col" class="text-center">{{ __('common.quantity') }}</th>
                            <th scope="col" class="text-center">{{ __('product.min_stock') }}</th>
                            <th scope="col" class="text-center">{{ __('product.max_stock') }}</th>
                            <th scope="col" class="text-center">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </x-admin.table-container>
        </div>
    </div>
</x-admin.section>

<div class="center_product_detail_view_div"></div>

<div class="modal fade" id="stockAlertModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('costcenter::inventory.stock_alerts') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-card">
                    <h3 id="stockAlertProductName">-</h3>
                    <input type="hidden" id="stockAlertProductId">
                    <input type="hidden" id="stockAlertCenterId">
                    <div class="row">
                        <div class="col-lg-6 form-group">
                            <label for="stockAlertMinInput" class="primary_input_label">{{ __('product.min_stock') }}</label>
                            <input type="number" id="stockAlertMinInput" class="primary_input_field" min="0" step="1" value="0">
                        </div>
                        <div class="col-lg-6 form-group">
                            <label for="stockAlertMaxInput" class="primary_input_label">{{ __('product.max_stock') }}</label>
                            <input type="number" id="stockAlertMaxInput" class="primary_input_field" min="0" step="1" value="0">
                        </div>
                        <div class="col-12">
                            <p class="badge_5">{{__('common.alert_stock_message')}}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn-toolkit btn-primary" id="saveStockAlert">{{ __('common.save') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let centerId = "{{ $centerId }}";
        let ajaxUrl = "{{ route('cost_centers.inventory.center-skus', ['centerId' => '__ID__']) }}".replace('__ID__', centerId);

        const inventoryColumns = [
            { data: 'DT_RowIndex', className: 'text-center', render: d => numbertrans(d), orderable: false, searchable: false },
            {
                data: 'image',
                className: 'text-center',
                render: data => '<div class="product_thumb_div"><img class="productImg" src="' + data + '" style="height:50px;width:50px;object-fit:cover;border-radius:4px"></div>',
                orderable: false, searchable: false
            },
            { data: 'product_name', className: 'text-center', defaultContent: '' },
            { data: 'brand', className: 'text-center', defaultContent: '' },
            { data: 'product_type', className: 'text-center', defaultContent: '' },
            {
                data: 'qty',
                className: 'text-center',
                render: function(d, type, row) {
                    let label = parseInt(d);
                    if (row.unit_type) label += ' (' + row.unit_type + ')';
                    return label;
                }
            },
            { data: 'min_stock', className: 'text-center', render: d => numbertrans(d ?? 0), searchable: false },
            { data: 'max_stock', className: 'text-center', render: d => numbertrans(d ?? 0), searchable: false },
            { data: 'actions', className: 'text-center', render: data => data, orderable: false, searchable: false },
        ];

        initGlobalDataTable('#centerProductTable', ajaxUrl, inventoryColumns);

        $(document).on('click', '.center_stock_alert', function() {
            let table = $('#centerProductTable').DataTable();
            let rowData = table.row($(this).closest('tr')).data();
            $('#stockAlertProductId').val($(this).data('product-id'));
            $('#stockAlertCenterId').val($(this).data('center-id'));
            $('#stockAlertMinInput').val($(this).data('min'));
            $('#stockAlertMaxInput').val($(this).data('max'));
            $('#stockAlertProductName').text(rowData ? rowData.product_name : '');
            $('#stockAlertModal').modal('show');
        });

        $('#saveStockAlert').on('click', function() {
            $('#pre-loader').removeClass('d-none');
            let $btn = $(this);
            $btn.prop('disabled', true);
            $.post('{{ route('cost_centers.inventory.product-alert') }}', {
                _token: '{{ csrf_token() }}',
                product_id: $('#stockAlertProductId').val(),
                center_id:  $('#stockAlertCenterId').val(),
                min_stock:  $('#stockAlertMinInput').val(),
                max_stock:  $('#stockAlertMaxInput').val(),
            }, function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#stockAlertModal').modal('hide');
                    $('#centerProductTable').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(res.message);
                }
                $('#pre-loader').addClass('d-none');
            }).fail(function() {
                $('#pre-loader').addClass('d-none');
                toastr.error('{{ __('common.error_message') }}');
            }).always(function() {
                $btn.prop('disabled', false);
            });
        });

        $(document).on('click', '.center_product_detail', function() {
            let productId = $(this).data('product-id');
            let centerId  = $(this).data('center-id');
            $('#pre-loader').removeClass('d-none');
            $.post('{{ route('cost_centers.inventory.product-detail') }}', {
                _token: '{{ csrf_token() }}',
                product_id: productId,
                center_id: centerId
            }, function(data) {
                $('.center_product_detail_view_div').html(data);
                $('#centerProductDetail').modal('show');
                $('#pre-loader').addClass('d-none');
            });
        });
    });
</script>
@endpush
