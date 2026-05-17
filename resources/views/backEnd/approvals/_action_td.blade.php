<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{ __('common.select') }}
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
        <a class="dropdown-item approve_request" data-id="{{ $pendingApproval->id }}">{{ __('common.approve') }}</a>
        <a class="dropdown-item reject_request" data-id="{{ $pendingApproval->id }}">{{ __('common.reject') }}</a>
    </div>
</div>
