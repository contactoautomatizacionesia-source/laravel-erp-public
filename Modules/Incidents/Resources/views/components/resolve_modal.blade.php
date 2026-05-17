<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('incidents::messages.resolve_incident') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <form id="form-resolve">
                <div class="modal-body">
                    <input type="hidden" id="resolve-party" name="resolution_party">
                    <div class="alert alert-info" id="resolve-info-text"></div>
                    <div class="form-group">
                        <label class="primary_input_label" for="resolve-notes">
                            {{ __('incidents::messages.resolution_notes') }} <span class="text-danger">*</span>
                        </label>
                        <textarea id="resolve-notes" name="resolution_notes" class="primary_textarea" rows="4"
                            placeholder="{{ __('incidents::messages.resolution_notes_placeholder') }}"
                            minlength="10" maxlength="2000" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" id="btn-resolve-submit" class="btn-toolkit btn-primary">
                        <span id="btn-resolve-text">{{ __('incidents::messages.confirm_resolution') }}</span>
                        <span id="btn-resolve-loader" class="d-none">
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
$('#resolveModal').on('show.bs.modal', function (e) {
    const party = $(e.relatedTarget).data('party') || $('#resolve-party').val();
    $('#resolve-party').val(party);
    const messages = {
        advisor:      '{{ __("incidents::messages.resolve_info_advisor") }}',
        organization: '{{ __("incidents::messages.resolve_info_organization") }}',
    };
    $('#resolve-info-text').text(messages[party] || '');
    // Resetear estado del botón al abrir
    $('#btn-resolve-submit').prop('disabled', false);
    $('#btn-resolve-text').removeClass('d-none');
    $('#btn-resolve-loader').addClass('d-none');
});
</script>
@endpush
