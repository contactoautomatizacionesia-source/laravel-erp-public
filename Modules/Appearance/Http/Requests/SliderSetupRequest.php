<?php

namespace Modules\Appearance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Product\Entities\Brand;
use Modules\Product\Entities\Category;
use Modules\Seller\Entities\SellerProduct;
use Modules\Setup\Entities\Tag;

class SliderSetupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (isModuleActive('FrontendMultiLang')) {
            $code = auth()->user()->lang_code;
            return [
                'name.'. $code => 'required',
                'slider_image_media' => 'required',
                'data_type' => 'required|in:product,category,brand,tag,url',
                'data_id' => ['required', function ($attribute, $value, $fail) { // NOSONAR
                    if (! $this->hasValidSliderTarget($value)) {
                        $fail('The selected slider target is invalid.');
                    }
                }]
            ];
        }else{
            return [
                'name' => 'required',
                'slider_image_media' => 'required',
                'data_type' => 'required|in:product,category,brand,tag,url',
                'data_id' => ['required', function ($attribute, $value, $fail) { // NOSONAR
                    if (! $this->hasValidSliderTarget($value)) {
                        $fail('The selected slider target is invalid.');
                    }
                }]
            ];
        }
    }
    public function messages()
    {
        if (isModuleActive('FrontendMultiLang')) {
            return [
                'name.*.required' => 'The slider name field is required',
            ];
        }else{
            return [
                'name.required' => 'The slider name field is required',
            ];
        }
    }
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    private function hasValidSliderTarget($value): bool
    {
        if ($this->input('data_type') === 'url') {
            return filled($value);
        }

        $targetModels = [
            'product' => SellerProduct::class,
            'category' => Category::class,
            'brand' => Brand::class,
            'tag' => Tag::class,
        ];

        $targetModel = $targetModels[$this->input('data_type')] ?? null;

        return $targetModel !== null && $targetModel::whereKey($value)->exists();
    }
}
