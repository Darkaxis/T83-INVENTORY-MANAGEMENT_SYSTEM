<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\DB;
use App\Models\Store;

class StoreStaffRequest extends FormRequest
{
    protected $databaseManager;
    
    public function __construct(TenantDatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
        parent::__construct();
    }
    
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'role' => 'required|in:manager,staff',
        ];
    }
    
    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Get current store
            $host = request()->getHost();
            $segments = explode('.', $host);
            $subdomain = $segments[0] ?? null;
            
            $store = Store::where('slug', $subdomain)->first();
            
            if (!$store) {
                $validator->errors()->add('email', 'Store not found.');
                return;
            }
            
            // Switch to tenant database to check email uniqueness
            $this->databaseManager->switchToTenant($store);
            
            // Check if email already exists
            $emailExists = DB::connection('tenant')->table('users')
                ->where('email', $this->email)
                ->exists();
            
            if ($emailExists) {
                $validator->errors()->add('email', 'This email is already registered.');
            }
            
            // Check user limit
            $userLimit = $store->pricingTier->user_limit ?? 0;
            $unlimited = $userLimit === null || $userLimit === -1;
            $currentCount = DB::connection('tenant')->table('users')->count();
            
            if (!$unlimited && $currentCount >= $userLimit) {
                $validator->errors()->add('general', 'You have reached the staff limit for your current plan. Please upgrade to add more staff.');
            }
            
            $this->databaseManager->switchToMain();
        });
    }
}