<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
        {{ __('common.select') }}
    </button>
    <div class="dropdown-menu dropdown-menu-right">
        @if(permissionCheck('inventory_count.audits.show'))
        <a class="dropdown-item" href="{{ route('inventory_count.audits.show', $row->id) }}">
            {{ __('inventorycount::messages.action_detail') }}
        </a>
        @else
         <span>{{__('common.no_action_available')}}</span>
        @endif
    </div>
</div>
