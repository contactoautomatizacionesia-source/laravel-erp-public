<div role="tabpanel" class="tab-pane fade show active" id="affiliate_link_tab">
    <div class="main-title">
        <h3 class="mb-20">{{__('affiliate.Affiliate Links')}}</h3>
    </div>
    <div class="QA_section QA_section_heading_custom check_box_table">
        <div class="QA_table ">
            <div class="">
                <table id="lms_table" class="table Crm_table_active3">
                    <thead>
                    <tr>
                        <th>{{__('affiliate.Affiliate Link')}}</th>
                        <th>{{__('affiliate.Visits')}}</th>
                        <th>{{__('affiliate.Registered')}}</th>
                        <th>{{__('affiliate.Purchased')}}</th>
                        <th>{{__('affiliate.Commissions')}}</th>
                        <th>{{__('common.action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $item)
                        <tr>
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
                                        <a  class="dropdown-item delete_link" data-value="{{route('affiliate.delete_link',$item->id)}}">{{ __('common.delete') }}</a>
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
