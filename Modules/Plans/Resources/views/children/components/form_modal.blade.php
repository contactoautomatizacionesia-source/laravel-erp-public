<div class="modal fade" id="childFormModal" tabindex="-1"  aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="childFormModalLabel">{{ __('common.new_subplan') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <form id="childForm">
                @csrf
                <input type="hidden" name="child_id" id="child_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label class="primary_input_label" for="child_title">{{ __('common.subplan_title') }} <span class="text-danger">*</span></label>
                            <input type="text" class="primary_input_field" name="title" id="child_title" required placeholder="Ej: Life Platino">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="primary_input_label" for="child_level_order">{{ __('common.subplan_level_order') }} <span class="text-danger">*</span></label>
                            <input type="number" class="primary_input_field" name="level_order" id="child_level_order" required min="1" placeholder="1">
                            <small class="text-muted">{{ __('common.subplan_level_order_help') }}</small>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="primary_input_label" for="child_description">{{ __('common.description') }}</label>
                            <textarea class="primary_textarea" name="description" id="child_description" rows="2"></textarea>
                        </div>
                        <div class="col-md-12 form-group d-flex align-items-center">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" id="child_is_active" value="1" checked>
                                <label class="form-check-label font-weight-bold" for="child_is_active">{{ __('common.subplan_active') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn-toolkit btn-primary btn-icon"><i class="fa fa-save"></i>{{ __('common.save_subplan') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
