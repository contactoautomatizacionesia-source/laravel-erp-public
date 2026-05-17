<style>
.ign-radio-primary.custom-control-input:checked ~ .custom-control-label::before {
    background-color: var(--ign-btn-primary-bg, #007bff);
    border-color:     var(--ign-btn-primary-bg, #007bff);
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var ref = document.querySelector('.btn-primary');
    if (ref) {
        var bg = getComputedStyle(ref).backgroundColor;
        document.documentElement.style.setProperty('--ign-btn-primary-bg', bg);
    }
});
</script>

<div class="modal fade" id="statementModal" tabindex="-1">
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('incidents::messages.submit_statement') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <form id="form-statement" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <span class="primary_input_label" >{{ __('incidents::messages.statement_type_label') }}</span>
                        <div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="stmt-acknowledged" name="statement_type" value="acknowledged" class="custom-control-input ign-radio-primary" required>
                                <label class="custom-control-label" for="stmt-acknowledged">
                                    {{ __('incidents::messages.statement_acknowledged') }}
                                </label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="stmt-rejected" name="statement_type" value="rejected" class="custom-control-input ign-radio-primary">
                                <label class="custom-control-label" for="stmt-rejected">
                                    {{ __('incidents::messages.statement_rejected') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="primary_input_label" for="notes">{{ __('incidents::messages.notes') }}</label>
                        <textarea name="notes" id="notes" class="primary_textarea" rows="3" maxlength="2000"></textarea>
                    </div>
                    <div class="form-group" id="evidence-upload-group">
                        <x-backEnd.file name="file" id="file" accept="image/*,application/pdf" :required="true" :field="trans('incidents::messages.evidence_file')" />
                        <small class="text-muted">{{ __('incidents::messages.evidence_hint') }}</small>
                        <input type="hidden" name="actor_role" value="origin">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" id="btn-statement-submit" class="btn-toolkit btn-primary">
                        <span id="btn-statement-text">{{ __('incidents::messages.submit_statement') }}</span>
                        <span id="btn-statement-loader" class="d-none">
                            <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                            {{ __('incidents::messages.sending') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
