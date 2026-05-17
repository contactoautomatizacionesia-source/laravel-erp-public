<div class="modal fade" id="closingModal" tabindex="-1" >
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('incidents::messages.link_to_closing') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <form id="form-closing">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold" for="closing-id">
                            {{ __('incidents::messages.cash_closing_id') }} <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="closing-id" name="cash_closing_id" class="form-control"
                            placeholder="{{ __('incidents::messages.cash_closing_id_placeholder') }}" required min="1">
                        <small class="text-muted">{{ __('incidents::messages.cash_closing_hint') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-dark">{{ __('incidents::messages.confirm_link') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
