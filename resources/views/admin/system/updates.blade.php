{{-- resources/views/admin/system/updates.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">System Updates</h1>
        <div>
            <span class="mr-2">Current Version: v{{ $currentVersion }}</span>
            <form action="{{ route('admin.system.updates.check') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-sync-alt fa-sm text-white-50 mr-1"></i> Check for Updates
                </button>
            </form>
        </div>
    </div>

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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Available Updates</h6>
        </div>
        <div class="card-body">
            @if($updates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Version</th>
                                <th>Status</th>
                                <th>Release Notes</th>
                                <th>Last Activity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($updates as $update)
                            <tr>
                                <td>v{{ $update->version }}</td>
                                <td>
                                    @if($update->status == 'checking')
                                        <span class="badge badge-info">Checking</span>
                                    @elseif($update->status == 'downloading')
                                        <span class="badge badge-warning">Downloading</span>
                                    @elseif($update->status == 'downloaded')
                                        <span class="badge badge-primary">Ready to Install</span>
                                    @elseif($update->status == 'installing')
                                        <span class="badge badge-warning">Installing</span>
                                    @elseif($update->status == 'completed')
                                        <span class="badge badge-success">Installed</span>
                                    @elseif($update->status == 'failed')
                                        <span class="badge badge-danger">Failed</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-link" data-toggle="modal" data-target="#notesModal{{ $update->id }}">
                                        View Notes
                                    </button>
                                    
                                    <!-- Notes Modal -->
                                    <div class="modal fade" id="notesModal{{ $update->id }}" tabindex="-1" role="dialog" aria-labelledby="notesModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="notesModalLabel">Release Notes - v{{ $update->version }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    {!! nl2br(e($update->release_notes)) !!}
                                                    
                                                    @if($update->error_message)
                                                        <hr>
                                                        <h6 class="text-danger">Error:</h6>
                                                        <p class="text-danger">{{ $update->error_message }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($update->installed_at)
                                        {{ $update->installed_at->format('M d, Y H:i') }}
                                    @elseif($update->downloaded_at)
                                        {{ $update->downloaded_at->format('M d, Y H:i') }}
                                    @elseif($update->checked_at)
                                        {{ $update->checked_at->format('M d, Y H:i') }}
                                    @else
                                        {{ $update->created_at->format('M d, Y H:i') }}
                                    @endif
                                </td>
                                <td>
                                    @if($update->status == 'checking')
                                        <form action="{{ route('admin.system.updates.download', $update->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">Download</button>
                                        </form>
                                    @elseif($update->status == 'downloaded')
                                        <form action="{{ route('admin.system.updates.install', $update->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Install</button>
                                        </form>
                                    @elseif($update->status == 'failed')
                                        <form action="{{ route('admin.system.updates.download', $update->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning">Retry</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $updates->links() }}
            @else
                <div class="text-center py-4">
                    <p class="text-muted">No updates available. Your system is up to date.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection