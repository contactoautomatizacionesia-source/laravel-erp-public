<div class="modal fade" id="evidenceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('incidents::messages.add_evidence') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <form id="form-evidence" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                       
                        <x-backEnd.file name="file_evidence" id="file_evidence" accept="image/jpeg,image/png,application/pdf" :required="true" :field="trans('incidents::messages.evidence_file')" />
                        
                    </div>
                    <div class="form-group">
                        <label class="primary_input_label" for="actor_role">{{ __('incidents::messages.actor_role') }}</label>
                        <select name="actor_role" id="actor_role" class="primary_input_select" required>
                            <option value="destination">{{ __('incidents::messages.role_destination') }}</option>
                            <option value="admin">{{ __('incidents::messages.role_admin') }}</option>
                            <option value="origin">{{ __('incidents::messages.role_origin') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="primary_input_label" for="notes">{{ __('incidents::messages.notes') }}</label>
                        <textarea name="notes" class="primary_textarea" rows="4" maxlength="1000" id="notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" id="btn-evidence-submit" class="btn-toolkit btn-primary">
                        <span id="btn-evidence-text">{{ __('incidents::messages.upload_evidence') }}</span>
                        <span id="btn-evidence-loader" class="d-none">
                            <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                            {{ __('incidents::messages.uploading') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
