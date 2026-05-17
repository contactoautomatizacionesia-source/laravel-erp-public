<div role="tabpanel" class="tab-pane fade" id="transaction_tab">
    <div class="main-title">
        <h3 class="mb-20">{{__('affiliate.Withdraw History')}}</h3>
    </div>
    <div class="QA_section QA_section_heading_custom check_box_table">
        <div class="QA_table ">
            <div class="">
                <table id="lms_table" class="table Crm_table_active3">
                    <thead>
                    <tr>
                        <th>{{__('common.sl')}}</th>
                        <th>{{__('affiliate.Request Date')}}</th>
                        <th>{{__('affiliate.Amount')}}</th>
                        <th>{{__('affiliate.Payment Type')}}</th>
                        <th>{{__('common.status')}}</th>
                        <th>{{__('affiliate.Confirm Date')}}</th>
                        <th>{{__('common.action')}}</th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach($user_transaction_data as  $item)
                        <tr>
                            <td>{{getNumberTranslate($loop->iteration)}}</td>
                            <td>{{dateConvert($item->request_date)}}</td>
                            <td>{{single_price($item->withdraw_amount)}}</td>
                            <td>
                                @if($item->payment_type == 1)
                                    <span class="badge_5">Offline</span>
                                @elseif($item->payment_type == 2)
                                    <span class="badge_5">{{__('affiliate.paypal')}}</span>
                                @else
                                    <span class="badge_5"> {{__('affiliate.add_user_wallet')}}</span>
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

                            <td>
                                <div class="dropdown CRM_dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('common.select') }}
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                        @if($item->status == 0)
                                            <a href="#" class="dropdown-item edit_row" data-id="{{$item->id}}">{{ __('common.edit') }}</a>
                                        @endif
                                        @if($item->status == 0)
                                            <a href="#" class="dropdown-item delete_row" data-id="{{ $item->id }}" type="button">{{ __('common.delete') }}</a>
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
