<?php

namespace Modules\CostCenter\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferInventoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Asumimos autorización por middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'origin_id' => 'required|string',
            'destination_id' => 'required|string|different:origin_id',
            'dispatched_by' => 'required|exists:users,id',
            'received_by' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|string|exists:product_sku,id',
            'items.*.lot_id' => 'required|exists:product_lots,id',
            'items.*.qty' => 'required|numeric|min:1',
            'movement_type_id' => 'nullable|exists:system_catalogs,id',
            'reason'           => 'nullable|string',
            'shipping_guide'   => 'nullable|string|max:255',
            'carrier_id'       => 'nullable|exists:carriers,id',
            'guide_date'       => 'nullable|date',
        ];
    }
}
