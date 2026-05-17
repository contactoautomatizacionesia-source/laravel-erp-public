@props([
    'steps' => []
])

@once
    <link rel="stylesheet" href="{{ asset('public/frontend/FormWizard/multistep.css') }}">
@endonce

<form {{ $attributes }} id="{{ $id ?? 'multistep-form' }}" data-multistep novalidate>
    @csrf

    {{-- Step indicator --}}
    <ul class="multistep-indicator scroll-x mb-4">
        @foreach($steps as $index => $label)
            <li class="multistep-indicator__step"
                data-step-indicator="{{ $index }}" data-action="any">
                <span class="step-number">{{ $index + 1 }}</span>
                <span class="step-label">{{ __($label) }}</span>
            </li>
        @endforeach
    </ul>

    <div class="multistep-wrapper">
        {{ $slot }}
    </div>

    {{-- Acciones del formulario --}}
    @isset($actions)
        <div class="multistep-actions mt-4">
            {{ $actions }}
        </div>
    @endisset
</form>

@once
   <script src="{{ asset('public/frontend/FormWizard/multistep.js') }}" defer></script>
@endonce