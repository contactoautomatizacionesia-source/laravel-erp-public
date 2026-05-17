<div class="modal fade admin-query" id="cascadeConfirmModal" tabindex="-1" role="dialog" aria-labelledby="cascadeConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cascadeConfirmModalLabel">{{ __('common.confirm_inactivation') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="cascadeModalMessage">
                    {{ __('common.cascade_inactivation_prefix') }} <strong id="cascadeStatesCount">0</strong> {{ __('common.cascade_inactivation_states_label') }} {{ __('common.cascade_inactivation_and') }} <strong id="cascadeCitiesCount">0</strong> {{ __('common.cascade_inactivation_cities_linked_suffix') }} {{ __('common.cascade_inactivation_question') }}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="primary-btn tr-bg" data-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="primary-btn fix-gr-bg" id="cascadeConfirmBtn">{{ __('common.confirm') }}</button>
            </div>
        </div>
    </div>
</div>
