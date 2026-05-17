@extends('backEnd.master')

@section('mainContent')

<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-lg-6">
                <div class="white_box_30px">
                    <form action="{{route('setup.update.oneclickorder.status')}}" enctype="multipart/form-data" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="box_header">
                                    <div class="main-title d-flex">
                                        <h3 class="mb-0 mr-30">{{__('setup.One Click Order Complete Configuration')}}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="primary_input">
                                    <span class="d-block primary_input_label">{{__('setup.One Click Order Complete')}}</span>
                                    <ul id="theme_nav" class="permission_list sms_list">
                                        <li>
                                            <label class="switch_toggle" for="status">
                                                <input type="checkbox" name="status" id="status" value="1" class="status_change_checkbox" @if(!empty($oneClickOrder->status) && $oneClickOrder->status==1) checked @endif>
                                                <div class="slider round"></div>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="submit_btn text-center">
                                    <button class="btn-toolkit btn-primary btn-icon" type="submit">
                                        <i class="ti-check"></i>{{__('common.update')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
