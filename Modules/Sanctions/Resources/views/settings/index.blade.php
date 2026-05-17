@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">
        <div class="row">
            <div class="col-xl-12">
                <div class="box_header common_table_header">
                    <div class="main-title">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px px-0"> {{ __('sanctions.settings') }} </h3>
                        <p class="text-muted mb-0 align-self-center"> {{ __('sanctions.settings_management') }} </p>
                    </div>
                </div>
            </div>
            <div class="col-md-12 box_header_right">
                <div class="pos_tab_btn justify-content-end">
                    <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                        <li class="nav-item">
                            <a class="nav-link active show" href="#manage_fault_types" role="tab" data-toggle="tab"
                                aria-selected="true">
                                {{ __('sanctions.manage_fault_types') }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#manage_sanctions_behavior" role="tab" data-toggle="tab"
                                aria-selected="false">
                                {{ __('sanctions.manage_sanctions_behivior') }}
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="#manage_action_types" role="tab" data-toggle="tab"
                                aria-selected="false">
                                {{ __('sanctions.manage_action_types') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#manage_mitigations" role="tab" data-toggle="tab"
                                aria-selected="false">
                                {{ __('sanctions.manage_mitigations') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="tab-content">
                    <div class="tab-pane fade active show" id="manage_fault_types" role="tabpanel">
                        <div>
                            <div>
                                <h2>{{ __('sanctions.fault_types') }}</h2>
                                <small class="text-muted">{{ __('sanctions.fault_types_description') }}</small>
                            </div>
                        </div>
                        <div class="QA_section QA_section_heading_custom check_box_table">
                            <div class="QA_table">
                                <div class="">
                                    @include('sanctions::components.fault_types_table')
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="manage_sanctions_behavior" role="tabpanel">
                        <div class="QA_section QA_section_heading_custom check_box_table">
                            <div class="QA_table">
                                <div class="">
                                    @include('sanctions::components.sanctions_behavior_table')
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="manage_action_types" role="tabpanel">
                        <div class="QA_section QA_section_heading_custom check_box_table">
                            <div class="QA_table">
                                <div class="">
                                    @include('sanctions::components.action_types_table')
                                </div>
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
    (function($) {
        "use strict";

        let faultTypesTable, sanctionsBehaviorTable, actionTypesTable;

        $(document).ready(function() {
            let faultTypesDtColumns = [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'fault_type_name',
                    name: 'fault_type_name'
                },
                {
                    data: 'fault_type_description',
                    name: 'fault_type_description'
                },
                {
                    data: 'fault_level',
                    name: 'fault_level'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ];

            let sanctionsBehaviorDtColumns = [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'description',
                    name: 'description'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ];
            faultTypesTable = initGlobalDataTable('#fault_types_table', null, faultTypesDtColumns, {});
            sanctionsBehaviorTable = initGlobalDataTable('#sanctions_behavior_table', null, sanctionsBehaviorDtColumns, {});
            actionTypesTable = initGlobalDataTable('#action_types_table', null, sanctionsBehaviorDtColumns, {});
        });
    })(jQuery);
</script>
@endpush
