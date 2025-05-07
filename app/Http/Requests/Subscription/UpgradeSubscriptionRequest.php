<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Store;

class UpgradeSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if the user is authorized to upgrade the subscription
        // You might want to check if the user owns the store or is an admin
        $subdomain = $this->route('subdomain');
        $store = Store::where('slug', $subdomain)->first();
        
        return $store && ($this->user()->id === $store->owner_id || 
                          $this->user()->role === 'admin');
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
            'billing_cycle' => 'sometimes|in:monthly,annual',
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
            'pricing_tier_id.required' => 'Please select a subscription plan.',
            'pricing_tier_id.exists' => 'The selected subscription plan is not valid.',
            'billing_cycle.in' => 'The billing cycle must be either monthly or annual.',
        ];
    }
}