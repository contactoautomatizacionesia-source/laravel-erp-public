@extends('backEnd.master')
@section('mainContent')

    <section class="admin-visitor-area up_st_admin_visitor ign-complete-withdraw">
        <div class="container-fluid white_box_30px">
            <div class="row">
                <div class="col-md-3 date-range-block">
                    <div class="primary_input mb-15 date_range">
                        <div class="primary_datepicker_input filter">
                            <label class="primary_input_label" for="">{{__('affiliate.Select Date Range')}}</label>
                            <div class="no-gutters input-right-icon">
                                <div class="col">
                                    <div class="">
                                        <input  readonly class="primary_input_field filter_date_input_field"  type="text" name="date_range_filter" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mt-30">
                    <div class="d-flex">
                        <button id="reset-date-filter" type="button" class="primary-btn mr-10 fix-gr-bg">{{__('affiliate.Filter')}}</button>
                        <a type="button" href="{{route('affiliate.complete_withdraw')}}" class="primary-btn  fix-gr-bg">{{__('shipping.reset')}}</a>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="box_header common_table_header">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('affiliate.Complete Withdrawn List')}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table ">
                            <div class="">
                                <table id="lms_table" class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{__('common.sl')}}</th>
                                            <th scope="col">{{__('affiliate.Request Date')}}</th>
                                            <th scope="col">{{__('affiliate.Amount')}}</th>
                                            <th scope="col">{{__('affiliate.Payment Type')}}</th>
                                            <th scope="col">{{__('affiliate.User')}}</th>
                                            <th scope="col">{{__('affiliate.Confirm Date')}}</th>
                                            <th scope="col">{{__('affiliate.Confirm By')}}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" value="{{route('affiliate.complete_withdraw.datatable')}}" id="datatable_url">
    </section>
@endsection
@push('scripts')
    <script src="{{asset('Modules/Affiliate/Resources/assets/js/complete_withdraw_index.js')}}"></script>
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
                $("#reset-date-filter").on('click',function(){
                    resetAfterChange();
                });

                function resetAfterChange(){
                    let table = $('#lms_table').DataTable() ;
                    table.clearPipeline();
                    table.ajax.reload();
                }

            });
        })(jQuery);
    </script>
@endpush

