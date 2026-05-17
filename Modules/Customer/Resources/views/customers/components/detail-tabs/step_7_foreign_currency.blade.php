@php
    $foreignFields = [
        ['label' => __('amazy.ops_foreign_currency'), 'value' => $customerFinancialProfile?->ops_foreign_currency ? 'SI' : 'NO'],
        ['label' => __('amazy.ops_foreign_desc'), 'value' => $customerFinancialProfile?->ops_foreign_desc ?? '---'],
        ['label' => __('amazy.has_foreign_accounts'), 'value' => $customerFinancialProfile?->has_foreign_accounts ? 'SI' : 'NO'],
        ['label' => __('amazy.foreign_bank'), 'value' => $customerFinancialProfile?->foreign_bank ?? '---'],
        ['label' => __('amazy.foreign_account_number'), 'value' => $customerFinancialProfile?->foreign_account_number ?? '---'],
        ['label' => __('amazy.currency'), 'value' => $customerFinancialProfile?->foreign_currency ?? '---'],
        ['label' => __('amazy.foreign_country'), 'value' => $customerFinancialProfile?->foreignCountry?->name ?? '---'],
        ['label' => __('amazy.foreign_city'), 'value' => $customerFinancialProfile?->foreignCity?->name ?? '---'],
    ];
@endphp

@include('customer::customers.components.detail-tabs.info_grid', ['items' => $foreignFields])
