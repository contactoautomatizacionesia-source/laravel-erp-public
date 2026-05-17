{{-- Vista de red unificada para el admin --}}
@php
    $admin_config = [
        'show_header' => true,
        'show_metrics' => true,
        'show_filters' => true,
        'is_admin' => true
    ];
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('/public/css/tree.css') }}">
@endpush

<div class="lif-admin-network-unified">
    @include('frontend.amazy.pages.profile.partials._network_tree_content', ['config' => $admin_config])
</div>

@push('scripts')
<script src="{{ asset('/public/js/d3.v7.min.js') }}"></script>
<script src="{{ asset('/public/js/plan-badge.js') }}"></script>

@include('frontend.amazy.partials.network_scripts', [
    'treeUrl' => route("network.admin.tree",  ["userId" => $customer->id]),
    'statsUrl' => route("network.admin.stats", ["userId" => $customer->id]),
    'plansUrl' => route("network.admin.plans", ["userId" => $customer->id]),
    'searchUrl' => route("network.admin.search", ["userId" => $customer->id]),
    'panelUrl' => route("network.admin.panel", ["userId" => $customer->id]),
    'childrenUrl' => route("network.admin.children", ["userId" => $customer->id]),
    'baseUserId' => $customer->id,
    'clickBehavior' => 'panel',
    'config' => $admin_config
])
@endpush
