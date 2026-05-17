<?php

return [
    // ── Generales ─────────────────────────────────────────────────────────────
    'cash_management'   => 'Gestión de Cajas',
    'save_changes'      => 'Guardar Cambios',

    // ── Menú sidebar ──────────────────────────────────────────────────────────
    'operations'        => 'Cierres',
    'assignments'       => 'Asignaciones',
    'settings'          => 'Configuraciones',

    // ── Configuraciones ───────────────────────────────────────────────────────
    'settings_title'       => 'Configuraciones de Caja',
    'tab_denominations'    => 'Denominaciones',
    'tab_structure'        => 'Estructura de Cajas',
    'tab_roles'            => 'Roles Operadores',

    // Denominaciones
    'new_denomination'     => 'Nueva Denominación',
    'denomination_country' => 'País',
    'denomination_created' => 'Denominación creada exitosamente.',
    'denomination_duplicate' => 'Ya existe una denominación con ese valor para este país.',
    'value'                => 'Valor',
    'value_example'        => '(ej. 50000)',
    'type'                 => 'Tipo',
    'type_bill'            => 'Billete',
    'type_coin'            => 'Moneda',

    // Cajas
    'new_box'              => 'Nueva Caja',
    'register_new_box'     => 'Registrar Nueva Caja',
    'unique_code'          => 'Código',
    'box_name'             => 'Nombre de la Caja',
    'hierarchy_type'       => 'Tipo',
    'type_vault'           => 'Caja Fuerte (Madre)',
    'type_principal'       => 'Caja Principal (Sucursal)',
    'type_auxiliary'       => 'Caja Auxiliar (Operador)',
    'initial_base'         => 'Base Inicial ($)',
    'alert_threshold'      => 'Tope de Alerta ($)',
    'box_parent'           => 'Caja Superior',
    'no_parent'            => 'Sin superior',
    'box_created'          => 'Caja creada con código :code.',
    'status'               => 'Estado',
    'actions'              => 'Acciones',
    'box_status_available'        => 'Disponible',
    'box_status_open'             => 'En Uso',
    'box_status_pending_receipt'  => 'Pendiente de Recibir',
    'box_status_maintenance'      => 'Mantenimiento',
    'box_status_inactive'         => 'Inactiva',

    // Roles
    'roles_description'    => 'Seleccione los roles del sistema que pueden ser asignados como operadores de caja.',
    'save_roles'           => 'Guardar Roles',
    'roles_min_one'        => 'Debe seleccionar al menos un rol.',

    // ── Asignaciones ──────────────────────────────────────────────────────────
    'assignments_title'        => 'Asignación de Cajas al Personal',
    'box_free'                 => 'Libre',
    'box_occupied'             => 'En Uso',
    'box_waiting_operator'     => 'Sin operador asignado',
    'no_boxes_configured'      => 'No hay cajas configuradas. Configúrelas en Configuraciones → Estructura de Cajas.',
    'base_assigned'            => 'Base asignada',
    'assign_operator'          => 'Asignar Operador',
    'revoke_assignment'        => 'Liberar Caja',
    'revoke_confirm'           => '¿Está seguro de liberar esta caja?',
    'assign_modal_title'       => 'Asignar operador a :box',
    'select_cashier'           => 'Seleccionar Operador',
    'select_user_placeholder'  => 'Seleccione un usuario...',
    'delivery_warning'         => 'Está a punto de entregar físicamente :amount como base inicial al operador.',
    'confirm_delivery'         => 'Confirmar Entrega y Abrir Caja',
    'assigned_since'           => 'Desde:',
    'assignment_success'       => 'Caja asignada y sesión abierta exitosamente.',
    'revoke_success'           => 'Asignación revocada. La caja vuelve a estar disponible.',

    // Confirmación de recepción (líder de caja superior)
    'confirm_receipt'              => 'Confirmar Recepción',
    'confirm_receipt_warning'      => 'Está a punto de confirmar la recepción física de :amount del operador :user. Esta acción cerrará la sesión definitivamente.',
    'receipt_confirmed_success'    => 'Recepción confirmada. Sesión cerrada exitosamente.',
    'session_pending_receipt_badge'=> 'Pendiente de Recibir',
    'error_session_not_pending'    => 'Esta sesión no está pendiente de recepción.',
    'error_not_parent_box_responsible' => 'No tiene autoridad para confirmar esta caja. Solo el responsable de la caja superior puede hacerlo.',
    'reviewer_has_incidents'       => '¿Se encontraron novedades en la revisión?',
    'reviewer_notes_label'         => 'Notas del revisor',
    'reviewer_notes_placeholder'   => 'Observaciones al confirmar la recepción...',

    // Envío al nivel superior (PRINCIPAL → VAULT)
    'submit_to_parent'             => 'Enviar Reporte a Caja Madre',
    'submit_to_parent_confirm'     => '¿Confirma el envío del reporte consolidado al VAULT? Todas las cajas auxiliares han sido revisadas.',
    'submitted_to_vault_success'   => 'Reporte enviado a Caja Madre. Pendiente de revisión.',
    'error_only_principal_can_submit' => 'Solo una caja PRINCIPAL puede enviar reportes al VAULT.',
    'error_children_not_closed'    => 'No se puede enviar: aún hay :count caja(s) auxiliar(es) pendientes de revisión.',
    'error_not_box_responsible'    => 'No tiene asignación activa sobre esta caja.',

    // Jerarquía de creación de cajas
    'box_type_auto'             => 'Tipo determinado automáticamente',
    'box_parent_auto'           => 'Caja superior (asignada automáticamente)',
    'error_hierarchy_violated'  => 'No se puede crear la caja: la jerarquía del sistema no lo permite.',
    'box_type_hint_vault'       => 'Se creará como Caja Madre (no existe ninguna en el sistema)',
    'box_type_hint_principal'   => 'Se creará como Caja Principal para este centro de costo',
    'box_type_hint_auxiliary'   => 'Se creará como Caja Auxiliar bajo la caja principal del centro de costo',
    'select_cc_first'           => 'Seleccione primero un centro de costo',

    // Errores de asignación
    'pending_receipt_manage_in_operations' => 'Cierre pendiente — gestionar en Cierres',
    'review_manage_in_operations'          => 'Gestionar cierres en la sección Cierres',
    'error_box_already_assigned' => 'Esta caja ya tiene un operador asignado.',
    'error_user_already_assigned'=> 'El usuario seleccionado ya tiene una caja activa.',
    'error_box_not_available'    => 'La caja no está disponible para asignación.',
    'error_revoke_pending'       => 'No se puede revocar: el dinero ya fue entregado y está pendiente de recepción.',

    // ── Operaciones (Cierres y Arqueo) ────────────────────────────────────────
    'operations_title'           => 'Cierre y Arqueo de Caja',

    // Vista de revisión (PRINCIPAL / VAULT)
    'review_title_principal'     => 'Revisión de Cierres — Caja Principal',
    'review_title_vault'         => 'Revisión de Reportes — Caja Madre',
    'review_pending_count'       => ':count cierre(s) pendiente(s) de revisión',
    'review_no_pending'          => 'Sin cierres pendientes',
    'review_no_pending_hint'     => 'Todas las cajas subordinadas están al día.',
    'review_submitted_waiting'   => 'Reporte enviado — pendiente de confirmación por la caja superior.',
    'review_submitted_hint'      => 'Una vez que la caja superior confirme la recepción, esta sesión quedará cerrada.',
    'review_already_submitted'        => 'Reporte ya enviado',
    'review_already_confirmed'        => 'Cierres ya confirmados en este turno',
    'session_confirmed_badge'         => 'Confirmado',
    'review_vault_total_received'     => 'Total consolidado a recibir',
    'review_breakdown_by_auxiliary'   => 'Desglose por caja auxiliar',
    'review_breakdown_by_principal'   => 'Desglose por caja principal',

    // Historial de cierres
    'history_title'   => 'Historial de cierres',
    'history_opened'  => 'Apertura',
    'submit_pending_children'    => 'Aún hay cierres pendientes de revisión en cajas auxiliares.',
    'has_incidents_badge'        => 'Con novedades',
    'no_incidents_badge'         => 'Sin novedades',
    'opening_base'               => 'Base de apertura',
    'closed_at'                  => 'Cerrada',

    // Resumen de sesión
    'session_summary'            => 'Mi Sesión',
    'user'                       => 'Operador',
    'active_box'                 => 'Caja',
    'cost_center'                => 'Centro de Costo',
    'session_opened_at'          => 'Abierta',
    'session_status_open'        => 'Abierta',
    'session_status_pending_receipt' => 'Pendiente de Recibir',
    'session_status_closed'      => 'Cerrada',
    'session_status_disputed'    => 'En Disputa',
    'time_elapsed'               => 'Tiempo Transcurrido',
    'session_already_closed'     => 'Esta sesión ya fue cerrada.',
    'session_closed_success'     => 'Caja cerrada exitosamente. Pendiente de recepción por la caja superior.',

    // Sin sesión
    'no_session_title'   => 'Sin caja asignada',
    'no_session_message' => 'No tiene ninguna caja activa en este momento. Contacte a su supervisor para que le asigne una.',

    // Medios de pago
    'payment_methods_declared'   => 'Medios de Pago Recibidos',
    'payment_methods_hint'       => 'Active los medios por los que recibió pagos durante el turno e ingrese los totales.',
    'total_amount'               => 'Total Recibido',
    'transaction_count'          => 'N° de Transacciones',
    'reference_data'             => 'Referencia / Lote',
    'reference_placeholder'      => 'Ej. Lote 0042, voucher...',
    'total_declared'             => 'Total Declarado',
    'enable_payment_form'        => 'Activar este medio de pago',

    // Arqueo físico
    'physical_count'     => 'Conteo Físico (Arqueo)',
    'denomination'       => 'Denominación',
    'quantity'           => 'Cantidad',
    'subtotal'           => 'Subtotal',
    'count_summary'      => 'Resumen del cuadre',

    // Totales y diferencia
    'total_counted'      => 'Total Contado',
    'base_to_deduct'     => 'Base Inicial (a descontar)',
    'cash_to_deliver'    => 'Efectivo a Entregar',
    'system_expected'    => 'Esperado (Sistema)',
    'difference'         => 'Diferencia',

    // Novedad (tipo + justificación + notas)
    'discrepancy_type'            => 'Tipo de Novedad',
    'discrepancy_type_placeholder'=> 'Seleccione un tipo...',
    'justification'               => 'Justificación (Obligatorio)',
    'justification_placeholder'   => 'Explique el motivo del descuadre...',
    'notes'                       => 'Notas adicionales',
    'notes_placeholder'           => 'Detalles adicionales sobre la novedad...',
    'notes_required_for_other'    => 'Las notas son obligatorias cuando el tipo es "Otro".',
    'error_justification_required'=> 'Debe ingresar una justificación cuando hay diferencia en el cuadre.',
    'error_discrepancy_type_required' => 'Debe seleccionar el tipo de novedad cuando hay diferencia en el cuadre.',

    // Botón cierre
    'close_box'          => 'Cerrar Caja y Entregar',

    // Errores de cierre
    'error_not_your_session'  => 'No puede cerrar una sesión que no le pertenece.',
    'error_session_not_open'  => 'Esta sesión no está abierta o ya fue cerrada.',
    'error_no_denominations'  => 'Debe ingresar al menos una denominación con cantidad mayor a cero.',
    'error_no_payments'       => 'Debe activar al menos un medio de pago con el total recibido.',

    'cash_management'    => 'Gestión de Cajas',

    // Permisos de menú
    'operations'         => 'Operaciones',
    'assignments'        => 'Asignaciones',
    'view_operations'    => 'Ver operaciones',
    'manage_assignments' => 'Gestionar asignaciones',
    'admin_settings'     => 'Administrar configuración',
];
