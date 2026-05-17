@php

    $financialFields = [
        ['label' => __('amazy.monthly_income'), 'value' => single_price($customerFinancialProfile?->monthly_income ?? 0)],
        ['label' => __('amazy.monthly_expenses'), 'value' => single_price($customerFinancialProfile?->monthly_expenses ?? 0)],
        ['label' => __('amazy.other_income'), 'value' => single_price($customerFinancialProfile?->other_income ?? 0)],
        ['label' => __('amazy.other_income_desc'), 'value' => $customerFinancialProfile?->other_income_desc ?? '---'],
        ['label' => __('amazy.total_assets'), 'value' => single_price($customerFinancialProfile?->total_assets ?? 0)],
        ['label' => __('amazy.total_liabilities'), 'value' => single_price($customerFinancialProfile?->total_liabilities ?? 0)],
        ['label' => __('amazy.total_equity'), 'value' => single_price($customerFinancialProfile?->total_equity ?? 0)],
    ];
@endphp

@include('customer::customers.components.detail-tabs.info_grid', ['items' => $financialFields])
