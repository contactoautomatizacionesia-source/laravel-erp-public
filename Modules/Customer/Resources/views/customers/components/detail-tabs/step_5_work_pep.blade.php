@php
    $workFields = [
        ['label' => __('amazy.company_name'), 'value' => $customerFinancialProfile?->company_name ?? '---'],
        ['label' => __('amazy.job_title'), 'value' => $customerFinancialProfile?->job_title ?? '---'],
        ['label' => __('amazy.work_city_dept'), 'value' => $customerFinancialProfile?->work_address ?? '---'],
        ['label' => __('amazy.administers_public_resources'), 'value' => $customerFinancialProfile?->public_resources ? 'SI' : 'NO'],
        ['label' => __('amazy.marital_society_active'), 'value' => $customerFinancialProfile?->marital_society ? 'SI' : 'NO'],
        ['label' => __('amazy.is_pep'), 'value' => $customerFinancialProfile?->is_pep ? 'SI' : 'NO'],
        ['label' => __('amazy.has_pep_family'), 'value' => $customerFinancialProfile?->pep_family ? 'SI' : 'NO'],
    ];
@endphp

@include('customer::customers.components.detail-tabs.info_grid', ['items' => $workFields])
