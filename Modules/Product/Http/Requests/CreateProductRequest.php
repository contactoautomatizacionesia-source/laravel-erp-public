<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use CodeZero\UniqueTranslation\UniqueTranslationRule;

class CreateProductRequest extends FormRequest
{
    public function rules(): array
    {
        $base = [
            'product_type'       => 'required',
            'category_ids'       => 'required',
            'unit_type_id'       => 'required',
            'tags'               => 'required',
            'discount'           => 'required',
            'minimum_order_qty'  => 'nullable|integer|min:1',
            'min_stock'          => 'nullable|integer|min:0',
            'max_stock'          => 'nullable|integer|min:0|gte:min_stock',
            'weight'             => 'nullable',
            'length'             => 'nullable',
            'breadth'            => 'nullable',
            'height'             => 'nullable',
            'subtitle_1'         => 'nullable|max:190',
            'subtitle_2'         => 'nullable|max:190',
            'auction_product'    => 'nullable',
            'date_range'         => 'nullable',
            'variant_sku_prefix' => 'nullable|required_if:product_type,==,2',
            'sku.*'              => [
                'required_if:product_type,==,2',
                Rule::unique('product_sku', 'sku')->where(fn ($q) => $q->where('product_id', '!=', $this->id)),
            ],
            'expiry_date'        => 'nullable|date',
        ];

        if (isModuleActive('FrontendMultiLang')) {
            $code = auth()->user()->lang_code;
            return array_merge($base, [
                'product_name.' . $code => 'required|max:255',
                'product_sku.' . $code  => ['nullable', UniqueTranslationRule::for('product_sku', 'sku')->ignore($this->id)],
                'pdf_file'              => 'nullable|mimes:pdf',
            ]);
        }

        return array_merge($base, [
            'product_name' => 'required|max:255',
            'product_sku'  => ['nullable', Rule::unique('product_sku', 'sku')->where(fn ($q) => $q->where('product_id', '!=', $this->id))],
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }
}
