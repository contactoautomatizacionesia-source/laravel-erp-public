@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">
        <div class="row">
            <div class="col-xl-12">
                <div class="">
                    <div class="box_header common_table_header">
                        <div class="main-title">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px px-0">{{ __('sanctions.fault_history') }}</h3>
                            <p class="text-muted mb-0 align-self-center">{{ __('sanctions.faults_completed_and_archived') }}</p>
                        </div>
                    </div>
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table">
                            <div class="">
                                @include('sanctions::components.history_table')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('public/js/nice-ajax.js') }}" defer></script>

<script type="text/javascript">
    (function($){
        let historyTable;
        $(document).ready(function(){

            let dtColumns = [
                { data: 'id', name: 'id' },
                { data: 'customer', name: 'customer' },
                { data: 'infringement', name: 'infringement' },
                { data: 'sanction', name: 'sanction' },
                { data: 'resolution_date', name: 'resolution_date' },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ];
            // --------------------------------------- Datos de prueba --------------------------------------- //
            // #TODO: Realizar consulta AJAX para obtener los datos
            // --------------------------------------- Inicializar datatable --------------------------------------- //
            historyTable = initGlobalDataTable("#history_table", null, dtColumns, {});
        });
    })(jQuery);
</script>
@endpush