<!-- Modal de confirmación con datos dinámicos -->
<div class="modal fade" id="double_approval_moda" tabindex="-1" role="dialog"
    data-backdrop="static" 
    data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">{{ __('common.double_approval_request') }}</h5>
                <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="ti-close "></i>
                </button> -->
            </div>
            <div class="modal-body">
                <!-- Información inicial -->
                <div id="approval_info">
                    <p><strong>{{ __('common.pending_approval_request_message') }} {{ __('common.information') }}:</strong></p>
                    <p id="new_data"></p>
                </div>

                <!-- Campo de razones (oculto inicialmente) -->
                <div id="cancel_reason_container" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="ti-alert"></i> {{ __('common.provide_cancellation_reason') }}
                    </div>
                    <div class="form-group">
                        <label for="cancel_reason" class="primary_input_label">
                            {{ __('common.rejection_reason') }} <span class="text-danger">*</span>
                        </label>
                        <textarea
                            class="primary_input_field form-control"
                            id="cancel_reason"
                            name="cancel_reason"
                            rows="4"
                            placeholder="{{ __('common.enter_reason') }}"
                        ></textarea>
                        <span class="text-danger" id="error_cancel_reason"></span>
                    </div>
                </div>

                <input type="hidden" id="modal_item_id">
            </div>
            <div class="modal-footer">
                <!-- Botones iniciales -->
                <div id="initial_buttons" class="mt-10 d-flex w-100 justify-content-between">
                    <button type="button" class="btn btn-danger fix-rd-bg" id="cancel_approved_btn">
                        {{ __('common.reject') }}
                    </button>
                    <button type="button" class="btn primary-btn fix-gr-bg" id="confirm_approved_btn">
                        {{ __('common.confirm') }}
                    </button>
                </div>

                <!-- Botones cuando se muestra el campo de razones (ocultos inicialmente) -->
                <div id="cancel_buttons" style="display: none;" class="mt-10 w-100 justify-content-between">
                    <button type="button" class="primary-btn" id="back_to_approval_btn">
                        <i class="ti-arrow-left"></i> {{ __('common.back') }}
                    </button>
                    <button type="button" class="primary-btn" id="confirm_cancel_btn">
                        <i class="ti-check"></i> {{ __('common.confirm_rejection') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
