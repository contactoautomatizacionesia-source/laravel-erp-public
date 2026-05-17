<label class="switch_toggle" for="checkbox{{ $cities->id }}" 
    @if($cities->status == 0 && $cities->state && $cities->state->status == 0)
        data-bs-toggle="tooltip" title="Inactivo por cascada de: {{ $cities->state->name }}"
    @elseif($cities->status == 0 && $cities->state && $cities->state->country && $cities->state->country->status == 0)
        data-bs-toggle="tooltip" title="Inactivo por cascada de: {{ $cities->state->country->name }}"
    @endif>
    <input type="checkbox" id="checkbox{{ $cities->id }}" @if(permissionCheck('setup.city.status')) class="status_change" {{$cities->status?'checked':''}} value="{{$cities->id}}" data-id="{{$cities->id}}" @else disabled @endif>
    <div class="slider round"></div>
</label>
