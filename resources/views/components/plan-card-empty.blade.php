@props([
    'showChangePlan' => false,
])
<x-plan-card
    color="#b7b7b7"
    icon='<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/> <line x1="12" y1="12" x2="12" y2="7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/> <line x1="12" y1="12" x2="16" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/> <circle cx="12" cy="12" r="1" fill="currentColor"/> </svg>'
    name="Sin plan"
    :hasNextPlan="true"
    nextPlanName="Un Sol"
    :progressPercent="0"
    :currentPoints="0"
    :goalPoints="0"
    remainingText="Realiza tu primera compra"
    progressColor="#ccc"
>
    <x-slot name="header">
        <p><span class="badge_5">Comienza ahora</span></p>
    </x-slot>
    <x-slot name="actions">
        @if($showChangePlan)
        <button class="btn-toolkit btn-secondary btn-icon" data-toggle="modal" data-target="#change_plan_modal">
            <i class="ti-pencil"></i>
            Elegir plan
        </button>
        @endif
    </x-slot>
</x-plan-card>
