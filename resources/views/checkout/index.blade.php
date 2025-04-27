<!-- filepath: d:\WST\inventory-management-system\resources\views\checkout\index.blade.php -->

@extends('layouts.app')

@section('title', 'Checkout - ' . $store->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light p-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0 fw-bold">Checkout</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="mb-4">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="product-search" 
                                   placeholder="Search products by name, SKU or scan barcode...">
                        </div>
                    </div>
                    
                    <!-- Popular products section - simplified without images -->
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Popular Products</h6>
                        <div class="row row-cols-2 row-cols-md-4 g-3" id="popular-products">
                            @foreach($popularProducts as $product)
                                <div class="col">
                                    <div class="card h-100 product-card" data-id="{{ $product->id }}" data-price="{{ $product->price }}" 
                                         data-name="{{ $product->name }}" data-sku="{{ $product->sku }}" data-stock="{{ $product->stock }}">
                                        <div class="card-body p-3">
                                            <h6 class="card-title mb-2">{{ $product->name }}</h6>
                                            <p class="card-text mb-0">
                                                <span class="text-primary fw-bold">₱{{ number_format($product->price, 2) }}</span>
                                            </p>
                                            <small class="text-muted">{{ $product->sku }}</small>
                                            <div class="mt-2 small">
                                                <span class="badge bg-light text-dark border">
                                                    <i class="fas fa-cubes me-1"></i> {{ $product->stock }} in stock
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Search results container - simplified without images -->
                    <div id="search-results" class="mb-4 d-none">
                        <h6 class="fw-bold mb-3">Search Results</h6>
                        <div class="row row-cols-2 row-cols-md-4 g-3" id="results-container">
                            <!-- Results will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light p-3">
                    <h5 class="mb-0 fw-bold">Current Sale</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="cart-table">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Product</th>
                                    <th class="border-0 text-center">Qty</th>
                                    <th class="border-0 text-end">Price</th>
                                    <th class="border-0 text-end">Total</th>
                                    <th class="border-0"></th>
                                </tr>
                            </thead>
                            <tbody id="cart-items">
                                <!-- Cart items will be populated here -->
                                <tr id="empty-cart-message">
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                        <p class="mb-0">No items added yet</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer bg-white p-3" id="checkout-summary">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span id="subtotal">₱0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (10%)</span>
                        <span id="tax">₱0.00</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold mb-3">
                        <span>Total</span>
                        <span id="total">₱0.00</span>
                    </div>
                    
                    <button class="btn btn-primary w-100 mb-3" id="complete-sale-btn" disabled>
                        <i class="fas fa-cash-register me-2"></i>Complete Sale
                    </button>
                    
                    <button class="btn btn-outline-danger w-100" id="clear-cart-btn" disabled>
                        <i class="fas fa-trash-alt me-2"></i>Clear All
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkoutModalLabel">Complete Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="checkout-form">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="customer_name" class="form-label">Customer Name (optional)</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_email" class="form-label">Email (optional)</label>
                            <input type="email" class="form-control" id="customer_email" name="customer_email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="customer_phone" class="form-label">Phone (optional)</label>
                            <input type="text" class="form-control" id="customer_phone" name="customer_phone">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span id="modal-subtotal">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (10%)</span>
                            <span id="modal-tax">₱0.00</span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span id="modal-total">₱0.00</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="process-payment-btn">
                    <i class="fas fa-check-circle me-2"></i>Process Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Sale Complete</h5>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success fa-5x mb-3"></i>
                <h4>Sale Completed Successfully!</h4>
                <p class="mb-1">Invoice: <strong id="invoice-number"></strong></p>
                <p>Total: <strong id="success-total"></strong></p>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-outline-primary" id="view-receipt-btn" target="_blank">
                    <i class="fas fa-file-alt me-2"></i>View Receipt
                </a>
                <button type="button" class="btn btn-success" id="new-sale-btn">
                    <i class="fas fa-plus-circle me-2"></i>New Sale
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .product-card {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid #e9ecef;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    #cart-items .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    #cart-items .form-control-sm {
        width: 60px;
    }
    
    .loading {
        position: relative;
    }
    
    .loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cart state
    let cart = [];
    
    // Elements
    const productSearch = document.getElementById('product-search');
    const popularProducts = document.getElementById('popular-products');
    const searchResults = document.getElementById('search-results');
    const resultsContainer = document.getElementById('results-container');
    const cartItems = document.getElementById('cart-items');
    const emptyCartMessage = document.getElementById('empty-cart-message');
    const subtotalElement = document.getElementById('subtotal');
    const taxElement = document.getElementById('tax');
    const totalElement = document.getElementById('total');
    const completeSaleBtn = document.getElementById('complete-sale-btn');
    const clearCartBtn = document.getElementById('clear-cart-btn');
    const checkoutForm = document.getElementById('checkout-form');
    const processPaymentBtn = document.getElementById('process-payment-btn');
    
    // Modal elements
    const modalSubtotal = document.getElementById('modal-subtotal');
    const modalTax = document.getElementById('modal-tax');
    const modalTotal = document.getElementById('modal-total');
    const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    
    // Add popular products to cart on click
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function() {
            const id = parseInt(this.dataset.id);
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const sku = this.dataset.sku;
            const availableQty = parseInt(this.dataset.stock);
            
            addToCart(id, name, price, sku, availableQty);
        });
    });
    
    // Search products
    let searchTimeout;
    productSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                fetchSearchResults(query);
            }, 300);
        } else {
            searchResults.classList.add('d-none');
        }
    });
    
    // Complete sale button
    completeSaleBtn.addEventListener('click', function() {
        if (cart.length === 0) return;
        
        const total = calculateTotal();
        modalSubtotal.textContent = formatCurrency(total.subtotal);
        modalTax.textContent = formatCurrency(total.tax);
        modalTotal.textContent = formatCurrency(total.total);
        
        checkoutModal.show();
    });
    
    // Process payment
    processPaymentBtn.addEventListener('click', function() {
        processPayment();
    });
    
    // Clear cart
    clearCartBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to clear all items?')) {
            clearCart();
        }
    });
    
    // New sale button
    document.getElementById('new-sale-btn').addEventListener('click', function() {
        successModal.hide();
        clearCart();
    });
    
    // Functions
    function addToCart(id, name, price, sku, availableQty) {
        // Check if product is already in cart
        const existingItem = cart.find(item => item.id === id);
        
        if (existingItem) {
            // Check if we can add more
            if (existingItem.stock < availableQty) {
                existingItem.stock++;
                existingItem.total = existingItem.stock * existingItem.price;
                updateCartDisplay();
            } else {
                alert(`Sorry, only ${availableQty} units available for this product.`);
            }
        } else {
            // Add new item
            cart.push({
                id: id,
                name: name,
                price: price,
                sku: sku,
                stock: 1,
                total: price,
                availableQty: availableQty
            });
            updateCartDisplay();
        }
    }
    
    function updateCartDisplay() {
        if (cart.length === 0) {
            emptyCartMessage.classList.remove('d-none');
            completeSaleBtn.disabled = true;
            clearCartBtn.disabled = true;
            return;
        }
        
        emptyCartMessage.classList.add('d-none');
        completeSaleBtn.disabled = false;
        clearCartBtn.disabled = false;
        
        // Clear current items
        const itemRows = document.querySelectorAll('.cart-item-row');
        itemRows.forEach(row => row.remove());
        
        // Add items
        cart.forEach((item, index) => {
            const row = document.createElement('tr');
            row.classList.add('cart-item-row');
            
            row.innerHTML = `
                <td>
                    <div>
                        <strong class="d-block">${item.name}</strong>
                        <small class="text-muted">${item.sku}</small>
                    </div>
                </td>
                <td class="text-center">
                    <div class="input-group input-group-sm">
                        <button class="btn btn-outline-secondary btn-sm decrement-qty" data-index="${index}">-</button>
                        <input type="text" class="form-control form-control-sm text-center qty-input" value="${item.stock}" readonly>
                        <button class="btn btn-outline-secondary btn-sm increment-qty" data-index="${index}">+</button>
                    </div>
                </td>
                <td class="text-end">₱${item.price.toFixed(2)}</td>
                <td class="text-end">₱${item.total.toFixed(2)}</td>
                <td class="text-end">
                    <button class="btn btn-outline-danger btn-sm remove-item" data-index="${index}">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
            
            cartItems.appendChild(row);
        });
        
        // Add event listeners for buttons
        document.querySelectorAll('.decrement-qty').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                decrementQuantity(index);
            });
        });
        
        document.querySelectorAll('.increment-qty').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                incrementQuantity(index);
            });
        });
        
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                removeItem(index);
            });
        });
        
        // Update totals
        const totals = calculateTotal();
        subtotalElement.textContent = formatCurrency(totals.subtotal);
        taxElement.textContent = formatCurrency(totals.tax);
        totalElement.textContent = formatCurrency(totals.total);
    }
    
    function decrementQuantity(index) {
        if (cart[index].stock > 1) {
            cart[index].stock--;
            cart[index].total = cart[index].stock * cart[index].price;
            updateCartDisplay();
        }
    }
    
    function incrementQuantity(index) {
        if (cart[index].stock < cart[index].availableQty) {
            cart[index].stock++;
            cart[index].total = cart[index].stock * cart[index].price;
            updateCartDisplay();
        } else {
            alert(`Sorry, only ${cart[index].availableQty} units available for this product.`);
        }
    }
    
    function removeItem(index) {
        cart.splice(index, 1);
        updateCartDisplay();
    }
    
    function clearCart() {
        cart = [];
        updateCartDisplay();
    }
    
    function calculateTotal() {
        const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
        const taxRate = 0.10;
        const tax = subtotal * taxRate;
        const total = subtotal + tax;
        
        return { subtotal, tax, total };
    }
    
    function formatCurrency(amount) {
        return `₱${amount.toFixed(2)}`;
    }
    
    function fetchSearchResults(query) {
        fetch(`{{ route('checkout.search', ['subdomain' => $store->slug]) }}?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.products && data.products.length > 0) {
                    displaySearchResults(data.products);
                } else {
                    resultsContainer.innerHTML = `
                        <div class="col-12 text-center py-4">
                            <p class="text-muted mb-0">No products found</p>
                        </div>
                    `;
                    searchResults.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error searching products:', error);
            });
    }
    
    function displaySearchResults(products) {
        resultsContainer.innerHTML = '';
        
        products.forEach(product => {
            const col = document.createElement('div');
            col.className = 'col';
            
            col.innerHTML = `
                <div class="card h-100 product-card" data-id="${product.id}" data-price="${product.price}" 
                     data-name="${product.name}" data-sku="${product.sku}" data-quantity="${product.stock}">
                    <div class="card-body p-3">
                        <h6 class="card-title mb-2">${product.name}</h6>
                        <p class="card-text mb-0">
                            <span class="text-primary fw-bold">₱${parseFloat(product.price).toFixed(2)}</span>
                        </p>
                        <small class="text-muted">${product.sku}</small>
                        <div class="mt-2 small">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-cubes me-1"></i> ${product.stock} in stock
                            </span>
                        </div>
                    </div>
                </div>
            `;
            
            resultsContainer.appendChild(col);
            
            // Add click event
            col.querySelector('.product-card').addEventListener('click', function() {
                addToCart(product.id, product.name, parseFloat(product.price), product.sku, product.stock);
            });
        });
        
        searchResults.classList.remove('d-none');
    }
    
    function processPayment() {
        // Validate form
        if (!checkoutForm.checkValidity()) {
            checkoutForm.reportValidity();
            return;
        }
        
        // Disable the button and show loading
        processPaymentBtn.disabled = true;
        processPaymentBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        
        const paymentMethod = document.getElementById('payment_method').value;
        const customerName = document.getElementById('customer_name').value;
        const customerEmail = document.getElementById('customer_email').value;
        const customerPhone = document.getElementById('customer_phone').value;
        const notes = document.getElementById('notes').value;
        
        // Prepare items for API - keep using 'stock'
        const items = cart.map(item => ({
            id: item.id,
            stock: item.stock,  // Keep this as stock
        }));
        
        // Send request
        fetch('{{ route("checkout.process", ["subdomain" => $store->slug]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                items: items,
                payment_method: paymentMethod,
                customer_name: customerName,
                customer_email: customerEmail,
                customer_phone: customerPhone,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            // Re-enable button
            processPaymentBtn.disabled = false;
            processPaymentBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Process Payment';
            
            if (data.success) {
                // Show success modal
                checkoutModal.hide();
                
                // Set success details
                document.getElementById('invoice-number').textContent = data.invoice_number;
                document.getElementById('success-total').textContent = formatCurrency(calculateTotal().total);
                document.getElementById('view-receipt-btn').href = data.receipt_url;
                
                // Show success modal
                successModal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            processPaymentBtn.disabled = false;
            processPaymentBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Process Payment';
            alert('Error processing payment. Please try again.');
            console.error('Error:', error);
        });
    }
});
</script>
@endpush