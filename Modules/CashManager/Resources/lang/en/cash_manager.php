<?php

return [

    // ── General ───────────────────────────────────────────────────────────────
    'cash_management'   => 'Cash Management',
    'save_changes'      => 'Save Changes',

    // ── Sidebar menu ──────────────────────────────────────────────────────────
    'operations'        => 'Closures',
    'assignments'       => 'Assignments',
    'settings'          => 'Settings',

    // ── Settings ──────────────────────────────────────────────────────────────
    'settings_title'       => 'Cash Settings',
    'tab_denominations'    => 'Denominations',
    'tab_structure'        => 'Cash Box Structure',
    'tab_roles'            => 'Operator Roles',

    // Denominations
    'new_denomination'       => 'New Denomination',
    'denomination_country'   => 'Country',
    'denomination_created'   => 'Denomination created successfully.',
    'denomination_duplicate' => 'A denomination with that value already exists for this country.',
    'value'                  => 'Value',
    'value_example'          => '(e.g. 50000)',
    'type'                   => 'Type',
    'type_bill'              => 'Bill',
    'type_coin'              => 'Coin',

    // Boxes
    'new_box'              => 'New Box',
    'register_new_box'     => 'Register New Box',
    'unique_code'          => 'Code',
    'box_name'             => 'Box Name',
    'hierarchy_type'       => 'Type',
    'type_vault'           => 'Vault (Mother)',
    'type_principal'       => 'Principal Box (Branch)',
    'type_auxiliary'       => 'Auxiliary Box (Operator)',
    'initial_base'         => 'Initial Base ($)',
    'alert_threshold'      => 'Alert Threshold ($)',
    'box_parent'           => 'Parent Box',
    'no_parent'            => 'No parent',
    'box_created'          => 'Box created with code :code.',
    'status'               => 'Status',
    'actions'              => 'Actions',
    'box_status_available'        => 'Available',
    'box_status_open'             => 'In Use',
    'box_status_pending_receipt'  => 'Pending Receipt',
    'box_status_maintenance'      => 'Maintenance',
    'box_status_inactive'         => 'Inactive',

    // Roles
    'roles_description' => 'Select the system roles that are allowed to operate cash boxes.',
    'save_roles'        => 'Save Roles',
    'roles_min_one'     => 'You must select at least one role.',

    // ── Assignments ───────────────────────────────────────────────────────────
    'assignments_title'        => 'Cash Box Personnel Assignment',
    'box_free'                 => 'Free',
    'box_occupied'             => 'In Use',
    'box_waiting_operator'     => 'No operator assigned',
    'no_boxes_configured'      => 'No boxes configured. Set them up in Settings → Cash Box Structure.',
    'base_assigned'            => 'Assigned base',
    'assign_operator'          => 'Assign Operator',
    'revoke_assignment'        => 'Release Box',
    'revoke_confirm'           => 'Are you sure you want to release this box?',
    'assign_modal_title'       => 'Assign operator to :box',
    'select_cashier'           => 'Select Operator',
    'select_user_placeholder'  => 'Select a user...',
    'delivery_warning'         => 'You are about to physically hand over :amount as the initial base to the operator.',
    'confirm_delivery'         => 'Confirm Delivery & Open Box',
    'assigned_since'           => 'Since:',
    'assignment_success'       => 'Box assigned and session opened successfully.',
    'revoke_success'           => 'Assignment revoked. The box is now available again.',

    // Receipt confirmation (parent box responsible)
    'confirm_receipt'              => 'Confirm Receipt',
    'confirm_receipt_warning'      => 'You are about to confirm the physical receipt of :amount from operator :user. This will permanently close the session.',
    'receipt_confirmed_success'    => 'Receipt confirmed. Session closed successfully.',
    'session_pending_receipt_badge'=> 'Pending Receipt',
    'error_session_not_pending'    => 'This session is not pending receipt.',
    'error_not_parent_box_responsible' => 'You are not authorized to confirm this box. Only the responsible of the parent box can do so.',
    'reviewer_has_incidents'       => 'Were any incidents found during review?',
    'reviewer_notes_label'         => 'Reviewer notes',
    'reviewer_notes_placeholder'   => 'Observations when confirming receipt...',

    // Submit to parent level (PRINCIPAL → VAULT)
    'submit_to_parent'             => 'Send Report to Vault',
    'submit_to_parent_confirm'     => 'Confirm sending the consolidated report to the VAULT? All auxiliary boxes have been reviewed.',
    'submitted_to_vault_success'   => 'Report sent to Vault. Pending review.',
    'error_only_principal_can_submit' => 'Only a PRINCIPAL box can submit reports to the VAULT.',
    'error_children_not_closed'    => 'Cannot submit: :count auxiliary box(es) still pending review.',
    'error_not_box_responsible'    => 'You do not have an active assignment for this box.',

    // Box creation hierarchy
    'box_type_auto'             => 'Type determined automatically',
    'box_parent_auto'           => 'Parent box (assigned automatically)',
    'error_hierarchy_violated'  => 'Cannot create box: system hierarchy does not allow it.',
    'box_type_hint_vault'       => 'Will be created as Vault (none exists in the system)',
    'box_type_hint_principal'   => 'Will be created as Principal Box for this cost center',
    'box_type_hint_auxiliary'   => 'Will be created as Auxiliary Box under the cost center\'s principal box',
    'select_cc_first'           => 'Select a cost center first',

    // Assignment errors
    'pending_receipt_manage_in_operations' => 'Closure pending — manage in Operations',
    'review_manage_in_operations'          => 'Manage closures in the Operations section',
    'error_box_already_assigned'  => 'This box already has an assigned operator.',
    'error_user_already_assigned' => 'The selected user already has an active box.',
    'error_box_not_available'     => 'The box is not available for assignment.',
    'error_revoke_pending'        => 'Cannot revoke: the cash is already in transit and pending receipt.',

    // ── Operations (Closures & Counting) ──────────────────────────────────────
    'operations_title'           => 'Cash Register Closure & Counting',

    // Review view (PRINCIPAL / VAULT)
    'review_title_principal'     => 'Closure Review — Principal Box',
    'review_title_vault'         => 'Report Review — Vault',
    'review_pending_count'       => ':count closure(s) pending review',
    'review_no_pending'          => 'No pending closures',
    'review_no_pending_hint'     => 'All subordinate boxes are up to date.',
    'review_submitted_waiting'   => 'Report submitted — awaiting confirmation from the parent box.',
    'review_submitted_hint'      => 'Once the parent box confirms receipt, this session will be closed.',
    'review_already_submitted'        => 'Report already submitted',
    'review_already_confirmed'        => 'Closures already confirmed this shift',
    'session_confirmed_badge'         => 'Confirmed',
    'review_vault_total_received'     => 'Consolidated total to receive',
    'review_breakdown_by_auxiliary'   => 'Breakdown by auxiliary box',
    'review_breakdown_by_principal'   => 'Breakdown by principal box',

    // Session history
    'history_title'   => 'Closure history',
    'history_opened'  => 'Opened',
    'submit_pending_children'    => 'There are still closures pending review in auxiliary boxes.',
    'has_incidents_badge'        => 'Has incidents',
    'no_incidents_badge'         => 'No incidents',
    'opening_base'               => 'Opening base',
    'closed_at'                  => 'Closed',

    // Session summary
    'session_summary'            => 'My Session',
    'user'                       => 'Operator',
    'active_box'                 => 'Box',
    'cost_center'                => 'Cost Center',
    'session_opened_at'          => 'Opened',
    'session_status_open'        => 'Open',
    'session_status_pending_receipt' => 'Pending Receipt',
    'session_status_closed'      => 'Closed',
    'session_status_disputed'    => 'Disputed',
    'time_elapsed'               => 'Time Elapsed',
    'session_already_closed'     => 'This session has already been closed.',
    'session_closed_success'     => 'Box closed successfully. Pending receipt by the parent box.',

    // No session
    'no_session_title'   => 'No box assigned',
    'no_session_message' => 'You have no active box at the moment. Contact your supervisor to get one assigned.',

    // Payment methods
    'payment_methods_declared'   => 'Received Payment Methods',
    'payment_methods_hint'       => 'Enable the payment methods you received during the shift and enter the totals.',
    'total_amount'               => 'Total Received',
    'transaction_count'          => 'Transaction Count',
    'reference_data'             => 'Reference / Batch',
    'reference_placeholder'      => 'E.g. Batch 0042, voucher...',
    'total_declared'             => 'Total Declared',
    'enable_payment_form'        => 'Enable this payment method',

    // Physical count
    'physical_count'     => 'Physical Count',
    'denomination'       => 'Denomination',
    'quantity'           => 'Quantity',
    'subtotal'           => 'Subtotal',
    'count_summary'      => 'Count summary',

    // Totals and difference
    'total_counted'      => 'Total Counted',
    'base_to_deduct'     => 'Initial Base (to deduct)',
    'cash_to_deliver'    => 'Cash to Deliver',
    'system_expected'    => 'Expected (System)',
    'difference'         => 'Difference',

    // Discrepancy (type + justification + notes)
    'discrepancy_type'            => 'Discrepancy Type',
    'discrepancy_type_placeholder'=> 'Select a type...',
    'justification'               => 'Justification (Required)',
    'justification_placeholder'   => 'Explain the reason for the discrepancy...',
    'notes'                       => 'Additional notes',
    'notes_placeholder'           => 'Additional details about the incident...',
    'notes_required_for_other'    => 'Notes are required when the type is "Other".',
    'error_justification_required'=> 'A justification is required when there is a discrepancy in the count.',
    'error_discrepancy_type_required' => 'You must select a discrepancy type when there is a difference in the count.',

    // Close button
    'close_box'          => 'Close Box & Submit',

    // Close errors
    'error_not_your_session' => 'You cannot close a session that does not belong to you.',
    'error_session_not_open' => 'This session is not open or has already been closed.',
    'error_no_denominations' => 'You must enter at least one denomination with a quantity greater than zero.',
    'error_no_payments'      => 'You must enable at least one payment method with the received total.',

    // Menú Lateral (Sidebar)
    'cash_management'    => 'Cash Management',

    // Menu permissions
    'operations'         => 'Operations',
    'assignments'        => 'Assignments',
    'view_operations'    => 'View operations',
    'manage_assignments' => 'Manage assignments',
    'admin_settings'     => 'Admin settings',
];
