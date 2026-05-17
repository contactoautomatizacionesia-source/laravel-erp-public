<?php

return [
    // System responses
    'created'              => 'Incident created successfully.',
    'statement_submitted'  => 'Statement submitted successfully.',
    'resolved'             => 'Incident resolved successfully.',
    'voided'               => 'Incident voided successfully.',
    'evidence_uploaded'    => 'Evidence uploaded successfully.',
    'settings_updated'     => 'Settings updated successfully.',
    'linked_to_closing'    => 'Incident linked to cash closing successfully.',
    'error_generic'        => 'An error occurred. Please try again.',

    // Statuses
    'status_pending'              => 'Pending',
    'status_awaiting_statement'   => 'Awaiting Statement',
    'status_awaiting'             => 'Awaiting Statement',
    'status_under_investigation'  => 'Under Investigation',
    'status_investigating'        => 'Under Investigation',
    'status_closed'               => 'Closed',
    'status_voided'               => 'Voided',
    'all_statuses'         => 'All statuses',

    // Types
    'type_transfer'        => 'Transfer',
    'type_inventory_count' => 'Inventory Count',
    'all_types'            => 'All types',

    // Table fields
    'code'           => 'Code',
    'type'           => 'Type',
    'status'         => 'Status',
    'product'        => 'Product',
    'branch'         => 'Branch',
    'advisor'        => 'Advisor',
    'missing_units'  => 'Missing units',
    'total_value'    => 'Total value',
    'total_open_value' => 'Total open value',
    'created_at'     => 'Created at',

    // Filters
    'date_from'      => 'From',
    'date_to'        => 'To',
    'apply_filters'  => 'Filter',

    // Detail
    'product_info'          => 'Product information',
    'responsibility'        => 'Responsibility',
    'responsible_branch'    => 'Responsible branch',
    'responsible_advisor'   => 'Responsible advisor',
    'origin_branch'         => 'Origin branch',
    'origin_advisor'        => 'Origin advisor',
    'public_price'          => 'Public price (snapshot)',
    'statement_info'        => 'Statement',
    'statement_deadline'    => 'Statement deadline',
    'statement_type'        => 'Statement type',
    'statement_acknowledged' => 'Acknowledged error',
    'statement_rejected'     => 'Rejected responsibility',
    'resolution_info'       => 'Resolution',
    'resolution_party'      => 'Responsible party',
    'resolved_at'           => 'Resolved at',
    'resolution_notes'      => 'Justification',
    'expired'               => 'Expired',
    'back_to_list'          => 'Back to list',
    'view_detail'           => 'View detail',

    // Resolution parties
    'party_advisor'      => 'Advisor',
    'party_organization' => 'Organization',
    'party_voided'       => 'Voided',

    // Evidences
    'evidences'          => 'Evidences',
    'add_evidence'       => 'Add evidence',
    'no_evidences'       => 'No evidences attached.',
    'evidence_file'      => 'File',
    'actor_role'         => 'Actor role',
    'role_destination'   => 'Destination',
    'role_origin'        => 'Origin',
    'role_admin'         => 'Administrator',
    'evidence_hint'      => 'Allowed formats: JPG, PNG, PDF. Max 10 MB.',
    'upload_evidence'    => 'Upload evidence',
    'uploading'          => 'Uploading...',
    'sending'            => 'Sending...',
    'notes'              => 'Notes',

    // Audit log
    'audit_log'          => 'Audit log',
    'no_audit_logs'      => 'No audit log entries.',

    // Action modals
    'submit_statement'            => 'Submit statement',
    'statement_type_label'        => 'Statement type',
    'resolve_incident'            => 'Resolve incident',
    'resolution_notes_placeholder'=> 'Describe the administrator\'s justification (minimum 10 characters)...',
    'confirm_resolution'          => 'Confirm resolution',
    'resolve_info_advisor'        => 'The advisor must remedy the situation by purchasing the product at public price.',
    'resolve_info_organization'   => 'The organization will assume the loss through an inventory exit.',
    'void_incident'               => 'Void incident',
    'void_warning'                => 'This action is irreversible. The incident will be voided.',
    'void_reason'                 => 'Reason for voiding',
    'confirm_void'                => 'Confirm void',
    'btn_advisor_pays'            => 'Advisor remedies',
    'btn_org_assumes'             => 'Organization assumes',
    'btn_void'                    => 'Void',
    'btn_link_closing'            => 'Link to closing',

    // Cash closing
    'link_to_closing'            => 'Link to cash closing',
    'cash_closing_id'            => 'Cash closing ID',
    'cash_closing_id_placeholder'=> 'Enter the cash closing ID',
    'cash_closing_hint'          => 'The closing must be in an editable state.',
    'confirm_link'               => 'Confirm link',

    // Settings
    'settings_deadline_section'      => 'Statement deadlines',
    'settings_notifications_section' => 'Notifications',
    'settings_price_section'         => 'Price reference',
    'statement_deadline_hours'       => 'Deadline hours',
    'statement_deadline_hint'        => 'Hours the origin branch has to respond. Default: 48 h.',
    'auto_escalate'                  => 'Auto-escalate when deadline expires',
    'send_reminder'                  => 'Send reminder before deadline',
    'reminder_hours_before'          => 'Hours before deadline to send reminder',
    'send_email'                     => 'Email notifications',
    'send_system_notif'              => 'In-system notifications',
    'price_reference'                => 'Price reference for incidents',
    'price_public'                   => 'Public price',
    'price_cost'                     => 'Cost price',
    'price_transfer'                 => 'Transfer price',
];
