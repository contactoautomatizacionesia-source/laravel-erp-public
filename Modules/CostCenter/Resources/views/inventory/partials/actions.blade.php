<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
        {{ __('common.select') }}
    </button>
    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item show_inventory" data-id="{{ $row->id }}">{{ __('common.view') }}</a>
        <a class="dropdown-item show_transfers" data-id="{{ $row->id }}" >{{ __('costcenter::inventory.see_transfers') }}</a>
    </div>
</div>
