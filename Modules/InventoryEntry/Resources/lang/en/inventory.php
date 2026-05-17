<?php

return [
    // Menu
    'menu_inventory_entry' => 'Inventory Entries',

    // General titles
    'inventory_entry_management' => 'Inventory Entry Management',
    'new_entry'                  => 'New Entry',
    'entry_detail'               => 'Entry Detail',
    'tab_active'                 => 'Active',
    'tab_modified'               => 'Modified',
    'tab_deleted'                => 'Deleted',

    // Form fields
    'product'          => 'Product',
    'product_hint'     => 'Search product by name or SKU',
    'variant'          => 'Variant / SKU',
    'variant_hint'     => 'Select variant',
    'lot_number'       => 'Lot Number',
    'lot_number_hint'  => 'Enter or search a lot number',
    'manufacture_date' => 'Manufacture Date',
    'expiration_date'  => 'Expiration Date',
    'quantity'         => 'Quantity',
    'location'         => 'Warehouse Location',
    'location_default' => 'Main',
    'warehouse_info'   => 'All entries are registered in the <strong>Main Central Warehouse</strong>.',
    'supplier'         => 'Supplier / Lot Origin',
    'unit_cost'        => 'Unit Cost Price',
    'unit_cost_hint'   => 'Optional — for reference only',
    'notes'            => 'Notes / Observations',
    'audit_notes'      => 'Reason',
    'audit_notes_placeholder' => 'Enter the reason for this action...',
    'audit_note_required' => 'The reason is required.',
    'edit_entry'       => 'Edit',
    'delete_entry'     => 'Delete',
    'save_changes'     => 'Save changes',

    // Table columns
    'col_product'      => 'Product',
    'col_sku'          => 'SKU / Variant',
    'col_lot'          => 'Lot',
    'col_manufacture'  => 'Mfg. Date',
    'col_expiration'   => 'Exp. Date',
    'col_quantity'     => 'Quantity',
    'col_status'       => 'Status',
    'col_created_by'   => 'Registered by',
    'col_created_at'   => 'Entry Date',

    // Status badges
    'status_valid'    => 'Valid',
    'status_expiring' => 'Expiring Soon',
    'status_expired'  => 'Expired',

    //status date
    'status_date' => 'The date must be later than the manufacturing date.',

    // Filters
    'filter_product'    => 'Filter by product',
    'filter_lot'        => 'Filter by lot',
    'filter_status'     => 'All statuses',
    'filter_date_from'  => 'Expiration from',
    'filter_date_to'    => 'Expiration to',

    // Confirmation summary
    'confirm_title'   => 'Confirm Inventory Entry',
    'confirm_message' => 'Review the data before saving. This action will add the quantity to current stock.',
    'confirm_save'    => 'Confirm & Save',

    // Messages
    'created_success'       => 'Inventory entry successfully registered.',
    'lot_found'             => 'Existing lot found. Data preloaded.',
    'lot_new'               => 'New lot number.',
    'no_entries_to_save'    => 'No rows with data to save. Fill at least one SKU.',
    'no_stock_data_for_update' => 'Data consistency error: missing stock data for update.',


    // Detail section
    'lot_info'         => 'Lot Information',
    'entries_history'  => 'Lot Entry History',
    'detail_view'      => 'View Detail',
    'updated_success'  => 'Record updated successfully.',
    'deleted_success'  => 'Record deleted successfully.',
    'delete_confirm_text' => 'This action will delete the record and adjust stock. Please provide a reason.',
    'audit_responsible' => 'Responsible',
    'audit_date_long'   => 'Date',
    'audit_ip'          => 'IP',
    'audit_agent'       => 'Browser',
    'audit_modified_info' => 'Modification Audit',
    'audit_deleted_info'  => 'Deletion Audit',
    'cannot_edit' => 'This record cannot be edited.',
    'cannot_edit_transfer_used' => 'Cannot modify/delete: the lot was used in transfers.',
    'cannot_edit_not_in_main' => 'Cannot modify/delete: inventory is not in main warehouse.',
    'insufficient_stock_for_update' => 'Insufficient stock to adjust this record.',
    'insufficient_stock_for_delete' => 'Insufficient stock to delete this record.',
];
