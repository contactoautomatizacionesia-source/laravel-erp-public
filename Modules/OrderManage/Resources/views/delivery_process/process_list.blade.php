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
                @if (permissionCheck('order_manage.process_update'))
                    <div class="dropdown CRM_dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{ __('common.select') }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                            <a class="dropdown-item edit_reason" data-name="{{$item->name}}" data-description="{{$item->description}}" data-id={{$item->id}}  type="button">{{ __('common.edit') }}</a>
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
<script>
    document.addEventListener('click', function (event) {
        // Verifica si el elemento clicado tiene la clase 'edit_reason'
        if (event.target.classList.contains('edit_reason')) {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    });
</script>
