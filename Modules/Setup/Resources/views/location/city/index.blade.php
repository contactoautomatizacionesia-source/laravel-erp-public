@extends('backEnd.master')

@section('styles')
<style>
    .location-inline-create h3.mb-20 {
        margin-bottom: 40px;
    }
    .location-inline-create .white-box.mb-5 {
        padding-top: 20px;
    }
</style>
@endsection

@section('mainContent')


    <section class="admin-visitor-area up_st_admin_visitor">

        <div class="container-fluid p-0">
            <div class="row justify-content-center">
                @if (permissionCheck('setup.city.store'))
                    <div class="col-lg-3">
                        <div class="row">
                            <div id="formHtml" class="col-lg-12 location-inline-create">
                                @include('setup::location.city.components.create')
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-lg-9 white_box_30px mb_30">
                    <div class="col-md-12 mb-10">
                        <div class="box_header_right">
                            <div class="pos_tab_btn justify-content-end">
                                <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                                    <li class="nav-item">
                                        <a class="nav-link active show" href="#all_cities" role="tab" data-toggle="tab" aria-selected="true" data-table="all">
                                            {{ __('common.all') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#active_cities" role="tab" data-toggle="tab" aria-selected="false" data-table="active">
                                            {{ __('common.active') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#inactive_cities" role="tab" data-toggle="tab" aria-selected="false" data-table="inactive">
                                            {{ __('common.inactive') }}
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="#default_cities" role="tab"
                                           data-toggle="tab" aria-selected="false" data-table="default">
                                            {{ __('setup.default') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="box_header common_table_header">
                            <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px" id="table_title">{{ __('common.city') }} {{ __('common.list')  }}</h3>


                            </div>
                        </div>
                    </div>
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table">
                            <div class="" id="item_table">

                                @include('setup::location.city.components.list')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@include('backEnd.partials.delete_modal',['item_name' => __('common.city')])
@include('setup::location.city.components.scripts')
