<?php

namespace App\Http\Requests\Admin\PricingTier;

use Illuminate\Foundation\Http\FormRequest;

class StorePricingTierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Or implement your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'nullable|numeric|min:0',
            'product_limit' => 'nullable|integer|min:-1',
            'user_limit' => 'nullable|integer|min:-1',
            'is_active' => 'boolean',
            'features' => 'nullable|array',
            'sort_order' => 'integer|min:0',
        ];
    }
}