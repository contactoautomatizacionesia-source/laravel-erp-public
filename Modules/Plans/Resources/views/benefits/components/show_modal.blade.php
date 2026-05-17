<div class="modal fade" id="benefitShowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('common.benefit_detail') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <div class="modal-body">

                <div class="form-card">
                    <h3 class="">{{ __('common.details') }}</h3>

                    <h2 class="fs-20 text-black"><span id="show_b_code" class="" ></span> - <span id="show_b_title" class="" ></span></h2>


                    <p class="text-black mb-3" id="show_b_description"></p>

                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">{{ __('common.status') }}</span>
                            <span class="text-black" id="show_b_active"></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">{{ __('common.type') }}</span>
                            <span class="text-black" id="show_b_type_label"></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">{{ __('common.category') }}</span>
                            <span class="text-black" id="show_b_category"></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">{{ __('common.cumulative') }}</span>
                            <span id="show_b_cumulative"></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span class="text-muted">{{ __('common.benefit_assigned_subplans') }}</span>
                            <span class="badge_5" id="show_b_plans_count"></span>
                        </li>
                    </ul>
                </div>

                <div class="form-card">
                    <h3 class="">{{ __('common.configured_params') }}</h3>
                    <ul class="list-group list-group-flush" id="show_b_answers"></ul>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.close') }}</button>
            </div>
        </div>
    </div>
</div>
