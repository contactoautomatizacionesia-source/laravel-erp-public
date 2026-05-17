@extends('backEnd.master')

@section('mainContent')
<x-admin.section class="ign-customer-list">
    <div class="row">
        <div class="col-12">
            <div class="box_header common_table_header ">
                <div class="main-title d-md-flex align-items-center ">
                    <x-backEnd.back-button :text="false" />
                    <h3 class="mb-0">{{ __('inventorycount::messages.count_details') }}: <span class="color-orange-toolkit">{{ $count->count_code }}</span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 px-2">
            @if($count->audit_status === 'closed')
                <div class="form-card conteo-result border-secondary" style="background:#f8f9fa;">
                    <div class="text-center gap-3">
                        <div class="icon-wrap">
                            <i class="ti-lock color-orange-toolkit"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="mb-1 font-weight-bold text-secondary">
                                {{ __('inventorycount::messages.count_closed_title') }}
                            </h3>
                            <p class="mb-0">{{ __('inventorycount::messages.count_closed_message') }}</p>
                            @if($approvedSibling)
                            <div class="mt-3">
                                <a href="{{ route('inventory_count.show', $approvedSibling->id) }}" class="btn-toolkit btn-primary btn-sm">
                                    <i class="ti-check mr-1"></i> {{ __('inventorycount::messages.count_closed_link') }}
                                    — {{ $approvedSibling->count_code }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif($isAdmin && $count->audit)
                @php
                    $isApproved  = $count->audit->status === 'approved';
                    $auditorName = trim(optional($count->audit->auditor)->first_name . ' ' . optional($count->audit->auditor)->last_name);
                    $auditDate   = $count->audit->created_at;
                @endphp
                <div class="form-card conteo-result {{ $isApproved ? 'conteo-aproved' : 'border-danger conteo-reject' }}">
                    <div class="text-center gap-3">
                        <div class="icon-wrap">
                            <i class="{{ $isApproved ? 'ti-check' : 'ti-close' }}"></i>
                        </div>
                        <div class="flex-grow-1 ">
                            <h3 class="mb-1  font-weight-bold ">
                                {{ $isApproved
                                    ? __('inventorycount::messages.audit_result_approved')
                                    : __('inventorycount::messages.audit_result_rejected') }}
                            </h3>
                            <div class=" mb-2">
                                <p class="mr-3">{{ __('inventorycount::messages.auditor') }}: <strong>{{ $auditorName ?: '-' }}</strong></p>
                                <span class="text-black" title="{{ $auditDate?->format('d/m/Y H:i') }}">{{ $auditDate?->diffForHumans() ?? '-' }}</span>
                            </div>
                            <p class="mb-0">{{ $count->audit->notes }}</p>
                            @if(!$isApproved)
                            <p class="mb-0 mt-2 text-black">
                                <i class="ti-info-alt mr-1"></i>{{ __('inventorycount::messages.audit_rejected_hint') }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            @if($isAdmin && $count->audit_status === 'pending' && permissionCheck('inventory_count.audits.store'))
            <div class="form-card">
                <div class="">
                    <h3 class="mb-2">{{ __('inventorycount::messages.review_count') }}</h3>
                   
                    <form id="auditForm">
                        @csrf
                        <input type="hidden" name="count_id" value="{{ $count->id }}">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="primary_input_label" for="auditStatusSelect">{{ __('inventorycount::messages.audit_status') }} <span class="text-danger">*</span></span>
                                    <select class="primary_input_select" name="audit_status" id="auditStatusSelect" required>
                                        <option value="">{{ __('common.select') }}...</option>
                                        <option value="approved">{{ __('inventorycount::messages.audit_approved') }}</option>
                                        <option value="rejected">{{ __('inventorycount::messages.audit_rejected') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="primary_input_label" for="auditNotes">{{ __('inventorycount::messages.audit_notes') }} <span class="text-danger">*</span></span>
                                    <textarea class="primary_textarea" name="notes" id="auditNotes" rows="3"
                                                maxlength="1000"
                                                placeholder="{{ __('inventorycount::messages.audit_notes_placeholder') }}"
                                                required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="button" class="btn-toolkit btn-primary btn-icon" id="saveAuditBtn">
                                <i class="ti-check mr-1"></i> {{ __('inventorycount::messages.save_audit') }}
                            </button>
                        </div>
                    </form>
                </div>
                <div id="auditConfirmModal" class="modal fade" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger-toolkit">
                                <h5 class="modal-title">{{ __('common.confirm') }}</h5>
                                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
                            </div>
                            <div class="modal-body">
                                <p>{{ __('inventorycount::messages.audit_confirm_text') }}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                                <button type="button" class="btn-toolkit btn-danger" id="confirmAuditBtn">{{ __('common.confirm') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div class="form-card">
                <h3 class="mb-3">{{ __('inventorycount::messages.info_count') }}</h3>
                <div class="row">
                    <div class="col-12 mb-2">
                        <div class="info-item d-flex align-items-start">
                            <div class="info-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="info-text">
                                <span class="primary_input_label mb-0">
                                    {{ __('inventorycount::messages.cost_center') }}
                                </span>
                                <p class="mb-0 font-weight-bold">
                                    {{ optional($count->costCenter)->name ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-4">
                        <div class="info-item d-flex align-items-start">
                            <div class="info-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="info-text">
                                <span class="primary_input_label mb-0">
                                    {{ __('inventorycount::messages.responsible') }}
                                </span>
                                <p class="mb-0 font-weight-bold">
                                    {{ optional($count->user)->first_name }} {{ optional($count->user)->last_name }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="primary_input_label">{{ __('inventorycount::messages.status') }}</span>
                        <p class="mb-0">
                            @include('inventorycount::counts.partials.status_badge', ['row' => $count])
                        </p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <span class="primary_input_label">{{ __('inventorycount::messages.audit_status') }}</span>
                        <p class="mb-0">
                            @include('inventorycount::counts.partials.audit_badge', ['row' => $count])
                        </p>
                    </div>
                    
                </div>
            </div>
            
        </div>
        <div class="col-lg-8 px-2">
            <div class="form-card">
                <h3 class="mb-2">{{ __('inventorycount::messages.products_counted') }}</h3>
                @if($count->observation)
                    <div class="alert alert-light border mb-4">
                        <strong>{{ __('inventorycount::messages.observation') }}:</strong> {{ $count->observation }}
                    </div>
                @endif
                <div class="dataTables_wrapper">
                    <x-admin.table-container>
                        <div class="table-responsive ign-scrollbar">
                            <table class="table dataTable table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('common.sl') }}</th>
                                        <th>{{ __('inventorycount::messages.product') }}</th>
                                        <th class="text-center">{{ __('inventorycount::messages.physical_quantity') }}</th>
                                        @if($isAdmin)
                                        <th class="text-center">{{ __('inventorycount::messages.system_stock') }}</th>
                                        <th class="text-center">{{ __('inventorycount::messages.difference') }}</th>
                                        @endif
                                        <th>{{ __('inventorycount::messages.observation_type') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($count->details as $i => $detail)
                                    @php
                                        $name = json_decode($detail->product->product_name ?? '{}', true);
                                        $productName = is_array($name) ? ($name[app()->getLocale()] ?? reset($name) ?? '-') : ($detail->product->product_name ?? '-');
                                        $diff = $detail->physical_quantity - $detail->system_stock;
                                    @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $productName }}</td>
                                        <td class="text-center">{{ $detail->physical_quantity ?? '-' }}</td>
                                        @if($isAdmin)
                                        <td class="text-center">{{ $detail->system_stock }}</td>
                                        <td class="text-center @if($diff < 0) text-danger @elseif($diff > 0) text-success @endif">
                                            {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                                        </td>
                                        @endif
                                        <td>{{ optional($detail->observationType)->name ?? '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="{{ $isAdmin ? 6 : 4 }}" class="text-center text-muted">{{ __('common.no_data_found') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-admin.table-container>
                </div>
            </div>
            <div class="form-card">
                <h3 class="mb-3">
                    {{ __('inventorycount::messages.attempts_history') }} ({{ $siblings->count() }})
                </h3>

                @if($siblings->isNotEmpty())
                <div class="timeline">
                    @foreach($siblings as $sibling)
                    <div class="timeline-item">
                        <div class="timeline-icon {{ $sibling->status === 'correct' ? 'success' : 'danger' }}">
                            <i class="fas {{ $sibling->status === 'correct' ? 'ti-check' : 'ti-close' }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <span class="text-black">
                                    <strong>
                                        @if($sibling->id !== $count->id)
                                            <a href="{{ route('inventory_count.show', $sibling->id) }}">{{ $sibling->count_code }}</a>
                                        @else
                                            {{ $sibling->count_code }}
                                        @endif
                                    </strong>
                                    &mdash;
                                    {{ optional($sibling->costCenter)->name ?? '-' }}
                                    &mdash;
                                    {{ trim(optional($sibling->user)->first_name . ' ' . optional($sibling->user)->last_name) ?: '-' }}
                                </span>
                                <span class="timeline-date" title="{{ $sibling->created_at?->format('d/m/Y H:i:s') }}">
                                    {{ $sibling->created_at?->diffForHumans() ?? '-' }}
                                </span>
                            </div>
                            <div class="timeline-meta">
                                @include('inventorycount::counts.partials.status_badge', ['row' => $sibling])
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

</x-admin.section>

@endsection

@if($isAdmin && $count->audit_status === 'pending')
@push('scripts')
<script>
$(document).ready(function () {
    $('#saveAuditBtn').on('click', function () {
        const status = $('#auditStatusSelect').val();
        const notes  = $('#auditNotes').val().trim();

        if (!status) { toastr.warning('{{ __("inventorycount::messages.select_audit_status") }}'); return; }
        if (!notes)  { toastr.warning('{{ __("inventorycount::messages.notes_required") }}'); return; }

        $('#auditConfirmModal').modal('show');
    });

    $('#confirmAuditBtn').on('click', function () {
        const $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url:    '{{ route("inventory_count.audits.store") }}',
            method: 'POST',
            data: {
                _token:       '{{ csrf_token() }}',
                count_id:     '{{ $count->id }}',
                audit_status: $('#auditStatusSelect').val(),
                notes:        $('#auditNotes').val().trim(),
            },
            success: function (res) {
                $('#auditConfirmModal').modal('hide');
                if (res.success) {
                    toastr.success(res.message);
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    toastr.error(res.message);
                    $btn.prop('disabled', false).text('{{ __("common.confirm") }}');
                }
            },
            error: function () {
                toastr.error('{{ __("common.error") }}');
                $btn.prop('disabled', false).text('{{ __("common.confirm") }}');
            }
        });
    });
});
</script>
@endpush
@endif
