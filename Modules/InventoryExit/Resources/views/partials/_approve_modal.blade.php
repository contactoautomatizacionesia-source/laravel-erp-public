<dialog id="approveExitModal" class="modal fade" tabindex="-1" aria-labelledby="approveExitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="approveExitModalLabel">
                    <i class="fa fa-check-circle"></i> {{ __('inventoryexit::messages.approve_title') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="ti-close"></i>
                </button>
            </div>

            <form id="approveExitForm">
                @csrf
                <div class="modal-body">

                    {{-- Sección 1 — Responsable --}}
                    <div class="form-section mb-20">
                        <h6 class="form-section-title">
                            <i class="fa fa-user"></i> {{ __('inventoryexit::messages.approve_responsible') }}
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <span class="d-block primary_input_label">{{ __('inventoryexit::messages.responsible_user') }}</span>
                                <p class="form-static-text">
                                    <strong>{{ auth()->user()->name }}</strong>
                                    <small class="text-muted">({{ auth()->user()->role?->name ?? '—' }})</small>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <span class="d-block primary_input_label">{{ __('inventoryexit::messages.approve_date') }}</span>
                                <p class="form-static-text" id="approve_exit_date">—</p>
                            </div>
                        </div>
                    </div>

                    {{-- Sección 2 — Estado y observación --}}
                    <div class="form-section">
                        <div class="row">
                            <div class="col-md-12 mb-15">
                                <label class="primary_input_label" for="approve_status">
                                    {{ __('inventoryexit::messages.approve_status') }} <span class="text-danger">*</span>
                                </label>
                                <select id="approve_status" name="status" class="primary_input_select" required>
                                    <option value="">— Seleccionar —</option>
                                    <option value="approved">{{ __('inventoryexit::messages.option_approve') }}</option>
                                    <option value="rejected">{{ __('inventoryexit::messages.option_reject') }}</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="primary_input_label" for="approve_note">
                                    {{ __('inventoryexit::messages.approve_note') }} <span class="text-danger">*</span>
                                </label>
                                <textarea id="approve_note" name="approval_note" class="primary_textarea"
                                    rows="3"
                                    placeholder="{{ __('inventoryexit::messages.approve_note_placeholder') }}"
                                    required></textarea>
                            </div>
                        </div>
                    </div>

                </div>{{-- /modal-body --}}

                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                        {{ __('inventoryexit::messages.btn_cancel') }}
                    </button>
                    <button type="submit" class="btn-toolkit btn-primary">
                        <i class="fa fa-save"></i> {{ __('inventoryexit::messages.btn_apply') }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</dialog>
