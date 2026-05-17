<div class="modal fade" id="planShowModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('common.plan_detail') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div>
                        <h5 class="mb-0" id="show_plan_title"></h5>
                        <small class="text-muted">
                            {{ __('common.scale') }}: <strong id="show_plan_scale"></strong>
                            &nbsp;·&nbsp; Orden: <strong id="show_plan_order"></strong>
                        </small>
                    </div>
                    <div class="ml-auto" id="show_plan_active"></div>
                </div>
                <p class="text-muted small mb-3" id="show_plan_description"></p>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">{{ __('common.subplans') }}</span>
                        <span class="badge badge-info text-white" id="show_plan_children_count"></span>
                    </li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.close') }}</button>
            </div>
        </div>
    </div>
</div>
