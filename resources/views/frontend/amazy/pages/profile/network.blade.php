@extends('frontend.amazy.pages.profile.layouts._profile_layout')

@push('styles')
<link rel="stylesheet" href="{{ asset('/public/css/tree.css') }}">
@endpush

@section('profile_content')
<div class="col-12">
    @include('frontend.amazy.pages.profile.partials._network_tree_content', [
        'config' => [
            'show_header' => true,
            'show_metrics' => true,
            'show_filters' => true,
            'is_admin' => false
        ]
    ])
</div>
@endsection

@push('scripts')
<script src="{{ asset('/public/js/d3.v7.min.js') }}"></script>
<script src="{{ asset('/public/js/plan-badge.js') }}"></script>
@include('frontend.amazy.partials.network_scripts', [
    'treeUrl' => route("network.tree"),
    'statsUrl' => route("network.stats"),
    'plansUrl' => route("network.plans"),
    'panelUrl' => route("network.panel"),
    'searchUrl' => route("network.search"),
    'childrenUrl' => route("network.children"),
    'baseUserId' => auth()->id(),
    'clickBehavior' => 'panel'
])
@endpush
