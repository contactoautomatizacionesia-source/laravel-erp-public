<div class="modal fade" id="planFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planFormModalLabel">{{ __('common.new_plan') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <form id="planForm">
                @csrf
                <input type="hidden" name="plan_id" id="plan_id">
                <div class="modal-body">
                    <div class="form-card">
                        <h3>{{__('common.plan_info')}}</h3>
                        <div class="row">
                            <div class="col-md-7 form-group">
                                <label class="primary_input_label" for="title">{{ __('common.plan_title') }} <span class="text-danger">*</span></label>
                                <input type="text" class="primary_input_field" name="title" id="title" required placeholder="Ej: Life">
                            </div>
                            <div class="col-md-5 form-group">
                                <label class="primary_input_label" for="order">{{ __('common.plan_order') }} <span class="text-danger">*</span></label>
                                <input type="number" class="primary_input_field" name="order" id="order" required min="1" placeholder="1">
                                <span class="primary_input_help">{{ __('common.plan_order_help') }}</span>
                            </div>
                            <div class="col-md-12 form-group">
                                <label class="primary_input_label" for="description">{{ __('common.description') }}</label>
                                <textarea class="primary_textarea" name="description" id="description" rows="3"></textarea>
                            </div>

                            <div class="col-md-4 form-group">
                                <label class="primary_input_label" for="scale_type">{{ __('common.plan_scale_type') }} <span class="text-danger">*</span></label>
                                <select class="primary_input_select" name="scale_type" id="scale_type" required>
                                    <option value="">{{ __('common.Select...') }}</option>
                                    <option value="CYCLE">{{ __('common.scale_type_cycle') }}</option>
                                    <option value="CUMULATIVE">{{ __('common.scale_type_cumulative') }}</option>
                                </select>
                            </div>

                            <div class="col-md-8 form-group">
                                <div class="row align-items-end">
                                    <div class="col-md-5">
                                        <x-backEnd.switch-toggle
                                            name="is_active"
                                            id="is_active"
                                            :label="__('common.plan_active')"
                                            :value="1"
                                            :checked="true"
                                        />
                                    </div>
                                    <div class="col-md-7">
                                        <input type="hidden" name="is_life_title" value="0">
                                        <label class="primary_checkbox d-flex mr-12 w-100 mb-2" for="is_life_title">
                                            <input type="checkbox" name="is_life_title" id="is_life_title" value="1">
                                            <span class="checkmark"></span>
                                            <p class="ml-2 mb-0">
                                                {{ __('common.life_network_help') }} <span class="text-danger">*</span>
                                            </p>
                                        </label>
                                        <span class="primary_input_help">{{ __('common.belongs_to_life_network') }} <span class="text-danger">*</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Imagen --}}
                            <div class="col-md-8 form-group">
                                <label class="primary_input_label" for="plan_image_name">{{ __('common.image') }}</label>
                                <div class="d-flex align-items-center" style="gap:12px;">
                                    <div id="plan_image_preview" style="width:60px;height:60px;border-radius:8px;border:1px solid #ddd;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f8f9fa;flex-shrink:0;">
                                        <i class="ti-image text-muted" id="plan_image_icon"></i>
                                        <img id="plan_image_thumb" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:none;">
                                    </div>
                                    <div class="flex-fill">
                                        <div class="primary_file_uploader">
                                            <input class="primary-input" type="text" id="plan_image_name" placeholder="{{ __('common.browse_image_file') }}" readonly>
                                            <button type="button">
                                                <label class="primary-btn small fix-gr-bg mb-0" for="plan_image_input">{{ __('common.browse') }}</label>
                                                <input type="file" id="plan_image_input" name="image" class="d-none" accept="image/*">
                                            </button>
                                        </div>
                                        <span class="primary_input_help">JPG, PNG, WEBP — máx. 2MB</span>
                                    </div>
                                    <button type="button" id="plan_image_clear" class="btn-toolkit btn-danger-outline btn-sm flex-shrink-0" style="display:none;" title="{{ __('common.remove') }}">
                                        <i class="ti-close"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="remove_image" id="remove_image" value="0">
                            </div>

                            {{-- Color --}}
                            <div class="col-md-4 form-group">
                                <label class="primary_input_label" for="plan_primary_color">{{ __('common.plan_primary_color') }}</label>
                                <div class="d-flex align-items-center" style="gap:8px;">
                                    <input type="color" id="plan_primary_color" name="primary_color" value="#4A90D9"
                                        style="width:44px;height:38px;padding:2px;border:1px solid #ddd;border-radius:6px;cursor:pointer;">
                                    <input type="text" class="primary_input_field" id="plan_primary_color_hex" placeholder="#4A90D9" maxlength="7">
                                </div>
                            </div>

                            {{-- Icono SVG --}}
                            <div class="col-md-12 form-group">
                                <label class="primary_input_label" for="plan_icon_textarea">{{ __('common.icon') }}</label>
                                <div class="d-flex align-items-start" style="gap:12px;">
                                    {{-- Preview --}}
                                    <div id="plan_icon_preview"
                                        style="width:60px;height:60px;flex-shrink:0;border:1px solid #ddd;border-radius:8px;
                                               background:#f8f9fa;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                                        <i class="ti-vector text-muted" id="plan_icon_placeholder"></i>
                                        <div id="plan_icon_render" style="width:44px;height:44px;display:none;"></div>
                                    </div>
                                    {{-- Textarea + actions --}}
                                    <div class="flex-fill">
                                        <textarea class="primary_textarea" name="icon" id="plan_icon_textarea"
                                            rows="4" placeholder="<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; viewBox=&quot;0 0 24 24&quot;>...</svg>"
                                            style="font-family:monospace;font-size:12px;resize:vertical;overflow:auto;"></textarea>
                                        <div class="d-flex align-items-center justify-content-between mt-1">
                                            <span class="primary_input_help" id="plan_icon_help">{{ __('common.icon_svg_help') }}</span>
                                            <button type="button" id="plan_icon_clear" class="btn-toolkit btn-danger-outline btn-sm" style="display:none;">
                                                <i class="ti-close mr-1"></i>{{ __('common.remove') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="remove_icon" id="remove_icon" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn-toolkit btn-primary btn-icon">{{ __('common.save_plan') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
