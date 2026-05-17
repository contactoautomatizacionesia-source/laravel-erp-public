@props(['step'])

<div class="multistep-step"
     data-step="{{ $step }}"
     >
    {{ $slot }}
</div>
