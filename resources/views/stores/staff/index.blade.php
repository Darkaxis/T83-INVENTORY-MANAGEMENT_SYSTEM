{{-- resources/views/store/staff/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Staff Management')

@section('page-title', 'Staff Management')

@section('content')
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between">
            <h6 class="text-white text-capitalize ps-3">{{ $store->name }} - Staff Members</h6>
            <a href="{{ route('staff.create') }}" class="btn btn-sm btn-success me-3">Add Staff</a>
          </div>
        </div>
        <div class="card-body px-0 pb-2">
          @if(session('success'))
            <div class="alert alert-success mx-3">
              {{ session('success') }}
            </div>
          @endif
          
          @if(session('error'))
            <div class="alert alert-danger mx-3">
              {{ session('error') }}
            </div>
          @endif
          
          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Email</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Position</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Role</th>
                  <th class="text-secondary opacity-7"></th>
                </tr>
              </thead>
              <tbody>
                @forelse($staff as $member)
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">{{ $member->name }}</h6>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="text-xs font-weight-bold mb-0">{{ $member->email }}</p>
                  </td>
                  <td>
                    <p class="text-xs text-secondary mb-0">{{ $member->position ?? 'Not specified' }}</p>
                  </td>
                  <td class="align-middle text-center text-sm">
                    <span class="badge badge-sm bg-gradient-{{ $member->role === 'manager' ? 'primary' : 'info' }}">
                      {{ ucfirst($member->role) }}
                    </span>
                  </td>
                  <td class="align-middle">
                    <a href="{{ route('staff.edit', $member->id) }}" class="text-secondary font-weight-bold text-xs me-2">
                      Edit
                    </a>
                    <a href="{{ route('staff.reset_password', $member->id) }}" class="text-warning font-weight-bold text-xs me-2">
                      Reset Password
                    </a>
                    <form action="{{ route('staff.destroy', $member->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-link p-0 text-danger text-xs font-weight-bold" 
                        onclick="return confirm('Are you sure you want to delete this staff member?')">
                        Delete
                      </button>
                    </form>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center py-4">
                    <p class="text-md">No staff members found. <a href="{{ route('staff.create') }}">Add your first staff member</a>.</p>
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