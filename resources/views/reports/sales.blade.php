<!-- filepath: d:\WST\inventory-management-system\resources\views\reports\sales.blade.php -->

@extends('layouts.app')

@section('title', 'Sales Report')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="h2 mb-0">
                <i class="fas fa-chart-line text-primary me-2"></i>Sales Report
            </h1>
            <p class="text-muted">Analyze your sales performance</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <div class="btn-group">
                <a href="{{ route('reports.sales.export.pdf', [
                    'subdomain' => $store->slug,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'group_by' => $groupBy
                ]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-file-pdf me-2"></i>Export PDF
                </a>
               
            </div>
        </div>
    </div>
    
    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('reports.sales', ['subdomain' => $store->slug]) }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label small text-muted">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label small text-muted">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="group_by" class="form-label small text-muted">Group By</label>
                    <select class="form-select" id="group_by" name="group_by">
                        <option value="day" {{ $groupBy == 'day' ? 'selected' : '' }}>Day</option>
                        <option value="week" {{ $groupBy == 'week' ? 'selected' : '' }}>Week</option>
                        <option value="month" {{ $groupBy == 'month' ? 'selected' : '' }}>Month</option>
                        <option value="product" {{ $groupBy == 'product' ? 'selected' : '' }}>Product</option>
                        <option value="cashier" {{ $groupBy == 'cashier' ? 'selected' : '' }}>Cashier</option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Total Sales</h6>
                            <h2 class="mb-0">₱{{ number_format($totalSales, 2) }}</h2>
                            <p class="text-muted mb-0">
                                {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape bg-primary text-white rounded-circle shadow">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Total Orders</h6>
                            <h2 class="mb-0">{{ number_format($orderCount) }}</h2>
                            <p class="text-muted mb-0">
                                {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape bg-success text-white rounded-circle shadow">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Average Order Value</h6>
                            <h2 class="mb-0">₱{{ number_format($averageOrderValue, 2) }}</h2>
                            <p class="text-muted mb-0">
                                {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape bg-info text-white rounded-circle shadow">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">Sales Trend</h5>
                    <p class="text-muted mb-0 small">
                        {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
                        ({{ ucfirst($groupBy) }})
                    </p>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Products -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">Top Products</h5>
                    <p class="text-muted mb-0 small">Bestsellers during this period</p>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($topProducts as $index => $product)
                            <div class="list-group-item border-0 d-flex align-items-center px-3">
                                <div class="bg-light rounded-circle me-3 icon-shape-sm d-flex align-items-center justify-content-center">
                                    <span class="fw-bold">{{ $index + 1 }}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                    <small class="text-muted">
                                        Qty: {{ number_format($product->total_quantity) }}
                                    </small>
                                </div>
                                <div>
                                    <span class="text-dark fw-bold">₱{{ number_format($product->total_amount, 2) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item border-0 text-center py-4">
                                <i class="fas fa-box-open text-muted mb-2"></i>
                                <p class="mb-0 text-muted">No products sold during this period</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detailed Data Table -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Detailed Report</h5>
                        <p class="text-muted mb-0 small">
                            {{ ucfirst($groupBy) }}ly breakdown
                        </p>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>{{ ucfirst($groupBy) }}</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Sales</th>
                                    <th class="text-end">Avg. Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData as $data)
                                <tr>
                                    <td>{{ $data->label }}</td>
                                    <td class="text-end">{{ number_format($data->order_count) }}</td>
                                    <td class="text-end">₱{{ number_format($data->total_amount, 2) }}</td>
                                    <td class="text-end">
                                        ₱{{ number_format($data->order_count > 0 ? $data->total_amount / $data->order_count : 0, 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No sales data available for this date range
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .icon-shape {
        width: 50px;
        height: 50px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    
    .icon-shape-sm {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get colors from CSS variables
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary') || '#4e73df';
    const primaryLightColor = hexToRgba(primaryColor, 0.1);
    
    // Sales trend chart
    const reportData = @json($reportData);
    const ctx = document.getElementById('salesTrendChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: reportData.map(item => item.label),
            datasets: [{
                label: 'Sales',
                data: reportData.map(item => item.total_amount),
                borderColor: primaryColor,
                backgroundColor: primaryLightColor,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: primaryColor,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `₱${Number(context.parsed.y).toFixed(2)}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        borderDash: [2,2]
                    },
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Helper function to convert hex to rgba
    function hexToRgba(hex, alpha) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }
});
</script>
@endpush