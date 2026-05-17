@php
    // Datos simulados o provenientes del modelo $customer
    // Usamos null coalescing (??) para evitar errores si el campo no existe aún
    $currentBalance = data_get($planContext ?? [], 'current_points', 0);
    $targetBalance = data_get($planContext ?? [], 'next_plan_target_points', 0);
    $progressPercent = data_get($planContext ?? [], 'progress_to_next_plan', 0);

    $scoreDetails = [
       
        [
            'title' => __('amazy.personal_points_month'),
            'value' => number_format($scoreData['personal_points_month'] ?? 0, 2, ',', '.'),
            'icon'  => 'ti-user',
            'color' => 'var(--base_color)'
        ],
        
        [
            'title' => __('amazy.personal_points_accumulated'),
            'value' => number_format($scoreData['personal_points_accumulated'] ?? 0, 2, ',', '.'),
            'icon'  => 'ti-package',
            'color' => 'var(--base_color)'
        ],
        
        [
            'title' => __('amazy.net_points_month'),
            'value' => number_format($scoreData['net_points_month'] ?? 0, 2, ',', '.'),
            'icon'  => 'ti-wallet',
            'color' => 'var(--base_color)'
        ],
    ];
@endphp

<div class="score-container">

    <div class="row">
        {{-- Tarjetas de métricas --}}
        @foreach ($scoreDetails as $detail)
            <div class="col-xl-6 col-lg-6 col-md-12 mb_20">
                {{-- Aquí usamos align-items-center para que icono y texto queden alineados --}}
                <div class="customer-info-card d-flex align-items-center">
                    
                    {{-- Icono --}}
                    <div class="icon-circle-box" style="background:rgba(var(--base_color_rgb), 0.1);">
                        <i class="{{ $detail['icon'] }}" style="font-size: 20px; color: {{ $detail['color'] }};"></i>
                    </div>
                    
                    {{-- Textos --}}
                    <div class="overflow-hidden">
                        <span class="card-label" title="{{ $detail['title'] }}">
                            {{ $detail['title'] }}
                        </span>
                        <h4 class="card-value">
                            {{ $detail['value'] }} 
                            @if(isset($detail['sub_value']))
                                <small class="text-muted" style="font-size: 12px;">{{ $detail['sub_value'] }}</small>
                            @endif
                        </h4>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Barra de Progreso (Mantenemos un poco de estilo inline específico para la animación de la barra) --}}
        <div class="col-md-6 mb_20">
            <div class="customer-info-card partial-card d-flex flex-column justify-content-center">
                
                <div class="w-100 mb-3">
                    <span class="card-label">
                        {{ __('amazy.partial_balance') }}
                    </span>
                </div>

                <div style="width:100%; position: relative; margin-top: 5px;">
                    {{-- Indicador flotante --}}
                    <div style="
                        position: absolute;
                        left: {{ $progressPercent }}%;
                        top: -25px;
                        transform: translateX(-50%);
                        font-size: 12px;
                        font-weight: 700;
                        color: var(--base_color);
                        white-space: nowrap;
                        transition: left 0.5s ease;
                    ">
                        {{ number_format($currentBalance, 0, ',', '.') }}
                    </div>

                    {{-- Background Barra --}}
                    <div style="width:100%; background:#e9ecef; border-radius:10px; height:8px; overflow: hidden;">
                        {{-- Relleno Barra --}}
                        <div style="
                            width: {{ $progressPercent }}%;
                            height: 100%; 
                            background: var(--base_color);
                            border-radius: 10px;
                            transition: width 0.5s ease;
                        "></div>
                    </div>

                    {{-- Meta --}}
                    <div style="position: absolute; right: 0; top: 12px; font-size: 11px; color: #adb5bd;">
                        {{ number_format($targetBalance, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
