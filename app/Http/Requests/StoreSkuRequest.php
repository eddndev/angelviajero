<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Rules\OneValuePerAttributeGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSkuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function getRulesForModel($model = null): array
    {
        // Tu observación es correcta, este bloque no se usa en la validación actual,
        // pero lo dejamos por si quieres implementar una regla más robusta en el futuro.
        // $product = Product::find($this->input('product'));
        // $requiredAttributeCount = $product ? $product->attributes()->count() : 0;

        return [
            'product' => ['required'],
            'sku_code' => [
                'required',
                'string',
                'max:100',
                // Usamos el Rule builder. Si le pasamos un modelo, ignora su ID.
                Rule::unique('skus', 'sku_code')->ignore($model),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],

            'default' => ['nullable', 'array'],
            'default.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],

            'attributeValues' => ['sometimes', 'array', new OneValuePerAttributeGroup],
        ];
    }
}