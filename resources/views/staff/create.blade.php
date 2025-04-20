<!-- filepath: d:\WST\inventory-management-system\resources\views\staff\create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-user-plus me-2"></i>Add Staff Member
            </h1>
            <p class="text-muted">Add a new staff member to your team</p>
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
            <form action="{{ route('staff.store', ['subdomain' => $store->slug]) }}" method="POST">
                @csrf
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="name" class="form-label fw-bold">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" required>
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
                                       value="{{ old('email') }}" required>
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
                                    <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
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
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    A random password will be generated and displayed after submission.
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('staff.index', ['subdomain' => $store->slug]) }}" class="btn btn-light shadow-sm px-4">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary shadow-sm px-4">
                        <i class="fas fa-save me-2"></i>Add Staff Member
                    </button>
                </div>
            </form>
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
    
    /* Additional accent styling */
    .card-header {
        border-bottom: 2px solid var(--primary, #4e73df);
    }
    
    /* Make the alert info use the accent color */
    .alert-info {
        background-color: var(--highlight, rgba(78, 115, 223, 0.25));
        border-color: var(--primary, #4e73df);
        color: var(--tertiary, #2653d4);
    }
    
    /* Custom select on focus */
    .form-select:focus {
        border-color: var(--primary, #4e73df);
    }
</style>
@endpush