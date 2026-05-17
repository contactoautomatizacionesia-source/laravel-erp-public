{{-- Vista de Árbol Global para el Superadmin --}}
@extends('backEnd.master')

@section('styles')
<link rel="stylesheet" href="{{ asset('/public/css/tree.css') }}">
@endsection

@section('mainContent')
@php
    $admin_config = [
        'show_header' => true, // Mostrar header para que se vea el título "Red Global"
        'show_metrics' => true,
        'show_filters' => true,
        'is_admin' => true
    ];
@endphp


<x-admin.section>
<div class="lif-admin-network-unified">
    {{-- Reutilizamos el componente que ya tienes --}}
    @include('frontend.amazy.pages.profile.partials._network_tree_content', ['config' => $admin_config])
</div>
</x-admin.section>

@push('scripts')
<script src="{{ asset('/public/js/d3.v7.min.js') }}"></script>
<script src="{{ asset('/public/js/plan-badge.js') }}"></script>

{{-- Inyectamos los endpoints apuntando SIEMPRE al ID del Empresario Root --}}
@include('frontend.amazy.partials.network_scripts', [
    'treeUrl' => route("network.admin.tree",  ["userId" => $rootUserId]),
    'statsUrl' => route("network.admin.stats", ["userId" => $rootUserId]),
    'plansUrl' => route("network.admin.plans", ["userId" => $rootUserId]), 
    'panelUrl' => route("network.admin.panel", ["userId" => $rootUserId]),
    'searchUrl' => route("network.admin.search", ["userId" => $rootUserId]),
    'childrenUrl' => route("network.admin.children", ["userId" => $rootUserId]),
    'clickBehavior' => 'panel',
    'baseUserId' => $rootUserId,
    'config' => $admin_config
])
@endpush
@endsection
