<!-- filepath: d:\WST\inventory-management-system\resources\views\admin\settings\tenant.blade.php -->
@extends('layouts.app')

@section('title', 'Tenant System Settings')

@section('page-title', 'Tenant Settings')

@section('content')
<div class="container-fluid py-4">
  @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif
  
  @if(session('error'))
    <div class="alert alert-danger">
      {{ session('error') }}
    </div>
  @endif

  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">Tenant System Settings</h6>
          </div>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.settings.tenant.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <h5 class="mb-4">Database Settings</h5>
            
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="input-group input-group-static mb-4">
                  <label>Default Database Connection</label>
                  <input type="text" class="form-control" name="db_connection" value="{{ config('database.default') }}">
                </div>
              </div>
              <div class="col-md-6">
                <div class="input-group input-group-static mb-4">
                  <label>Tenant Database Prefix</label>
                  <input type="text" class="form-control" name="tenant_prefix" value="tenant_">
                </div>
              </div>
            </div>
            
            <h5 class="mb-4">Tenant Access Settings</h5>
            
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="allow_tenant_registration" name="allow_tenant_registration" checked>
                  <label class="form-check-label" for="allow_tenant_registration">
                    Allow tenant self-registration
                  </label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="require_email_verification" name="require_email_verification" checked>
                  <label class="form-check-label" for="require_email_verification">
                    Require email verification
                  </label>
                </div>
              </div>
            </div>
            
            <button type="submit" class="btn bg-gradient-primary">Save Settings</button>
          </form>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card mb-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">System Status</h6>
          </div>
        </div>
        <div class="card-body">
          <ul class="list-group">
            <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
              <div class="d-flex align-items-center">
                <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                  <i class="fas fa-store text-white opacity-10"></i>
                </div>
                <div class="d-flex flex-column">
                  <h6 class="mb-1 text-dark text-sm">Active Stores</h6>
                  <span class="text-xs">Status of tenant stores</span>
                </div>
              </div>
              <div class="d-flex align-items-center text-success text-gradient text-sm font-weight-bold">
                {{ $activeStores }} / {{ $totalStores }}
              </div>
            </li>
            <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
              <div class="d-flex align-items-center">
                <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                  <i class="fas fa-database text-white opacity-10"></i>
                </div>
                <div class="d-flex flex-column">
                  <h6 class="mb-1 text-dark text-sm">Database Issues</h6>
                  <span class="text-xs">Stores with database problems</span>
                </div>
              </div>
              <div class="d-flex align-items-center {{ $databaseIssues > 0 ? 'text-danger' : 'text-success' }} text-gradient text-sm font-weight-bold">
                {{ $databaseIssues }}
              </div>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="card">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
          <div class="bg-gradient-warning shadow-warning border-radius-lg pt-4 pb-3">
            <h6 class="text-white text-capitalize ps-3">Quick Actions</h6>
          </div>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <a href="{{ route('stores.index') }}" class="btn btn-outline-primary btn-sm">
              <i class="fas fa-store me-2"></i> Manage Stores
            </a>
            <button class="btn btn-outline-warning btn-sm" type="button">
              <i class="fas fa-sync me-2"></i> Check All Databases
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection