@extends('backEnd.master')

@section('mainContent')
    <section class="admin-visitor-area up_st_admin_visitor ign-referral-code">
        <div class="container-fluid p-0">
            <div class="row">

                @csrf
                @if (permissionCheck('marketing.referral-code.update-setup'))
                    <div id="form_div" class="col-lg-3">
                        @include('marketing::referral_code.components.setup')
                    </div>
                @endif

                <div class="{{permissionCheck('marketing.referral-code.update-setup') ? 'col-lg-9':'col-lg-12'}} white_box_30px">

                    <div class="row ">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-4 no-gutters">
                                    <div class="main-title">
                                        <h3 class="mb-30">{{ __('marketing.referral_code_list') }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div id="item_table">
                                @include('marketing::referral_code.components.list')
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection

@include('marketing::referral_code.components._scripts')
