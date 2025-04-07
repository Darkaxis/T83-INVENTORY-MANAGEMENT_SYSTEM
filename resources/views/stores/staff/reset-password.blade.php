{{-- resources/views/store/staff/reset-password.blade.php --}}
@extends('layouts.app')

@section('title', 'Reset Password')

@section('page-title', 'Reset Password')

@section('content')
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-lg-8 col-md-10 mx-auto">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-warning shadow-warning border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">Reset Password for {{ $staff->name }}</h6>
          </div>
        </div>
        <div class="card-body px-0 pb-2">
          <form method="POST" action="{{ route('staff.update_password', $staff->id) }}" class="p-4">
            @csrf
            
            <div class="row">
              <div class="col-md-6">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">New Password</label>
                  <input type="password" class="form-control" name="password" required>
                </div>
                @error('password')
                  <div class="text-danger text-xs mt-1">{{ $message }}</div>
                @enderror
              </div>
              
              <div class="col-md-6">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Confirm New Password</label>
                  <input type="password" class="form-control" name="password_confirmation" required>
                </div>
              </div>
            </div>
            
            <div class="row mt-4">
              <div class="col-md-12">
                <button type="submit" class="btn bg-gradient-warning">Reset Password</button>
                <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection