<?php return [
    'all_pending_approvals' => 'All Pending Approvals',
    'approval_created_message' => 'A double approval has been requested for the following action type: :action_type',
    'approved_message' => 'Double approval has been approved',
    'rejected_message' => 'Double approval has been rejected',
    'error_messages' => [
        'unauthorized' => 'Sorry, you do not have permission to approve this request. Only the assigned user can perform this action.',
        'blocked_due_to_pending_approval' => 'Double approval creation blocked — there is already a pending request.',
        'access_forbidden' => 'Unauthorized double approval attempt by staff ID: :user_id.',
        'approval_completed' => 'This approval has already been completed. No further action is required',
        'invalid_code' => 'Invalid or non-existent code',
    ],
    'action_types' => [
        'clubpoint_set_massive_points' => 'Set Massive Points',
        'clubpoint_convert_point_to_wallet' => 'Convert points to wallet',
    ],
    'modules' => [
        'ClubPoint' => 'Club Point',
    ],
];
