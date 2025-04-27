<!-- filepath: d:\WST\inventory-management-system\resources\views\checkout\receipt.blade.php -->

@extends('layouts.app')

@section('title', 'Receipt - ' . $sale->invoice_number)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light p-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Receipt #{{ $sale->invoice_number }}</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Receipt
                    </button>
                </div>
                
                <div class="card-body p-4" id="receipt-content">
                    <div class="text-center mb-4">
                        <h4 class="mb-0">{{ $store->name }}</h4>
                        <p class="mb-1">{{ $store->address }}</p>
                        <p>{{ $store->city }}, {{ $store->state }} {{ $store->zip }}</p>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <p class="mb-1"><strong>Invoice #:</strong> {{ $sale->invoice_number }}</p>
                            <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->created_at)->format('F j, Y') }}</p>
                            <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($sale->created_at)->format('g:i A') }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="mb-1"><strong>Cashier:</strong> {{ $cashier->name ?? 'Unknown' }}</p>
                            <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst($sale->payment_method) }}</p>
                            <p><strong>Status:</strong> <span class="badge bg-success">{{ ucfirst($sale->payment_status) }}</span></p>
                        </div>
                    </div>
                    
                    @if($sale->customer_name)
                    <div class="mb-3 p-3 bg-light rounded">
                        <h6 class="mb-2">Customer Information</h6>
                        <p class="mb-1"><strong>Name:</strong> {{ $sale->customer_name }}</p>
                        @if($sale->customer_email)
                            <p class="mb-1"><strong>Email:</strong> {{ $sale->customer_email }}</p>
                        @endif
                        @if($sale->customer_phone)
                            <p class="mb-0"><strong>Phone:</strong> {{ $sale->customer_phone }}</p>
                        @endif
                    </div>
                    @endif
                    
                    <div class="table-responsive mb-3">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                <tr>
                                    <td>
                                        <div>
                                            <span class="fw-bold">{{ $item->product_name }}</span>
                                            <small class="d-block text-muted">{{ $item->product_sku }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $item->stock }}</td>
                                    <td class="text-end">₱{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">₱{{ number_format($item->line_total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end">₱{{ number_format($sale->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tax (10%):</strong></td>
                                    <td class="text-end">₱{{ number_format($sale->tax_amount, 2) }}</td>
                                </tr>
                                @if($sale->discount_amount > 0)
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Discount:</strong></td>
                                    <td class="text-end">-₱{{ number_format($sale->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end fw-bold">₱{{ number_format($sale->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    @if($sale->notes)
                    <div class="mb-3">
                        <h6>Notes:</h6>
                        <p class="mb-0">{{ $sale->notes }}</p>
                    </div>
                    @endif
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">Thank you for your business!</p>
                    </div>
                </div>
                
                <div class="card-footer bg-white p-3">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('checkout.index', ['subdomain' => $store->slug]) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Checkout
                        </a>
                        <a href="{{ route('checkout.history', ['subdomain' => $store->slug]) }}" class="btn btn-outline-primary">
                            <i class="fas fa-history me-2"></i>View All Sales
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    /* Print styles */
    @page {
        size: auto;
        margin: 0mm;
    }
    
    body {
        background-color: #ffffff;
        margin: 0;
        padding: 15mm;
    }
    
    .container, .card {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        box-shadow: none !important;
    }
    
    .navbar, .card-header button, .card-footer, footer, .btn {
        display: none !important;
    }
    
    #receipt-content {
        padding: 0 !important;
    }
</style>
@endsection