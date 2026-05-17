<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
        {{ __('common.select') }}
    </button>
    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item view_entry_detail" data-id="{{ $row->id }}" style="cursor:pointer;">
            {{ __('inventoryentry::inventory.detail_view') }}
        </a>
        @if(($table ?? 'active') !== 'deleted')
            <a class="dropdown-item edit_entry" data-id="{{ $row->id }}" style="cursor:pointer;">
                {{ __('inventoryentry::inventory.edit_entry') }}
            </a>
            <a class="dropdown-item delete_entry text-danger" data-id="{{ $row->id }}" style="cursor:pointer;">
                {{ __('inventoryentry::inventory.delete_entry') }}
            </a>
        @endif
    </div>
</div>
