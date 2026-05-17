@props([
    'planContext' => [],
    
    // Props directas (usadas por plan-card-empty, sin planContext)
    'color' => null,
    'icon' => null,
    'name' => null,
    'hasNextPlan' => null,
    'nextPlanName' => null,
    'progressPercent' => null,
    'currentPoints' => null,
    'goalPoints' => null,
    'progressColor' => null,

    // Opcionales para sobrescribir la lógica del contexto
    'remainingText' => '-',

    // Props visuales y de texto
    'extraClasses' => '',
    'iconSize' => 'svg-md',
    'nextPlanLabel' => __('common.next_plan'),
    'maxTitle' => __('common.max_plan_title'),
    'maxDesc' => __('common.max_plan_desc'),
    'insigniaClass' => '',
])

@php
    // Modo 1: planContext completo (dashboard / show_details con plan asignado)
    // Modo 2: props directas (plan-card-empty → sin planContext)
    $hasContext = !empty($planContext);
    $hasDirectProps = $color !== null;
    $shouldRender = $hasContext || $hasDirectProps;

    if ($hasContext) {
        $color         = data_get($planContext, 'current_plan.styles.primaryColor', '#ccc');
        $icon          = data_get($planContext, 'current_plan.styles.icon');
        $name          = data_get($planContext, 'display_name', '-');
        $hasNextPlan   = !empty($planContext['next_plan'] ?? null);
        $nextPlanName  = data_get($planContext, 'next_plan_child.display_name');
        $nextPlanScaleType = data_get($planContext, 'next_plan.scale_type');
        $nextPlanIcon  = data_get($planContext, 'next_plan.styles.icon');
        $progressColor = data_get($planContext, 'next_plan.styles.primaryColor', '#ccc');
        $progressPercent = data_get($planContext, 'progress_to_next_plan', 0);
        $currentPoints = data_get($planContext, 'current_points', 0);
        $goalPoints    = data_get($planContext, 'next_plan_target_points', 0);
    } else {
        // Props directas ya están asignadas vía @props
        $nextPlanScaleType = null;
        $nextPlanIcon      = null;
    }
@endphp

@if($shouldRender)

    <div class="form-card wrap-current-plan {{ $extraClasses }}"
         style="border-color:{{ $color }}; background-color:{{ $color }}24">

        <div>
            {{-- INSIGNIA --}}
            <div class="wrap-insignia">
                <div class="insignia mb-3 {{ $insigniaClass }}" style="border-color: {{ $color }};">
                    <span class="svg-icon-plan {{ $iconSize }}">
                        {!! $icon !!}
                    </span>
                </div>

                <h2 class="fs-20 uppercase fw-500 mb-2 text-dark-green"
                    data-plan-color="{{ $color }}">
                    {{ $name }}
                </h2>

                {{-- SLOT EXTRA --}}
                {{ $header ?? '' }}
            </div>

            {{-- PROGRESO --}}
            <div class="form-card shadow-none my-3">
                @if($hasNextPlan)
                    <div>
                        <p class="text-muted mb-1">
                            {{ $nextPlanLabel }}
                            {{ $nextPlanBadge ?? '' }}
                            @if ($nextPlanScaleType)
                                <span class="badge_micro">{{ __('common.'.$nextPlanScaleType) }}</span>
                            @endif
                        </p>

                        <div class="d-flex justify-content-between">
                            <p class="fs-20 fw-500">
                                <span class="svg-icon-plan svg-xs">
                                    {!! $nextPlanIcon !!}
                                </span>
                                {{ $nextPlanName }}
                            </p>
                            <span class="fs-20 fw-500" style="color: {{ $color }} !important;">{{ $progressPercent }}%</span>
                        </div>
                    </div>

                    {{-- BARRA --}}
                    <div class="my-2" style="width:100%; background:#e9ecef; border-radius:10px; height:8px;">
                        <div style="
                            width: {{ $progressPercent }}%;
                            height: 100%;
                            background: {{ $color }} !important;
                            border-radius: 10px;
                        "></div>
                    </div>

                    {{-- META --}}
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <span class="text-muted">{{ $currentPoints }}</span> /
                            <span class="text-muted">{{ $goalPoints }} pts</span>
                        </div>
                        <div>
                            <span class="text-muted" style="font-style:italic">
                                {{ $remainingText }}
                            </span>
                        </div>
                    </div>
                    <div class="text-center">
                        {{ $requirements ?? '' }}
                    </div>
                @else
                    <div class="text-center py-3">
                        <p class="fw-500">{{ $maxTitle }}</p>
                        <small class="text-muted">{{ $maxDesc }}</small>
                    </div>
                @endif
            </div>

            {{-- BOTONES --}}
            <div class="text-center mt-2 d-flex gap-2 justify-content-center">
                {{ $actions ?? '' }}
            </div>
        </div>
    </div>
@endif
