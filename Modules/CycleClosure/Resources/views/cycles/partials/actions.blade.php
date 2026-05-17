<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
        {{ __('common.select') }}
    </button>
    <div class="dropdown-menu dropdown-menu-right">

        @if(permissionCheck('cycle_closure.show'))
        <a class="dropdown-item" href="{{ route('cycle_closure.show', $cycle->id) }}">
             {{ __('common.details') }}
        </a>
        @endif

        @if($cycle->status === 'closed' && permissionCheck('cycle_closure.acta'))
        <a class="dropdown-item" href="{{ route('cycle_closure.acta', $cycle->id) }}" target="_blank">
             {{ __('cycleclosure::messages.phase_pdf_generation') }}
        </a>
        @endif

        @if($cycle->status === 'pending_approval' && permissionCheck('cycle_closure.approve'))
        <a class="dropdown-item text-warning js-approve-cycle" href="#"
           data-id="{{ $cycle->id }}">
            {{ __('cycleclosure::messages.closure_approve') ?? 'Firmar / Aprobar' }}
        </a>
        @endif

        @if(permissionCheck('cycle_closure.status'))
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-danger js-change-status" href="#"
           data-id="{{ $cycle->id }}"
           data-current="{{ $cycle->status }}">
             {{ __('cycleclosure::messages.status_changed') ?? 'Cambiar Estado' }}
        </a>
        @endif

    </div>
</div>
