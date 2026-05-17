@extends('backEnd.master')
@section('styles')
    <style>
        .mtr-10{
            margin-top: -10px;
        }
        .cursor-not-allowed{
            cursor: not-allowed;
        }
        .badge_5 {
            background: rgba(140, 143, 141, 0.1);
            font-size: 13px !important;
            font-weight: 500 !important;
            color: var(--secondary) !important;
            border: 0;
            display: inline-block;
            border-radius: 10px;
            padding: 7px 21px;
            white-space: nowrap;
            line-height: 1.2;
            text-transform: none;
        }
        .primary_datepicker_input button {
            position: absolute;
            color: #828BB2;
            font-size: 14px;
            font-weight: 400;
            background: transparent;
            border: 0;
            cursor: pointer;
            z-index: 999;
            top: 70%;
            transform: translateY(-50%);
            right: 14px;
        }
        .primary_datepicker_input button i {
            top: 0;
            cursor: pointer;
            z-index: 9;
        }
        .info_msg{
            color: var(--gradient_3) !important;
        }
        .btn-line-height{
            /*line-height: 20px !important;*/
            /*padding: 1px 20px !important;*/
            white-space: nowrap;
            text-align: center;
        }
        .nav-tabs {
            border: 0 !important;
        }
    </style>
@endsection
@section('mainContent')
    @if(hasAffiliateAccess())
        @if($start_date && $end_date)
            <section class="sms-breadcrumb mb-40 white-box">
                <div class="container-fluid">
                    <div class="row justify-content-between">
                        <h1>[ {{showDate($start_date)}} - {{showDate($end_date)}} Filter Record ] </h1>
                    </div>
                </div>
            </section>
        @endif
        <section class="admin-visitor-area up_st_admin_visitor ign">
            <div class="container-fluid p-0">
                @include('affiliate::affiliate.components._filter')
                @include('affiliate::affiliate.components._balance_info')
                <div class="row">
                    <div class="col-lg-3">

                        @include('affiliate::affiliate.components._create_link')
                        @include('affiliate::affiliate.components._paypal_account')

                    </div>
                    <div class="col-lg-9">
                        @include('affiliate::affiliate.components._table_data')
                    </div>
                </div>
            </div>
            <div id="append_html"></div>
            @include('affiliate::affiliate.components._withdraw_request_modal')
            @include('affiliate::affiliate.components._balance_transfer_modal')
            @include('affiliate::_deleteModalForAjax',['item_name' => __("affiliate.Withdraw")])
            <input type="hidden" value="{{affiliateConfig('min_withdraw')}}" id="minimum_withdraw_amount">
            <input type="hidden" value="{{$user->affiliateWallet ? $user->affiliateWallet->amount : 0}}" id="user_balance">
            <input type="hidden" value="{{route('affiliate.withdraw_request.store')}}" id="withdraw_request_store_url">
            <input type="hidden" value="{{route('affiliate.withdraw_request.destroy')}}" id="withdraw_request_delete_url">
            <input type="hidden" value="{{route('affiliate.withdraw_request.edit',':id')}}" id="withdraw_request_edit_url">
            <input type="hidden" value="{{route('affiliate.withdraw_request.update',':id')}}" id="withdraw_request_update_url">
            <input type="hidden" value="{{route('affiliate.balance_transfer_to_wallet')}}" id="balance_transfer_url">
        </section>
    @elseif(auth()->check() && auth()->user()->accept_affiliate_request ==2)
        <section class="admin-visitor-area up_st_admin_visitor white-box">
            <div class="container-fluid p-0">
                <div class="row justify-content-between">
                    <h1 class="info_msg">[{{__('affiliate.Info')}} : {{__('affiliate.You are blocked from admin. Please contact with admin.')}} ] </h1>
                </div>
            </div>
        </section>
    @else
        <section class="admin-visitor-area up_st_admin_visitor white-box">
            <div class="container-fluid p-0">
                <div class="row justify-content-between">
                    <h1 class="info_msg">[{{__('affiliate.Info')}} : {{__('affiliate.Your affiliate joining request is under review. After confirming your request you can join our affiliate program.')}} ] </h1>
                </div>
            </div>
        </section>
    @endif
    @include('backEnd.partials.delete_modal')
@endsection
@push('scripts')
    <script src="{{asset('Modules/Affiliate/Resources/assets/js/affiliate_link.js')}}"></script>
    <script src="{{asset('Modules/Affiliate/Resources/assets/js/balance_transfer.js')}}"></script>
    <script src="{{asset('Modules/Affiliate/Resources/assets/js/daterangepicker.min.js')}}"></script>
    <script>
        (function($) {
            "use strict";
            $(document).ready(function(){
                $('input[name="date_range_filter"]').daterangepicker({
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    }

                }, function (start, end, label) {
                    $('#start').val(start.format('YYYY-MM-DD'))
                    $('#end').val(end.format('YYYY-MM-DD'))
                });
                $(document).on('click','#reset-date-filter',function(){
                    let filterRange = $('input[name="date_range_filter"]').val();
                    let formatDate = filterRange.split('-');
                    let startDate = dateFormat(formatDate[0]);
                    let endDate = dateFormat(formatDate[1]);
                    var params = [
                        "startDate=" +startDate,
                        "endDate=" + endDate
                    ];
                    window.location.href = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + params.join('&');
                });

                $(document).on('click','.copy_btn',function(event){
                    event.preventDefault();
                    let id = $(this).data('id');
                    var r = document.createRange();
                    r.selectNode(document.getElementById('link_'+id));
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(r);
                    document.execCommand('copy');
                    window.getSelection().removeAllRanges();
                    toastr.success("{{__('common.link_copied_successfully')}}", "{{__('common.success')}}");
                });

                $(document).on('click', '.delete_link', function(event){
                    event.preventDefault();
                    let value = $(this).data('value');
                    confirm_modal(value);
                });

                function dateFormat(date){
                    var newdate = new Date(date);
                    var dd =("0" + (newdate.getDate())).slice(-2);
                    var mm =("0" + (newdate.getMonth() + 1)).slice(-2);
                    var y = newdate.getFullYear();
                    return  y + '-' + mm + '-' + dd;
                }

            });
        })(jQuery);
    </script>
@endpush
