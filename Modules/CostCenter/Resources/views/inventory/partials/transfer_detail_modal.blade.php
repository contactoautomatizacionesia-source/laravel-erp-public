@section('styles')
    <link rel="stylesheet" href="{{asset(asset_path('modules/product/css/product_index.css'))}}">
@endsection
@php
    $originName = $transfer->source_type === 'main'
        ? __('costcenter::main_warehouse.name')
        : ($transfer->sourceCostCenter->name ?? 'N/A');
    $destinationName = $transfer->destination_type === 'main'
        ? __('costcenter::main_warehouse.name')
        : ($transfer->destinationCostCenter->name ?? 'N/A');
@endphp

<div class="modal fade" id="transferDetailModal" tabindex="-1" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header py-3">
                <div class="d-flex align-items-center">
                    <div class="modal-icon mr-3" style="background: var(--base_color); color: #fff; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="ti-receipt fs-20"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0">{{ __('costcenter::inventory.transaction_detail') }} #{{ $transfer->reference_code ?? $transfer->id }}</h5>
                        <p class="text-white">{{ $transfer->created_at->format('d M, Y - H:i') }}</p>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="ti-close"></i>
                </button>
            </div>
            <div class="modal-body ">
                @if(!empty($showCountdown))
                <div id="detailStepIntro" class="text-center py-4 bg-light-soft rounded mb-4 border" style="border-style: dashed !important;">
                    <div class="mb-3">
                        <i class="ti-check-box display-4 text-success"></i>
                    </div>
                    <h5 class="font-weight-bold mb-1">{{ __('costcenter::inventory.transfer_success_title') }}</h5>
                    <p class="small text-muted mb-3">{{ __('costcenter::inventory.transfer_success_message', ['from' => $originName, 'to' => $destinationName]) }}</p>
                    <div class="d-flex justify-content-center align-items-center gap-3">
                        <span class="small text-muted">{{ __('costcenter::inventory.summary_will_show_in') }} <span id="detailCountdown" class="font-weight-bold text-primary">5</span>s</span>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="detailShowSummaryBtn">
                            {{ __('costcenter::inventory.view_summary') }}
                        </button>
                    </div>
                </div>
                @endif

                <div id="detailStepSummary" class="{{ !empty($showCountdown) ? 'd-none' : '' }} animate-fade-in">
                    
                    <div class="form-card">
                        <h3>{{ __('costcenter::inventory.route') }}</h3>
                        <div class="row ">
                            <div class="col-md-6">
                                <div class="form-card previous-state  shadow-none position-relative">
                                    <h3 class="text-black">{{ __('costcenter::inventory.origin') }}</h3>
                                    <div>
                                        <label for="" class="primary_input_label">{{ __('costcenter::inventory.cost_center') }}</label>
                                        <h4 class="mb-2 text-black fs-20">{{ $originName }}</h4>
                                        <span class="badge badge-toolkit bg-white border text-muted px-2 py-1" data-toggle="tooltip" title="{{ __('costcenter::inventory.dispatched_by') }}">
                                            <i class="ti-user mr-1 text-primary"></i> {{ $transfer->dispatchedBy->first_name ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="arrow-change ">
                                        <i class="ti-arrow-right arrow-hint"></i>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-card  shadow-none ">
                                    <h3 class="text-black">{{ __('costcenter::inventory.destination') }}</h3>
                                    <div>
                                        <label for="" class="primary_input_label">{{ __('costcenter::inventory.cost_center') }}</label>
                                        <h4 class="mb-2 text-black fs-20">{{ $destinationName }}</h4>
                                        <span class="badge badge-toolkit bg-white border text-muted px-2 py-1" data-toggle="tooltip" title="{{ __('costcenter::inventory.received_by') }}">
                                            <i class="ti-user mr-1 text-success"></i> {{ $transfer->receivedBy->first_name ?? 'N/A' }}
                                        </span>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                   
                    <div class="form-card ">
                        <h3 class="">
                             {{ __('costcenter::inventory.logistics_data') ?? 'Datos Logísticos' }}
                        </h3>
                        <div class="row">
                            <div class="col-12 col-md-auto mb-3">
                                <span class="primary_input_label" >{{ __('costcenter::inventory.movement_type') }}</span>
                                <span class="text-dark" >{{ $transfer->movementType->name ?? 'N/A' }}</span>
                            </div>
                            <div class="col-12 col-md-auto mb-3">
                                <span class="primary_input_label" >{{ __('costcenter::inventory.shipping_guide') }}</span>
                                <span class="info-value font-italic text-muted">{{ $transfer->shipping_guide ?? 'N/A' }}</span>
                            </div>
                            <div class="col-12 col-md-auto mb-3">
                                <span class="primary_input_label" >{{ __('costcenter::inventory.guide_date') }}</span>
                                <span class="info-value text-muted">{{ $transfer->guide_date ? \Carbon\Carbon::parse($transfer->guide_date)->format('d/m/Y') : 'N/A' }}</span>
                            </div>
                            <div class="col-12 col-md-auto mb-3">
                                <span class="primary_input_label" >{{ __('costcenter::inventory.carrier') }}</span>
                                <span class="info-value text-muted">
                                    @if($transfer->carrier)
                                        {{ is_array($transfer->carrier->name) ? ($transfer->carrier->name[app()->getLocale()] ?? reset($transfer->carrier->name)) : $transfer->carrier->name }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="col-12">
                                <span class="primary_input_label" >{{ __('costcenter::inventory.reason') }}</span>
                                <span class="info-value text-muted small" style="line-height: 1.4;">{{ $transfer->reason ?? __('costcenter::inventory.none') }}</span>
                            </div>
                        </div>
                    </div>
                      
                    <div class="form-card">
                        <h3>{{ __('costcenter::inventory.transfer_summary') }}</h3>
                        
                        <div class="table-responsive rounded border mb-0" >
                            <table class="table table-hover mb-0">
                                <thead style="background-color: #f8fafc;">
                                    <tr>
                                        <th class="border-0 small font-weight-bold text-muted">{{ __('costcenter::inventory.sku') }}</th>
                                        <th class="border-0 small font-weight-bold text-muted">{{ __('common.product') }}</th>
                                        <th class="border-0 small font-weight-bold text-muted">{{ __('product.lot') }}</th>
                                        <th class="border-0 small font-weight-bold text-muted">{{ __('inventoryentry::inventory.expiration_date') }}</th>
                                        <th class="border-0 small font-weight-bold text-muted text-center">{{ __('common.quantity') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    @foreach($transfer->items as $item)
                                    <tr>
                                        <td class="">{{ $item->productSku->sku ?? 'N/A' }}</td>
                                        <td>{{ $item->productSku->product->getTranslation('product_name', app()->getLocale()) ?? $item->productSku->product->product_name ?? 'N/A' }}</td>
                                        <td><span class="">{{ $item->lot->lot_number ?? 'N/A' }}</span></td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $item->lot?->expiration_date ? $item->lot->expiration_date->format('Y-m-d') : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-center font-weight-bold">{{ number_format($item->dispatched_qty, 0) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="bg-white p-3 d-flex justify-content-between align-items-center rounded-bottom border border-top-0" style="background: linear-gradient(to right, #ffffff, #f8fafc);">
                            <span class="font-weight-bold text-muted small text-uppercase" style="letter-spacing: 0.05em;">{{ __('costcenter::inventory.total_transferred') ?? 'Total Transferido' }}</span>
                            <div class="badge_5" >
                                <i class="ti-package mr-2"></i> {{ number_format($transfer->total_qty, 0) }} {{ __('costcenter::inventory.items') }}
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 py-3">
                <button type="button" class="btn-toolkit btn-secondary-outline border-0 bg-transparent" id="closeDetailModalBtn" data-dismiss="modal">{{ __('common.close') }}</button>
                <button type="button" class="btn-toolkit btn-primary px-4 shadow-sm" data-dismiss="modal">
                    <i class="ti-check mr-2"></i> {{ __('common.done') }}
                </button>
            </div>
        </div>
    </div>
</div>


<script>
    (function () {
        let detailTimer = null;

        function showSummary() {
            clearInterval(detailTimer);
            $('#detailStepIntro').addClass('d-none');
            $('#detailStepSummary').removeClass('d-none');
        }

        $('#detailShowSummaryBtn').on('click', function () {
            showSummary();
        });

        $('#transferDetailModal').on('shown.bs.modal', function () {
            let useCountdown = {{ !empty($showCountdown) ? 'true' : 'false' }};

            if (!useCountdown) {
                return;
            }

            let countdown = 5;
            $('#detailCountdown').text(countdown);
            $('#detailStepIntro').removeClass('d-none');
            $('#detailStepSummary').addClass('d-none');

            detailTimer = setInterval(function () {
                countdown -= 1;
                $('#detailCountdown').text(countdown);
                if (countdown <= 0) {
                    showSummary();
                }
            }, 1000);
        }).on('hidden.bs.modal', function () {
            clearInterval(detailTimer);
            let useCountdown = {{ !empty($showCountdown) ? 'true' : 'false' }};
            if (useCountdown) {
                $('#pre-loader').removeClass('d-none');
                window.location.href = "{{ $redirectUrl ?? route('cost_centers.inventory.index') }}";
            }
        });

        $('[data-toggle="tooltip"]').tooltip();
    })();
</script>
