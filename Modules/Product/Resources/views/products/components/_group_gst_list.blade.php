@php
    $same_state_gst = json_decode($group->same_state_gst, true);
    $same_state_gst = (array)$same_state_gst;
    $outsite_state_gst = json_decode($group->outsite_state_gst, true);
    $outsite_state_gst = (array)$outsite_state_gst;
@endphp

<div class="p-3 mt-2" style="background-color: #fcfcfc; border: 1px dashed #e1e5eb; border-radius: 8px;">
    
    {{-- ==========================================
         SECCIÓN: SAME STATE
    =========================================== --}}
    <h6 class="text-muted small text-uppercase font-weight-bold mb-3 pb-2 border-bottom">
        <i class="ti-receipt mr-1"></i> {{__('Same State TAX/GST List For: ')}} <strong>{{$group->name}}</strong>
    </h6>
    
    @if(count($same_state_gst) > 0)
        @foreach($same_state_gst as $gst_id => $percent)
            @php
                $gst = \Modules\GST\Entities\GstTax::find($gst_id);
            @endphp
            <div class="d-flex justify-content-between align-items-center mb-2 px-2">
                <span class="text-dark font-weight-500">{{ $gst ? $gst->name : 'N/A' }}</span>
                
                {{-- Si el porcentaje es mayor a 0, lo resaltamos con el color corporativo --}}
                @if($percent > 0)
                    <span class="badge shadow-sm" style="background-color: var(--base_color); color: #fff; font-size: 13px; padding: 5px 10px;">
                        {{$percent}} %
                    </span>
                @else
                    <span class="badge badge-light border text-muted" style="font-size: 13px; padding: 5px 10px;">
                        {{$percent}} %
                    </span>
                @endif
            </div>
        @endforeach
    @else
        <p class="text-muted small px-2 mb-0">No hay impuestos configurados.</p>
    @endif

    {{-- ==========================================
         SECCIÓN: OUTSIDE STATE
    =========================================== --}}
    <h6 class="text-muted small text-uppercase font-weight-bold mt-4 mb-3 pb-2 border-bottom">
        <i class="ti-truck mr-1"></i> {{__('Outsite State TAX/GST List For: ')}} <strong>{{$group->name}}</strong>
    </h6>
    
    @if(count($outsite_state_gst) > 0)
        @foreach($outsite_state_gst as $gst_id => $percent)
            @php
                $gst = \Modules\GST\Entities\GstTax::find($gst_id);
            @endphp
            <div class="d-flex justify-content-between align-items-center mb-1 px-2">
                <span class="text-dark font-weight-500">{{ $gst ? $gst->name : 'N/A' }}</span>
                
                {{-- Lógica de resaltado visual --}}
                @if($percent > 0)
                    <span class="badge shadow-sm" style="background-color: var(--base_color); color: #fff; font-size: 13px; padding: 5px 10px;">
                        {{$percent}} %
                    </span>
                @else
                    <span class="badge badge-light border text-muted" style="font-size: 13px; padding: 5px 10px;">
                        {{$percent}} %
                    </span>
                @endif
            </div>
        @endforeach
    @else
        <p class="text-muted small px-2 mb-0">No hay impuestos configurados.</p>
    @endif

</div>