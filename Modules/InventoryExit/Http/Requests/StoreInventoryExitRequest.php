<?php

namespace Modules\InventoryExit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryExitRequest extends FormRequest
{
    private const MAX_DOCUMENT_SIZE_KB = 5120;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exit_reason_id'        => ['required', 'integer', 'exists:system_catalogs,id'],
            'location'              => ['required', 'string'],
            'exit_date'             => ['required', 'date'],
            'observation'           => ['required', 'string', 'max:1000'],

            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_sku_id'=> ['required', 'integer', 'exists:product_sku,id'],
            'items.*.lot_id'        => ['required', 'integer', 'exists:product_lots,id'],
            'items.*.qty_requested' => ['required', 'numeric', 'min:0.01'],

            'documents'             => ['nullable', 'array', 'max:10'],
            'documents.*'           => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:' . self::MAX_DOCUMENT_SIZE_KB],
        ];
    }

    public function messages(): array
    {
        return [
            'exit_reason_id.required'           => __('inventoryexit::validation.exit_reason_required'),
            'exit_reason_id.exists'             => __('inventoryexit::validation.exit_reason_invalid'),
            'location.required'                 => __('inventoryexit::validation.cost_center_required'),
            'exit_date.required'                => __('inventoryexit::validation.exit_date_required'),
            'observation.required'              => __('inventoryexit::validation.observation_required'),

            'items.required'                    => __('inventoryexit::validation.items_required'),
            'items.min'                         => __('inventoryexit::validation.items_required'),
            'items.*.product_sku_id.required'   => __('inventoryexit::validation.sku_required'),
            'items.*.product_sku_id.exists'     => __('inventoryexit::validation.sku_invalid'),
            'items.*.lot_id.required'           => __('inventoryexit::validation.lot_required'),
            'items.*.lot_id.exists'             => __('inventoryexit::validation.lot_invalid'),
            'items.*.qty_requested.required'    => __('inventoryexit::validation.qty_required'),
            'items.*.qty_requested.min'         => __('inventoryexit::validation.qty_positive'),

            'documents.required'                => __('inventoryexit::validation.documents_required'),
            'documents.*.mimes'                 => __('inventoryexit::validation.document_mimes'),
            'documents.*.max'                   => __('inventoryexit::validation.document_max', ['size' => (int) (self::MAX_DOCUMENT_SIZE_KB / 1024)]),
        ];
    }
}
