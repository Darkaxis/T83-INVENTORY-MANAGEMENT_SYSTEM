<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only store owners and managers can update staff
        return in_array($this->user()->role, ['admin', 'store_owner', 'manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $staffId = $this->route('id');
        
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($staffId),
            ],
            'role' => ['required', 'string', Rule::in(['manager', 'staff'])],
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
            'status' => 'sometimes|string|in:active,inactive',
        ];
    }
}