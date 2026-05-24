<label class="switch_toggle" for="checkbox{{ $country->id }}">
    <input type="checkbox" id="checkbox{{ $country->id }}" @if(permissionCheck('setup.country.status')) class="status_change" {{$country->status?'checked':''}} value="{{$country->id}}" data-id="{{$country->id}}" @else disabled @endif>
    <div class="slider round"></div>
</label>
@if((int) $country->is_default === 1)
    <span class="badge badge-success d-inline-block mt-2">{{ __('setup.default') }}</span>
@endif
