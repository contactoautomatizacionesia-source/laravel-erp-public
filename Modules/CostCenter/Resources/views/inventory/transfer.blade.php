@extends('backEnd.master')

@section('mainContent')
<link rel="stylesheet" href="{{ asset('public/css/const-center.css') }}">

<x-admin.section class="tranfer-index">
<div class="container-fluid p-0 wrap-transfer" >
    <form id="transferForm" method="POST" action="{{ route('cost_centers.inventory.process') }}" class="h-100 d-flex flex-column">
        @csrf
        @php
            $user = auth()->user();
            $isGlobal = in_array($user->role_id, [1, 2]) || empty($user->cost_center_id);
            $assignedCenterId = $user->cost_center_id;
            
            $defaultOrigin = '';
            if (isset($preselectedCenterId) && $preselectedCenterId) {
                $defaultOrigin = 'center-' . $preselectedCenterId;
            } elseif ($isGlobal) {
                $defaultOrigin = 'main';
            } elseif ($assignedCenterId) {
                $defaultOrigin = 'center-' . $assignedCenterId;
            }
        @endphp

        {{-- Header Bar --}}
        <div class="px-4 py-2  bg-white d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center main-title">
                <x-backEnd.back-button :text="false" />
                <h3 class="">{{ __('costcenter::inventory.inventory_management') }}</h3>
            </div>
        </div>

        <div class="mobile-tabs-nav d-md-none border-bottom bg-white">
            <ul class="nav nav-tabs nav-fill border-0" id="transferTabs">
                <li class="nav-item">
                    <a class="nav-link active py-3 border-0" id="logistics-tab" data-toggle="tab" href="#logistics-panel" role="tab" style="font-weight: 700; font-size: 13px; text-transform: uppercase; color: #64748b;">
                        <i class="ti-truck mr-2"></i> {{ __('costcenter::inventory.logistics') ?? 'Logística' }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 border-0" id="products-tab" data-toggle="tab" href="#products-panel" role="tab" style="font-weight: 700; font-size: 13px; text-transform: uppercase; color: #64748b;">
                        <i class="ti-package mr-2"></i> {{ __('costcenter::inventory.products') ?? 'Productos' }}
                        <span class="badge badge-pill badge-primary ml-1 d-none" id="mobileItemsBadge" style="font-size: 10px; padding: 3px 6px;">0</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="row flex-grow-1 overflow-hidden tab-content mx-0 pt-2" id="transferTabContent">
            {{-- Left Panel: Logistics --}}
            <div class="col-md-4 col-xxl-3  tab-pane active d-md-block  h-100" id="logistics-panel" role="tabpanel" >
                <div class="form-card">
                    <h3 class="">
                         {{ __('costcenter::inventory.origin_location') }}
                    </h3>
                    <div class="form-group mb-3">
                        <label class="primary_input_label" for="origin_location">
                            {{ __('costcenter::inventory.cost_center') }} <span class="text-danger">*</span>
                            <i class="ti-info-alt ml-1" data-toggle="tooltip" title="{{ __('costcenter::inventory.origin_location_tooltip') }}"></i>
                        </label>
                        <select name="origin_location" id="origin_location" class="primary_input_select" required>
                            @if($isGlobal)
                                <option value="">{{ __('common.select') }}...</option>
                                <option value="main" {{ $defaultOrigin === 'main' ? 'selected' : '' }}>{{ __('costcenter::main_warehouse.name') }}</option>
                                @foreach($costCenters as $center)
                                    <option value="center-{{ $center->id }}" {{ $defaultOrigin === 'center-'.$center->id ? 'selected' : '' }}>
                                        {{ $center->name }} ({{ $center->code }})
                                    </option>
                                @endforeach
                            @else
                                @foreach($costCenters as $center)
                                    @if($center->id == $assignedCenterId)
                                        <option value="center-{{ $center->id }}" selected>
                                            {{ $center->name }} ({{ $center->code }})
                                        </option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="primary_input_label" for="dispatched_by">{{ __('costcenter::inventory.dispatched_by') }} <span class="text-danger">*</span> <i class="ti-info-alt ml-1" data-toggle="tooltip" title="{{ __('costcenter::inventory.dispatched_by_tooltip') }}"></i></label>
                        <select name="dispatched_by" id="dispatched_by" class="primary_input_select" required disabled>
                            <option value="">{{ __('costcenter::inventory.select_origin_first') }}</option>
                        </select>
                    </div>
                </div>
                <div class="animate from-top delay-4">
                    <div class="arrow-change-vertical mb-0">
                        <i class="ti-arrow-down"></i>
                    </div>
                </div>
                <div class="form-card">
                    <h3 class="">
                         {{ __('costcenter::inventory.destination_location') }}
                    </h3>
                    <div class="form-group mb-3">
                        <label class="primary_input_label" for="destination_location">
                            {{ __('costcenter::inventory.cost_center') }} <span class="text-danger">*</span>
                            <i class="ti-info-alt ml-1" data-toggle="tooltip" title="{{ __('costcenter::inventory.destination_location_tooltip') }}"></i>
                        </label>
                        <select name="destination_location" id="destination_location" class="primary_input_select" required>
                            <option value="">{{ __('common.select') }}...</option>
                            <option value="main">{{ __('costcenter::main_warehouse.name') }}</option>
                            @foreach($costCenters as $center)
                                <option value="center-{{ $center->id }}">{{ $center->name }} ({{ $center->code }})</option>
                            @endforeach
                        </select>
                        <small class="text-danger" id="destError"></small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="primary_input_label" for="received_by">{{ __('costcenter::inventory.received_by') }} <span class="text-danger">*</span> <i class="ti-info-alt ml-1" data-toggle="tooltip" title="{{ __('costcenter::inventory.received_by_tooltip') }}"></i></label>
                        <select name="received_by" id="received_by" class="primary_input_select" required disabled>
                            <option value="">{{ __('costcenter::inventory.select_destination_first') }}</option>
                        </select>
                    </div>

                </div>
                <div class="form-card">
                    <h3 class="">
                         {{ __('costcenter::inventory.logistics') }}
                    </h3>
                    <div class="form-group mb-3">
                        <label class="primary_input_label" for="carrier_id">{{ __('costcenter::inventory.carrier') ?? 'Transportista' }}</label>
                        <select name="carrier_id" id="carrier_id" class="primary_input_select">
                            <option value="">{{ __('common.select') }}...</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="primary_input_label" for="guide_date">{{ __('costcenter::inventory.guide_date') ?? 'Fecha de Guía' }}</label>
                        <input type="date" class="primary_input_field" name="guide_date" id="guide_date">
                    </div>

                    <div class="form-group mb-3">
                        <label class="primary_input_label" for="movement_type_id">{{ __('costcenter::inventory.movement_type') }} <span class="text-danger">*</span> <i class="ti-info-alt ml-1" data-toggle="tooltip" title="{{ __('costcenter::inventory.movement_type_tooltip') }}"></i></label>
                        <select name="movement_type_id" id="movement_type_id" class="primary_input_select" required>
                            <option value="">{{ __('common.select') }}...</option>
                            @foreach($movementTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="primary_input_label" for="shipping_guide">{{ __('costcenter::inventory.shipping_guide') ?? 'Guía de Envío' }} <i class="ti-info-alt ml-1" data-toggle="tooltip" title="{{ __('costcenter::inventory.shipping_guide_tooltip') }}"></i></label>
                        <input type="text" class="primary_input_field" name="shipping_guide" id="shipping_guide" placeholder="Ej: GUIA-123456">
                    </div>

                    <div class="form-group mb-3">
                        <label class="primary_input_label" for="reason">{{ __('costcenter::inventory.reason') }} <i class="ti-info-alt ml-1" data-toggle="tooltip" title="{{ __('costcenter::inventory.reason_tooltip') }}"></i></label>
                        <textarea name="reason" id="reason" class="primary_textarea" rows="2" placeholder="{{ __('costcenter::inventory.reason_placeholder') }}"></textarea>
                    </div>
                    
                    
                </div>
            </div>

            {{-- Right Panel: Selected Products Table --}}
            <div class="col-md-8 col-xxl-9 d-flex flex-column h-100 bg-white overflow-hidden tab-pane d-md-flex pr-md-1 px-md-3 px-0 pr-0" id="products-panel" role="tabpanel">
                <div class="form-card">
                    <div class="pb-3 border-bottom d-flex justify-content-between align-items-center bg-white " style="z-index: 5;">
                        <h3 class="mb-0">
                             {{ __('costcenter::inventory.selected_products') ?? 'Productos Seleccionados' }}
                        </h3>
                        <button type="button" class="btn-toolkit btn-primary btn-sm" id="btnAddProducts" disabled>
                            <i class="ti-plus mr-2"></i> {{ __('costcenter::inventory.add_products') ?? 'Añadir Productos' }}
                        </button>
                    </div>

                    <div class="selected-products-container flex-grow-1 ign-scrollbar">
                        <div id="emptySelectionState" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-5">
                            <i class="ti-shopping-cart display-4 mb-4 opacity-10" style="transform: rotate3d(0, 1, 0, 180deg);"></i>
                            <h5 class="mb-1 text-center">{{ __('costcenter::inventory.no_products_selected') ?? 'No hay productos seleccionados' }}</h5>
                            <p class="text-center">{{ __('costcenter::inventory.click_add_products_to_start') ?? 'Seleccione un destino y luego haga clic en el botón superior para empezar a añadir productos.' }}</p>
                        </div>
                        <div class="overflow-auto ign-scrollbar">

                        
                            <table class="selected-table d-none" id="selectedItemsTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('common.product') }}</th>
                                        <th>{{ __('common.image') }}</th>
                                        <th>{{ __('product.sku') }}</th>
                                        <th>{{ __('product.brand') }}</th>
                                        <th>{{ __('product.lot') }}</th>
                                        <th>{{ __('inventoryentry::inventory.expiration_date') }}</th>
                                        <th class="text-center">{{ __('costcenter::inventory.origin_stock') ?? 'Stock Origen' }}</th>
                                        <th class="text-center">{{ __('costcenter::inventory.destination_stock') ?? 'Stock Destino' }}</th>
                                        <th class="text-center" style="width: 120px;">{{ __('common.requested') }}</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="selectedItemsBody">
                                    <!-- Selected items will appear here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Footer Summary --}}
                    <div class="row mx-0 justify-content-between  align-items-center mt-4">
                        <div class=" col-xl-auto text-center mb-2">
                                <span class="badge_6 mr-2 px-2">{{ __('costcenter::inventory.items') }}: <span class="value" id="footerItemsCount">0</span></span>
                                
                                <span class="badge_6 px-2">{{ __('costcenter::inventory.units') }}: <span class="value" id="footerTotalUnits">0</span></span>
                                
                        </div>
                        <div class="col-xl-auto text-center mb-2">
                                <a href="{{ route('cost_centers.inventory.index') }}" class="btn-toolkit btn-secondary-outline mr-2 mb-1">
                                    {{ __('common.cancel') }}
                                </a>
                                <button type="submit" class="btn-toolkit btn-primary mb-1 btn-icon" id="submitBtn" disabled>
                                    <i class="ti-check mr-2"></i> {{ __('costcenter::inventory.process_transfer') }}
                                </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</x-admin.section>

@include('costcenter::inventory.partials.product_selection_modal')
@include('costcenter::inventory.partials.confirm_transfer_modal')
<div id="detailModalContainer"></div>

@push('scripts')
@include('costcenter::inventory.partials.transfer_scripts')
@endpush
@endsection




