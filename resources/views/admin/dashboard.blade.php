<!-- resources/views/admin/dashboard.blade.php -->
@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('page-title', 'Admin Dashboard')

@section('content')
<div class="container-fluid py-4">
  <div class="row">
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Stores</p>
                <h5 class="font-weight-bolder mb-0">
                  {{ $storesCount }}
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                <i class="ni ni-shop text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Active Stores</p>
                <h5 class="font-weight-bolder mb-0">
                  {{ $activeStoresCount }}
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                <i class="ni ni-check-bold text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Users</p>
                <h5 class="font-weight-bolder mb-0">
                  {{ $usersCount }}
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                <i class="ni ni-single-02 text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-body p-3">
          <div class="row">
            <div class="col-8">
              <div class="numbers">
                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Products</p>
                <h5 class="font-weight-bolder mb-0">
                  {{ $productsCount }}
                </h5>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-lg-7 mb-lg-0 mb-4">
      <div class="card">
        <div class="card-header pb-0">
          <h6>Recent Stores</h6>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Store</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Location</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                  <th class="text-secondary opacity-7"></th>
                </tr>
              </thead>
              <tbody>
                @foreach($stores as $store)
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">
                          <a href="{{ route('stores.show', $store) }}">{{ $store->name }}</a>
                        </h6>
                        <p class="text-xs text-secondary mb-0">{{ $store->email ?? 'No email' }}</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="text-xs font-weight-bold mb-0">{{ ($store->city ?? 'N/A') }}, {{ ($store->state ?? 'N/A') }}</p>
                    <p class="text-xs text-secondary mb-0">{{ $store->address ?? 'No address' }}</p>
                  </td>
                  <td class="align-middle text-center text-sm">
                    <span class="badge badge-sm bg-gradient-{{ ($store->status ?? 'inactive') === 'active' ? 'success' : 'secondary' }}">{{ $store->status ?? 'inactive' }}</span>
                  </td>
                  <td class="align-middle">
                    <a href="{{ route('stores.edit', $store) }}" class="text-secondary font-weight-bold text-xs me-2">
                      Edit
                    </a>
                    <a href="http://{{ $store->slug }}.localhost/admin" target="_blank" class="text-info font-weight-bold text-xs">
                      Access
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-lg-5">
      <div class="card">
        <div class="card-header pb-0 p-3">
          <h6 class="mb-0">Platform Statistics</h6>
        </div>
        <div class="card-body p-3">
          <ul class="list-group">
            <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
              <div class="d-flex align-items-center">
                <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                  <i class="ni ni-mobile-button text-white opacity-10"></i>
                </div>
                <div class="d-flex flex-column">
                  <h6 class="mb-1 text-dark text-sm">New Stores</h6>
                  <span class="text-xs">Last 7 days</span>
                </div>
              </div>
              <div class="d-flex align-items-center text-sm">
                {{ $recentStoresCount }}
                <button class="btn btn-link text-dark text-sm mb-0 px-0 ms-4">
                  <i class="fas fa-file-pdf text-lg me-1"></i> Report
                </button>
              </div>
            </li>
            <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
              <div class="d-flex align-items-center">
                <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                  <i class="ni ni-tag text-white opacity-10"></i>
                </div>
                <div class="d-flex flex-column">
                  <h6 class="mb-1 text-dark text-sm">New Users</h6>
                  <span class="text-xs">Last 7 days</span>
                </div>
              </div>
              <div class="d-flex align-items-center text-sm">
                {{ $recentUsersCount }}
                <button class="btn btn-link text-dark text-sm mb-0 px-0 ms-4">
                  <i class="fas fa-file-pdf text-lg me-1"></i> Report
                </button>
              </div>
            </li>
          </ul>
          <div class="mt-4 text-center">
            <a href="{{ route('stores.create') }}" class="btn bg-gradient-dark w-100">Create New Store</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection