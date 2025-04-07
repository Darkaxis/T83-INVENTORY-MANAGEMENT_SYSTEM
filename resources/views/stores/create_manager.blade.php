<!-- resources/views/stores/create_manager.blade.php -->
@extends('layouts.app')

@section('title', 'Create Store Manager')

@section('content')
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">Create Manager for {{ $store->name }}</h6>
          </div>
        </div>
        <div class="card-body px-0 pb-2">
          <form method="POST" action="{{ route('stores.store_manager', $store) }}" class="p-4">
            @csrf
            
            <div class="row">
              <div class="col-md-6">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Manager Name</label>
                  <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                </div>
                @error('name')
                  <div class="text-danger text-xs">{{ $message }}</div>
                @enderror
              </div>
              
              <div class="col-md-6">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                </div>
                @error('email')
                  <div class="text-danger text-xs">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Password</label>
                  <input type="password" class="form-control" name="password" required>
                </div>
                @error('password')
                  <div class="text-danger text-xs">{{ $message }}</div>
                @enderror
              </div>
              
              <div class="col-md-6">
                <div class="input-group input-group-outline my-3">
                  <label class="form-label">Confirm Password</label>
                  <input type="password" class="form-control" name="password_confirmation" required>
                </div>
              </div>
            </div>
            
            <div class="row mt-4">
              <div class="col-md-12">
                <button type="submit" class="btn bg-gradient-success">Create Manager</button>
                <a href="{{ route('stores.index') }}" class="btn btn-outline-secondary ms-2">Skip</a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection