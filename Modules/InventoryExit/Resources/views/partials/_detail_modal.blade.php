<dialog id="exitDetailModal" class="modal fade" tabindex="-1" aria-labelledby="exitDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exitDetailModalLabel">
                    <i class="fa fa-info-circle"></i> {{ __('inventoryexit::messages.detail_title') }}
                    <small class="text-muted ml-10">#{{ $exitRequest->id }}</small>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="ti-close"></i>
                </button>
            </div>

            <div class="modal-body">

                {{-- ============================================================ --}}
                {{-- Información de solicitud --}}
                {{-- ============================================================ --}}
                <div class="form-section mb-25">
                    <h6 class="form-section-title">
                        <i class="fa fa-clipboard"></i> {{ __('inventoryexit::messages.detail_request_info') }}
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <span class="d-block primary_input_label">{{ __('inventoryexit::messages.exit_reason') }}</span>
                            <p class="form-static-text">{{ $exitRequest->exitReason?->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <span class="d-block primary_input_label">{{ __('inventoryexit::messages.cost_center') }}</span>
                            <p class="form-static-text">{{ $exitRequest->costCenter?->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <span class="d-block primary_input_label">{{ __('inventoryexit::messages.col_date') }}</span>
                            <p class="form-static-text">{{ $exitRequest->exit_date?->format('d/m/Y') ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="row mt-10">
                        <div class="col-md-4">
                            <span class="d-block primary_input_label">{{ __('inventoryexit::messages.col_requested_by') }}</span>
                            <p class="form-static-text">{{ $exitRequest->requestedBy?->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <span class="d-block primary_input_label">{{ __('inventoryexit::messages.request_date') }}</span>
                            <p class="form-static-text">{{ $exitRequest->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <span class="d-block primary_input_label">{{ __('inventoryexit::messages.col_status') }}</span>
                            <p class="form-static-text">
                                @if($exitRequest->status === 'pending')
                                    <span class="badge_3">{{ __('inventoryexit::messages.status_pending') }}</span>
                                @elseif($exitRequest->status === 'approved')
                                    <span class="badge_1">{{ __('inventoryexit::messages.status_approved') }}</span>
                                @else
                                    <span class="badge_2">{{ __('inventoryexit::messages.status_rejected') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="row mt-10">
                        <div class="col-md-12">
                            <span class="d-block primary_input_label">{{ __('inventoryexit::messages.observation_label') }}</span>
                            <p class="form-static-text">{{ $exitRequest->observation }}</p>
                        </div>
                    </div>
                    {{-- Audit trail solicitud --}}
                    <div class="row mt-10">
                        <div class="col-md-6">
                            <span class="d-block primary_input_label">IP solicitante</span>
                            <p class="form-static-text text-muted">{{ $exitRequest->requested_ip ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <span class="d-block primary_input_label">Navegador / Agente</span>
                            <p class="form-static-text text-muted" style="font-size:11px;word-break:break-all">
                                {{ $exitRequest->requested_user_agent ?? '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ============================================================ --}}
                {{-- Información de aprobación (solo si fue procesada) --}}
                {{-- ============================================================ --}}
                @if(!$exitRequest->isPending())
                <div class="form-section mb-25">
                    <h6 class="form-section-title">
                        <i class="fa fa-check-square-o"></i> {{ __('inventoryexit::messages.detail_approval_info') }}
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <span class="d-block primary_input_label">{{ __('inventoryexit::messages.col_approved_by') }}</span>
                            <p class="form-static-text">{{ $exitRequest->approvedBy?->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <span class="d-block primary_input_label">{{ __('inventoryexit::messages.approve_date') }}</span>
                            <p class="form-static-text">{{ $exitRequest->approved_at?->format('d/m/Y H:i') ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <span class="d-block primary_input_label">IP aprobación</span>
                            <p class="form-static-text text-muted">{{ $exitRequest->approved_ip ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="row mt-10">
                        <div class="col-md-12">
                            <span class="d-block primary_input_label">{{ __('inventoryexit::messages.approve_note') }}</span>
                            <p class="form-static-text">{{ $exitRequest->approval_note ?? '—' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- ============================================================ --}}
                {{-- Productos --}}
                {{-- ============================================================ --}}
                <div class="form-section mb-25">
                    <h6 class="form-section-title">
                        <i class="fa fa-cubes"></i> {{ __('inventoryexit::messages.detail_products') }}
                    </h6>
                    <div class="table-responsive ign-scrollbar">
                        <table class="table table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th>{{ __('inventoryexit::messages.col_sku') }}</th>
                                    <th>{{ __('inventoryexit::messages.col_product') }}</th>
                                    <th>{{ __('inventoryexit::messages.col_lot') }}</th>
                                    <th>{{ __('inventoryexit::messages.col_lot_expiry') }}</th>
                                    <th>{{ __('inventoryexit::messages.col_qty_requested') }}</th>
                                    @if(!$exitRequest->isPending())
                                    <th>{{ __('inventoryexit::messages.col_qty_approved') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($exitRequest->items as $item)
                                <tr>
                                    <td>{{ $item->productSku?->sku ?? '—' }}</td>
                                    <td>{{ $item->productSku?->product?->product_name ?? '—' }}</td>
                                    <td>{{ $item->lot?->lot_number ?? '—' }}</td>
                                    <td>
                                        @if($item->lot?->expiration_date)
                                            <span title="{{ $item->lot->expiration_date->format('d/m/Y') }}">
                                                {{ $item->lot->expiration_date->format('d/m/Y') }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ number_format($item->qty_requested, 2) }}</td>
                                    @if(!$exitRequest->isPending())
                                    <td>{{ $item->qty_approved !== null ? number_format($item->qty_approved, 2) : '—' }}</td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ============================================================ --}}
                {{-- Documentos soporte --}}
                {{-- ============================================================ --}}
                <div class="form-section">
                    <h6 class="form-section-title">
                        <i class="fa fa-paperclip"></i> {{ __('inventoryexit::messages.detail_documents') }}
                    </h6>
                    @if($exitRequest->documents->isEmpty())
                        <p class="text-muted">{{ __('inventoryexit::messages.no_documents') }}</p>
                    @else
                        <div class="row">
                            @foreach($exitRequest->documents as $doc)
                            <div class="col-md-4 mb-10">
                                <div class="border rounded p-10 d-flex align-items-center">
                                    <i class="fa fa-file-o fa-2x mr-10 text-muted"></i>
                                    <div style="overflow:hidden">
                                        <p class="mb-0" style="font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                                           title="{{ $doc->file_name }}">
                                            {{ $doc->file_name }}
                                        </p>
                                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                                           class="text-primary" style="font-size:11px">
                                            <i class="fa fa-download"></i> {{ __('inventoryexit::messages.download') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>{{-- /modal-body --}}

            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                    {{ __('inventoryexit::messages.btn_cancel') }}
                </button>
            </div>

        </div>
    </div>
</dialog>
