<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\TenantDatabaseManager;
use Illuminate\Support\Facades\DB;
use App\Models\Store;

class UpdateStaffRequest extends FormRequest
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
            'is_active' => 'sometimes|boolean',
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
            
            // Get staff ID from route
            $staffId = $this->route('staff_id');
            
            // Switch to tenant database to check email uniqueness
            $this->databaseManager->switchToTenant($store);
            
            // Check if email exists for other users
            $emailExists = DB::connection('tenant')->table('users')
                ->where('email', $this->email)
                ->where('id', '!=', $staffId)
                ->exists();
            
            if ($emailExists) {
                $validator->errors()->add('email', 'This email is already used by another user.');
            }
            
            $this->databaseManager->switchToMain();
        });
    }
}