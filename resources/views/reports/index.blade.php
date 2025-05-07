<!-- filepath: d:\WST\inventory-management-system\resources\views\reports\index.blade.php -->

@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container-fluid py-4">
    
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-chart-line me-2"></i>Reports
            </h1>
            <p class="text-muted">Generate detailed reports about your business</p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="d-inline-block p-3 bg-light rounded-circle">
                <img src="{{ asset('images/pro-badge.svg') }}" alt="Pro Feature" width="30">
            </div>
            <div class="d-block text-muted small">Professional Feature</div>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <div class="row">
        <!-- Sales Report Card -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-elevation">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <div class="feature-icon bg-gradient-primary text-white rounded-circle mb-3">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary">Most Popular</span>
                        </div>
                    </div>
                    
                    <h4 class="card-title">Sales Report</h4>
                    <p class="card-text text-muted">
                        Analyze your sales performance over time, by product, category, or staff member.
                    </p>
                    
                    <ul class="list-unstyled mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Daily, weekly, or monthly breakdowns</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Visual charts and graphs</li>
                      
                    </ul>
                    
                    <a href="{{ route('reports.sales', ['subdomain' => $store->slug]) }}" class="btn btn-primary w-100">
                        <i class="fas fa-chart-bar me-2"></i>Generate Report
                    </a>
                </div>
            </div>
        </div>
        
    
    </div>
</div>
@endsection

@push('styles')
<style>
    .feature-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .hover-elevation {
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .hover-elevation:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }
</style>
@endpush