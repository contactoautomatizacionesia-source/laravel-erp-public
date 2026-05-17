<?php

return [
    // Permisos
    'view'          => 'Ver novedad',
    'resolve'       => 'Resolver novedad',
    'void'          => 'Anular novedad',
    'settings_save' => 'Guardar configuración',

    // Respuestas del sistema
    'created'              => 'Novedad creada correctamente.',
    'statement_submitted'  => 'Pronunciamiento registrado correctamente.',
    'resolved'             => 'Novedad resuelta correctamente.',
    'voided'               => 'Novedad anulada correctamente.',
    'evidence_uploaded'    => 'Evidencia cargada correctamente.',
    'settings_updated'     => 'Configuración actualizada correctamente.',
    'linked_to_closing'    => 'Novedad vinculada al cierre de caja correctamente.',
    'error_generic'        => 'Ocurrió un error. Por favor intente de nuevo.',

    // Estados
    'status_pending'              => 'Pendiente',
    'status_awaiting_statement'   => 'Esperando descargo',
    'status_awaiting'             => 'Esperando descargo',
    'status_under_investigation'  => 'En investigación',
    'status_investigating'        => 'En investigación',
    'status_closed'               => 'Cerrada',
    'status_voided'               => 'Anulada',
    'all_statuses'         => 'Todos los estados',

    // Tipos
    'type_transfer'        => 'Transferencia',
    'type_inventory_count' => 'Conteo',
    'all_types'            => 'Todos los tipos',

    // Campos de la tabla principal
    'code'           => 'Código',
    'type'           => 'Tipo',
    'status'         => 'Estado',
    'product'        => 'Producto',
    'branch'         => 'Sede',
    'advisor'        => 'Asesor',
    'missing_units'  => 'Uds. faltantes',
    'total_value'    => 'Valor total',
    'total_open_value' => 'Valor total abierto',
    'created_at'     => 'Creado el',

    // Filtros
    'date_from'      => 'Desde',
    'date_to'        => 'Hasta',
    'apply_filters'  => 'Filtrar',

    // Detalle de novedad
    'product_info'          => 'Información del producto',
    'responsibility'        => 'Responsabilidad',
    'responsible_branch'    => 'Sede responsable',
    'responsible_advisor'   => 'Asesor responsable',
    'origin_branch'         => 'Sede origen',
    'origin_advisor'        => 'Asesor origen',
    'public_price'          => 'Precio público (snapshot)',
    'statement_info'        => 'Pronunciamiento',
    'statement_deadline'    => 'Plazo de pronunciamiento',
    'statement_type'        => 'Tipo de pronunciamiento',
    'statement_acknowledged' => 'Reconoció el error',
    'statement_rejected'     => 'Rechazó la responsabilidad',
    'resolution_info'       => 'Resolución',
    'resolution_party'      => 'Responsable',
    'resolved_at'           => 'Resuelto el',
    'resolution_notes'      => 'Justificación',
    'expired'               => 'Vencido',
    'back_to_list'          => 'Volver al listado',
    'view_detail'           => 'Ver detalle',

    // Partes de resolución
    'party_advisor'      => 'Asesor',
    'party_organization' => 'Organización',
    'party_voided'       => 'Anulada',

    // Evidencias
    'evidences'          => 'Evidencias',
    'add_evidence'       => 'Agregar evidencia',
    'no_evidences'       => 'Sin evidencias adjuntas.',
    'evidence_file'      => 'Archivo',
    'actor_role'         => 'Rol del actor',
    'role_destination'   => 'Destino',
    'role_origin'        => 'Origen',
    'role_admin'         => 'Administrador',
    'evidence_hint'      => 'Formatos permitidos: JPG, PNG, PDF. Máximo 10 MB.',
    'upload_evidence'    => 'Subir evidencia',
    'uploading'          => 'Subiendo...',
    'sending'            => 'Enviando...',
    'notes'              => 'Observaciones',

    // Log de auditoría
    'audit_log'          => 'Log de auditoría',
    'no_audit_logs'      => 'Sin registros de auditoría.',

    // Modales de acción
    'submit_statement'            => 'Presentar descargo',
    'statement_type_label'        => 'Tipo de pronunciamiento',
    'resolve_incident'            => 'Resolver novedad',
    'resolution_notes_placeholder'=> 'Describa la justificación del administrador (mínimo 10 caracteres)...',
    'confirm_resolution'          => 'Confirmar resolución',
    'resolve_info_advisor'        => 'El asesor deberá subsanar comprando el producto al precio público.',
    'resolve_info_organization'   => 'La organización asumirá la pérdida mediante una salida de inventario.',
    'void_incident'               => 'Anular novedad',
    'void_warning'                => 'Esta acción es irreversible. La novedad quedará anulada.',
    'void_reason'                 => 'Motivo de anulación',
    'confirm_void'                => 'Confirmar anulación',
    'btn_advisor_pays'            => 'Asesor subsana',
    'btn_org_assumes'             => 'Organización asume',
    'btn_void'                    => 'Anular',
    'btn_link_closing'            => 'Vincular a cierre',

    // Cierre de caja
    'link_to_closing'            => 'Vincular a cierre de caja',
    'cash_closing_id'            => 'ID del cierre de caja',
    'cash_closing_id_placeholder'=> 'Ingrese el ID del cierre de caja',
    'cash_closing_hint'          => 'El cierre debe estar en estado editable.',
    'confirm_link'               => 'Confirmar vinculación',

    // Configuración
    'settings_deadline_section'      => 'Plazos de pronunciamiento',
    'settings_notifications_section' => 'Notificaciones',
    'settings_price_section'         => 'Precio de referencia',
    'statement_deadline_hours'       => 'Horas de plazo',
    'statement_deadline_hint'        => 'Horas que tiene la sede origen para pronunciarse. Por defecto: 48 h.',
    'auto_escalate'                  => 'Escalar automáticamente al vencer el plazo',
    'send_reminder'                  => 'Enviar recordatorio antes del vencimiento',
    'reminder_hours_before'          => 'Horas antes del vencimiento para el recordatorio',
    'send_email'                     => 'Notificaciones por correo electrónico',
    'send_system_notif'              => 'Notificaciones internas en el sistema',
    'price_reference'                => 'Precio de referencia para novedades',
    'price_public'                   => 'Precio público',
    'price_cost'                     => 'Precio de costo',
    'price_transfer'                 => 'Precio de transferencia',
];
