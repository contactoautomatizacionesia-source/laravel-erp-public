@extends('backEnd.master')
@section('styles')

<link rel="stylesheet" href="{{asset(asset_path('modules/marketing/css/style.css'))}}" />
@endsection
@section('mainContent')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @include('backEnd.partials._deleteModalForAjax',['item_name' => __('marketing.flash_deal')])
    <section class="admin-visitor-area up_st_admin_visitor ign-flash-deals">

        <div class="container-fluid white_box_30px">
            <div class="row justify-content-center">

                <div class="col-12">
                    <div class="pos_tab_btn w-100 mb-10">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                            @if (permissionCheck('marketing.flash-deals.create'))
                            
                                <li class="nav-item"><a href="{{ route('marketing.flash-deals.create') }}" class="nav-item action"><i
                                            class="ti-plus mr-2"></i>{{ __('common.add_new') }}</a></li>
                        
                            @endif
                            
                        </ul>
                    </div>
                    <div class="box_header common_table_header">
                        
                        <div class="main-title d-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('marketing.flash_deal') }} {{__('common.list')}}</h3>
                            
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table">
                            <div class="" id="item_table">
                                @include('marketing::flash_deals.components.list')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@include('marketing::flash_deals.components._scripts')
