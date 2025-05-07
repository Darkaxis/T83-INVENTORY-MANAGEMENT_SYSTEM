<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePricingTierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Or check if user is admin
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'pricing_tier_id' => 'required|exists:pricing_tiers,id',
            'billing_cycle' => 'required|in:monthly,annual',
            'auto_renew' => 'sometimes|boolean',
            'reset_dates' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pricing_tier_id.exists' => 'The selected pricing tier does not exist.',
            'billing_cycle.in' => 'The billing cycle must be either monthly or annual.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to boolean
        $this->merge([
            'auto_renew' => $this->has('auto_renew'),
            'reset_dates' => $this->has('reset_dates'),
        ]);
    }
}