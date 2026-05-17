<?php

return [
    // Menú
    'menu_inventory_entry' => 'Ingresos de Inventario',

    // Títulos generales
    'inventory_entry_management' => 'Gestión de Ingresos de Inventario',
    'new_entry'                  => 'Nuevo Ingreso',
    'entry_detail'               => 'Detalle de Ingreso',
    'tab_active'                 => 'Activos',
    'tab_modified'               => 'Modificados',
    'tab_deleted'                => 'Eliminados',

    // Campos del formulario
    'product'          => 'Producto',
    'product_hint'     => 'Buscar producto por nombre o SKU',
    'variant'          => 'Variante / SKU',
    'variant_hint'     => 'Seleccionar variante',
    'lot_number'       => 'Número de Lote',
    'lot_number_hint'  => 'Ingrese o busque un número de lote',
    'manufacture_date' => 'Fecha de Fabricación',
    'expiration_date'  => 'Fecha de Vencimiento',
    'quantity'         => 'Cantidad',
    'location'         => 'Ubicación en Bodega',
    'location_default' => 'Principal',
    'warehouse_info'   => 'Todos los ingresos se registran en la <strong>Bodega Principal Central</strong>.',
    'supplier'         => 'Proveedor / Origen del Lote',
    'unit_cost'        => 'Precio de Costo Unitario',
    'unit_cost_hint'   => 'Opcional — solo de referencia',
    'notes'            => 'Notas u Observaciones',
    'audit_notes'      => 'Motivo',
    'audit_notes_placeholder' => 'Ingrese el motivo de la accion...',
    'audit_note_required' => 'El motivo es obligatorio.',
    'edit_entry'       => 'Editar',
    'delete_entry'     => 'Eliminar',
    'save_changes'     => 'Guardar cambios',

    // Columnas tabla
    'col_product'      => 'Producto',
    'col_sku'          => 'SKU / Variante',
    'col_lot'          => 'Lote',
    'col_manufacture'  => 'F. Fabricación',
    'col_expiration'   => 'F. Vencimiento',
    'col_quantity'     => 'Cantidad',
    'col_status'       => 'Estado',
    'col_created_by'   => 'Registrado por',
    'col_created_at'   => 'Fecha Ingreso',

    // Badges de estado
    'status_valid'    => 'Vigente',
    'status_expiring' => 'Por Vencer',
    'status_expired'  => 'Vencido',

    //status date
    'status_date' => 'La fecha debe ser posterior a la fecha de fabricación.',

    // Filtros
    'filter_product'    => 'Filtrar por producto',
    'filter_lot'        => 'Filtrar por lote',
    'filter_status'     => 'Todos los estados',
    'filter_date_from'  => 'Vencimiento desde',
    'filter_date_to'    => 'Vencimiento hasta',

    // Resumen de confirmación
    'confirm_title'   => 'Confirmar Ingreso de Inventario',
    'confirm_message' => 'Revise los datos antes de guardar. Esta acción sumará la cantidad al stock actual.',
    'confirm_save'    => 'Confirmar y Guardar',

    // Mensajes
    'created_success'       => 'Ingreso de inventario registrado exitosamente.',
    'lot_found'             => 'Lote existente encontrado. Datos precargados.',
    'lot_new'               => 'Número de lote nuevo.',
    'no_entries_to_save'    => 'No hay filas con datos para guardar. Complete al menos un SKU.',
    'no_stock_data_for_update' => 'Error de consistencia de datos: Faltan registros de stock para la actualización.',

    // Sección detalle
    'lot_info'         => 'Información del Lote',
    'entries_history'  => 'Historial de Ingresos del Lote',
    'detail_view'      => 'Ver Detalle',
    'updated_success'  => 'Registro actualizado correctamente.',
    'deleted_success'  => 'Registro eliminado correctamente.',
    'delete_confirm_text' => 'Esta accion eliminara el registro y ajustara el stock. Confirme el motivo.',
    'audit_responsible' => 'Responsable',
    'audit_date_long'   => 'Fecha',
    'audit_ip'          => 'IP',
    'audit_agent'       => 'Navegador',
    'audit_modified_info' => 'Auditoria de Modificacion',
    'audit_deleted_info'  => 'Auditoria de Eliminacion',
    'cannot_edit' => 'No es posible editar este registro.',
    'cannot_edit_transfer_used' => 'No se puede modificar/eliminar: el lote ya fue usado en transferencias.',
    'cannot_edit_not_in_main' => 'No se puede modificar/eliminar: el inventario no esta en bodega principal.',
    'insufficient_stock_for_update' => 'Stock insuficiente para ajustar el registro.',
    'insufficient_stock_for_delete' => 'Stock insuficiente para eliminar el registro.',
];
