<dialog class="modal fade" id="entryDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti-clipboard mr-2"></i>
                    {{ __('inventoryentry::inventory.entry_detail') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="ti-close"></i>
                </button>
            </div>
            <div class="modal-body">

                <div class="form-card">
                    <h3>{{ __('inventoryentry::inventory.lot_info') }}</h3>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class=" ">
                                <label class="primary_input_label" for="">{{ __('inventoryentry::inventory.lot_number') }}</label>
                                <input class="primary_input_field border-0" type="text" value="{{ $entry->lot->lot_number }}" readonly >
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class=" ">
                                <label class="primary_input_label" for="">{{ __('inventoryentry::inventory.col_status') }}</label>
                                <span class="{{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span>

                            </div>
                        </div>
                        @if($entry->lot->manufacture_date)
                        <div class="col-md-6 mb-2">
                            <div class=" ">
                                <label class="primary_input_label" for="">{{ __('inventoryentry::inventory.manufacture_date') }}</label>
                                <input class="primary_input_field border-0" type="text" value="{{ $entry->lot->manufacture_date->format('Y-m-d') }}" readonly>
                            </div>
                        </div>
                        @endif
                        @if($entry->lot->expiration_date)
                        <div class="col-md-6 mb-2">
                            <div class=" ">
                                <label class="primary_input_label" for="">{{ __('inventoryentry::inventory.expiration_date') }}</label>
                                <input class="primary_input_field border-0" type="text" value="{{ $entry->lot->expiration_date->format('Y-m-d') }}" readonly>
                            </div>
                        </div>
                        @endif
                        @if($entry->supplier)
                        <div class="col-md-6 mb-2">
                            <div class=" ">
                                <label class="primary_input_label" for="">{{ __('inventoryentry::inventory.supplier') }}</label>
                                <input class="primary_input_field border-0" type="text" value="{{ $entry->supplier }}" readonly>
                            </div>
                        </div>
                        @endif
                        @if($entry->notes)
                        <div class="col-md-12">
                            <div class=" ">
                                <label class="primary_input_label" for="">{{ __('inventoryentry::inventory.notes') }}</label>
                                <span>{{ $entry->notes }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @if($latestModifiedAudit)
                <div class="form-card">
                    <h3>{{ __('inventoryentry::inventory.audit_modified_info') }}</h3>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="primary_input_label">{{ __('inventoryentry::inventory.audit_responsible') }}</label>
                            <p class="mb-0">
                                {{ trim(optional($latestModifiedAudit->responsible)->first_name . ' ' . optional($latestModifiedAudit->responsible)->last_name) ?: 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="primary_input_label">{{ __('inventoryentry::inventory.audit_date_long') }}</label>
                            <p class="mb-0" title="{{ $latestModifiedAudit->created_at?->format('Y-m-d H:i') }}">
                                {{ $latestModifiedAudit->created_at?->translatedFormat('l d \\d\\e F Y H:i') ?? '-' }}
                            </p>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="primary_input_label">{{ __('inventoryentry::inventory.audit_notes') }}</label>
                            <p class="mb-0">{{ $latestModifiedAudit->notes ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="primary_input_label">{{ __('inventoryentry::inventory.audit_ip') }}</label>
                            <p class="mb-0">{{ $latestModifiedAudit->ip_address ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="primary_input_label">{{ __('inventoryentry::inventory.audit_agent') }}</label>
                            <p class="mb-0">{{ $latestModifiedAudit->user_agent ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($latestDeletedAudit)
                <div class="form-card">
                    <h3>{{ __('inventoryentry::inventory.audit_deleted_info') }}</h3>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="primary_input_label">{{ __('inventoryentry::inventory.audit_responsible') }}</label>
                            <p class="mb-0">
                                {{ trim(optional($latestDeletedAudit->responsible)->first_name . ' ' . optional($latestDeletedAudit->responsible)->last_name) ?: 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="primary_input_label">{{ __('inventoryentry::inventory.audit_date_long') }}</label>
                            <p class="mb-0" title="{{ $latestDeletedAudit->created_at?->format('Y-m-d H:i') }}">
                                {{ $latestDeletedAudit->created_at?->translatedFormat('l d \\d\\e F Y H:i') ?? '-' }}
                            </p>
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="primary_input_label">{{ __('inventoryentry::inventory.audit_notes') }}</label>
                            <p class="mb-0">{{ $latestDeletedAudit->notes ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="primary_input_label">{{ __('inventoryentry::inventory.audit_ip') }}</label>
                            <p class="mb-0">{{ $latestDeletedAudit->ip_address ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="primary_input_label">{{ __('inventoryentry::inventory.audit_agent') }}</label>
                            <p class="mb-0">{{ $latestDeletedAudit->user_agent ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                @endif
                {{-- Historial de ingresos del lote --}}
                <div class="form-card">
                    <h3 class=""> {{ __('inventoryentry::inventory.entries_history') }}</h3>
                    <div class="top-scroll ign-scrollbar">
                        <div class="top-scroll-inner"></div>
                    </div>
                    <div class="table-responsive ign-scrollbar bottom-scroll">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('inventoryentry::inventory.col_product') }}</th>
                                    <th>{{ __('inventoryentry::inventory.col_sku') }}</th>
                                    <th>{{ __('inventoryentry::inventory.col_quantity') }}</th>
                                    <th>{{ __('inventoryentry::inventory.unit_cost') }}</th>
                                    <th>{{ __('inventoryentry::inventory.supplier') }}</th>
                                    <th>{{ __('inventoryentry::inventory.notes') }}</th>
                                    <th>{{ __('inventoryentry::inventory.col_created_by') }}</th>
                                    <th>{{ __('inventoryentry::inventory.col_created_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entry->lot->entries as $lotEntry)
                                @php
                                    $productName = '';
                                    if ($lotEntry->productSku?->product) {
                                        $name    = $lotEntry->productSku->product->product_name;
                                        $decoded = json_decode($name, true);
                                        $productName = is_array($decoded)
                                            ? ($decoded[app()->getLocale()] ?? reset($decoded) ?? $name)
                                            : $name;
                                    }
                                    $skuLabel = $lotEntry->productSku?->track_sku ?: ($lotEntry->productSku?->sku ?: "SKU #{$lotEntry->product_sku_id}");
                                    $isCurrentEntry = $lotEntry->id === $entry->id;
                                @endphp
                                <tr @if($isCurrentEntry) style="background:rgba(var(--base_color_rgb),.06);" @endif>
                                    <td>{{ $productName ?: 'N/A' }}</td>
                                    <td>
                                        @if($lotEntry->productSku?->track_sku)
                                            <span class="badge_5">{{ $lotEntry->productSku->track_sku }}</span>
                                        @else
                                            {{ $skuLabel }}
                                        @endif
                                    </td>
                                    <td><strong>{{ number_format($lotEntry->quantity, 0) }}</strong></td>
                                    <td>{{ $lotEntry->unit_cost ? number_format($lotEntry->unit_cost, 2) : '—' }}</td>
                                    <td>{{ $lotEntry->supplier ?: '—' }}</td>
                                    <td>{{ $lotEntry->notes ?: '—' }}</td>
                                    <td>
                                        @if($lotEntry->createdBy)
                                            {{ trim($lotEntry->createdBy->first_name . ' ' . $lotEntry->createdBy->last_name) }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <span title="{{ $lotEntry->created_at->format('Y-m-d H:i') }}">
                                            {{ $lotEntry->created_at->diffForHumans() }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <script>
                        function syncTableScroll() {
                            const top = document.querySelector(".top-scroll");
                            const inner = document.querySelector(".top-scroll-inner");
                            const bottom = document.querySelector(".bottom-scroll");
                            const table = bottom ? bottom.querySelector("table") : null;

                            if (!top || !bottom || !table) return;

                            // Sincronizar el ancho del div fantasma con el de la tabla real
                            inner.style.width = table.scrollWidth + "px";

                            // Eliminar listeners previos para evitar duplicados si se abre varias veces
                            top.onscroll = function() {
                                bottom.scrollLeft = top.scrollLeft;
                            };
                            bottom.onscroll = function() {
                                top.scrollLeft = bottom.scrollLeft;
                            };
                        }
                        // retry build
                        // Usar namespaces para evitar duplicados al recargar el contenido
                        $('#entryDetailModal').off('shown.bs.modal.sync').on('shown.bs.modal.sync', syncTableScroll);
                        $(window).off('resize.sync').on('resize.sync', syncTableScroll);
                    </script>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                    {{ __('common.close') }}
                </button>
            </div>
        </div>
    </div>
</dialog>
