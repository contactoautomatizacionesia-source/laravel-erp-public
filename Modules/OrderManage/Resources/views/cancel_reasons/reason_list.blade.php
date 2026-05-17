<table class="table Crm_table_active3">
    <thead>
    <tr>
        <th scope="col">{{__('common.id')}}</th>
        <th scope="col">{{__('refund.process')}}</th>
        <th scope="col">{{__('refund.description')}}</th>
        <th scope="col">{{__('common.action')}}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $key => $item)
        <tr>
            <th>{{ getNumberTranslate($key+1) }}</th>
            <td>{{ $item->name }}</td>
            <td>{{ $item->description }}</td>
            <td>
                <!-- shortby  -->
                @if (permissionCheck('order_manage.cancel_reason_update') || permissionCheck('order_manage.cancel_reason_destroy'))
                    <div class="dropdown CRM_dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ __('common.select') }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            @if (permissionCheck('order_manage.cancel_reason_update'))
                                <a class="dropdown-item edit_reason" data-value="{{$item}}" type="button">{{ __('common.edit') }}</a>
                            @endif
                            @if (permissionCheck('order_manage.cancel_reason_destroy'))
                                <a class="dropdown-item delete_item" data-value="{{route('order_manage.cancel_reason_destroy', $item->id)}}">{{__('common.delete')}}</a>
                            @endif
                        </div>
                    </div>
                @else
                    <button class="primary_btn_2" type="button" disabled>{{ __('common.you_don_t_have_this_permission') }}</button>
                @endif
                <!-- shortby  -->
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
