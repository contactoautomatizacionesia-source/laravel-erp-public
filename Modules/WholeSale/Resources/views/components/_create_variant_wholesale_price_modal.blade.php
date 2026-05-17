<div class="modal fade admin-query" id="variant_wholesale_price_modal_{{ $modalTargetId }}">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content tk-modal-content">
            
            <div class="modal-header tk-modal-header">
                <h4 class="modal-title tk-modal-title"><i class="ti-layers mr-2"></i>{{ __('wholesale.Wholesale Price') }} ({{ __('common.create') }})</h4>
                <button type="button" class="close tk-btn-close" data-dismiss="modal">
                    <i class="ti-close"></i>
                </button>
            </div>

            <div class="modal-body py-3 px-md-4 px-3">
                <div class="form-card mb-0">
                    <h5 class="tk-section-title mb-3">{{ __('wholesale.wholesale_price_ranges') }}</h5>
                    
                    <div class="row d-none d-md-flex mb-2 pb-2" style="border-bottom: 2px solid var(--toolkit_secondary-blue-color);">
                        <div class="col-md-3"><span class="primary_input_label mb-0">{{ __('wholesale.Min QTY') }}</span></div>
                        <div class="col-md-3"><span class="primary_input_label mb-0">{{ __('wholesale.Max QTY') }}</span></div>
                        <div class="col-md-4"><span class="primary_input_label mb-0">{{ __('wholesale.Price per piece') }}</span></div>
                        <div class="col-md-2 text-center"><span class="primary_input_label mb-0">{{ __('common.action') }}</span></div>
                    </div>

                    <div id="repeat_{{$modalTargetId}}">
                        <div class="col-lg-12 mb-2 p-0">
                            <div class="row align-items-center">
                                <div class="col-12 col-md-3 mb-2 mb-md-0">
                                    <input type="number" class="form-control primary_input_field" placeholder="{{ __('wholesale.min_qty') }}" name="wholesale_min_qty_{{$incKey}}[]">
                                </div>
                                <div class="col-12 col-md-3 mb-2 mb-md-0">
                                    <input type="number" class="form-control primary_input_field" placeholder="{{ __('wholesale.max_qty') }}" name="wholesale_max_qty_{{$incKey}}[]">
                                </div>
                                <div class="col-12 col-md-4 mb-2 mb-md-0">
                                    <input type="number" step="0.01" class="form-control primary_input_field" placeholder="{{ __('wholesale.price_per_piece') }}" name="wholesale_price_{{$incKey}}[]">
                                </div>
                                <div class="col-12 col-md-2 text-center">
                                    <button type="button" data-id="#repeat_{{$modalTargetId}}" incKey="{{$incKey}}" class="btn-toolkit btn-sm add_variant__whole_sale_price" style="background-color: var(--toolkit_corporative-green-color); color: white; border-radius: 4px;">
                                        <i class="ti-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #eef0f3; background-color: #f8f9fa;">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn-toolkit btn-primary wholesale_p_save_btn" append_w_priceId="{{$modalTargetId}}" w_incKey="{{$incKey}}">
                    <i class="ti-check mr-2"></i>{{ __('common.save') }}
                </button>
            </div>
            
        </div>
    </div>
</div>
