<div class="modal fade admin-query" id="rejection_reason_modal">
    <div class="modal-dialog modal_800px modal-dialog-centered">
        <div class="modal-content custom-modal-content">
            <div class="modal-header custom-modal-header">
                <h4 class="modal-title">{{ __('common.rejection_reason') }}</h4>
                <button type="button" class="close custom-close-btn" data-dismiss="modal">
                    <i class="ti-close"></i>
                </button>
            </div>
            <div class="modal-body custom-modal-body">
                <form id="edit_form" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="pending_approval_id">
                    <div class="row pending-approval-fields d-none" data-slug="daily-count-failures">
                        <div class="col-lg-12">
                            <div class="primary_input mb-25">
                                <label class="primary_input_label">{{ __('common.provide_cancellation_reason') }} <span class="text-dark">*</span></label>
                                <input
                                    type="text"
                                    name="rejection_reason"
                                    id="rejection_reason"
                                    class="primary_input_field"
                                    placeholder="{{ __('common.write_rejection_reason') }}"
                                    pattern="^(?!.*(?:SELECT|INSERT|UPDATE|DELETE|DROP|TRUNCATE|ALTER|CREATE|EXEC|UNION|CAST|CONVERT|DECLARE|FETCH|CURSOR|KILL|BACKUP|RESTORE|GRANT|REVOKE|XP_)).*\S.*"
                                    required
                                >
                            </div>
                        </div>
                    </div>
                    <div class="row mt-10 footer-btn-container">
                        <div class="col-lg-12">
                            <button type="submit" class="primary-btn fix-gr-bg w-100 modern-submit-btn">
                                <span class="btn-text">{{ __('common.confirm_rejection') }}</span>
                                <i class="ti-check"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
