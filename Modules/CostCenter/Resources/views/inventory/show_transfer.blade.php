@extends('backEnd.master')

@section('styles')
<link rel="stylesheet" href="{{ asset('public/css/const-center.css') }}">
@endsection

@section('mainContent')
<x-admin.section>
<div class="row justify-content-center">

    <div class="col-lg-12">
        <div class="box_header common_table_header">
            <div class="main-title d-md-flex align-items-center">
                <x-backEnd.back-button :text="false" />
                <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px text-black fs-20">
                    {{ __('costcenter::inventory.transfer_detail') }}: {{ $transfer->reference_code }}
                </h3>

                @if($transfer->status === 'dispatched')
                    <span class="badge_3" style="font-size: 14px; padding: 8px;">
                        {{ __('costcenter::inventory.in_transit') }}
                    </span>
                @elseif($transfer->status === 'received')
                    <span class="badge_1" style="font-size: 14px; padding: 8px;">
                        {{ __('costcenter::inventory.received_total') }}
                    </span>
                @else
                    <span class="badge_2 text-white" style="font-size: 14px; padding: 8px;">
                        {{ __('costcenter::inventory.received_with_novelty') }}
                    </span>
                @endif
            </div>
        </div>

        <div class="white_box_30px box_shadow_white">
            <div class="form-card transfer-timeline status-{{ $transfer->status }}">
                <div class="timeline-progress"></div>

                <div class="timeline-step completed">
                    <div class="timeline-icon">
                        <i class="ti-truck"></i>
                    </div>
                    <div class="timeline-content">
                        <h5 class="text-dark-green fs-16">1. {{ __('costcenter::inventory.step_dispatched') }}</h5>
                        <p class="text-black line-normal">{{ $transfer->dispatched_at ?? 'N/A' }}</p>
                        <span class="text-color-stats" style="font-size: 12px">{{ __('costcenter::inventory.by') }}: {{ $transfer->dispatchedBy->first_name ?? 'N/A' }}</span>
                    </div>
                </div>

                @php
                    $step2Class = '';
                    $step2TextClass = '';
                    if ($transfer->status === 'dispatched') {
                        $step2Class = 'in-progress';
                        $step2TextClass = 'color-orange-toolkit';
                    } elseif ($transfer->status === 'received') {
                        $step2Class = 'completed';
                        $step2TextClass = 'text-dark-green';
                    } elseif ($transfer->status === 'received_with_discrepancies') {
                        $step2Class = 'novelty';
                        $step2TextClass = 'text-danger';
                    }
                @endphp

                <div class="timeline-step {{ $step2Class }}">
                    <div class="timeline-icon">
                        <i class="ti-check-box"></i>
                    </div>
                    <div class="timeline-content">
                        <h5 class="fs-16 {{ $step2TextClass }}">
                            2. {{ __('costcenter::inventory.step_reception') }}
                        </h5>
                        <p class="text-black line-normal">{{ $transfer->received_at ?? __('costcenter::inventory.pending') }}</p>
                        <small class="text-color-stats">{{ __('costcenter::inventory.by') }}: {{ $transfer->receivedBy->first_name ?? __('costcenter::inventory.pending') }}</small>
                    </div>
                </div>
            </div>

            <div class="row text-center text-black form-card">
                <div class="col-md-4 d-flex flex-column">
                    <strong class="text-color-base">{{ __('costcenter::inventory.origin') }}:</strong>
                    @if($transfer->source_type == 'main')
                        <span>{{ __('costcenter::main_warehouse.name') }}</span>
                    @else
                        <span>{{ $transfer->sourceCostCenter->code }} - {{ $transfer->sourceCostCenter->name }}</span>
                        <span>{{ $transfer->sourceCostCenter->address }}</span>
                    @endif
                </div>

                <div class="col-md-4 d-flex flex-column">
                    <strong class="text-color-base">{{ __('costcenter::inventory.destination') }}:</strong>
                    @if($transfer->destination_type == 'main')
                        <span>{{ __('costcenter::main_warehouse.name') }}</span>
                    @else
                        <span>{{ $transfer->destinationCostCenter->code }} - {{ $transfer->destinationCostCenter->name }}</span>
                        <span>{{ $transfer->destinationCostCenter->address }}</span>
                    @endif
                </div>
                <div class="col-md-4 d-flex flex-column">
                    <strong class="text-color-base">{{ __('costcenter::inventory.carrier') }}:</strong>
                    <span>{{ $transfer->carrier->name ?? 'N/A' }}</span>

                    <strong class="text-color-base">{{ __('costcenter::inventory.guide') }}:</strong>
                    <span>{{ $transfer->shipping_guide ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="white_box_30px box_shadow_white">
            <form id="receiveTransferForm" enctype="multipart/form-data">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-primary-50 text-color-base">
                            <tr>
                                <th>{{ __('costcenter::inventory.product') }}</th>
                                <th>{{ __('costcenter::inventory.sku') }}</th>
                                <th>{{ __('costcenter::inventory.lot') }}</th>
                                <th>{{ __('costcenter::inventory.qty_dispatched') }}</th>
                                <th>{{ __('costcenter::inventory.qty_received') }}</th>
                                <th>{{ __('costcenter::inventory.difference') }}</th>
                                <th class="text-center">{{ __('costcenter::inventory.novelties') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transfer->items as $index => $item)
                            <tr>
                                <td class="text-black text-center">{{ $item->productSku->product->product_name ?? 'N/A' }}</td>
                                <td class="text-black text-center">{{ $item->productSku->sku ?? 'N/A' }}</td>
                                <td class="text-black text-center">{{ $item->lot->lot_number ?? 'N/A' }}</td>
                                <td class="text-center font-weight-bold fs-12 text-black" id="dispatched_qty_{{ $item->id }}">{{ $item->dispatched_qty }}</td>

                                <td style="width: 150px;" class="text-center">
                                    @if($transfer->status === 'dispatched')
                                        <input type="hidden" name="items[{{ $index }}][transfer_item_id]" value="{{ $item->id }}">
                                        <input type="number"
                                               name="items[{{ $index }}][received_qty]"
                                               class="primary_input_field form-control received-qty-input"
                                               data-item-id="{{ $item->id }}"
                                               data-index="{{ $index }}"
                                               max="{{ $item->dispatched_qty }}"
                                               min="0"
                                               value="{{ $item->dispatched_qty }}"
                                               required>
                                    @else
                                        <span class="fs-12 {{ $item->received_qty < $item->dispatched_qty ? 'text-danger font-weight-bold' : 'text-dark-green' }}">
                                            {{ $item->received_qty }}
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center align-middle" style="width: 100px;">
                                    @if($transfer->status === 'dispatched')
                                        <span id="diff_qty_{{ $item->id }}" class="fs-16 font-weight-bold text-dark-green">0</span>
                                    @else
                                        @php
                                            $diff = $item->dispatched_qty - $item->received_qty;
                                        @endphp
                                        <span class="fs-16 font-weight-bold {{ $diff > 0 ? 'text-danger' : 'text-dark-green' }}">
                                            {{ $diff }}
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center" style="width: 200px;">
                                    @if($transfer->status === 'dispatched')
                                        <button type="button"
                                                class="btn-toolkit btn-primary radius_30px btn-outline-danger btn-novelty-modal"
                                                id="btn_novelty_{{ $item->id }}"
                                                data-toggle="modal"
                                                data-target="#noveltyModal_{{ $item->id }}"
                                                style="display: none; padding: 0 15px; line-height: 30px; background: transparent; color: var(--toolkit_corporative-red-color); border: 1px solid var(--toolkit_corporative-red-color);">
                                            <i class="ti-plus"></i> {{ __('costcenter::inventory.add_novelty') }}
                                        </button>
                                        <span id="novelty_status_{{ $item->id }}" class="text-dark-green mt-1" style="display:none; font-size: 12px;"><i class="ti-check"></i> Completo</span>
                                    @else
                                        @if($item->discrepancies->isNotEmpty())
                                            <button type="button"
                                                    class="btn-toolkit btn-primary radius_30px btn-outline-info"
                                                    data-toggle="modal"
                                                    data-target="#noveltyModal_{{ $item->id }}"
                                                    style="padding: 0 15px; line-height: 30px;">
                                                {{ __('common.view') }}
                                            </button>
                                        @else
                                            <span class="text-black"><i class="ti-check"></i> {{ __('costcenter::inventory.received_ok') }}</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($transfer->status === 'dispatched')
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <label class="text-color-base">{{ __('costcenter::inventory.general_reception_notes') }}</label>
                            <textarea name="reception_notes" class="primary_textarea form-control" rows="3"></textarea>
                        </div>
                        <div class="col-lg-12 text-center mt-3">
                            <button type="button" id="btnConfirmReceive" class="btn-toolkit btn-primary fix-gr-bg submit-btn">
                                <i class="ti-check"></i> {{ __('costcenter::inventory.confirm_reception') }}
                            </button>
                        </div>
                    </div>
                @elseif($transfer->reception_notes)
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="alert bg-primary-50 text-black border-0">
                                <strong>{{ __('costcenter::inventory.general_reception_notes') }}:</strong><br>
                                {{ $transfer->reception_notes }}
                            </div>
                        </div>
                    </div>
                @endif

                @foreach($transfer->items as $index => $item)
                    @include('costcenter::inventory.partials.novelty_modal')
                @endforeach
                </form>
        </div>
    </div>
</div>
</x-admin.section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // 1. Mostrar/Ocultar botón de Novedad al cambiar cantidad y Calcular Diferencia
        $('.received-qty-input').on('input', function() {
            let itemId = $(this).data('item-id');
            let dispatchedQty = parseFloat($('#dispatched_qty_' + itemId).text());
            let receivedQty = parseFloat($(this).val()) || 0; // Si borran el número, entonces asumimos 0
            
            let btnNovelty = $('#btn_novelty_' + itemId);
            let statusNovelty = $('#novelty_status_' + itemId);
            let diffSpan = $('#diff_qty_' + itemId);

            // Validar que no reciba más de lo enviado
            if(receivedQty > dispatchedQty) {
                toastr.warning('No puede recibir más cantidad de la que se despachó.');
                $(this).val(dispatchedQty);
                receivedQty = dispatchedQty;
            }

            // Calcular diferencia
            let difference = dispatchedQty - receivedQty;
            diffSpan.text(difference);

            // Cambiar color: Rojo si hay faltante (> 0), Verde si está completo
            if (difference > 0) {
                diffSpan.removeClass('text-dark-green').addClass('text-danger');
            } else {
                diffSpan.removeClass('text-danger').addClass('text-dark-green');
            }

            // Lógica de mostrar/ocultar el botón de novedad
            if (receivedQty < dispatchedQty) {
                btnNovelty.show();
                checkNoveltyCompletion(itemId);
            } else {
                btnNovelty.hide();
                statusNovelty.hide();
                // Limpiar inputs del modal si se arrepiente y pone la cantidad completa
                $('#novelty_select_' + itemId).val('');
                $('#novelty_desc_' + itemId).val('');
                $('#novelty_file_' + itemId).val('');
            }
        });

        // 2. Revisar si la modal fue llenada al cerrarla
        $('.novelty-modal').on('hidden.bs.modal', function (e) {
            let modalId = $(this).attr('id');
            let itemId = modalId.split('_')[1];
            checkNoveltyCompletion(itemId);
        });

        function checkNoveltyCompletion(itemId) {
            let novId = $('#novelty_select_' + itemId).val();
            let novDesc = $('#novelty_desc_' + itemId).val();
            let novFile = $('#novelty_file_' + itemId).val();
            let btn = $('#btn_novelty_' + itemId);
            let status = $('#novelty_status_' + itemId);

            // Solo hacemos el check si el botón está visible (hay diferencia)
            if(btn.is(':visible')) {
                if (novId && novDesc && novFile) {
                    // Completado
                    btn.css({'background': 'transparent', 'color': 'var(--base_color)', 'border': '1px solid var(--base_color)'})
                       .html('<i class="ti-pencil"></i> {{ __('common.edit') }}');
                    status.show();
                } else {
                    // Faltan datos
                    btn.css({'background': 'transparent', 'color': 'var(--toolkit_corporative-red-color)', 'border': '1px solid var(--toolkit_corporative-red-color)'})
                       .html('<i class="ti-plus"></i> {{ __('costcenter::inventory.add_novelty') }}');
                    status.hide();
                }
            }
        }

        // 3. Procesar Formulario
        $('#btnConfirmReceive').on('click', function(e) {
            e.preventDefault();

            // Validación Manual de Novedades
            let isValid = true;
            $('.received-qty-input').each(function() {
                let itemId = $(this).data('item-id');
                let dispatchedQty = parseFloat($('#dispatched_qty_' + itemId).text());
                let receivedQty = parseFloat($(this).val());

                if (receivedQty < dispatchedQty) {
                    let novId = $('#novelty_select_' + itemId).val();
                    let novDesc = $('#novelty_desc_' + itemId).val();
                    let novFile = $('#novelty_file_' + itemId).val();

                    if (!novId || !novDesc || !novFile) {
                        isValid = false;
                        let btn = $('#btn_novelty_' + itemId);
                        // Resaltar el botón que falta
                        btn.css({'background': 'var(--toolkit_corporative-red-color)', 'color': '#fff'});
                    }
                }
            });

            if (!isValid) {
                toastr.error("{{ __('costcenter::inventory.please_fill_all_novelties') }}");
                return;
            }

            let form = $('#receiveTransferForm')[0];
            let formData = new FormData(form);
            let btn = $(this);

            btn.prop('disabled', true).html('<i class="ti-settings fa-spin"></i> Procesando...');
            $('#pre-loader').removeClass('d-none');

            $.ajax({
                url: "{{ route('cost_centers.inventory.transactions.receive', $transfer->id) }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#pre-loader').addClass('d-none');
                    if(response.success) {
                        toastr.success(response.message);
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="ti-check"></i> {{ __('costcenter::inventory.confirm_reception') }}');
                    $('#pre-loader').addClass('d-none');
                    let res = xhr.responseJSON;

                    if(res && res.errors) {
                        $.each(res.errors, function(key, val) { toastr.error(val[0]); });
                    } else if(res && res.message) {
                        toastr.error(res.message);
                    } else {
                        toastr.error('Ocurrió un error al procesar la recepción.');
                    }
                }
            });
        });
    });
</script>
@endpush
