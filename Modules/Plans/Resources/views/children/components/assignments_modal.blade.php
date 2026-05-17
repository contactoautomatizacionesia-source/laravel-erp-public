<div class="modal fade" id="assignmentsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">{{ __('common.assign_rules_benefits') }}</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">

                    {{-- REGLAS --}}
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-4 main-title">
                            <h3 class="mb-0"><i class="ti-ruler-pencil mr-1"></i> {{ __('common.Rules') }}</h3>
                            <button type="button" class="btn-toolkit btn-secondary btn-icon" id="btn-save-rules">
                                <i class="ti-save mr-1"></i> {{ __('common.save_rules') }}
                            </button>
                        </div>
                        <p class="text-muted mb-2">{{ __('common.rules_assignment_help') }}</p>
                        <div class="mb-2">
                            <input type="text" id="rules-search" class="primary_input_field" placeholder="{{ __('common.search') }}...">
                        </div>
                        <div id="rules-assignment-list" style="max-height:390px; overflow-y:auto;">
                            <div class="text-center py-3"><i class="ti-reload spin"></i> {{ __('common.loading_form') }}</div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('plans.rules.index') }}" class="btn-toolkit btn-ghost" target="_blank">
                                <i class="ti-plus mr-1"></i> {{ __('common.create_new_rule') }}
                            </a>
                        </div>
                    </div>

                    {{-- BENEFICIOS --}}
                    <div class="col-md-6 border-left">
                        <div class="d-flex justify-content-between align-items-center mb-4 main-title">
                            <h3 class=" mb-0"><i class="ti-gift mr-1"></i> {{ __('common.Benefits') }}</h3>
                            <button type="button" class="btn-toolkit btn-secondary btn-icon" id="btn-save-benefits">
                                <i class="ti-save mr-1"></i> {{ __('common.save_benefits') }}
                            </button>
                        </div>
                        <p class="text-muted  mb-2">{{ __('common.benefits_assignment_help') }}</p>
                        <div class="mb-2">
                            <input type="text" id="benefits-search" class="primary_input_field" placeholder="{{ __('common.search') }}...">
                        </div>
                        <div id="benefits-assignment-list" style="max-height:390px; overflow-y:auto;">
                            <div class="text-center py-3"><i class="ti-reload spin"></i> {{ __('common.loading_form') }}</div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('plans.benefits.index') }}" class="btn-toolkit btn-ghost" target="_blank">
                                <i class="ti-plus mr-1"></i> {{ __('common.create_new_benefit') }}
                            </a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.close') }}</button>
            </div>
        </div>
    </div>
</div>
