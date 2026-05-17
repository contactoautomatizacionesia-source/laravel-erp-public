<div class="wrap-timeline">
    <div class="timeline">
        @forelse($items as $item)
            @php
                $title = $item['title'] ?? '';
                $meta = $item['meta'] ?? '';
                $dateTitle = $item['date_title'] ?? '';
                $dateHuman = $item['date_human'] ?? '';
                
                $iconSvg = $item['icon_svg'] ?? null;
                $primaryColor = $item['primary_color'] ?? '#28a745';
                $isActive = !empty($item['active']);
            @endphp

            <div class="timeline-item">
                <x-time-arrow-icon />
                
                {{-- ICONO DINÁMICO --}}
                {{-- Si es el plan actual, lo rellenamos de color. Si es histórico, lo dejamos blanco con el borde de color --}}
                

                <div class="timeline-content">
                    <div class="timeline-header">
                        <div class="d-flex">
                            <div class="timeline-icon"
                                style="background-color: #ffffff;
                                        color: {{ $isActive ? '#ffffff' : $primaryColor }};
                                        border: 2px solid {{ $primaryColor }};
                                        display: flex; align-items: center; justify-content: center;">
                                
                                @if(!empty($iconSvg))
                                    <span class="svg-icon-plan" style="width: 14px; height: 14px; display: flex; align-items: center; justify-content: center; fill: currentColor;">
                                        {!! $iconSvg !!}
                                    </span>
                                @else
                                    <i class="fas ti-check" style="font-size: 12px;"></i>
                                @endif
                            </div>
                            <span class="text-black fw-500 d-flex align-items-center flex-wrap" style="gap: 8px;">
                                <span
                                    class="open-plan-modal"
                                    style="cursor:pointer"
                                    data-title="{{ $title }}"
                                    data-items='@json([$item["meta"]] ?? [])'
                                >
                                    {{ $title }}
                                </span>
                                
                                {{-- IDENTIFICADORES VISUALES (DÓNDE ESTÁ Y DÓNDE EMPEZÓ) --}}
                                
                                @if($isActive)
                                    <span class="badge_1" style="color: #fff; border: none; font-size: 10px; padding: 2px 6px;">{{ __('common.current') ?? 'Actual' }}</span>
                                @endif

                                @if($loop->last)
                                    <span class="badge_4" style="font-size: 10px; padding: 2px 6px;">{{ __('common.start') ?? 'Inicio' }}</span>
                                @endif
                            </span>
                        </div>
                        
                        
                        @if(!empty($dateHuman))
                            <span class="timeline-date" title="{{ $dateTitle }}">
                                {{ $dateHuman }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="timeline-meta">
                        {{ $meta }}
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted small">{{ $emptyText ?? __('common.no_results_found') }}</p>
        @endforelse
    </div>
</div>
<div class="modal fade" id="rulesFollowed" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" >{{__('common.rules_followed')}}: <span id="planModalTitle"></span></h5>
        <button type="button" class="close" data-bs-dismiss="modal"><i class="ti-close"></i></button>
      </div>

      <div class="modal-body text-black">
        <ul id="planModalList"></ul>
      </div>

    </div>
  </div>
</div>

