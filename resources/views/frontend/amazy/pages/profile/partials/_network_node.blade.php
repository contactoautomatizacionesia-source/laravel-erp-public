@php
    $initials = collect(explode(' ', $node['name']))->map(function($p){
        $char = function_exists('mb_substr') ? mb_substr($p, 0, 1) : substr($p, 0, 1);
        return strtoupper($char);
    })->take(2)->implode('');
    $hasChildren = !empty($node['children']);
    $light = $level > 0 ? ' light' : '';
@endphp
<div class="lif-node-wrap" style="display:flex" data-level="{{ $level }}">
    <div class="lif-node{{ $light }}"
         data-id="{{ $node['id'] }}"
         data-name="{{ $node['name'] }}"
         data-plan="{{ $node['plan'] }}"
         data-discount="{{ $node['discount'] }}"
         data-directs="{{ $node['directs'] }}"
         data-points="{{ $node['points'] ?? '0' }}"
         data-level="{{ $level }}">
        <div class="lif-node-head">
            <div class="lif-avatar">{{ $initials }}</div>
            <div>
                <p class="lif-node-name">{{ $node['name'] }}</p>
                <div class="lif-node-meta">{{ $node['id'] }} · {{ $node['discount'] }}%</div>
            </div>
        </div>
        <div style="margin-top:6px;">
            <span class="lif-badge" data-plan="{{ $node['plan'] }}">{{ $node['plan'] }}</span>
        </div>
        <div class="lif-node-foot">
            <span>Directos: {{ $node['directs'] }}</span>
            <span>{{ $node['points'] ?? '0' }} pts</span>
        </div>
        @if($hasChildren)
            <button type="button" class="lif-expand">Expandir / Colapsar</button>
        @endif
    </div>
    @if($hasChildren)
        <div class="lif-children" data-level="{{ $level + 1 }}">
            @foreach($node['children'] as $child)
                @include('frontend.amazy.pages.profile.partials._network_node', ['node' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
