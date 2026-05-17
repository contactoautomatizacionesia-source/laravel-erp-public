@php
    $basicFields = [
        ['label' => __('amazy.first_name'), 'value' => $customer->first_name ?? '---'],
        ['label' => __('amazy.last_name'), 'value' => $customer->last_name ?? '---'],
        ['label' => __('amazy.middle_name'), 'value' => $customer->middle_name ?? '---'],
        ['label' => __('common.document_type'), 'value' => $customerProfile?->documentType?->name ?? '---'],
        
        ['label' => __('common.document_number'), 'value' => $customerProfile?->document_number ?? '---'],

        ['label' => __('common.gender'), 'value' => $customerProfile?->gender?->name ?? '---'],
        
        // Aquí pasamos null a showDate si no existe el perfil, asegúrate que showDate soporte null o usa el ?? antes
        ['label' => __('common.date_of_birth'), 'value' => showDate($customerProfile?->date_of_birth) ?? '---'],
        
        // Aquí hay doble relación, usamos ?-> dos veces
        ['label' => __('amazy.place_of_birth'), 'value' => $customerProfile?->birthCity?->name ?? '---'],
        
        ['label' => __('amazy.document_issue_date'), 'value' => showDate($customerProfile?->issue_date) ?? '---'],
        
        ['label' => __('amazy.document_issue_place'), 'value' => $customerProfile?->issueCity?->name ?? '---'],

        ['label' => __('amazy.document_expiration_date'), 'value' => showDate($customerProfile?->expiration_date) ?? '---']
    ];
@endphp

@include('customer::customers.components.detail-tabs.info_grid', ['items' => $basicFields])
