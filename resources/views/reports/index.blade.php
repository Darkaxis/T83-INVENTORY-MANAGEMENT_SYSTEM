<!-- filepath: d:\WST\inventory-management-system\resources\views\reports\index.blade.php -->

@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-chart-bar me-2"></i>Reports
            </h1>
            <p class="text-muted">Generate insights about your business</p>
        </div>
    </div>
    
    <div class="card shadow-sm border-0">
        <div class="card-body p-5">
            <div class="text-center">
                <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                <h3>Pro Feature</h3>
                <p class="text-muted">Advanced reporting is available on your Pro plan.</p>
                
                <div class="row mt-5">
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-pie fa-2x text-info mb-3"></i>
                                <h5>Sales Analysis</h5>
                                <p class="small text-muted">Track your sales performance over time</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-boxes fa-2x text-success mb-3"></i>
                                <h5>Inventory Reports</h5>
                                <p class="small text-muted">Monitor stock levels and movements</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x text-warning mb-3"></i>
                                <h5>Customer Insights</h5>
                                <p class="small text-muted">Understand your customer base</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection