<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">{{ __('common.select') }}</button>
    <div class="dropdown-menu dropdown-menu-right">
        @if($table === 'deleted')
            <a class="dropdown-item restore_cost_center" href="#" data-value="{{ $row->id }}">{{ __('common.restore') }}</a>
        @else
            <a class="dropdown-item edit_cost_center" href="#" data-value="{{ $row->id }}">{{ __('common.edit') }}</a>
            <a class="dropdown-item delete_cost_center" href="#" data-value="{{ $row->id }}">{{ __('common.delete') }}</a>
        @endif
    </div>
</div>
