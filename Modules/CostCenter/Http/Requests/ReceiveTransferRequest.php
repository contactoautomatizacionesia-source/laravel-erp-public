<?php

namespace Modules\CostCenter\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\CostCenter\Entities\CostCenterTransferItem;

class ReceiveTransferRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'reception_notes'            => 'nullable|string|max:500',
            'items'                      => 'required|array|min:1',
            'items.*.transfer_item_id'   => 'required|integer|exists:cost_center_transfer_items,id',
            'items.*.received_qty'       => 'required|numeric|min:0',

            // Estos campos se dejan nullable en las reglas generales
            // porque si NO hay diferencia en las cantidades, vendrán vacíos.
            'items.*.novelty_id'         => 'nullable|integer|exists:system_catalogs,id',
            'items.*.description'        => 'nullable|string|max:1000',
            'items.*.evidence_file'      => 'nullable|file|mimes:pdf|max:5120', // Solo PDF, max 5MB
        ];
    }

    public function messages()
    {
        return [
            'items.*.evidence_file.mimes'   => 'La evidencia adjunta debe ser un archivo de tipo PDF.',
            'items.*.evidence_file.max'     => 'El archivo PDF no debe pesar más de 5MB.',
            'items.*.received_qty.required' => 'Debe especificar la cantidad recibida para todos los productos.',
        ];
    }

    /**
     * Hook para validaciones condicionales y de base de datos
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $itemsData = $this->input('items', []);
            $itemIds = collect($itemsData)->pluck('transfer_item_id')->filter()->toArray();

            if (empty($itemIds)) {
                return;
            }

            $dbItems = CostCenterTransferItem::whereIn('id', $itemIds)->get()->keyBy('id');

            foreach ($itemsData as $index => $item) {
                $this->evaluateItemDiscrepancy($validator, $item, $index, $dbItems);
            }
        });
    }

    /**
     * Evalúa si un ítem específico necesita validación de novedad.
     */
    private function evaluateItemDiscrepancy($validator, array $item, $index, $dbItems): void
    {
        $transferItemId = $item['transfer_item_id'] ?? null;
        $receivedQty = $item['received_qty'] ?? null;

        // Si el ítem no es válido o no existe en la BD, saltamos la validación
        if (!$transferItemId || !isset($dbItems[$transferItemId])) {
            return;
        }

        $dbItem = $dbItems[$transferItemId];

        // REGLA DE NEGOCIO: Si se recibe menos de lo despachado, EXIGIR la novedad
        if ($receivedQty !== null && $receivedQty < $dbItem->dispatched_qty) {
            $this->validateDiscrepancyFields($validator, $item, $index);
        }
    }

    /**
     * Valida que los campos obligatorios de la novedad estén presentes.
     */
    private function validateDiscrepancyFields($validator, array $item, $index): void
    {
        if (empty($item['novelty_id'])) {
            $validator->errors()->add("items.{$index}.novelty_id", "Debe seleccionar un tipo de novedad para los productos con diferencias.");
        }

        if (empty($item['description'])) {
            $validator->errors()->add("items.{$index}.description", "Debe detallar la justificación en la descripción de la novedad.");
        }

        if (!$this->hasFile("items.{$index}.evidence_file")) {
            $validator->errors()->add("items.{$index}.evidence_file", "La evidencia en formato PDF es obligatoria para justificar el faltante.");
        }
    }
}
