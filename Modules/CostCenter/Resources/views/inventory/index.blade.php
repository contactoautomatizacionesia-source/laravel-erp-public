@extends('backEnd.master')

@section('mainContent')
    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid white_box_30px mb_30">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('cost_center.inventory_management') }}</h3>
                        </div>
                    </div>
                    <div class="QA_section QA_list_admin_dashboard_inventory">
                        <div class="QA_table">
                            <table class="table" id="inventoryTable">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center">{{ __('common.sl') }}</th>
                                        <th scope="col" class="text-center">{{ __('cost_center.cost_center') }}</th>
                                        <th scope="col" class="text-center">{{ __('cost_center.products') }}</th>
                                        <th scope="col" class="text-center">{{ __('cost_center.quantities') }}</th>
                                        <th scope="col" class="text-center">{{ __('common.status') }}</th>
                                        <th scope="col" class="text-center">{{ __('common.action') }}</th>
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
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            let ajaxUrl = "{{ route('cost_centers.inventory.get-data') }}";
            let orderColumns = [
                { data: 'DT_RowIndex', name: 'id', render: d => numbertrans(d), className: 'text-center' },
                { data: 'name', name: 'name', className: 'text-center' },
                { data: 'product_count', name: 'product_count', searchable: false, className: 'text-center' },
                { data: 'total_quantity', name: 'total_quantity', searchable: false, className: 'text-center' },
                { data: 'status_toggle', name: 'status_toggle', orderable: false, searchable: false, className: 'text-center' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
            ];
            initGlobalDataTable("#inventoryTable", ajaxUrl, orderColumns);

            // Status toggle listener
            $(document).on('change', '.status_toggle', function() {
                $('#pre-loader').removeClass('d-none');
                let id = $(this).data('id');
                let status = $(this).prop('checked') ? 1 : 0;
                let url = "{{ route('cost_centers.update-status') }}";
                
                $.post(url, {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    status: status
                }, function(response) {
                    if (response.success) {
                        toastr.success(response.message, "{{ __('common.success') }}");
                    } else {
                        toastr.error(response.error || "{{ __('common.error_message') }}", "{{ __('common.error') }}");
                        $(this).prop('checked', !status);
                    }
                    $('#pre-loader').addClass('d-none');
                }).fail(function(xhr) {
                    toastr.error(xhr.responseJSON?.error || "{{ __('common.error_message') }}", "{{ __('common.error') }}");
                    $('#pre-loader').addClass('d-none');
                    $(this).prop('checked', !status);
                });
            });

            function redirectToCenter(routeTemplate, id) {
                $('#pre-loader').removeClass('d-none');

                let url = routeTemplate.replace('__ID__', id);
                window.location.href = url;
            }

            // Ir a la vista de productos del centro
            $(document).on('click', '.show_inventory', function () {
                let id = $(this).data('id');
                let route = "{{ route('cost_centers.inventory.show-products', ['centerId' => '__ID__']) }}";
                redirectToCenter(route, id);
            });

            // Ir a la vista de transferencias del centro
            $(document).on('click', '.show_transfers', function () {
                let id = $(this).data('id');
                let route = "{{ route('cost_centers.inventory.transactions', ['centerId' => '__ID__']) }}";
                redirectToCenter(route, id);
            });

            $(window).on('load pageshow', function () {
                $('#pre-loader').addClass('d-none');
            });
        });
    </script>
@endpush
