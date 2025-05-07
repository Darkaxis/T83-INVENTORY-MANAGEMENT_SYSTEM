@extends('layouts.app')

@section('title', 'System Update')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">System Update</h1>
            <p class="mb-4">Update your inventory management system to the latest version</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Current System</h6>
                    <form action="{{ route('admin.system.update.check') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-sync-alt mr-1"></i> Check for Updates
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5>Current Version</h5>
                        <div class="bg-light p-3 rounded">
                            <h2 class="mb-0">{{ $currentVersion }}</h2>
                        </div>
                    </div>
                    
                    @if(isset($latestRelease))
                        <div class="mb-3">
                            <h5>Latest Available Version</h5>
                            <div class="bg-light p-3 rounded">
                                <h2 class="mb-0">{{ $latestRelease['version'] ?? 'Unknown' }}</h2>
                                <p class="small text-muted">Released: {{ isset($latestRelease['published_at']) ? \Carbon\Carbon::parse($latestRelease['published_at'])->format('M d, Y') : 'Unknown' }}</p>
                            </div>
                        </div>
                        
                        @if($latestRelease['has_update'] ?? false)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i> A newer version is available!
                            </div>
                            
                            <div class="mb-4">
                                <h5>Release Notes</h5>
                                <div class="border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                                    {!! nl2br(e($latestRelease['body'] ?? 'No release notes available.')) !!}
                                </div>
                            </div>
                            
                            <form action="{{ route('admin.system.update.process') }}" method="POST" onsubmit="return confirm('Are you sure you want to update the system to version {{ $latestRelease['version'] }}? This process cannot be undone.');">
                                @csrf
                                <input type="hidden" name="version" value="{{ $latestRelease['version'] }}">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-download mr-1"></i> Update to v{{ $latestRelease['version'] }}
                                </button>
                            </form>
                        @else
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle mr-1"></i> Your system is up to date.
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Unable to check for updates. Please try again later.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update History</h6>
                </div>
                <div class="card-body">
                    @if($updateHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($updateHistory as $update)
                                        <tr>
                                            <td>{{ $update->created_at->format('M d, Y H:i') }}</td>
                                            <td>{{ $update->version_from }}</td>
                                            <td>{{ $update->version_to }}</td>
                                            <td>
                                                @if($update->status == 'completed')
                                                    <span class="badge badge-success">Completed</span>
                                                @elseif($update->status == 'processing')
                                                    <span class="badge badge-warning">Processing</span>
                                                @else
                                                    <span class="badge badge-danger" title="{{ $update->error_message }}">Failed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-gray-300 mb-3"></i>
                            <p>No update history found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection