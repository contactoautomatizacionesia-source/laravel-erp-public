@props([
    'type' => null,
    'permission' => null,
    'route' => '#',
    'title' => '',
    'icon' => '',
    'value' => 0,
    'module' => null,
    'isCurrency' => false
])

@php
    $dashboardItem = $type ? app('dashboard_setup')->where('type', $type)->first() : null;
@endphp

@if(
    (!$permission || permissionCheck($permission)) &&
    (!$module || isModuleActive($module)) &&
    ($dashboardItem && $dashboardItem->is_active)
)

@php
    $activeClass = $dashboardItem->is_active == 1
        ? 'active'
        : ($dashboardItem->is_active == 2 ? 'bg_active' : '');

    
    $translatedValue = $isCurrency ? single_price($value) : getNumberTranslate($value)  ;
@endphp

<div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 mb-sm-4 mb-2 {{$type}} px-2">
    <div class="white-box single-summery {{ $activeClass }}">
        <a href="{{ $route != '#' ? route($route) : '#' }}" target="_blank">
            <div class="d-block">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class=" fw-500">{{ __($title) }}</h3>
                    <span class="{{ $icon }} stat-icon"></span>
                    <img class="demo_wait d-none" height="60px"
                         src="{{ showImage('backend/img/loader.gif') }}" alt="">
                </div>

                <div class="my-2">
                    <h1 class="stat-number {{ visualSizeClass($translatedValue) }}">
                        {{ $translatedValue }}
                    </h1>
                </div>

                
            </div>
        </a>
    </div>
</div>

@endif
