<!-- filepath: resources/views/admin/support/index.blade.php -->
@extends('layouts.app')

@section('title', 'Support Tickets Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="h3 mb-0 text-gray-800">Support Tickets</h1>
            <p class="mb-4">Manage all customer support tickets</p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="btn-group" role="group">
                <button type="button" class="btn bg-gradient-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-filter me-2"></i> Filter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.support.index', ['status' => 'open']) }}">Open</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.support.index', ['status' => 'in_progress']) }}">In Progress</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.support.index', ['status' => 'waiting']) }}">Waiting</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.support.index', ['status' => 'resolved']) }}">Resolved</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.support.index', ['status' => 'closed']) }}">Closed</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('admin.support.index') }}">All Tickets</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Open Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $openCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Urgent Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $urgentCount }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            @if($tickets->count() > 0)
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7">ID/Subject</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Store</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 ps-2">Category</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 ps-2">Priority</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 ps-2">Created</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 ps-2">Updated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            <tr>
                                <td>
                                    <div class="d-flex px-3 py-1">
                                        <div>
                                            <i class="fas fa-ticket-alt text-primary me-3 fa-lg"></i>
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">#{{ $ticket->id }}</h6>
                                            <p class="text-sm text-dark mb-0">{{ $ticket->subject }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex px-3 py-1">
                                        <span class="text-xs font-weight-bold">{{ $ticket->store->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-xs font-weight-bold">{{ $ticket->category }}</span>
                                </td>
                                <td>
                                    @if($ticket->status == 'open')
                                        <span class="badge bg-primary">Open</span>
                                    @elseif($ticket->status == 'in_progress')
                                        <span class="badge bg-warning text-dark">In Progress</span>
                                    @elseif($ticket->status == 'waiting')
                                        <span class="badge bg-info">Waiting</span>
                                    @elseif($ticket->status == 'resolved')
                                        <span class="badge bg-success">Resolved</span>
                                    @elseif($ticket->status == 'closed')
                                        <span class="badge bg-secondary">Closed</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->priority == 'low')
                                        <span class="badge bg-success">Low</span>
                                    @elseif($ticket->priority == 'medium')
                                        <span class="badge bg-info">Medium</span>
                                    @elseif($ticket->priority == 'high')
                                        <span class="badge bg-warning text-dark">High</span>
                                    @elseif($ticket->priority == 'urgent')
                                        <span class="badge bg-danger">Urgent</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-xs font-weight-bold">
                                        {{ $ticket->created_at->format('M d, Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-xs font-weight-bold">
                                        {{ $ticket->updated_at->diffForHumans() }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('admin.support.show', $ticket->id) }}" class="btn btn-link text-dark px-3 mb-0">
                                        <i class="fas fa-eye text-dark me-2"></i>View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-3">
                    {{ $tickets->links() }}
                </div>
            @else
                <div class="text-center p-5">
                    <i class="fas fa-ticket-alt fa-3x text-secondary mb-3"></i>
                    <h4>No Support Tickets</h4>
                    <p>There are no support tickets matching your filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection