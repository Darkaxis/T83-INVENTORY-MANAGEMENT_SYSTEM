<!-- filepath: d:\WST\inventory-management-system\resources\views\checkout\history.blade.php -->

@extends('layouts.app')

@section('title', 'Sales History')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-history me-2"></i>Sales History
            </h1>
            <p class="text-muted">View all completed sales</p>
        </div>
        <div class="col-auto align-self-center">
            <a href="{{ route('checkout.index', ['subdomain' => $store->slug]) }}" class="btn btn-primary">
                <i class="fas fa-cash-register me-2"></i>New Sale
            </a>
        </div>
    </div>
    
    <!-- Display any error messages -->
    @if(session('error'))
    <div class="alert alert-danger mb-4">
        {{ session('error') }}
    </div>
    @endif
    
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light p-3">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="mb-0 fw-bold">All Sales</h6>
                </div>
                <div class="col-auto">
                    <!-- Filters can be added here later -->
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($sales->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-receipt fa-2x text-muted mb-3"></i>
                                    <p class="mb-0 text-muted">No sales records found</p>
                                </td>
                            </tr>
                        @else
                            @foreach($sales as $sale)
                                <tr>
                                    <td>{{ $sale->invoice_number }}</td>
                                    <td>{{ date('M j, Y g:i A', strtotime($sale->created_at)) }}</td>
                                    <td>
                                        {{ $sale->item_count }} {{ $sale->item_count == 1 ? 'item' : 'items' }}
                                    </td>
                                    <td>
                                        @if($sale->customer_name)
                                            {{ $sale->customer_name }}
                                        @else
                                            <span class="text-muted">Walk-in customer</span>
                                        @endif
                                    </td>
                                    <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('checkout.receipt', ['subdomain' => $store->slug, 'sale_id' => $sale->id]) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-receipt"></i> Receipt
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $sales->links() }}
        </div>
    </div>
</div>
@endsection