<style>
.wizard-step { display:flex; flex-direction:column; align-items:center; cursor:default; min-width:80px; }
.wizard-circle { width:38px; height:38px; border-radius:50%; background:#dee2e6; color:#6c757d; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.95rem; transition:background .2s, color .2s; }
.wizard-step.active .wizard-circle { background:var(--toolkit_corporative-orange-color); color:#fff; }
.wizard-label { margin-top:5px; color:#adb5bd; font-size:.72rem; letter-spacing:.04em; transition:color .2s; }
.wizard-step.active .wizard-label { color:var(--toolkit_base_color_dark_green); font-weight:600; }
.wizard-connector { width:60px; height:2px; background:#dee2e6; margin-bottom:22px; border-radius:1px; flex-shrink:0; }

#dynamic-form-container{min-height: 300px}
</style>
<div class="modal fade" id="ruleFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ruleFormModalLabel">{{ __('common.new_rule') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <form id="ruleForm">
                @csrf
                <input type="hidden" id="rule_id">

                {{-- Wizard header --}}
                <div class="modal-body">
                    <div class="d-flex align-items-center justify-content-center mb-1 pb-1 " id="rule-wizard-headers">
                        <div class="rule-step-header wizard-step active" data-step="1">
                            <div class="wizard-circle">1</div>
                            <small class="wizard-label">{{ __('common.step_information') }}</small>
                        </div>
                        <div class="wizard-connector mx-3"></div>
                        <div class="rule-step-header wizard-step" data-step="2">
                            <div class="wizard-circle">2</div>
                            <small class="wizard-label">{{ __('common.step_parameters') }}</small>
                        </div>
                    </div>

                    {{-- Step 1: Basic info --}}
                    <div class="rule-wizard-step" id="rule-step-1">
                        <div class="form-card">
                            <h3>{{__('common.rule_info')}}</h3>
                            <div class="row">
                                <div class="col-lg-4 form-group">
                                    <label class="primary_input_label" for="code">{{ __('common.code') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="primary_input_field" id="code" name="code" maxlength="20" required placeholder="Ej: R1">
                                    <span class="primary_input_help">{{ __('common.rule_code_help') }}</span>
                                </div>
                                <div class="col-lg-6 form-group">
                                    <label class="primary_input_label" for="title">{{ __('common.title') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="primary_input_field" id="title" name="title" maxlength="100" required placeholder="{{ __('common.example_rule_title_placeholder') }}">
                                </div>
                                <div class="col-md-2 form-group">
                                    <x-backEnd.switch-toggle
                                    name="is_active"
                                    id="is_active"
                                    :label="__('common.rule_active')"
                                    :value="1"
                                    :checked="true"
                                    />
                                </div>
                                
                                <div class="col-md-12 form-group">
                                    <label class="primary_input_label" for="description">{{ __('common.description') }}</label>
                                    <textarea class="primary_textarea" id="description" name="description" rows="2" placeholder="{{ __('common.description_rule_help') }}"></textarea>
                                </div>
                                <div class="col-lg-8 form-group">
                                    <label class="primary_input_label" for="rule_category_id">{{ __('common.rule_category') }} <span class="text-danger">*</span></label>
                                    <select class="primary_input_select" id="rule_category_id" name="rule_category_id" required>
                                        <option value="">{{ __('common.rule_category_select') }}</option>
                                        @foreach($categories->groupBy(fn($c) => $c->type ? $c->type->label : __('common.no_type')) as $typeName => $cats)
                                            <optgroup label="{{ $typeName }}">
                                                @foreach($cats as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <span class="primary_input_help">{{ __('common.rule_category_help') }}</span>
                                    <div id="rule-category-description" class="badge_5 py-3 mt-3" style="display:none;"></div>
                                    @php
                                        $ruleCategoryDescMap = $categories->mapWithKeys(fn($c) => [(string)$c->id => $c->getTranslations('description')])->toArray();
                                    @endphp
                                    <script>
                                        (function() {
                                            var map = @json($ruleCategoryDescMap);
                                            $(document).on('change', '#rule_category_id', function() {
                                                var desc = map[this.value];
                                                var lang = document.documentElement.lang || 'es';
                                                var text = desc ? (desc[lang] || desc['es'] || '') : '';
                                                var $el = $('#rule-category-description');
                                                text ? $el.text(text).show() : $el.hide();
                                            });
                                        })();
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Dynamic form --}}
                    <div class="rule-wizard-step d-none" id="rule-step-2">
                        <div id="dynamic-form-container">
                            <div class="text-center text-muted py-4">
                                <i class="ti-info-alt mr-1"></i> {{ __('common.select_category_hint') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between flex-column flex-md-row align-items-center border-0 w-100 mt-2">
                    <div style="min-width:100px;">
                        <button type="button" class="btn-toolkit btn-ghost btn-icon d-none" id="rule-btn-prev">
                            <i class="ti-arrow-left mr-1"></i> {{ __('common.btn_prev') }}
                        </button>
                    </div>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn-toolkit btn-secondary-outline mr-2" data-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="button" class="btn-toolkit btn-primary btn-icon" id="rule-btn-next">
                            {{ __('common.btn_next') }} <i class="ti-arrow-right ml-1"></i>
                        </button>
                        <button type="submit" class="btn-toolkit btn-primary d-none" id="rule-btn-save">
                            <i class="ti-save mr-1"></i> {{ __('common.save_rule') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
