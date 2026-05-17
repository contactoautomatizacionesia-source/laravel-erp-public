<?php

return [
    // Validación
    'period_type_required'    => 'El tipo de período de cierre es obligatorio.',
    'period_type_invalid'     => 'Tipo de período inválido.',
    'execution_day_required'  => 'El día de ejecución es obligatorio para período mensual.',
    'execution_day_range'     => 'El día de ejecución debe estar entre 1 y 31.',
    'executor_required'       => 'Se requiere designar un ejecutor.',
    'executor_not_found'      => 'El ejecutor seleccionado no fue encontrado.',
    'executor_same_user'      => 'El ejecutor no puede ser el mismo usuario que realiza la configuración.',
    'approver_required'       => 'Se requiere un co-aprobador (Contador).',
    'approver_not_found'      => 'El co-aprobador seleccionado no fue encontrado.',
    'approver_same_user'      => 'El co-aprobador no puede ser el mismo usuario que realiza la configuración.',
    // Configuración
    'setting_saved'           => 'Configuración guardada y activada. La anterior fue reemplazada.',

    // Ciclo
    'pre_validation_failed'       => 'La pre-validación falló. El ejecutor designado ha sido notificado para revisión.',
    'pre_validation_passed'       => 'Pre-validación exitosa. En espera de confirmación del co-aprobador.',
    'closure_approve'             => 'Firmar / Aprobar',
    'cycle_approved'              => 'Ciclo aprobado. El acta ha sido generada.',
    'cycle_rejected'              => 'Ciclo rechazado por el co-aprobador.',
    'cycle_cancelled'             => 'Ciclo cancelado correctamente.',
    'executor_approved'           => 'Ciclo aprobado manualmente. El co-aprobador ha sido notificado.',
    'cycle_not_pending'           => 'El ciclo no está en estado pendiente de aprobación.',
    'cycle_not_needs_review'      => 'El ciclo no requiere revisión del ejecutor.',
    'not_authorized_executor'     => 'No está autorizado para actuar como ejecutor en este ciclo.',
    'not_authorized_approver'     => 'No está autorizado para aprobar este ciclo.',
    'status_changed'              => 'Estado del ciclo actualizado correctamente.',
    'scheduled'                   => 'Programado',
    'pending'                     => 'Pendiente',

    // Fases
    'phase_pipeline'               => 'Pipeline',
    'phase_pipeline_start'         => 'Inicio de Cierre',
    'phase_pipeline_end'           => 'Cierre Finalizado',
    'phase_act_generation'         => 'Generación de Acta',
    'phase_check_pending_orders'   => 'Verificación de Órdenes',
    'phase_check_pending_inventory'=> 'Verificación de Inventario',
    'phase_sales_consolidation'    => 'Consolidación de Ventas',
    'phase_points_conversion'      => 'Conversión de Puntos',
    'phase_pre_validation'         => 'Pre-Validación',
    'phase_consolidation'          => 'Consolidación',
    'phase_pdf_generation'         => 'Generación de PDF',
    'phase_block'                  => 'Bloqueo de Período',
    'phase_notification'           => 'Notificación',

    // Etiquetas de estado
    'status_running'          => 'Ejecutando',
    'status_needs_review'     => 'Requiere Revisión',
    'status_pre_validation'   => 'Validando',
    'status_pending_approval' => 'Pendiente de Aprobación',
    'status_processing'       => 'Procesando',
    'status_closed'           => 'Cerrado',
    'status_cancelled'        => 'Anulado',

    // Nota día 31
    'day_31_note'             => 'En meses cortos, el cierre se ejecutará el último día del mes.',

    // Bloqueo de período
    'period_blocked'          => 'El período solicitado está bloqueado por un cierre de ciclo. No se pueden realizar modificaciones retroactivas.',

    // UI labels (settings)
    'settings_active'              => 'Configuración activa',
    'settings_history_title'       => 'Historial de configuraciones',
    'settings_configured_by'       => 'Configurado por',
    'setting_status_active'        => 'Activa',
    'setting_status_superseded'    => 'Reemplazada',
    'day'                     => 'Día',
    'period_type_label'       => 'Tipo de Período',
    'period_daily'            => 'Diario',
    'period_monthly'          => 'Mensual',
    'period_annual'           => 'Anual',
    'execution_day_label'     => 'Día de Ejecución',
    'executor_label'          => 'Ejecutor',
    'executor_help'           => 'Administrador designado para revisar si el cierre automático falla (SuperAdmin o Admin).',
    'double_approval_label'   => 'Co-Aprobador',
    'approver_help'           => 'Contador que firma el cierre una vez validado.',
    'approver_not_current_user_help' => 'El co-aprobador no puede ser el usuario actual.',

    // Warning banner (settings)
    'settings_warning_title'  => '¡Atención! Este proceso es irreversible.',
    'settings_warning_body'   => 'Este cierre realizará procesos financieros importantes: liquidación de puntos, facturación y actualización de rangos de empresarios.',
    'next_closure_scheduled_for' => 'Próximo cierre programado para:',

    // Cron copy
    'cron_command_label'      => 'Comando para el servidor',
    'copy'                    => 'Copiar',
    'cron_copied'             => 'Comando copiado al portapapeles.',
    'cron_copy_failed'        => 'No se pudo copiar el comando.',

    // Modals
    'confirm_settings_title'              => 'Confirmar configuración',
    'confirm_settings_executor_prefix'    => 'Ejecutor designado ante fallos:',
    'confirm_settings_body_prefix'        => 'Co-Aprobador del cierre:',
    'confirm_settings_body_suffix'        => '',
    'confirm_and_save'        => 'Confirmar y guardar',

    // JS warnings
    'select_executor_warning' => 'Seleccione un ejecutor.',
    'select_approver_warning' => 'Seleccione un co-aprobador.',
];
