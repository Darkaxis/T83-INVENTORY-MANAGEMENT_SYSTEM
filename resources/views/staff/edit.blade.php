<!-- filepath: d:\WST\inventory-management-system\resources\views\staff\edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-user-edit me-2"></i>Edit Staff Member
            </h1>
            <p class="text-muted">Update staff member information</p>
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

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-light py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="mb-0 fw-bold">Staff Information</h6>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('staff.update', ['subdomain' => $store->slug, 'staff_id' => $staff->id]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="name" class="form-label fw-bold">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $staff->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Enter the staff member's full name</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="email" class="form-label fw-bold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $staff->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">The staff member will use this email to log in</small>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="role" class="form-label fw-bold">Role</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="staff" {{ (old('role', $staff->role) == 'staff') ? 'selected' : '' }}>Staff</option>
                                    <option value="manager" {{ (old('role', $staff->role) == 'manager') ? 'selected' : '' }}>Manager</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">
                                Managers can add/remove other staff members and have full access
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('staff.index', ['subdomain' => $store->slug]) }}" class="btn btn-light shadow-sm px-4">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary shadow-sm px-4">
                        <i class="fas fa-save me-2"></i>Update Staff Member
                    </button>
                </div>
            </form>
        </div>
        
        <div class="card-footer bg-light py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted">Created: 
                        {{ \Carbon\Carbon::parse($staff->created_at)->format('M d, Y') }}
                    </span>
                </div>
                <div>
                    <form action="{{ route('staff.reset-password', ['subdomain' => $store->slug, 'staff_id' => $staff->id]) }}" 
                          method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm" 
                                onclick="return confirm('Are you sure you want to reset the password for this user?')">
                            <i class="fas fa-key me-1"></i>Reset Password
                        </button>
                    </form>
                    
                    <form action="{{ route('staff.destroy', ['subdomain' => $store->slug, 'staff_id' => $staff->id]) }}" 
                          method="POST" class="d-inline ms-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" 
                                onclick="return confirm('Are you sure you want to delete this staff member?')">
                            <i class="fas fa-trash me-1"></i>Delete Staff Member
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-control:focus, .form-select:focus, .form-check-input:focus {
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
    
    .form-check-input:checked {
        background-color: var(--primary, #4e73df);
        border-color: var(--primary, #4e73df);
    }
    
    /* Additional accent styling */
    .card-header {
        border-bottom: 2px solid var(--primary, #4e73df);
    }
    
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--primary, #4e73df);
        border-color: var(--primary, #4e73df);
    }
    
    .btn-warning:hover {
        background-color: #e0ac29;
    }
    
    .btn-danger:hover {
        background-color: #c0372a;
    }
</style>
@endpush