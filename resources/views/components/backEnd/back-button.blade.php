@props([
    'href' => null,
    'text' => true,
])

<a href="{{ $href ?? url()->previous() }}"
   onclick="event.preventDefault(); window.history.length > 1 ? history.back() : window.location.href=this.href"
   class="btn-toolkit btn-primary btn-sm  mr-2">
    <i class="ti-arrow-left"></i>

    @if($text)
    {{ $slot->isEmpty() ? __('common.back') : $slot }}
    @endif
</a>
