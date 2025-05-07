<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:stores,slug|regex:/^[a-z0-9\-]+$/',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'pricing_tier_id' => 'required|exists:pricing_tiers,id',
            'billing_cycle' => 'required|in:monthly,annual',
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
            'slug.regex' => 'The store URL can only contain lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'This store URL is already taken. Please choose another one.',
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
        $this->merge([
            'status' => 'inactive',
            'approved' => false,
        ]);
    }
}