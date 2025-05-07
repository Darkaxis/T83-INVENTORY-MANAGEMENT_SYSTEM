<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class ApproveStoreRequest extends FormRequest
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
            'owner_name' => 'nullable|string|max:255',
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
            'owner_name.max' => 'The owner name cannot be longer than 255 characters.',
        ];
    }
    
    /**
     * Check if the store can be approved.
     *
     * @return bool
     */
    public function canApproveStore(): bool
    {
        $store = $this->route('store');
        return !$store->approved;
    }
}