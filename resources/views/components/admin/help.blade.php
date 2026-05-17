@props([
    'id' => 'help_modal',
    'title' => __('common.help')
])

{{-- Botón --}}
<button type="button" class="btn-toolkit btn-sm" data-toggle="modal" data-target="#{{ $id }}">
    <i class="ti-help border rounded-pill p-2"></i>
</button>

{{-- Modal --}}
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="ti-close"></i>
                </button>
            </div>

            <div class="modal-body">
                {{ $slot }}
            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                    {{ __('common.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
