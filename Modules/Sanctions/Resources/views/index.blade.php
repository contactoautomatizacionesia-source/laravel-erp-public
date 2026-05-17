@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">
        <div class="row">
            <div class="col-xl-12">
                <div class="box_header common_table_header">
                    <div class="pos_tab_btn justify-content-end">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                            <li class="nav-item">
                                <a class="nav-link action" href="#" id="btn_create_case">
                                    <i class="ti-plus mr-2"></i>{{ __('sanctions.create_case') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="tab-content">
                    <div class="box_header common_table_header">
                        <div class="main-title">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px px-0">{{ __('sanctions.active_cases') }}</h3>
                            <p class="text-muted mb-0 align-self-center">{{ __('sanctions.investigations_on_going') }}</p>
                        </div>
                    </div>
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table">
                            <div class="">
                                <table class="table" id="cases_table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('sanctions.customer') }}</th>
                                            <th>{{ __('sanctions.offence_scale') }}</th>
                                            <th>{{ __('sanctions.status') }}</th>
                                            <th>{{ __('sanctions.investigation_start_date') }}</th>
                                            <th>{{ __('sanctions.actions') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('sanctions::components.create_case_form_modal')
</section>
@endsection

@push('scripts')
<script src="{{ asset('public/js/nice-ajax.js') }}" defer></script>

<script type="text/javascript">
(function ($) {
    $(document).ready(function () {

        // ─── DataTable de Casos Activos ───────────────────────────────────
        

        let dtColumns = [
                { data: 'DT_RowIndex',          name: 'DT_RowIndex',   orderable: false, searchable: false },
                { data: 'eui_info',             name: 'eui_id',        orderable: false },
                { data: 'offense_badge',        name: 'offense_type_id', orderable: false },
                { data: 'status_badge',         name: 'process_status_id', orderable: false },
                { data: 'opened_at_formatted',  name: 'opened_at' },
                { data: 'action',               name: 'action',        orderable: false, searchable: false }
            ];

        window.casesTable = initGlobalDataTable("#cases_table", null, dtColumns, {});

        // ─── Abrir modal ──────────────────────────────────────────────────
        $('#btn_create_case').on('click', function (e) {
            e.preventDefault();
            $('#caseFormModal').modal('show');
        });

    });
})(jQuery);
</script>
@endpush
