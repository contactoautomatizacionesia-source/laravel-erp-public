@extends('backEnd.master')
@section('styles')
    <style>
        .dashed {
            margin-top: 1rem;
            margin-bottom: 1rem;
            border: 0;
            border-top: 1px dashed var(--gradient_1);
        }
        .text_18{
            font-size: 18px;
            cursor: pointer;
        }
    </style>
@endsection

@section('mainContent')

    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="white-box">
                                <div class="add-visitor">
                                    <div class="main-title">
                                        <h3 class="mb-15">
                                            {{__('affiliate.Configurations')}}
                                        </h3>
                                        <hr class="dashed">
                                    </div>

                                    {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'affiliate.configurations.update',
                                        'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                                    <div class="row">

                                        <div class="col-lg-3">
                                            <div class="primary_input mb-15">
                                                <label class="primary_input_label" for="min_withdraw"> {{__('affiliate.Minimum Withdraw')}} <span class="required_mark_theme">*</span> </label>
                                                <input autocomplete="off" step="0.01" class="primary_input_field" name="min_withdraw" id="min_withdraw" placeholder="{{__('affiliate.Minimum Withdraw')}}" type="number" value="{{affiliateConfig('min_withdraw')}}">
                                                <span class="text-danger">{{$errors->first('min_withdraw')}}</span>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="primary_input mb-15">
                                                <label class="primary_input_label" for="balance_add_account_after_days"> {{__('affiliate.Balance Add Account After')}} [{{__('affiliate.In Days')}}] <span class="required_mark_theme">*</span> </label>
                                                <input step="1" class="primary_input_field" name="balance_add_account_after_days" id="balance_add_account_after_days" placeholder="{{__('affiliate.Balance Add Account After')}}" type="number" value="{{affiliateConfig('balance_add_account_after_days')}}">
                                                <span class="text-danger">{{$errors->first('balance_add_account_after_days')}}</span>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <label class="primary_input_label" for="">{{__('affiliate.Wallet Transfer Approval Need ?')}} <span class="required_mark_theme">*</span></label>
                                            <ul class="permission_list sms_list">
                                                <li>
                                                    <label class="primary_checkbox d-flex mr-12 ">
                                                        <input name="transfer_approval_need" class="transfer_approval" type="radio" id="transfer_approval" value="1" {{affiliateConfig('transfer_approval_need') == 1? 'Checked' :''}}>
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <p>{{__('affiliate.Yes')}}</p>
                                                </li>
                                                <li>
                                                    <label class="primary_checkbox d-flex mr-12 ">
                                                        <input name="transfer_approval_need" class="transfer_approval" type="radio" id="transfer_approval" value="0"  {{affiliateConfig('transfer_approval_need') == 0? 'Checked' :''}}>
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <p>{{__('affiliate.No')}}</p>
                                                </li>
                                            </ul>
                                            <span class="text-danger">{{$errors->first('transfer_approval_need')}}</span>
                                        </div>

                                        <div class="col-lg-3">
                                            <label class="primary_input_label" for="">{{__('affiliate.Affiliate User Approval ?')}} <span class="required_mark_theme">*</span></label>
                                            <ul class="permission_list sms_list">
                                                <li>
                                                    <label class="primary_checkbox d-flex mr-12 ">
                                                        <input name="admin_approval_need" class="admin_approval_need" type="radio" id="admin_approval_need" value="1" {{affiliateConfig('admin_approval_need') == 1? 'Checked' :''}}>
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <p>{{__('affiliate.Yes')}}</p>
                                                </li>
                                                <li>
                                                    <label class="primary_checkbox d-flex mr-12 ">
                                                        <input name="admin_approval_need" class="admin_approval_need" type="radio" id="admin_approval_need" value="0"  {{affiliateConfig('admin_approval_need') == 0? 'Checked' :''}}>
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <p>{{__('affiliate.No')}}</p>
                                                </li>
                                            </ul>
                                            <span class="text-danger">{{$errors->first('admin_approval_need')}}</span>
                                        </div>

                                    </div>


                                    <div class="row">

                                        <div class="col-lg-4">
                                            <label class="primary_input_label" for="">{{__('affiliate.Referral Duration Type')}} <span class="required_mark_theme">*</span></label>
                                            <ul class="permission_list sms_list">
                                                <li>
                                                    <label class="primary_checkbox d-flex mr-12 ">
                                                        <input name="referral_duration_type" class="referral_duration_type" type="radio" id="referral_duration_type" value="Onetime" {{affiliateConfig('referral_duration_type') == 'Onetime'? 'Checked' :''}}>
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <p>{{__('affiliate.Onetime')}}</p>
                                                </li>
                                                <li>
                                                    <label class="primary_checkbox d-flex mr-12 ">
                                                        <input name="referral_duration_type" class="referral_duration_type" type="radio" id="referral_duration_type" value="Lifetime"  {{affiliateConfig('referral_duration_type') == 'Lifetime'? 'Checked' :''}}>
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <p>{{__('affiliate.Lifetime')}} [{{__('affiliate.All Purchase')}}]</p>
                                                </li>

                                                <li>
                                                    <label class="primary_checkbox d-flex mr-12 ">
                                                        <input name="referral_duration_type" class="referral_duration_type" type="radio" id="referral_duration_type" value="Fixed"  {{affiliateConfig('referral_duration_type') == 'Fixed'? 'Checked' :''}}>
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <p>{{__('affiliate.Fixed')}}</p>
                                                </li>
                                            </ul>
                                            <span class="text-danger">{{$errors->first('referral_duration_type')}}</span>
                                        </div>

                                        <div class="col-lg-3 referral_duration_div">
                                            <div class="primary_input mb-15">
                                                <label class="primary_input_label" for="balance_add_account_after_days"> {{__('affiliate.Referral Duration')}} [{{__('affiliate.In Days')}}] <span class="required_mark_theme">*</span> </label>
                                                <input step="1" class="primary_input_field" name="referral_duration" id="referral_duration" placeholder=" {{__('affiliate.Referral Duration')}}" type="number" value="{{affiliateConfig('referral_duration')}}">
                                                <span class="text-danger">{{$errors->first('referral_duration')}}</span>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="primary_input mb-15">
                                                <label class="primary_input_label" for="balance_add_account_after_days">{{__('affiliate.cron_job_URL_for_commision_payment_status_(set with daily)')}} <span class="text_18 copy_cron"><i class="ti-layers"></i></span></label>
                                                <input step="1" class="primary_input_field" id="cron_job" placeholder=" {{__('affiliate.Referral Duration')}}" type="text" readonly value="{{url('/affiliate/pending-commission/approved')}}">
                                            </div>
                                        </div>

                                    </div>


                                    <div class="row">
                                        <div class="col-lg-3">
                                            <label class="primary_input_label" for="">{{__('affiliate.Commission Type')}} <span class="required_mark_theme">*</span></label>
                                            <ul class="permission_list sms_list">
                                                <li>
                                                    <label class="primary_checkbox d-flex mr-12 ">
                                                        <input name="commission_type" class="commission_type" type="radio" id="commission_type" value="Product" {{affiliateConfig('commission_type') == 'Product'? 'Checked' :''}}>
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <p>{{__('affiliate.Product Wise')}}</p>
                                                </li>
                                                <li>
                                                    <label class="primary_checkbox d-flex mr-12 ">
                                                        <input name="commission_type" class="commission_type" type="radio" id="commission_type" value="Category"  {{affiliateConfig('commission_type') == 'Category'? 'Checked' :''}}>
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <p>{{__('affiliate.Category Wise')}}</p>
                                                </li>

                                            </ul>
                                            <span class="text-danger">{{$errors->first('commission_type')}}</span>
                                        </div>
                                        <div class="col">
                                            <div id="product_wise_commission_div">
                                                <div class="row">
                                                    <div class="col-lg-3">
                                                        <label class="primary_input_label" for="">{{__('affiliate.Amount Type')}} <span class="required_mark_theme">*</span></label>
                                                        <ul class="permission_list sms_list">
                                                            <li>
                                                                <label class="primary_checkbox d-flex mr-12 ">
                                                                    <input name="amount_type" class="amount_type" type="radio" id="amount_type" value="Percentage" {{affiliateConfig('amount_type') == 'Percentage'? 'Checked' :''}}>
                                                                    <span class="checkmark"></span>
                                                                </label>
                                                                <p>{{__('affiliate.Percentage')}}</p>
                                                            </li>
                                                            <li>
                                                                <label class="primary_checkbox d-flex mr-12 ">
                                                                    <input name="amount_type" class="amount_type" type="radio" id="amount_type" value="Flat"  {{affiliateConfig('amount_type') == 'Flat'? 'Checked' :''}}>
                                                                    <span class="checkmark"></span>
                                                                </label>
                                                                <p>{{__('affiliate.Flat')}}</p>
                                                            </li>

                                                        </ul>
                                                        <span class="text-danger">{{$errors->first('amount_type')}}</span>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="primary_input mb-15">
                                                            <label class="primary_input_label" for="commission_amount"> {{__('affiliate.Commission Amount')}} <span class="required_mark_theme">*</span> </label>
                                                            <input step="{{step_decimal()}}" class="primary_input_field" name="commission_amount" id="commission_amount" placeholder="{{__('affiliate.Commission Amount')}}" type="number" value="{{affiliateConfig('commission_amount')}}">
                                                            <span class="text-danger">{{$errors->first('commission_amount')}}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="category_wise_commission_div">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <label class="primary_input_label" for="">{{__('affiliate.Commission Calculate For Multi Category')}} <span class="required_mark_theme">*</span></label>
                                                        <ul class="permission_list sms_list">
                                                            <li>
                                                                <label class="primary_checkbox d-flex mr-12 ">
                                                                    <input name="multi_category_commission_calculate" class="multi_category_commission_calculate" type="radio" id="multi_category_commission_calculate" value="Minimum" {{affiliateConfig('multi_category_commission_calculate') == 'Minimum'? 'Checked' :''}}>
                                                                    <span class="checkmark"></span>
                                                                </label>
                                                                <p>{{__('affiliate.Minimum')}}</p>
                                                            </li>
                                                            <li>
                                                                <label class="primary_checkbox d-flex mr-12 ">
                                                                    <input name="multi_category_commission_calculate" class="multi_category_commission_calculate" type="radio" id="multi_category_commission_calculate" value="Average" {{affiliateConfig('multi_category_commission_calculate') == 'Average'? 'Checked' :''}}>
                                                                    <span class="checkmark"></span>
                                                                </label>
                                                                <p>{{__('affiliate.Average')}}</p>
                                                            </li>

                                                            <li>
                                                                <label class="primary_checkbox d-flex mr-12 ">
                                                                    <input name="multi_category_commission_calculate" class="multi_category_commission_calculate" type="radio" id="multi_category_commission_calculate" value="Maximum" {{affiliateConfig('multi_category_commission_calculate') == 'Maximum'? 'Checked' :''}}>
                                                                    <span class="checkmark"></span>
                                                                </label>
                                                                <p>{{__('affiliate.Maximum')}}</p>
                                                            </li>

                                                        </ul>
                                                        <span class="text-danger">{{$errors->first('multi_category_commission_calculate')}}</span>
                                                    </div>

                                                    <div class="col-lg-3">
                                                        <label class="primary_input_label" for="">{{__('affiliate.Common Calculation Method')}} <span class="required_mark_theme">*</span></label>
                                                        <ul class="permission_list sms_list">
                                                            <li>
                                                                <label class="primary_checkbox d-flex mr-12 ">
                                                                    <input name="common_calculation_method" class="common_calculation_method" type="radio" id="common_calculation_method" value="Percentage" {{affiliateConfig('common_calculation_method') == 'Percentage'? 'Checked' :''}}>
                                                                    <span class="checkmark"></span>
                                                                </label>
                                                                <p>{{__('affiliate.Percentage')}}</p>
                                                            </li>
                                                            <li>
                                                                <label class="primary_checkbox d-flex mr-12 ">
                                                                    <input name="common_calculation_method" class="common_calculation_method" type="radio" id="common_calculation_method" value="Flat" {{affiliateConfig('common_calculation_method') == 'Flat'? 'Checked' :''}}>
                                                                    <span class="checkmark"></span>
                                                                </label>
                                                                <p>{{__('affiliate.Flat')}}</p>
                                                            </li>

                                                        </ul>
                                                        <span class="text-danger">{{$errors->first('common_calculation_method')}}</span>
                                                    </div>

                                                    <div class="col-lg-3">
                                                        <div class="primary_input mb-25 position-relative">
                                                            <label class="primary_input_label" for="common_amount">{{ __('affiliate.Common Amount')}} <span class="required_mark_theme">*</span></label>
                                                            <input value="{{affiliateConfig('common_amount')}}" step="{{step_decimal()}}" name="common_amount" id="common_amount"  class="primary_input_field commission_amount" placeholder="{{ __('affiliate.Common Amount')}}" type="number">
                                                            <span class="text-danger">{{$errors->first('common_amount')}}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="QA_section QA_section_heading_custom check_box_table">
                                                        <div class="QA_table ">
                                                            <div class="table-responsive">
                                                                <table class="table">
                                                                    <tr>
                                                                        <th scope="col">{{ __('affiliate.Category')}}</th>
                                                                        <th scope="col">{{ __('affiliate.Calculation Method')}}</th>
                                                                        <th scope="col">{{ __('affiliate.Commission Amount')}}</th>
                                                                    </tr>
                                                                    @foreach($categories as $category)
                                                                        <tr>
                                                                           <td>
                                                                               {{$category->name}}
                                                                               <input type="hidden" value="{{$category->id}}" name="categories[]">
                                                                           </td>
                                                                           <td>
                                                                               <select class="primary_select calculation_method" name="calculation_method{{$category->id}}">
                                                                                   @foreach($calculation_methods as $value)
                                                                                       <option {{$category->affiliateCategoryCommission ? $category->affiliateCategoryCommission->calculation_method == $value ? 'selected':"":""  }} value="{{$value}}">{{$value}}</option>
                                                                                   @endforeach
                                                                               </select>
                                                                           </td>
                                                                           <td>
                                                                               <input value="{{$category->affiliateCategoryCommission ? $category->affiliateCategoryCommission->amount : affiliateConfig('common_amount')  }}" step="{{step_decimal()}}" name="commission_amount{{$category->id}}" id="commission_amount"  class="primary_input_field commission_amount" placeholder="{{ __('affiliate.Commission Amount')}}" type="number">
                                                                           </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row mt-10">
                                        <div class="col-lg-12 text-center">
                                            <button class="primary-btn fix-gr-bg submit" >
                                                <span class="ti-check"></span>
                                                {{__('common.update')}}
                                            </button>
                                        </div>
                                    </div>
                                    {{ Form::close() }}

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
    <script src="{{asset('Modules/Affiliate/Resources/assets/js/repeater/repeater.js')}}"></script>
    <script src="{{asset('Modules/Affiliate/Resources/assets/js/repeater/indicator-repeater.js')}}"></script>
    <script src="{{asset('Modules/Affiliate/Resources/assets/js/config.js')}}"></script>
@endpush

