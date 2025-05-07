<!-- filepath: resources/views/tenant/support/index.blade.php -->
@extends('layouts.app')

@section('title', 'Support Tickets')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-lg-8">
            <h1 class="h3 mb-0 text-gray-800">Support Tickets</h1>
            <p class="mb-4">Manage your support requests and get help from our team</p>
        </div>
        <div class="col-lg-4 text-end">
            <a href="{{ route('tenant.support.create') }}" class="btn bg-gradient-primary">
                <i class="fas fa-plus me-2"></i> Create Ticket
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header p-3">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-0">Your Support Tickets</h5>
                </div>
                <div class="col-md-6 text-end">
                    <span class="badge bg-primary me-2">Open: {{ $tickets->where('status', 'open')->count() }}</span>
                    <span class="badge bg-warning me-2">In Progress: {{ $tickets->where('status', 'in_progress')->count() }}</span>
                    <span class="badge bg-success">Resolved: {{ $tickets->where('status', 'resolved')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            @if($tickets->count() > 0)
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7">Ticket</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 ps-2">Category</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 ps-2">Priority</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 ps-2">Created</th>
                            <th class="text-uppercase text-xxs font-weight-bolder opacity-7 ps-2">Last Updated</th>
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
                                    <a href="{{ route('tenant.support.show', $ticket->id) }}" class="btn btn-link text-dark px-3 mb-0">
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
                    <p>You haven't created any support tickets yet.</p>
                    <a href="{{ route('tenant.support.create') }}" class="btn bg-gradient-primary mt-3">Create Your First Ticket</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection