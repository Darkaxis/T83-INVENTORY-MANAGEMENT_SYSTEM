<?phpnamespace App\Http\Requests\Product;use Illuminate\Foundation\Http\FormRequest;use App\Services\TenantDatabaseManager;use Illuminate\Support\Facades\DB;class StoreProductRequest extends FormRequest{    protected $databaseManager;    /**     * Create a new form request instance.     */    public function __construct(TenantDatabaseManager $databaseManager)    {        $this->databaseManager = $databaseManager;        parent::__construct();    }        /**     * Determine if the user is authorized to make this request.     */    public function authorize(): bool    {        return true;    }    /**     * Get the validation rules that apply to the request.     *     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>     */    public function rules(): array    {        return [            'name' => 'required|string|max:255',            'sku' => 'required|string|max:255|unique:products,sku', // Will validate against tenant DB            'price' => 'required|numeric|min:0',            'stock' => 'required|integer|min:0',            'description' => 'nullable|string',
            'barcode' => 'nullable|string|max:100',
            'category_id' => 'nullable',
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
        ];
    }
}
