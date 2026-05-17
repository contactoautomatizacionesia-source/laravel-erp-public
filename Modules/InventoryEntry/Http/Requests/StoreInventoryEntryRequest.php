<?php

namespace Modules\InventoryEntry\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entries'                          => ['required', 'array', 'min:1'],
            'entries.*.product_sku_id'         => ['required', 'integer', 'exists:product_sku,id'],
            'entries.*.lot_number'             => ['required', 'string', 'max:100'],
            'entries.*.manufacture_date'       => ['nullable', 'date'],
            'entries.*.expiration_date'        => ['nullable', 'date', 'after_or_equal:entries.*.manufacture_date'],
            'entries.*.quantity'               => ['required', 'numeric', 'min:0.01'],
            'entries.*.unit_cost'              => ['nullable', 'numeric', 'min:0'],
            'entries.*.warehouse_location'     => ['nullable', 'string', 'max:150'],
            'entries.*.supplier'               => ['nullable', 'string', 'max:200'],
            'entries.*.notes'                  => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'entries.required'                      => 'Debe ingresar al menos un ítem.',
            'entries.min'                           => 'Debe ingresar al menos un ítem.',
            'entries.*.product_sku_id.required'     => __('inventoryentry::inventory.product') . ' ' . __('common.field_required'),
            'entries.*.product_sku_id.exists'       => __('inventoryentry::inventory.product') . ': no encontrado en el catálogo.',
            'entries.*.lot_number.required'         => __('inventoryentry::inventory.lot_number') . ' ' . __('common.field_required'),
            'entries.*.quantity.required'           => __('inventoryentry::inventory.quantity') . ' ' . __('common.field_required'),
            'entries.*.quantity.min'                => __('inventoryentry::inventory.quantity') . ': debe ser mayor a 0.',
            'entries.*.expiration_date.after_or_equal' => __('inventoryentry::inventory.expiration_date') . ': debe ser igual o posterior a la fecha de fabricación.',
        ];
    }
}
