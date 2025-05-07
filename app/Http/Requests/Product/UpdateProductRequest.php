<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\TenantDatabaseManager;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    protected $databaseManager;

    /**
     * Create a new form request instance.
     */
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
        $productId = $this->route('product_id');
        
        return [
            'name' => 'required|string|max:255',
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products')->ignore($productId)
            ],
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'barcode' => 'required|string|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
            'status' => 'sometimes|boolean',
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'sku.unique' => 'This SKU is already in use for another product.',
            'category_id.exists' => 'The selected category does not exist.',
        ];
    }
}