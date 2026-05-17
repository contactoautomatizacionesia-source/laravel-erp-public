<div class="modal fade" id="confirmTransferModal" tabindex="-1" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header py-3">
                <div class="d-flex align-items-center">
                    <div class="modal-icon mr-3" style="background: var(--toolkit_corporative-orange-color); color: #fff; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="ti-check-box fs-20"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0">{{ __('costcenter::inventory.transfer_summary') }}</h5>
                        <small class="text-white">{{ __('costcenter::inventory.verify_details_before_confirm') }}</small>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="ti-close"></i>
                </button>
            </div>
            <div class="modal-body p-4">
                <div id="confirmStepIntro" class="text-center py-5">
                    <div class="mb-4">
                        <i class="ti-alert display-3 text-warning opacity-50"></i>
                    </div>
                    <h4 class="font-weight-bold mb-2">{{ __('costcenter::inventory.verify_transfer_details') }}</h4>
                    <p class="text-muted mb-4">{{ __('costcenter::inventory.action_modifies_inventory') }}</p>
                    <div class="d-inline-block px-4 py-2 bg-light rounded-pill">
                        <span class="small text-muted">{{ __('costcenter::inventory.summary_will_show_in') }}</span>
                        <span id="confirmCountdown" class="font-weight-bold text-primary ml-1">10</span>
                        <span class="small text-muted">{{ __('costcenter::inventory.seconds_short') }}</span>
                    </div>
                </div>

                <div id="confirmStepSummary" class="d-none animate-fade-in">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="section-card h-100">
                                <h6 class="text-uppercase small font-weight-bold text-muted mb-3 border-bottom pb-2">
                                    <i class="ti-map-alt mr-2"></i> {{ __('costcenter::inventory.route') }}
                                </h6>
                                <div class="mb-3">
                                    <span class="info-label">{{ __('costcenter::inventory.origin') }}</span>
                                    <span class="info-value text-primary"><i class="ti-location-pin mr-1"></i> <span id="summaryOrigin"></span></span>
                                </div>
                                <div class="mb-0">
                                    <span class="info-label">{{ __('costcenter::inventory.destination') }}</span>
                                    <span class="info-value text-success"><i class="ti-location-arrow mr-1"></i> <span id="summaryDestination"></span></span>
                                </div>
                                <div class="col-12">
                                    <span class="info-label">{{ __('costcenter::inventory.reason') }}</span>
                                    <span class="info-value text-muted small" id="summaryReason"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="section-card h-100">
                                <h6 class="text-uppercase small font-weight-bold text-muted mb-3 border-bottom pb-2">
                                    <i class="ti-info-alt mr-2"></i> {{ __('costcenter::inventory.logistics_data') ?? 'Datos Logísticos' }}
                                </h6>
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <span class="info-label">{{ __('costcenter::inventory.movement_type') }}</span>
                                        <span class="text-dark" id="summaryMovementType"></span>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <span class="info-label">{{ __('costcenter::inventory.shipping_guide') }}</span>
                                        <span class="info-value font-italic" id="summaryShippingGuide"></span>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <span class="info-label">{{ __('costcenter::inventory.carrier') ?? 'Transportista' }}</span>
                                        <span class="info-value" id="summaryCarrier"></span>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <span class="info-label">{{ __('costcenter::inventory.guide_date') ?? 'Fecha de Guía' }}</span>
                                        <span class="info-value" id="summaryGuideDate"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive rounded border mb-0" style="max-height: 250px;">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 small font-weight-bold text-muted">{{ __('costcenter::inventory.sku') }}</th>
                                    <th class="border-0 small font-weight-bold text-muted">{{ __('common.product') }}</th>
                                    <th class="border-0 small font-weight-bold text-muted">{{ __('product.lot') }}</th>
                                    <th class="border-0 small font-weight-bold text-muted">{{ __('inventoryentry::inventory.expiration_date') }}</th>
                                    <th class="border-0 small font-weight-bold text-muted text-center">{{ __('common.quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody id="summaryProductsTable" class="small">
                                <!-- Data injected via JS -->
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-light p-3 d-flex justify-content-between align-items-center rounded-bottom border border-top-0">
                        <span class="font-weight-bold text-muted small text-uppercase">{{ __('costcenter::inventory.total_to_transfer') }}</span>
                        <div class="badge badge-toolkit bg-primary text-white py-2 px-4 fs-14">
                            <span id="summaryTotalQty" class="mr-2">0</span> {{ __('costcenter::inventory.items') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 py-3">
                <button type="button" class="btn-toolkit btn-secondary-outline border-0 bg-transparent" data-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn-toolkit btn-primary px-5 shadow-sm" id="confirmTransferBtn">
                    <i class="ti-eye mr-2"></i> {{ __('costcenter::inventory.view_summary') }}
                </button>
            </div>
        </div>
    </div>
</div>


