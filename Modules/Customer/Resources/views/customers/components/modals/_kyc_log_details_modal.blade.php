<div class="modal fade" id="kycDetailModal-{{ $log->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden; box-shadow: 0 15px 50px rgba(0,0,0,0.2);">
            
            {{-- Header Estilizado --}}
            <div class="modal-header p-4">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="modal-title font-weight-bold m-0">
                            {{ __('amazy.kyc_update_detail') }} -
                            {{ showDate($log->created_at) }}
                        </h5>
                        {{-- <span class="text-white">{{ showDate($log->created_at) }}</span> --}}
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="opacity: 1; transition: all 0.3s;">
                    <span aria-hidden="true" style="font-size: 28px;"><i class="ti-close"></i></span>
                </button>
            </div>

            <div class="modal-body p-4" style="background-color: #f8fafc; max-height: 70vh; overflow-y: auto;">
                @php
                    $before = is_string($log->payload_before) ? json_decode($log->payload_before, true) : $log->payload_before;
                    $after = is_string($log->payload_after) ? json_decode($log->payload_after, true) : $log->payload_after;
                    $before = $before ?? [];
                    $after = $after ?? [];
                    
                    $allKeys = array_unique(array_merge(array_keys($before), array_keys($after)));
                    
                    // Contamos los cambios visuales reales
                    $realChangesCount = 0;
                    foreach($allKeys as $key) {
                        if($resolveValue($key, $before[$key] ?? null) !== $resolveValue($key, $after[$key] ?? null)) {
                            $realChangesCount++;
                        }
                    }
                @endphp

                @if($realChangesCount > 0)
                    {{-- GRILLLA DE CAMBIOS (El código que ya tenías) --}}
                    <div class="row">
                        @foreach($allKeys as $key)
                            @php
                                $valBefore = $resolveValue($key, $before[$key] ?? null);
                                $valAfter  = $resolveValue($key, $after[$key] ?? null);
                            @endphp

                            @if($valBefore !== $valAfter)
                                <div class="col-xl-6 col-lg-12 mb-3">
                                    <div class="kyc-diff-card" style="background: white; border-radius: 15px; border: 1px solid #e2e8f0; padding: 18px; transition: transform 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                        <div class="d-flex align-items-center mb-3">
                                            <span style="width: 8px; height: 8px; background: var(--base_color); border-radius: 50%; margin-right: 10px;"></span>
                                            <h6 class="m-0 font-weight-bold" style="color: #475569; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
                                                {{ $formatKey($key) }}
                                            </h6>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <div class="flex-fill p-3" style="background: #fff5f5; border-radius: 10px; border: 1px solid #fed7d7; min-height: 60px; display: flex; flex-direction: column; justify-content: center;">
                                                <small class="text-uppercase font-weight-bold mb-2" style="font-size: 9px; color: #c53030; opacity: 0.7;">{{ __('amazy.previous_value') }}</small>
                                                <div style="color: #c53030; font-size: 14px; text-decoration: line-through; word-break: break-all;">
                                                    {!! in_array($key, ['front_id_image', 'back_id_image', 'declaration_pdffile', 'rut_file']) ? $valBefore : e($valBefore) !!}
                                                </div>
                                            </div>
                                            <div class="px-2">
                                                <i class="ti-arrow-right text-muted" style="font-size: 18px;"></i>
                                            </div>
                                            <div class="flex-fill p-3" style="background: #f0fff4; border-radius: 10px; border: 1px solid #c6f6d5; min-height: 60px; display: flex; flex-direction: column; justify-content: center;">
                                                <small class="text-uppercase font-weight-bold mb-2" style="font-size: 9px; color: #2f855a; opacity: 0.7;">{{ __('amazy.new_value') }}</small>
                                                <div style="color: #2f855a; font-size: 14px; font-weight: 600; word-break: break-all;">
                                                    {!! in_array($key, ['front_id_image', 'back_id_image', 'declaration_pdffile', 'rut_file']) ? $valAfter : e($valAfter) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    {{-- NUEVO: ESTADO VACÍO (Revalidación Exitosa) --}}
                    <div class="text-center py-5">
                        <div class="mb-4 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: #f0fff4; color: #10b981; border-radius: 50%; box-shadow: 0 0 0 10px rgba(16, 185, 129, 0.1);">
                            <i class="ti-shield" style="font-size: 36px;"></i>
                        </div>
                        <h4 class="font-weight-bold" style="color: #1e293b;">{{ __('general_settings.revalidated_successfully') }}</h4>
                        <p class="text-muted px-4" style="max-width: 450px; margin: 0 auto; font-size: 14px;">
                            {{__('general_settings.revalidated_successfully_desc')}}
                        </p>
                    </div>
                @endif
            </div>

            <div class="modal-footer p-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn-toolkit btn-secondary" data-dismiss="modal" style="border-radius: 8px;">
                    {{ __('common.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .kyc-diff-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        border-color: var(--base_color) !important;
    }
    /* Estilo para el scroll de la modal */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }
    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .modal-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

