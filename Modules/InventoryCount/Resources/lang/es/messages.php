<?php
return [
    // Settings - listado
    'configured_centers'            => 'Centros con configuración',
    'no_settings_yet'               => 'Aún no hay centros configurados.',
    'edit_settings'                 => 'Editar',
    'new_settings'                  => 'Nueva Configuración',
    'unlimited'                     => 'Sin límite',
    'attempts_label'                => ':n intento(s)',
    'attempts'                      => 'Intentos',

    // Settings - formulario
    'select_cost_center'            => 'Centro de Costo',
    'count_role'                    => 'Rol encargado del conteo',
    'count_role_hint'               => 'Solo los usuarios con este rol podrán realizar conteos.',
    'max_attempts'                  => 'Cantidad máxima de intentos',
    'max_attempts_hint_zero'        => 'Valor 0 = sin limitación de intentos.',
    'max_attempts_hint_limited'     => 'Se permiten hasta :n intentos por conteo.',
    'notify_users'                  => 'Notificar a',
    'notify_users_hint'             => 'Se notificará a estos administradores cuando se supere el límite de intentos.',
    'notify_required_when_limited'  => 'Debe seleccionar al menos un administrador para notificar cuando hay límite de intentos.',
    'allow_history_view'            => 'Permitir ver historial propio',
    'allow_history_view_hint'       => 'Si está activo, el asesor puede ver todos sus conteos anteriores.',
    'history_view_confirm'          => 'Al activar esta opción los asesores podrán ver su historial completo de conteos. ¿Desea continuar?',

    // Conteo general
    'create_count'          => 'Crear Conteo',
    'count_code'            => 'Código',
    'cost_center'           => 'Centro de Costo',
    'responsible'           => 'Responsable',
    'status'                => 'Estado',
    'audit_status'          => 'Auditado',
    'attempt_number'        => 'Intento N°',
    'observation'           => 'Observación',
    'product'               => 'Producto',
    'physical_quantity'     => 'Cantidad Física',
    'system_stock'          => 'Stock Sistema',
    'difference'            => 'Diferencia',
    'observation_type'      => 'Tipo de Observación',
    'products_counted'      => 'Productos contados',
    'counted'               => 'Contados',
    'draft_saved'           => 'Borrador guardado',
    'search_product'        => 'Buscar por nombre...',
    'filter_all'            => 'Ver Todos',
    'filter_pending'        => 'Pendientes',
    'start_count'           => 'Iniciar Conteo',
    'start_count_hint'      => 'Haga clic para iniciar el conteo. Se registrará la información de su dispositivo.',
    'submit_count'          => 'Enviar Conteo',
    'submit_confirm_text'   => 'Este proceso realizará validaciones que no pueden devolverse y notificará a los administradores en caso de diferencias.',

    // R1/R2 mensajes
    'count_correct'                        => '¡Conteo correcto! Los inventarios coinciden.',
    'count_incorrect_with_remaining'       => 'El inventario no coincide. Le quedan :remaining intento(s).',
    'count_incorrect_no_limit'             => 'El inventario no coincide. Por favor revise las cantidades e intente nuevamente.',
    'count_limit_exceeded'                 => 'Ha superado el límite de intentos permitidos. Se ha notificado a los administradores.',
    'no_cost_center_assigned'              => 'No tiene un centro de costo asignado. Contacte al administrador.',
    'count_started'                        => 'Conteo iniciado. ¡Puede comenzar el registro!',
    'count_resumed'                        => 'Retomando conteo pendiente anterior.',
    'count_start_error'                    => 'Error al iniciar el conteo. Intente nuevamente.',

    // Estados
    'status_pending'    => 'Pendiente',
    'status_correct'    => 'Correcto',
    'status_incorrect'  => 'Incorrecto',
    'status_closed'     => 'Cerrado',
    'audit_pending'     => 'Pendiente de Revisión',
    'audit_rejected'    => 'Rechazado - Re-conteo',
    'audit_approved'    => 'Aprobado',
    'audit_closed'      => 'Cerrado',

    // Conteo cerrado
    'count_closed_title'   => 'Conteo Cerrado',
    'count_closed_message' => 'Este intento fue cerrado automáticamente porque otro conteo del mismo día fue aprobado.',
    'count_closed_link'    => 'Ver conteo aprobado',

    // Auditoría
    'review_count'              => 'Revisar Conteo',
    'info_count'                => 'Información del conteo',
    'audit_detail'              => 'Detalle de Auditoría',
    'audit_info'                => 'Información de Auditoría',
    'audit_notes'               => 'Notas de Auditoría',
    'audit_notes_placeholder'   => 'Ingrese las notas de la revisión...',
    'save_audit'                => 'Guardar Auditoría',
    'select_audit_status'       => 'Seleccione un estado de auditoría.',
    'notes_required'            => 'Las notas de auditoría son obligatorias.',
    'audit_confirm_text'        => 'El sistema creará un registro con la auditoría. Tenga en cuenta que este proceso no puede devolverse.',
    'audit_saved'               => 'Auditoría guardada correctamente.',
    'asesor'                    => 'Asesor',
    'auditor'                   => 'Auditor',
    'rejected_history'          => 'Conteos rechazados',
    'times'                     => 'veces',
    'attempts_history'          => 'Historial de Intentos',
    'result'                    => 'Resultado',
    'result_correct'            => 'Correcto',
    'result_incorrect'          => 'Incorrecto',
    'count_details'             => 'Detalles del Conteo',
    'attempt'                   => 'Intento',

    'count_submitted_with_differences'  => 'Conteo enviado. Se detectaron diferencias. Quedará pendiente de revisión por un administrador.',
    'count_already_approved_today'      => 'Ya existe un conteo aprobado hoy para este centro de costo.',

    // Resultado de auditoría
    'audit_result_approved' => 'Conteo Aprobado',
    'audit_result_rejected' => 'Conteo Rechazado',
    'audit_rejected_hint'   => 'El asesor debe realizar un nuevo conteo.',

    // Acciones
    'action_detail' => 'Detalle',
    'action_review' => 'Revisar',

    // Errores de proceso
    'audit_already_processed' => 'Este conteo ya fue aprobado y no puede ser auditado nuevamente.',
    'audit_rejected_error_log_msg' => 'Hubo un error al rechazar el conteo. Error: :error',

    // Mensajes de log activity
    'audit_rejected_log_msg' => 'Reconteo del inventario con codigo :code por el usuario con id :userId fue rechazado por el siguiente motivo: :notes',
];
