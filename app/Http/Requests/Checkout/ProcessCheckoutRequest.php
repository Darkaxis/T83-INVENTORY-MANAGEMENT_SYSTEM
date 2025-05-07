<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\TenantDatabaseManager;

class ProcessCheckoutRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:tenant.products,id',
            'items.*.stock' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,other',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:500',
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
            'items.required' => 'No items in cart',
            'items.min' => 'Your cart must contain at least one item',
            'items.*.id.exists' => 'One of the products in your cart is not available',
            'items.*.stock.min' => 'Quantity must be at least 1',
            'payment_method.in' => 'Invalid payment method selected'
        ];
    }
}