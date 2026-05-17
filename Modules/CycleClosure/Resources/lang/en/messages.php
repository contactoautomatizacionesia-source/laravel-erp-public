<?php

return [
    // Validation
    'period_type_required'    => 'The closing period type is required.',
    'period_type_invalid'     => 'Invalid period type.',
    'execution_day_required'  => 'The execution day is required for monthly period.',
    'execution_day_range'     => 'Execution day must be between 1 and 31.',
    'executor_required'       => 'A designated executor is required.',
    'executor_not_found'      => 'The selected executor was not found.',
    'executor_same_user'      => 'The executor cannot be the same user making the configuration.',
    'approver_required'       => 'A co-approver (Accountant) is required.',
    'approver_not_found'      => 'The selected co-approver was not found.',
    'approver_same_user'      => 'The co-approver cannot be the same user making the configuration.',
    // Settings
    'setting_saved'           => 'Configuration saved and activated. The previous one was superseded.',

    // Cycle
    'pre_validation_failed'       => 'Pre-validation failed. The designated executor has been notified for review.',
    'pre_validation_passed'       => 'Pre-validation passed. Waiting for co-approver confirmation.',
    'closure_approve'             => 'Sign / Approve',
    'cycle_approved'              => 'Cycle approved. The act has been generated.',
    'cycle_rejected'              => 'Cycle rejected by the co-approver.',
    'cycle_cancelled'             => 'Cycle cancelled successfully.',
    'executor_approved'           => 'Cycle manually approved. The co-approver has been notified.',
    'cycle_not_pending'           => 'The cycle is not in pending approval status.',
    'cycle_not_needs_review'      => 'The cycle does not require executor review.',
    'not_authorized_executor'     => 'You are not authorized to act as executor for this cycle.',
    'not_authorized_approver'     => 'You are not authorized to approve this cycle.',
    'status_changed'              => 'Cycle status updated successfully.',
    'scheduled'                   => 'Scheduled',
    'pending'                     => 'Pending',

    // Phases
    'phase_pipeline'               => 'Pipeline',
    'phase_pipeline_start'         => 'Closure Start',
    'phase_pipeline_end'           => 'Closure Finished',
    'phase_act_generation'         => 'Act Generation',
    'phase_check_pending_orders'   => 'Pending Orders Check',
    'phase_check_pending_inventory'=> 'Pending Inventory Check',
    'phase_sales_consolidation'    => 'Sales Consolidation',
    'phase_points_conversion'      => 'Points Conversion',
    'phase_pre_validation'         => 'Pre-Validation',
    'phase_consolidation'          => 'Consolidation',
    'phase_pdf_generation'         => 'PDF Generation',
    'phase_block'                  => 'Period Lock',
    'phase_notification'           => 'Notification',

    // Status labels
    'status_running'          => 'Running',
    'status_needs_review'     => 'Needs Review',
    'status_pre_validation'   => 'Validating',
    'status_pending_approval' => 'Pending Approval',
    'status_processing'       => 'Processing',
    'status_closed'           => 'Closed',
    'status_cancelled'        => 'Cancelled',

    // Day 31 note
    'day_31_note'             => 'In short months, the closure will run on the last day of the month.',

    // Period lock
    'period_blocked'          => 'The requested period is locked by a cycle closure. Retroactive modifications are not allowed.',

    // UI labels (settings)
    'settings_active'              => 'Active configuration',
    'settings_history_title'       => 'Configuration history',
    'settings_configured_by'       => 'Configured by',
    'setting_status_active'        => 'Active',
    'setting_status_superseded'    => 'Superseded',
    'day'                     => 'Day',
    'period_type_label'       => 'Period Type',
    'period_daily'            => 'Daily',
    'period_monthly'          => 'Monthly',
    'period_annual'           => 'Annual',
    'execution_day_label'     => 'Execution Day',
    'executor_label'          => 'Executor',
    'executor_help'           => 'Administrator designated to review if the automatic closure fails (SuperAdmin or Admin).',
    'double_approval_label'   => 'Co-Approver',
    'approver_help'           => 'Accountant who signs the closure once validated.',
    'approver_not_current_user_help' => 'The co-approver cannot be the current user.',

    // Warning banner (settings)
    'settings_warning_title'  => 'Attention! This process is irreversible.',
    'settings_warning_body'   => 'This closure will perform important financial processes: points settlement, invoicing, and business rank updates.',
    'next_closure_scheduled_for' => 'Next closure scheduled for:',

    // Cron copy
    'cron_command_label'      => 'Server command',
    'copy'                    => 'Copy',
    'cron_copied'             => 'Command copied to clipboard.',
    'cron_copy_failed'        => 'Could not copy the command.',

    // Modals
    'confirm_settings_title'           => 'Confirm configuration',
    'confirm_settings_executor_prefix' => 'Designated executor for failures:',
    'confirm_settings_body_prefix'     => 'Cycle co-approver:',
    'confirm_settings_body_suffix'     => '',
    'confirm_and_save'        => 'Confirm and save',

    // JS warnings
    'select_executor_warning' => 'Select an executor.',
    'select_approver_warning' => 'Select a co-approver.',
];
