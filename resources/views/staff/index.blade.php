<!-- filepath: d:\WST\inventory-management-system\resources\views\staff\index.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-users me-2"></i>Staff Management
            </h1>
            <p class="text-muted">Manage your store staff members</p>
        </div>
        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end mt-3 mt-md-0">
            <a href="{{ route('staff.create', ['subdomain' => $store->slug]) }}" 
               class="btn btn-primary btn-lg shadow-sm">
                <i class="fas fa-user-plus me-2"></i>Add Staff Member
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger shadow-sm border-start border-danger border-4">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                </div>
                <div>{{ session('error') }}</div>
            </div>
        </div>
    @endif
    
    @if(session('success'))
        <div class="alert alert-success shadow-sm border-start border-success border-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x text-success me-3"></i>
                <div>{{ session('success') }}</div>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-users text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">Staff Usage</h6>
                            <p class="mb-0 text-muted small">
                                {{ $store->pricingTier->name }} Plan • 
                                {{ $store->billing_cycle == 'annual' ? 'Annual' : 'Monthly' }} billing
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-2">
                        <div class="d-flex align-items-center justify-content-center">
                            <span class="h3 mb-0 me-2">{{ $staff->total() }}</span>
                            <span class="text-muted">/</span>
                            <span class="h4 mb-0 ms-2">{{ $store->pricingTier->user_limit < 0 ? '∞' : number_format($store->pricingTier->user_limit) }}</span>
                            <span class="ms-2 text-muted">staff members</span>
                        </div>
                        @php
                            $staffPercentUsed = $store->pricingTier->user_limit > 0 ? 
                                min(100, round(($staff->total() / $store->pricingTier->user_limit) * 100)) : 0;
                        @endphp
                        <div class="progress mt-2" style="height: 8px">
                            <div class="progress-bar bg-{{ $staffPercentUsed > 90 ? 'danger' : ($staffPercentUsed > 75 ? 'warning' : 'info') }}" 
                                 role="progressbar" 
                                 style="width: {{ $staffPercentUsed }}%" 
                                 aria-valuenow="{{ $staffPercentUsed }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100"></div>
                        </div>
                        
                        @if($staffPercentUsed > 75)
                            <div class="text-center mt-3">
                                <a href="{{ route('subscription.index', ['subdomain' => $store->slug]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-arrow-circle-up me-1"></i> Upgrade Plan
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-light py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="mb-0 fw-bold">Staff Members</h6>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">Name</th>
                            <th>Email</th>
                            <th>Role</th>
                          
                            <th class="text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $member)
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold">{{ $member->name }}</div>
                                </td>
                                <td>
                                    <span>{{ $member->email }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $member->role == 'manager' ? 'role-badge-manager' : 'bg-secondary' }} rounded-pill px-3">
                                        {{ ucfirst($member->role) }}
                                    </span>
                                </td>
                                
                                
                                <td class="text-end px-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('staff.edit', ['subdomain' => $store->slug, 'staff_id' => $member->id]) }}" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('staff.reset-password', ['subdomain' => $store->slug, 'staff_id' => $member->id]) }}" 
                                              method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-info" 
                                                    onclick="return confirm('Are you sure you want to reset the password for this user?')">
                                                <i class="fas fa-key"></i> Reset Password
                                            </button>
                                        </form>
                                        <form action="{{ route('staff.destroy', ['subdomain' => $store->slug, 'staff_id' => $member->id]) }}" 
                                              method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this staff member?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center p-5">
                                    <div class="py-5">
                                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                                        <h4>No Staff Members Found</h4>
                                        <p class="text-muted">Start adding staff to manage your store</p>
                                        <a href="{{ route('staff.create', ['subdomain' => $store->slug]) }}" 
                                           class="btn btn-primary mt-2">
                                            <i class="fas fa-user-plus me-2"></i>Add First Staff Member
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-center">
                {{ $staff->appends(['subdomain' => $store->slug])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem var(--highlight, rgba(78, 115, 223, 0.25));
        border-color: var(--tertiary, #bac8f3);
    }
    
    .input-group-text {
        background-color: #f8f9fa;
    }
    
    .card {
        transition: all 0.2s ease-in-out;
    }
    
    .btn {
        font-weight: 500;
    }
    
    .btn-primary {
        background-color: var(--primary, #4e73df);
        border-color: var(--primary, #4e73df);
    }
    
    .btn-primary:hover {
        background-color: var(--secondary, #2e59d9);
        border-color: var(--tertiary, #2653d4);
    }
    
    .text-primary {
        color: var(--primary, #4e73df) !important;
    }
    
    .bg-primary {
        background-color: var(--primary, #4e73df) !important;
    }
    
    /* Style pagination to match accent color */
    .pagination .page-item.active .page-link {
        background-color: var(--primary, #4e73df);
        border-color: var(--primary, #4e73df);
    }
    
    .pagination .page-link {
        color: var(--primary, #4e73df);
    }
    
    .pagination .page-link:hover {
        color: var(--secondary, #2e59d9);
    }
    
    .pagination .page-link:focus {
        box-shadow: 0 0 0 0.25rem var(--highlight, rgba(78, 115, 223, 0.25));
    }
    
    /* Role badge styling - make manager badges use the primary color */
    .role-badge-manager {
        background-color: var(--primary, #4e73df) !important;
    }
</style>
@endpush