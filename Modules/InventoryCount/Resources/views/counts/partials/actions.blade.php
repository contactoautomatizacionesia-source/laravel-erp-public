<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
        {{ __('common.select') }}
    </button>
    <div class="dropdown-menu dropdown-menu-right">
        @if(permissionCheck('inventory_count.show'))
        <a class="dropdown-item" href="{{ route('inventory_count.show', $row->id) }}">
            {{ __('inventorycount::messages.action_detail') }}
        </a>
        @endif
    </div>
</div>
