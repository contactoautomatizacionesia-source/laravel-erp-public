@extends('backEnd.master')
@section('styles')
<link rel="stylesheet" href="{{asset('Modules/Affiliate/Resources/assets/css/show_details.css')}}" />
@endsection
@section('mainContent')
    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="white_box_30px box_shadow_white">
                        <div class="box_header">
                            <div class="main-title d-flex">
                                <h3 class="mb-0 mr-30">{{ __('affiliate.affiliate_user_profile')}}</h3>
                            </div>


                        </div>
                        <div class="row">
                            <div class="col-md-4 col-sm-12">
                                <div class="img_div">
                                    <img class="student-meta-img mb-3" src="{{ (@$user->avatar != null) ? showImage($user->avatar) : showImage('frontend/default/img/avatar.jpg') }}"  alt="">
                                </div>
                                <h3>{{$user->first_name}} {{$user->last_name}}</h3>
                                <table class="table table-borderless customer_view">
                                    <tr>
                                        <td>{{ __('common.name') }}</td>
                                        <td>: <span class="ml-1"></span>{{$user->first_name}} {{$user->last_name}}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('common.email') }}</td>
                                        <td>: <span class="ml-1"></span>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('common.phone') }}</td>
                                        <td>: <span class="ml-1"></span>{{ ($user->phone) ?? $user->username }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('common.registered_date') }}</td>
                                        <td>: <span class="ml-1"></span>{{ date(app('general_setting')->dateFormat->format, strtotime($user->created_at)) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('common.active_status') }}</td>
                                        <td>: <span class="ml-1"></span>
                                            @if ($user->is_active == 1)
                                                <span class="badge_1">{{__('common.active')}}</span>
                                            @else
                                                <span class="badge_4">{{__('common.in-active')}}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Current balance') }}</td>
                                        <td>: <span class="ml-1"></span>{{$user->affiliateWallet ? single_price($user->affiliateWallet->amount) : 0}}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-3 col-sm-12 customer_profile m-2">
                                <h3>{{__('Earning Info')}}</h3>
                                <table class="table table-borderless customer_view">
                                    <tr><td>{{__('Total earnings')}}</td>
                                    <td>: <span class="ml-1"></span>{{single_price($user->affiliateCommissions->where('status',1)->sum('amount'))}}</td></tr>
                                    <tr><td>{{__('Pending')}}</td>
                                    <td>: <span class="ml-1"></span>{{single_price($user->affiliateCommissions->where('status',0)->sum('amount'))}}</td></tr>
                                    <tr><td>{{__('Cancelled')}}</td>
                                    <td>: <span class="ml-1"></span>{{single_price($user->affiliateCommissions->where('status',2)->sum('amount'))}}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-3 col-sm-12 customer_profile m-2">
                                <h3>{{__('Wallet & withdraw info')}}</h3>
                                <table class="table table-borderless customer_view">
                                    <tr><td>{{__('Total transfer to wallet')}}</td>
                                    <td>: <span class="ml-1"></span>{{single_price($user->affiliateTransaction->where('status',1)->where('payment_type',3)->sum('withdraw_amount'))}}</td></tr>
                                    <tr><td>{{__('Pending transfer balance')}}</td>
                                    <td>: <span class="ml-1"></span>{{single_price($user->affiliateTransaction->where('status',0)->where('payment_type','!=',3)->sum('withdraw_amount'))}}</td></tr>
                                    <tr><td>{{__('Total withdraw balance')}}</td>
                                    <td>: <span class="ml-1"></span>{{single_price($user->affiliateTransaction->where('status',1)->where('payment_type','!=',3)->sum('withdraw_amount'))}}</td></tr>
                                    <tr><td>{{__('Pending withdraw balance')}}</td>
                                    <td>: <span class="ml-1"></span>{{single_price($user->affiliateTransaction->where('status',0)->where('payment_type','!=',3)->sum('withdraw_amount'))}}</td></tr>
                                </table>
                            </div>
                        </div>
                        @if ($user->description)
                            <hr>
                                <div class="row">
                                    <div class="col">
                                        <label class="primary_input_label" for="">
                                            @php
                                                echo $user->description;
                                            @endphp
                                        </label>
                                    </div>
                                </div>
                            <hr>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <div class="white_box_30px box_shadow_white">
                        <div class="col-lg-12 student-details">
                            <ul class="nav nav-tabs tab_column mb-50" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#Order" role="tab" data-toggle="tab">{{ __('affiliate.Affiliate Links') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#Wallet" role="tab" data-toggle="tab">{{ __('affiliate.Commission History') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#Address" role="tab" data-toggle="tab">{{ __('affiliate.Withdraw History') }}</a>
                                </li>
                            </ul>
                            <div class="tab-content pt-30">

                                <div role="tabpanel" class="tab-pane fade show active" id="Order">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="QA_section QA_section_heading_custom check_box_table">
                                                <div class="QA_table ">
                                                    <div class="">
                                                        <table class="table Crm_table_active3" id="orderTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{__('common.sl')}}</th>
                                                                    <th>{{__('affiliate.Affiliate Link')}}</th>
                                                                    <th>{{__('affiliate.Visits')}}</th>
                                                                    <th>{{__('affiliate.Registered')}}</th>
                                                                    <th>{{__('affiliate.Purchased')}}</th>
                                                                    <th>{{__('affiliate.Commissions')}}</th>
                                                                    <th>{{__('common.action')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($links as $key => $item)
                                                                    <tr>
                                                                        <td>{{$key + 1}}</td>
                                                                        <td id="link_{{$item->id}}">{{$item->affiliate_link}}</td>
                                                                        <td>{{$item->visits}}</td>
                                                                        <td>{{$item->registerUser->count()}}</td>
                                                                        <td>{{$item->payment->count()}}</td>
                                                                        <td>{{single_price($item->payment->sum('amount'))}}</td>
                                                                        <td>

                                                                            <div class="dropdown CRM_dropdown">
                                                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                    {{ __('common.select') }}
                                                                                </button>
                                                                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                                                                    <a href="#" class="dropdown-item show_category copy_btn" data-id="{{$item->id}}">{{ __('affiliate.copy_link') }}</a>
                                                                                    @if(auth()->user()->role->type == 'superadmin' || auth()->user()->role->type == 'admin' || auth()->user()->role->type == 'staff')
                                                                                        <a  class="dropdown-item delete_link" data-value="{{route('affiliate.delete_link',$item->id)}}">{{ __('common.delete') }}</a>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="Wallet">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="QA_section QA_section_heading_custom check_box_table">
                                                <div class="QA_table ">

                                                    <div class="">
                                                        <table class="table Crm_table_active3" id="walletTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{__('common.sl')}}</th>
                                                                    <th>{{__('affiliate.Date')}}</th>
                                                                    <th>{{__('affiliate.Amount')}}</th>
                                                                    <th>{{__('affiliate.Product')}}</th>
                                                                    <th>{{__('affiliate.Status')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($user_income_data as  $item)
                                                                    <tr>
                                                                        <td>{{$loop->iteration}}</td>
                                                                        <td>{{showDate($item->date)}}</td>
                                                                        <td>{{single_price($item->amount)}}</td>
                                                                        <td>{{$item->item?@$item->item->product_name:""}}</td>
                                                                        <td>
                                                                            @if($item->status == 0)
                                                                                <span class="badge_3">{{__('affiliate.Pending')}}</span>
                                                                            @elseif($item->status == 1)
                                                                                <span class="badge_1">{{__('affiliate.Done')}}</span>
                                                                            @elseif($item->status == 2)
                                                                                <span class="badge_2">{{__('common.cancelled')}}</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="Address">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="QA_section QA_section_heading_custom check_box_table">
                                                <div class="QA_table ">
                                                    <div class="">
                                                        <table class="table Crm_table_active3">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{__('affiliate.SL')}}</th>
                                                                    <th>{{__('affiliate.Request Date')}}</th>
                                                                    <th>{{__('affiliate.Amount')}}</th>
                                                                    <th>{{__('affiliate.Payment Type')}}</th>
                                                                    <th>{{__('affiliate.Status')}}</th>
                                                                    <th>{{__('affiliate.Confirm Date')}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($user_transaction_data as  $item)
                                                                    <tr>
                                                                        <td>{{$loop->iteration}}</td>
                                                                        <td>{{showDate($item->request_date)}}</td>
                                                                        <td>{{single_price($item->withdraw_amount)}}</td>
                                                                        <td>
                                                                            @if($item->payment_type == 1)
                                                                                <span class="badge_5">Offline</span>
                                                                            @elseif($item->payment_type == 2)
                                                                                <span class="badge_5">Paypal</span>
                                                                            @else
                                                                                <span class="badge_5"> Add User Wallet</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            @if($item->status == 0)
                                                                                <span class="badge_3">{{__('affiliate.Pending')}}</span>
                                                                            @elseif($item->status == 1)
                                                                                <span class="badge_1">{{__('affiliate.Done')}}</span>
                                                                            @else
                                                                                <span class="badge_4">{{__('affiliate.Cancel')}}</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            {{$item->confirm_date?showDate($item->confirm_date):"NA"}}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('backEnd.partials.delete_modal')
    </section>
@endsection

@push('scripts')
    <script>
        (function($){
            "use strict";
            $(document).ready(function(){
                $(document).on('click','.copy_btn',function(event){
                    event.preventDefault();
                    let id = $(this).data('id');
                    var r = document.createRange();
                    r.selectNode(document.getElementById('link_'+id));
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(r);
                    document.execCommand('copy');
                    window.getSelection().removeAllRanges();
                    toastr.success('¡Enlace copiado exitosamente!', "{{__('common.success')}}");
                });

                $(document).on('click', '.delete_link', function(event){
                    event.preventDefault();
                    let value = $(this).data('value');
                    confirm_modal(value);
                });
            });
        })(jQuery);
    </script>
@endpush
