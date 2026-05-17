<?php return [
    'all_pending_approvals' => 'Todas las aprobaciones pendientes',
    'approval_created_message' => 'Se ha solicitado una doble aprobación para el siguiente tipo de acción: :action_type',
    'approved_message' => 'La doble aprobación ha sido aprobada',
    'rejected_message' => 'La doble aprobación ha sido rechazada',
    'error_messages' => [
        'unauthorized' => 'Lo sentimos, no tiene permiso para aprobar esta solicitud. Solo el usuario asignado puede realizar esta acción.',
        'blocked_due_to_pending_approval' => 'Se ha bloqueado la creación de una doble aprobación, existe una solicitud pendiente.',
        'access_forbidden' => 'Intento de doble aprobación no autorizado por el staff con id: :user_id.',
        'approval_completed' => 'Esta aprobación ya fue completada. No es necesario realizar ninguna acción adicional',
        'invalid_code' => 'Código inválido o no encontrado',
    ],
    'action_types' => [
        'clubpoint_set_massive_points' => 'Establecer puntos masivos',
        'clubpoint_convert_point_to_wallet' => 'Convertir Puntos a dinero de billetera',
    ],
    'modules' => [
        'ClubPoint' => 'Puntos',
    ],
];
