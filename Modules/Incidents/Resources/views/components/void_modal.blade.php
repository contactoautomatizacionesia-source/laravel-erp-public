<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('incidents::messages.void_incident') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <form id="form-void">
                <div class="modal-body">
                    <div class="alert alert-warning">{{ __('incidents::messages.void_warning') }}</div>
                    <div class="form-group">
                        <label class="primary_input_label" for="void-reason">
                            {{ __('incidents::messages.void_reason') }} <span class="text-danger">*</span>
                        </label>
                        <textarea id="void-reason" name="reason" class="primary_textarea" rows="3"
                            minlength="5" maxlength="2000" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" id="btn-void-submit" class="btn-toolkit btn-primary">
                        <span id="btn-void-text">{{ __('incidents::messages.confirm_void') }}</span>
                        <span id="btn-void-loader" class="d-none">
                            <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                            {{ __('incidents::messages.sending') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$('#voidModal').on('show.bs.modal', function () {
    $('#btn-void-submit').prop('disabled', false);
    $('#btn-void-text').removeClass('d-none');
    $('#btn-void-loader').addClass('d-none');
});
</script>
@endpush
