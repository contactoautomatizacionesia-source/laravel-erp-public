<div role="tabpanel" class="tab-pane fade" id="income_tab">
    <div class="main-title">
        <h3 class="mb-20">{{__('affiliate.Commission History')}}</h3>
    </div>
    <div class="QA_section QA_section_heading_custom check_box_table">
        <div class="QA_table ">
            <div class="">
                <table id="lms_table" class="table Crm_table_active3">
                    <thead>
                    <tr>
                        <th>{{__('common.sl')}}</th>
                        <th>{{__('common.date')}}</th>
                        <th>{{__('affiliate.Amount')}}</th>
                        <th>{{__('affiliate.Product')}}</th>
                        <th>{{__('common.status')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($user_income_data as  $item)
                        <tr>
                            <td>{{getNumberTranslate($loop->iteration)}}</td>
                            <td>{{dateConvert($item->date)}}</td>
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
