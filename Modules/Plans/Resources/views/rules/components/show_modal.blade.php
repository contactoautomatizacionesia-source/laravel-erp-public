<div class="modal fade" id="ruleShowModal" tabindex="-1"  aria-hidden="true">
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('common.rule_detail') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">

                <div class="d-flex align-items-center mb-3">
                    <span class="badge badge-secondary px-3 py-2 mr-3" style="font-size:1rem;" id="show_rule_code"></span>
                    <div>
                        <h5 class="mb-0" id="show_rule_title">-</h5>
                        <small class="text-muted">
                            {{ __('common.category') }}: <strong id="show_rule_category"></strong>
                            &nbsp;·&nbsp; {{ __('common.type') }}: <strong id="show_rule_type"></strong>
                        </small>
                    </div>
                    <div class="ml-auto" id="show_rule_active"></div>
                </div>

                <p class="text-muted small mb-3" id="show_rule_description"></p>

                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">{{ __('common.rule_assigned_subplans') }}</span>
                        <span class="badge badge-info text-white" id="show_rule_plans_count"></span>
                    </li>
                </ul>

                <h6 class="text-primary mb-2">{{ __('common.configured_params') }}</h6>
                <ul class="list-group list-group-flush" id="show_rule_answers"></ul>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.close') }}</button>
            </div>
        </div>
    </div>
</div>
