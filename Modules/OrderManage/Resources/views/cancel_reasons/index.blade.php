@extends('backEnd.master')
@section('page-title', __('order.cancel_reason'))
@section('styles')
<style>
.anchore_color{
color: #415094;
}
</style>
@endsection
@section('mainContent')
@if(isModuleActive('FrontendMultiLang'))
@php
$LanguageList = getLanguageList();
@endphp
@endif
    <section class="admin-visitor-area up_st_admin_visitor ign-cancel-reason">
        <div class="container-fluid p-0">
            <div class="row justify-content-center">
                <div class="col-lg-4">

                    <div class="create_div">
                        @include('ordermanage::cancel_reasons.create')
                    </div>
                    <div class="edit_div d-none">
                        @include('ordermanage::cancel_reasons.edit')
                    </div>
                </div>
                <div class="col-lg-8 white_box_30px">
                    <div class="box_header common_table_header">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('order.cancel_reason') }}</h3>
                        </div>
                    </div>
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table">
                            <!-- table-responsive -->
                            <div class="">
                                @if (permissionCheck('order_manage.cancel_reason_list'))
                                    <div id="refund_process_list">
                                        @include('ordermanage::cancel_reasons.reason_list')
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <button class="primary_btn_2" type="button" disabled>{{ __('common.you_don_t_have_this_permission') }}</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <input type="hidden" name="app_base_url" id="app_base_url" value="{{ URL::to('/') }}">

@include('backEnd.partials.delete_modal')
@endsection
@include('ordermanage::cancel_reasons.scripts')
