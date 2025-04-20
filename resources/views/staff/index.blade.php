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
                                    <span class="badge bg-{{ $member->role == 'manager' ? 'primary' : 'secondary' }} rounded-pill px-3">
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