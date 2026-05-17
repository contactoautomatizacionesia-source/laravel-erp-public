<div class="kyc-history-wrapper p-3" style="max-height: 500px; overflow-y: auto; overflow-x: hidden;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-dark-green m-0">{{ __('amazy.kyc_history_title') }}</h4>
    </div>

    @if($customer->dataUpdateLogs && $customer->dataUpdateLogs->count() > 0)
        <div class="kyc-timeline">
            @php 
                $renderedLogs = 0; // Usamos un contador para saber cuál es realmente el primero en renderizarse
            @endphp
            
            @foreach($customer->dataUpdateLogs->sortByDesc('created_at') as $log)
                @php
                    $before = is_string($log->payload_before) ? json_decode($log->payload_before, true) : $log->payload_before;
                    $after = is_string($log->payload_after) ? json_decode($log->payload_after, true) : $log->payload_after;
                    $before = $before ?? []; $after = $after ?? [];
                    $allKeys = array_unique(array_merge(array_keys($before), array_keys($after)));
                    $changedFieldsCount = 0;
                    foreach($allKeys as $key) { if(($before[$key] ?? null) != ($after[$key] ?? null)) $changedFieldsCount++; }
                @endphp

                {{-- YA NO bloqueamos por $changedFieldsCount, mostramos TODO --}}
                @php $renderedLogs++; @endphp
                
                <div class="kyc-timeline-item {{ $renderedLogs === 1 ? 'is-latest' : 'is-older' }}">
                    
                    <div class="kyc-timeline-marker">
                        @if($renderedLogs === 1)
                            <div class="pulse-ring"></div>
                        @endif
                    </div>
                    
                    <div class="kyc-timeline-content form-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-black">
                                    <i class="ti-calendar mr-2"></i>{{ showDate($log->created_at) }} a las {{ date('h:i A', strtotime($log->created_at)) }}
                                    
                                    @if($renderedLogs === 1)
                                        <span class="ml-2 badge_1">
                                            {{__('common.new')}}
                                        </span>
                                    @endif

                                    {{-- Badge especial para Revalidaciones sin cambios --}}
                                    @if($changedFieldsCount == 0)
                                        <span class="ml-2 badge_5">
                                            <i class="ti-check-box mr-1"></i>{{__('common.revalidated')}}
                                        </span>
                                    @endif
                                </h6>
                                <p class="text-muted mb-0" style="font-size: 13px;">
                                    @if($changedFieldsCount > 0)
                                        {{ __('amazy.kyc_detected_changes', ['count' => $changedFieldsCount]) }}
                                    @else
                                        {{ __('general_settings.kyc_revalidated_msg') }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-right d-flex flex-column align-items-end">
                                <button type="button" class="btn-toolkit btn-primary" data-toggle="modal" data-target="#kycDetailModal-{{ $log->id }}">
                                    {{ __('common.details') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            
            {{-- Indicador visual de fin de historial --}}
            <div class="kyc-timeline-end">
                <span style="font-size: 11px; color: #94a3b8; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="ti-time mr-1"></i> {{__('common.data_origin')}}
                </span>
            </div>
        </div>
    @else
        <div class="alert alert-info d-flex align-items-center justify-content-center flex-column py-5" style="border-radius: 12px;">
            <i class="ti-info-alt mb-2" style="font-size: 30px; opacity: 0.5;"></i>
            <h5>{{ __('common.no_records_found') }}</h5>
            <p class="mb-0">{{ __('general_settings.kyc_no_history_desc') }}</p>
        </div>
    @endif
</div>

<style>
    /* Contenedor principal de la línea */
    .kyc-timeline { 
        position: relative; 
        padding-left: 35px; 
        margin-top: 25px; 
        padding-bottom: 20px;
    }
    
    /* La Línea Vertical con Degradado (De color corporativo a gris) */
    .kyc-timeline::before { 
        content: ''; 
        position: absolute; 
        top: 10px; 
        bottom: 0; 
        left: 14px; 
        width: 3px; 
        background: linear-gradient(to bottom, var(--base_color) 0%, #e2e8f0 25%, #e2e8f0 100%);
        border-radius: 3px;
        z-index: 0;
    }

    /* Flecha al final de la línea */
    .kyc-timeline::after {
        content: '';
        position: absolute;
        bottom: 12px;
        left: 10.5px; /* Centrado con la línea de 3px */
        border-width: 8px 5px 0 5px;
        border-style: solid;
        border-color: #cbd5e1 transparent transparent transparent;
        z-index: 1;
    }

    .kyc-timeline-item { 
        position: relative; 
        margin-bottom: 30px; 
    }

    /* El círculo base */
    .kyc-timeline-marker { 
        position: absolute; 
        left: -30.5px; /* Alineado al centro de la línea */
        top: 18px; 
        width: 18px; 
        height: 18px; 
        border-radius: 50%; 
        background: #fff;
        border: 4px solid #cbd5e1; 
        z-index: 2; 
        transition: all 0.3s; 
    }

    /* Estilo Especial para el Más Reciente */
    .kyc-timeline-item.is-latest .kyc-timeline-marker {
        background: var(--base_color);
        border-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(var(--base_color_rgb), 0.3);
    }

    .kyc-timeline-item.is-latest .kyc-timeline-content {
        border: 1px solid rgba(var(--base_color_rgb), 0.3) !important;
    }

    /* Animación Radar / Pulso para el Nuevo */
    .pulse-ring {
        position: absolute;
        top: -6px;
        left: -6px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid var(--base_color);
        animation: radar-pulse 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    }

    @keyframes radar-pulse {
        0% { transform: scale(0.8); opacity: 1; }
        100% { transform: scale(2.5); opacity: 0; }
    }

    /* Contenido de la Tarjeta */
    .kyc-timeline-content { 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important; 
        border: 1px solid #e2e8f0 !important; 
        padding: 20px !important; 
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease; 
        background: #fff;
    }
    
    .kyc-timeline-content:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.08) !important; 
    }

    /* Texto final de la línea */
    .kyc-timeline-end {
        position: relative;
        left: -8px;
        margin-top: 10px;
    }

    /* Scrollbar estético para el contenedor */
    .kyc-history-wrapper::-webkit-scrollbar {
        width: 6px;
    }
    .kyc-history-wrapper::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    .kyc-history-wrapper::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .kyc-history-wrapper::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>