@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor ign-customer-list">
    <div class="container-fluid white_box_30px mb_30">
        <div class="row">
            <div class="col-md-12 mb-10">
                <div class="box_header_right">
                    <div class=" pos_tab_btn justify-content-end">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                            <li class="nav-item">
                                <a class="nav-link active show" href="#general_settings" role="tab" data-toggle="tab"
                                    id="1" aria-selected="true">{{ __('common.general_settings') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#api_settings" role="tab" data-toggle="tab"
                                    id="2" aria-selected="true">{{ __('common.api_settings') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="">
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade active show" id="general_settings">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('common.general_settings')}}</h3>
                                </div>
                            </div>
                            @livewire('generalsetting::dian-general-settings-table')
                        </div>

                        <div role="tabpanel" class="tab-pane fade" id="api_settings">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('common.api_settings')}}</h3>
                                </div>
                            </div>
                            @livewire('generalsetting::dian-settings-table')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        if (window._dianToastrListenersRegistered) return;
        window._dianToastrListenersRegistered = true;

        window.addEventListener('toastr:success', function (event) {
            $('#pre-loader').addClass('d-none');
            toastr.success(event.detail.message);
        });

        window.addEventListener('toastr:error', function (event) {
            $('#pre-loader').addClass('d-none');
            toastr.error(event.detail.message);
        });

        window.addEventListener('toastr:warning', function (event) {
            $('#pre-loader').addClass('d-none');
            toastr.warning(event.detail.message);
        });
    })();
</script>
@endpush
