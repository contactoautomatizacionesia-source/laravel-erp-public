@extends('backEnd.master')

@section('styles')
    <style>
        .cc-center-highlight{
            color: var(--base_color);
            font-weight: 700;
        }
    </style>
@endsection

@section('mainContent')
<link rel="stylesheet" href="{{ asset('public/css/const-center.css') }}">
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">
        <div class="row">
            <div class="col-12 mb-10">
                <div class="box_header_right">
                    <div class=" pos_tab_btn justify-content-end">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                            <li class="nav-item">
                                <a href="{{ route('cost_centers.inventory.manage') }}" class="nav-link action">
                                    <i class="ti-arrows-vertical"></i> {{ __('costcenter::inventory.transfer') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="row justify-content-between align-items-start mx-0">
                    <div class="main-title d-flex justify-content-start align-items-center">
                        <x-backEnd.back-button :text="false" />
                        <h3 class="mb-0">{{ __('costcenter::inventory.transfers') }}</h3>
                    </div>
                    <div class="col-md-4 form-card mt-md-0 mt-2 bg-primary-50">
                        <div class="info-item d-flex align-items-start">
                            <div class="info-icon">
                                <i class="fas {{ $center->id ? 'fa-building' : 'fa-list' }}"></i>
                            </div>
                            <div class="">
                                <span class="primary_input_label mb-0">
                                    {{ $center->id ? __('cost_center.cost_center') : __('costcenter::inventory.general') }}
                                </span>
                                <p class="mb-0 font-weight-bold fs-20">
                                    {{ $center->name }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="col-lg-12 mt-3">
                <div class="QA_section QA_list_admin_dashboard_inventory">
                    <div class="QA_table">
                        <table class="table" id="transactionsTable">
                            <thead>
                                <tr>
                                    <th class="text-center">{{ __('costcenter::inventory.date') }}</th>
                                    <th class="text-center">{{ __('costcenter::inventory.reference') }}</th>
                                    <th class="text-center">{{ __('common.status') }}</th> 
                                    <th class="text-center">{{ __('costcenter::inventory.transfer_type') }}</th>
                                    <th class="text-center">{{ __('costcenter::inventory.movement_type') }}</th>
                                    <th class="text-center">{{ __('costcenter::inventory.origin_location') }}</th>
                                    <th class="text-center">{{ __('costcenter::inventory.destination_location') }}</th>
                                    <th class="text-center">{{ __('costcenter::inventory.dispatched_by') }}</th>
                                    <th class="text-center">{{ __('costcenter::inventory.received_by') }}</th>
                                    <th class="text-center">{{ __('costcenter::inventory.items') }}</th>
                                    <th class="text-center">{{ __('common.action') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="transactionModalContainer"></div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let centerId = @json($center->id);
        let ajaxUrl = centerId
            ? "{{ route('cost_centers.inventory.transactions-data', ':id') }}".replace(':id', centerId)
            : "{{ route('cost_centers.inventory.all-transactions-data') }}";
        
        let columns = [
            { data: 'date', name: 'created_at', className: 'text-center' },
            { data: 'reference_code', name: 'reference_code', searchable: true, className: 'text-center' },
            // NUEVO OBJETO PARA LA COLUMNA DE ESTADO
            { data: 'status', name: 'status', searchable: false, orderable: false, className: 'text-center' },
            { data: 'transfer_type', name: 'transfer_type', searchable: false, orderable: false, className: 'text-center' },
            { data: 'type', name: 'type', searchable: false, className: 'text-center' },
            { data: 'origin', name: 'origin', searchable: false, className: 'text-center' },
            { data: 'destination', name: 'destination', searchable: false, className: 'text-center' },
            { data: 'dispatched_by', name: 'dispatchedBy.first_name', orderable: false, className: 'text-center' },
            { data: 'received_by', name: 'receivedBy.first_name', orderable: false, className: 'text-center' },
            {
                data: null,
                searchable: false,
                orderable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `<strong class="mr-1">${row.total_products}</strong> {{ __('costcenter::inventory.items') }}<br>
                            <small class="ml-1 text-muted">(${Math.trunc(row.total_qty)} {{ strtolower(__('costcenter::inventory.units')) }})</small>`;
                }
            },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ];

        initGlobalDataTable('#transactionsTable', ajaxUrl, columns, {
            stateSave: false,
            ajax: {
                url: ajaxUrl,
                data: function (d) {
                    d.center_id = centerId;
                }
            }
        });

        $(document).on('click', '.view_transaction', function() {
            let id = $(this).data('id');
            let url = "{{ route('cost_centers.inventory.transactions.show', ':id') }}".replace(':id', centerId)
            $('#pre-loader').removeClass('d-none');
            
            $.get(url, function(html) {
                $('#transactionModalContainer').html(html);
                $('#transferDetailModal').modal('show');
                $('#pre-loader').addClass('d-none');
            });
        });
    });
</script>
@endpush