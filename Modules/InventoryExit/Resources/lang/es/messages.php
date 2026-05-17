<?php

return [
    // Estados
    'status_pending'  => 'Pendiente',
    'status_approved' => 'Aprobada',
    'status_rejected' => 'Rechazada',

    // Acciones
    'detail'          => 'Detalle',
    'change_status'   => 'Cambiar estado',

    // Mensajes de éxito/error
    'request_created'    => 'Solicitud de salida registrada correctamente.',
    'request_approved'   => 'Solicitud aprobada. Inventario descontado.',
    'request_rejected'   => 'Solicitud rechazada.',
    'already_processed'  => 'Esta solicitud ya fue procesada.',

    'insufficient_lot_stock' => 'Stock insuficiente en el lote :lot. Disponible: :available, solicitado: :requested.',

    // UI
    'title'              => 'Salidas de Inventario',
    'new_request'        => 'Nueva Salida',
    'filter_status'      => 'Estado',
    'filter_cost_center' => 'Centro de Costo',
    'filter_date_from'   => 'Desde',
    'filter_date_to'     => 'Hasta',
    'all_statuses'       => 'Todos los estados',
    'all_cost_centers'   => 'Todos los centros',
    'export'             => 'Exportar',

    // Columnas DataTable
    'col_date'         => 'Fecha',
    'col_type'         => 'Tipo',
    'col_products'     => 'Producto/SKU',
    'col_cost_center'  => 'Centro de Costo',
    'col_requested_by' => 'Solicitó',
    'col_approved_by'  => 'Aprobó',
    'col_status'       => 'Estado',
    'col_actions'      => 'Acciones',
    'search_produc'    => 'Seleccionar producto/SKU',

    // Formulario de solicitud
    'section_responsible'    => 'Responsable',
    'section_products'       => 'Productos a sacar',
    'section_observation'    => 'Justificación y soporte',
    'section_confirm'        => 'Confirmar',
    'responsible_user'       => 'Usuario solicitante',
    'request_date'           => 'Fecha de solicitud',
    'exit_reason'            => 'Motivo de salida',
    'exit_reason_placeholder'=> 'Seleccione o cree un motivo',
    'add_reason'             => '+ Agregar motivo',
    'cost_center'            => 'Centro de Costo / Bodega origen',
    'search_product'         => 'Buscar producto o SKU',
    'col_sku'                => 'SKU',
    'col_product'            => 'Producto',
    'col_stock'              => 'Stock disponible',
    'col_lot'                => 'Lote',
    'col_lot_expiry'         => 'Vencimiento',
    'col_qty_requested'      => 'Cantidad a sacar',
    'col_final_stock'        => 'Stock final',
    'observation_label'      => 'Observación',
    'observation_placeholder'=> 'Describa el motivo detallado de la salida...',
    'documents_label'        => 'Documentos soporte',
    'documents_hint'         => 'PDF, JPG o PNG. Máximo 10 archivos de 10MB cada uno.',
    'btn_confirm'            => 'Confirmar solicitud',
    'btn_cancel'             => 'Cancelar',

    // Modal de confirmación
    'confirm_title'    => 'Confirmar solicitud de salida',
    'confirm_body'     => 'Se registrará una salida de productos del inventario del centro de costo :cost_center. Esta acción no descuenta inventario hasta que sea aprobada por un administrador.',
    'btn_accept'       => 'Aceptar',

    // Modal de aprobación
    'approve_title'       => 'Cambiar estado de solicitud',
    'approve_responsible' => 'Responsable',
    'approve_date'        => 'Fecha y hora',
    'approve_status'      => 'Cambiar estado',
    'option_approve'      => 'Aprobar',
    'option_reject'       => 'Rechazar',
    'approve_note'        => 'Observación',
    'approve_note_placeholder' => 'Describa el motivo de la decisión...',
    'btn_apply'           => 'Aplicar',

    // Modal de detalle
    'detail_title'         => 'Detalle de Solicitud',
    'detail_request_info'  => 'Información de Solicitud',
    'detail_approval_info' => 'Información de Aprobación',
    'detail_products'      => 'Productos',
    'detail_documents'     => 'Documentos soporte',
    'no_documents'         => 'Sin documentos adjuntos.',
    'col_qty_approved'     => 'Cant. aprobada',
    'download'             => 'Descargar',
];
