<!-- filepath: d:\WST\inventory-management-system\resources\views\dashboard\index.blade.php -->

@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Error Alert -->
    @if(isset($error))
    <div class="alert alert-danger" role="alert">
        {{ $error }}
    </div>
    @endif

    <!-- Welcome Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col">
                            
                            <p class="text-white-50 mb-0">Here's what's happening with your store today</p>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('checkout.index', ['subdomain' => $store->slug]) }}" class="btn btn-light">
                                <i class="fas fa-cash-register me-2"></i>New Sale
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Today's Sales</h6>
                            <h2 class="mb-0">₱{{ number_format($todaySales ?? 0, 2) }}</h2>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape bg-primary text-white rounded-circle shadow">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">This Week</h6>
                            <h2 class="mb-0">₱{{ number_format($weekSales ?? 0, 2) }}</h2>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape bg-info text-white rounded-circle shadow">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Products</h6>
                            <h2 class="mb-0">{{ number_format($productCount ?? 0) }}</h2>
                            @if(isset($lowStockCount) && $lowStockCount > 0)
                                <small class="text-danger">{{ $lowStockCount }} low stock</small>
                            @endif
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape bg-success text-white rounded-circle shadow">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Inventory Value</h6>
                            <h2 class="mb-0">₱{{ number_format($inventoryValue ?? 0, 2) }}</h2>
                        </div>
                        <div class="col-auto">
                            <div class="icon-shape bg-warning text-white rounded-circle shadow">
                                <i class="fas fa-warehouse"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Sales Chart -->
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent pb-0">
                    <h5 class="mb-0">Sales Overview</h5>
                    <p class="text-sm mb-0 text-muted">Last 7 Days Performance</p>
                </div>
                <div class="card-body p-3">
                    <div class="chart">
                        <canvas id="salesChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Category Breakdown -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent pb-0">
                    <h5 class="mb-0">Sales by Category</h5>
                    <p class="text-sm mb-0 text-muted">Distribution of revenue</p>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="categoryChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Top Products -->
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Top Selling Products</h5>
                        <p class="text-sm mb-0 text-muted">Based on units sold</p>
                    </div>
                    <a href="{{ route('products.index', ['subdomain' => $store->slug]) }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts ?? [] as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex px-3 py-2">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $product->name }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle text-sm">₱{{ number_format($product->price, 2) }}</td>
                                    <td class="align-middle">
                                        @if($product->stock > 10)
                                            <span class="badge bg-success">{{ $product->stock }}</span>
                                        @elseif($product->stock > 0)
                                            <span class="badge bg-warning">{{ $product->stock }}</span>
                                        @else
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <span class="text-sm font-weight-bold">{{ $product->sold_count }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No sales data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Recent Activity</h5>
                    <p class="text-sm mb-0 text-muted">Latest updates from your store</p>
                </div>
                <div class="card-body p-0">
                    <div class="timeline timeline-one-side px-3 py-3" data-timeline-axis-style="dashed">
                        @forelse($recentActivities ?? [] as $activity)
                        <div class="timeline-block mb-3">
                            <span class="timeline-step {{ $activity->type == 'sale' ? 'bg-success' : 'bg-info' }}">
                                <i class="fas {{ $activity->type == 'sale' ? 'fa-receipt' : 'fa-box' }} text-white"></i>
                            </span>
                            <div class="timeline-content">
                                <h6 class="text-dark mb-1">{{ $activity->description }}</h6>
                                <div class="d-flex align-items-center">
                                    <p class="text-xs text-muted mb-0">By {{ $activity->user_name }}</p>
                                    <p class="text-xs text-muted mb-0 ms-auto">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-muted">
                            No recent activity
                        </div>
                        @endforelse
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
        display: inline-flex;
        padding: 12px;
        text-align: center;
        border-radius: 50%;
        align-items: center;
        justify-content: center;
    }
    
    .timeline {
        position: relative;
        margin-top: 1rem;
    }
    
    .timeline-block {
        display: flex;
    }
    
    .timeline-step {
        position: relative;
        min-height: 40px;
        min-width: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 1rem;
        z-index: 1;
    }
    
    .timeline-content {
        position: relative;
        width: 100%;
        padding-bottom: 1.5rem;
        border-left: 2px solid #e9ecef;
        padding-left: 1.5rem;
        margin-left: -1rem;
    }
    
    .timeline-block:last-child .timeline-content {
        padding-bottom: 0;
        border-left: 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default Chart.js colors
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary') || '#4e73df';
    const secondaryColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-secondary') || '#858796';
    const successColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-success') || '#1cc88a';
    const infoColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-info') || '#36b9cc';
    const warningColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-warning') || '#f6c23e';
    const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-danger') || '#e74a3b';
    
    // Sales Chart
    const salesChartData = @json($salesChartData ?? []);
    
    if (document.getElementById('salesChart')) {
        const salesChart = new Chart(document.getElementById('salesChart'), {
            type: 'line',
            data: {
                labels: salesChartData.map(item => item.date),
                datasets: [{
                    label: 'Sales',
                    data: salesChartData.map(item => item.amount),
                    borderColor: primaryColor,
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: primaryColor,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    borderWidth: 2
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
                                return `₱${context.parsed.y.toFixed(2)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            borderDash: [2, 2]
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    // Category Chart
    const categoryData = @json($categoryData ?? []);
    
    if (document.getElementById('categoryChart') && categoryData.length > 0) {
        const categoryChart = new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categoryData.map(item => item.category),
                datasets: [{
                    data: categoryData.map(item => item.total),
                    backgroundColor: [
                        primaryColor,
                        successColor,
                        infoColor,
                        warningColor,
                        dangerColor,
                        '#6f42c1',
                        '#fd7e14',
                        '#20c9a6',
                        '#6610f2',
                        '#e83e8c'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: ₱${value.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush