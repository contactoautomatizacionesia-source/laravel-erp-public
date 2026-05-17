<label class="switch_toggle" for="checkbox{{ $states->id }}" 
    @if($states->status == 0 && $states->country && $states->country->status == 0)
        data-bs-toggle="tooltip" title="Inactivo por cascada de: {{ $states->country->name }}"
    @endif>
    <input type="checkbox" id="checkbox{{ $states->id }}" @if (permissionCheck('setup.state.status')) class="status_change" {{$states->status?'checked':''}} value="{{$states->id}}" data-id="{{$states->id}}" @else disabled @endif>
    <div class="slider round"></div>
</label>
