<style>
.wizard-step { display:flex; flex-direction:column; align-items:center; cursor:default; min-width:80px; }
.wizard-circle { width:38px; height:38px; border-radius:50%; background:#dee2e6; color:#6c757d; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.95rem; transition:background .2s, color .2s; }
.wizard-step.active .wizard-circle { background:var(--toolkit_corporative-orange-color); color:#fff; }
.wizard-label { margin-top:5px; color:#adb5bd; font-size:.72rem; letter-spacing:.04em; transition:color .2s; }
.wizard-step.active .wizard-label { color:var(--toolkit_base_color_dark_green); font-weight:600; }
.wizard-connector { width:60px; height:2px; background:#dee2e6; margin-bottom:22px; border-radius:1px; flex-shrink:0; }

#benefit-dynamic-form{min-height: 300px}
</style>
<div class="modal fade" id="benefitFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="benefitFormModalLabel">{{ __('common.new_benefit') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <form id="benefitForm">
                @csrf
                <input type="hidden" id="benefit_id">

                <div class="modal-body">
                    {{-- Wizard header --}}
                    <div class="d-flex align-items-center justify-content-center mb-1 pb-1 ">
                        <div class="b-step-header wizard-step active" data-step="1">
                            <div class="wizard-circle">1</div>
                            <small class="wizard-label">{{ __('common.step_information') }}</small>
                        </div>
                        <div class="wizard-connector mx-3"></div>
                        <div class="b-step-header wizard-step" data-step="2">
                            <div class="wizard-circle">2</div>
                            <small class="wizard-label">{{ __('common.step_parameters') }}</small>
                        </div>
                    </div>

                    {{-- Step 1: Basic info --}}
                    <div class="b-wizard-step" id="b-step-1">
                        <div class="form-card">
                            <h3>{{__('common.benefit_info')}}</h3>
                            <div class="row">
                                <div class="col-lg-3 form-group">
                                    <label class="primary_input_label" for="b_code">{{ __('common.code') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="primary_input_field" id="b_code" name="code" maxlength="20" required placeholder="Ej: B1">
                                    <span class="primary_input_help">{{ __('common.rule_code_help') }}</span>
                                </div>
                                <div class="col-lg-6 form-group">
                                    <label class="primary_input_label" for="b_title">{{ __('common.benefit_title') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="primary_input_field" id="b_title" name="title" maxlength="100" required placeholder="{{ __('common.example_benefit_title_placeholder') }}">
                                </div>
                                <div class="col-lg-3 form-group d-flex align-items-end">
                                    <div class="mx-3">
                                        <x-backEnd.switch-toggle
                                        name="is_cumulative"
                                        id="b_is_cumulative"
                                        :label="__('common.benefit_cumulative')"
                                        :value="1"
                                        :checked="true"
                                    />
                                    </div>
                                    <div class="mx-3">
                                        <x-backEnd.switch-toggle
                                        name="is_active"
                                        id="b_is_active"
                                        :label="__('common.benefit_active')"
                                        :value="1"
                                        :checked="true"
                                        />
                                    </div>
                                </div>
                             
                                <div class="col-md-12 form-group">
                                    <label class="primary_input_label" for="b_description">{{ __('common.description') }}</label>
                                    <textarea class="primary_textarea" id="b_description" name="description" rows="3" placeholder="{{ __('common.example_benefit_description_placeholder') }}"></textarea>
                                </div>
                                <div class="col-lg-8 form-group">
                                    <label class="primary_input_label" for="benefit_category_id">{{ __('common.benefit_category') }} <span class="text-danger">*</span></label>
                                    <select class="primary_input_select" id="benefit_category_id" name="benefit_category_id" required>
                                        <option value="">{{ __('common.rule_category_select') }}</option>
                                        @foreach($categories->groupBy(fn($c) => $c->type ? $c->type->label : 'Sin tipo') as $typeName => $cats)
                                            <optgroup label="{{ $typeName }}">
                                                @foreach($cats as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <span class="primary_input_help">{{ __('common.benefit_category_help') }}</span>
                                    <div id="benefit-category-description" class="badge_5 py-3 mt-3" style="font-size:.82rem; display:none;"></div>
                                    @php
                                        $benefitCategoryDescMap = $categories->mapWithKeys(fn($c) => [(string)$c->id => $c->getTranslations('description')])->toArray();
                                    @endphp
                                    <script>
                                        (function() {
                                            var map = @json($benefitCategoryDescMap);
                                            $(document).on('change', '#benefit_category_id', function() {
                                                var desc = map[this.value];
                                                var lang = document.documentElement.lang || 'es';
                                                var text = desc ? (desc[lang] || desc['es'] || '') : '';
                                                var $el = $('#benefit-category-description');
                                                text ? $el.text(text).show() : $el.hide();
                                            });
                                        })();
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Dynamic form --}}
                    <div class="b-wizard-step d-none" id="b-step-2">
                        <div id="benefit-dynamic-form">
                            <div class="text-center text-muted py-4">
                                <i class="ti-info-alt mr-1"></i> {{ __('common.select_category_hint') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between flex-column flex-md-row align-items-center border-0 w-100 mt-2">
                    <div style="min-width:100px;">
                        <button type="button" class="btn-toolkit btn-ghost btn-icon d-none" id="b-btn-prev">
                            <i class="ti-arrow-left mr-1"></i> {{ __('common.btn_prev') }}
                        </button>
                    </div>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn-toolkit btn-secondary-outline mr-2" data-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="button" class="btn-toolkit btn-primary btn-icon" id="b-btn-next">
                            {{ __('common.btn_next') }} <i class="ti-arrow-right ml-1"></i>
                        </button>
                        <button type="submit" class="btn-toolkit btn-primary btn-icon d-none" id="b-btn-save">
                            <i class="ti-save mr-1"></i> {{ __('common.save_benefit') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
