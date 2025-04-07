@extends('layouts.app')

@section('title', $store->name . ' Dashboard')

@section('content')
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12 mb-4">
      <div class="card">
        <div class="card-header p-3">
          <h5 class="mb-0">{{ $store->name }} Dashboard</h5>
          <p class="text-sm mb-0">
            Store Management Portal
          </p>
        </div>
        <div class="card-body p-3">
          <div class="alert alert-info text-white">
            Welcome to your store management dashboard. You are currently managing <strong>{{ $store->name }}</strong>.
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="row">
    <div class="col-xl-3 col-sm-6 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Products</p>
                <h5 class="font-weight-bolder mb-0">
                  {{ $store->products()->count() }}
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-sm-6 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Staff</p>
                <h5 class="font-weight-bolder mb-0">
                  {{ $store->users()->count() }}
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                <i class="ni ni-single-02 text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Add more dashboard cards as needed -->
  </div>
  
  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header pb-0">
          <div class="row">
            <div class="col-6 d-flex align-items-center">
              <h6 class="mb-0">Quick Actions</h6>
            </div>
          </div>
        </div>
        <div class="card-body pt-4 p-3">
          <ul class="list-group">
            <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
              <div class="d-flex flex-column">
                <h6 class="mb-3 text-sm">Manage Products</h6>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-info mb-0">View Products</a>
              </div>
            </li>
            <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
              <div class="d-flex flex-column">
                <h6 class="mb-3 text-sm">Manage Staff</h6>
                <a href="{{ route('staff.index') }}" class="btn btn-sm btn-info mb-0">View Staff</a>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection