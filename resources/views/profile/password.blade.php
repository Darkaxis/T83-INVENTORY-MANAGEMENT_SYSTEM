<!-- filepath: d:\WST\inventory-management-system\resources\views\profile\password.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="display-5 fw-bold text-primary">
                <i class="fas fa-key me-2"></i>Change Password
            </h1>
            <p class="text-muted">Update your account password</p>
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

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="mb-0 fw-bold">Password Settings</h6>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update-password', ['subdomain' => $store->slug]) }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <div class="form-group mb-3">
                                <label for="current_password" class="form-label fw-bold">Current Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="current_password" id="current_password" 
                                          class="form-control @error('current_password') is-invalid @enderror" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-group mb-3">
                                <label for="password" class="form-label fw-bold">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" name="password" id="password" 
                                          class="form-control @error('password') is-invalid @enderror" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">
                                    Use at least 8 characters with a mix of letters, numbers & symbols
                                </small>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-group mb-3">
                                <label for="password_confirmation" class="form-label fw-bold">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                                    <input type="password" name="password_confirmation" id="password_confirmation" 
                                          class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary shadow-sm py-2">
                                <i class="fas fa-save me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        border-color: #bac8f3;
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
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2653d4;
    }
</style>
@endpush