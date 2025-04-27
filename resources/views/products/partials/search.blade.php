<!-- filepath: d:\WST\inventory-management-system\resources\views\products\partials\search.blade.php -->

<div class="card mb-4">
    <div class="card-body p-3">
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" class="form-control" id="productSearch" 
                   placeholder="Search products by name, SKU or barcode..." 
                   autocomplete="off"
                   value="{{ request()->get('search') }}">
            <button class="btn btn-primary" type="button" id="searchButton">Search</button>
            @if(request()->has('search'))
                <a href="{{ route('products.index', ['subdomain' => $store->slug]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            @endif
        </div>
    </div>
</div>