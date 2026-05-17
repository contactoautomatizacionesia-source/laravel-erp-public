<div class="modal fade" id="productSelectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-white border-0 py-4 px-5">
                <div class="d-flex align-items-center">
                    <div class="modal-icon mr-4 shadow-sm btn-toolkit btn-primary btn-sm py-2" >
                        <i class="ti-package fs-20"></i>
                    </div>
                    <div>
                        <h5 class="modal-title  mb-0 text-dark">{{ __('costcenter::inventory.select_products') }}</h5>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 28px; opacity: 0.5;">
                    <i class="ti-close"></i>
                </button>
            </div>
            <div class="modal-body p-0 d-flex flex-column">
                

                <div class="mobile-tabs-nav d-md-none border-bottom bg-white sticky-top" style="z-index: 104; top: 0px;">
                    <ul class="nav nav-tabs nav-fill border-0" id="modalTransferTabs">
                        
                        <li class="nav-item">
                            <a class="nav-link py-3 active border-0" id="modal-products-tab" data-toggle="tab" href="#modal-products-panel" role="tab" style="font-weight: 700; font-size: 13px; text-transform: uppercase; color: #64748b;">
                                <i class="ti-layout-grid2 mr-2"></i> {{ __('costcenter::inventory.products') ?? 'Productos' }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link  py-3 border-0" id="modal-filters-tab" data-toggle="tab" href="#modal-filters-panel" role="tab" style="font-weight: 700; font-size: 13px; text-transform: uppercase; color: #64748b;">
                                <i class="ti-info-alt mr-2"></i> {{ __('common.details') ?? 'Detalles' }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="row no-gutters flex-grow-1 tab-content overflow-hidden" id="modalTransferTabContent">
                    {{-- Sidebar Filtros (Detalles) --}}
                    <div class="col-md-3 bg-light-soft border-right p-lg-4 p-2 flex-column tab-pane  d-md-flex" id="modal-filters-panel" role="tabpanel" style="min-height: 65vh; background-color: #f8fafc;">
                        <div class="form-group mb-4 d-none d-md-block">
                            <label class="info-label mb-2" for="modalProductSearch">{{ __('common.search') }}</label>
                            <div class="search-input-wrapper position-relative">
                                <i class="ti-search position-absolute" style="left: 15px; top: 12px; color: #94a3b8; z-index: 5;"></i>
                                <input type="text" id="modalProductSearch" class="form-control border-0 shadow-sm" style="padding-left: 40px; border-radius: 12px; height: 45px;" placeholder="{{ __('costcenter::inventory.search_placeholder') ?? 'Buscar productos...' }}">
                            </div>
                        </div>
                        
                        <div class="form-card">
                            <h3 class="">{{ __('costcenter::inventory.current_selection') ?? 'Selección Actual' }}</h3>
                            <div class="d-flex justify-content-between mb-2 align-items-center ">
                                <span class=" text-muted">{{ __('costcenter::inventory.items') }}:</span>
                                <span class="badge_6" id="modalSelectedCount">0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center ">
                                <span class=" text-muted">{{ __('costcenter::inventory.units') }}:</span>
                                <span class="badge_6" id="modalTotalUnits">0</span>
                            </div>
                        </div>

                        <div class="form-card">
                            <h3 class="">{{ __('costcenter::inventory.route') ?? 'Ruta de Envío' }}</h3>
                            <div class="">
                                <div class=" flex-grow-1 mb-3">
                                    <span class="d-block  text-muted " >{{ __('costcenter::inventory.origin') }}</span>
                                    <span class="font-weight-bold text-black" id="modalOriginName" style="font-size: 11px; line-height: 1.2; display: block;">---</span>
                                </div>
                                {{-- <div class="px-2">
                                    <i class="ti-arrow-right text-muted" style="font-size: 18px; opacity: 0.5;"></i>
                                </div> --}}
                                <div class=" flex-grow-1">
                                    <span class="d-block  text-muted " >{{ __('costcenter::inventory.destination') }}</span>
                                    <span class="font-weight-bold text-black" id="modalDestinationName" style="font-size: 11px; line-height: 1.2; display: block;">---</span>
                                </div>
                            </div>
                        </div>

                        <div class="shadow-top d-none d-md-block">
                            <button type="button" class="btn-toolkit btn-primary btn-icon w-100 justify-content-center" data-dismiss="modal" style="border-radius: 12px; font-weight: 700;">
                                <i class="ti-check mr-2"></i> {{ __('common.done') ?? 'Listo' }}
                            </button>
                        </div>
                    </div>

                    {{-- Lista de Productos --}}
                    <div class="col-md-9 flex-column bg-white tab-pane d-md-flex active" id="modal-products-panel" role="tabpanel" style="height: 75vh;">
                        {{-- Sticky Search Bar for Mobile --}}
                        <div class="px-4 py-3 border-bottom bg-white d-md-none">
                            <div class="search-input-wrapper position-relative">
                                <input type="text" class="form-control" id="modalProductSearchMobile" placeholder="{{ __('common.search') }}..." style="border-radius: 10px; padding-left: 45px;">
                                <i class="ti-search position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 16px;"></i>
                            </div>
                        </div>
                        {{-- Estado: Cargando --}}
                        <div id="modalLoadingState" class="flex-grow-1 d-flex flex-column align-items-center justify-content-center p-5 text-center d-none">
                            <div class="mb-5 position-relative" style="width: 150px; height: 80px;">
                                <div style="transform: rotateY(180deg);">
                                    <i class="ti-shopping-cart display-4 cart-moving" ></i>
                                </div>
                                <div class="dots-trail">
                                    <span class="dot d1"></span>
                                    <span class="dot d2"></span>
                                    <span class="dot d3"></span>
                                </div>
                            </div>
                            <h5 class="font-weight-bold text-dark mb-1">{{ __('costcenter::inventory.loading_products') }}</h5>
                            <p class="small text-muted">{{ __('costcenter::inventory.verify_details_before_confirm') }}</p>
                        </div>

                        {{-- Estado: Vacío / No encontrado --}}
                        <div id="modalEmptyState" class="flex-grow-1 flex-column align-items-center justify-content-center text-muted p-5 text-center d-none">
                            <div class="mb-4 bg-light-soft rounded-circle p-4">
                                <i class="ti-shopping-cart display-4 opacity-25 text-primary"></i>
                            </div>
                            <h5 class="font-weight-bold text-dark mb-1">{{ __('costcenter::inventory.no_products_found') }}</h5>
                            <p class="small">{{ __('costcenter::inventory.try_adjust_search') }}</p>
                        </div>

                        

                        <div id="modalProductsList" class="flex-grow-1 p-4 d-none" style="overflow-y: auto;">
                            <!-- Products will be rendered here dynamically -->
                        </div>

                        {{-- Floating Done Button for Mobile --}}
                        <button type="button" class="btn btn-primary d-md-none mobile-modal-done-btn" data-dismiss="modal">
                            <i class="ti-check" style="font-size: 24px;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-product-card {
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
    .modal-product-header {
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        padding: 12px 15px;
    }
    .modal-product-header:hover {
        background-color: #f1f5f9;
    }
    .modal-skus-container {
        transition: max-height 0.4s ease-out, opacity 0.3s;
        max-height: 2000px; /* Suficiente para acomodar listas largas */
        opacity: 1;
    }
    .modal-product-card.is-collapsed .modal-skus-container {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        padding: 0 !important;
    }
    .toggle-icon {
        transition: transform 0.3s ease;
        margin-left: auto;
        color: #94a3b8;
    }
    .modal-product-card.is-collapsed .toggle-icon {
        transform: rotate(-180deg);
    }
    .modal-product-card.has-selection {
        border-color: var(--base_color);
        background-color: #f8fafc;
    }
    .header-badges-container {
        display: flex;
        gap: 8px;
        margin-right: 15px;
    }
    .badge-req {
        transition: all 0.3s;
    }
    .badge-req.has-val {
        background-color: var(--base_color);
        color: #fff !important;
        border-color: var(--base_color);
        box-shadow: 0 0 0 2px rgba(var(--base_color_rgb), 0.2);
    }
    /* Estilos para que el input se vea premium */
    .lot-exposed-row input[type=number]::-webkit-inner-spin-button, 
    .lot-exposed-row input[type=number]::-webkit-outer-spin-button {  
        opacity: 1;
        height: 25px;
    }
    .lot-exposed-row:hover {
        background-color: #fff !important; /* Resalta sutilmente la fila al pasar el mouse */
        border-radius: 8px;
    }
</style>
