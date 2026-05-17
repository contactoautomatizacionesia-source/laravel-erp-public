@php
    $taxFields = [
        ['label' => __('amazy.iva_responsibility'), 'value' => $customerFinancialProfile?->iva_responsibility ? 'SI' : 'NO'],
        ['label' => __('amazy.rent_retention_agent'), 'value' => $customerFinancialProfile?->rent_retention_agent ? 'SI' : 'NO'],
        ['label' => __('amazy.ica_retention_agent'), 'value' => $customerFinancialProfile?->ica_retention_agent ? 'SI' : 'NO'],
        ['label' => __('amazy.sales_tax_responsible'), 'value' => $customerFinancialProfile?->sales_tax_responsible ? 'SI' : 'NO'],
        ['label' => __('amazy.grand_contributor'), 'value' => $customerFinancialProfile?->grand_contributor ? 'SI' : 'NO'],
        ['label' => __('amazy.self_withholder'), 'value' => $customerFinancialProfile?->self_withholder ? 'SI' : 'NO'],
        ['label' => __('amazy.source_retention'), 'value' => $customerFinancialProfile?->source_retention ? 'SI' : 'NO'],
        // Este campo es "explain reason" si la anterior es NO, lo pongo opcional
        ['label' => __('amazy.retention_reason'), 'value' => $customerFinancialProfile?->retention_reason ?? '---'],
        ['label' => __('amazy.ica_tax'), 'value' => $customerFinancialProfile?->ica_tax ? 'SI' : 'NO'],
        ['label' => __('amazy.ica_rate'), 'value' => $customerFinancialProfile?->ica_rate ? 'SI' : 'NO'],
        ['label' => __('amazy.declaration_city'), 'value' => $customerFinancialProfile?->declarationCity->name ?? '---'],
        ['label' => __('amazy.declaration_pdf'), 'value' => $customerFinancialProfile?->declaration_pdffile ?? '---'],
        ['label' => __('amazy.RUT'), 'value' => $customerFinancialProfile?->has_rut ?? '---'],
    ];
@endphp

@include('customer::customers.components.detail-tabs.info_grid', ['items' => $taxFields])