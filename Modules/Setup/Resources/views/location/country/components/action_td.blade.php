@if (permissionCheck('setup.country.update') || permissionCheck('setup.country.destroy'))
    <div class="dropdown CRM_dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu{{ $country->id }}"
            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {{ __('common.select') }}
        </button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu{{ $country->id }}">
            @if(permissionCheck('setup.country.update'))
                <a href="javascript:void(0);" class="dropdown-item edit_country" data-id="{{ $country->id }}">{{ __('common.edit') }}</a>
            @endif
            @if(permissionCheck('setup.country.destroy'))
                <a href="javascript:void(0);" class="dropdown-item delete_country" data-id="{{ $country->id }}">{{ __('common.delete') }}</a>
            @endif
        </div>
    </div>
@else
    <button class="primary_btn_2" type="button">{{ __('common.no_action_permitted') }}</button>
@endif
