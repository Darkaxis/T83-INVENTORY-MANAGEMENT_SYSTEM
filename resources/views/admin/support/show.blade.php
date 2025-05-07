<!-- filepath: resources/views/admin/support/show.blade.php -->
@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->id)

@section('styles')
<style>
    .message-bubble {
        border-radius: 1rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        position: relative;
    }
    .message-admin {
        background-color: rgba(78, 115, 223, 0.1);
    }
    .message-user {
        background-color: rgba(28, 200, 138, 0.1);
    }
    .message-time {
        font-size: 0.75rem;
        color: #858796;
        margin-top: 5px;
    }
    .attachment-item {
        display: inline-block;
        padding: 5px 10px;
        margin: 5px;
        background: #f8f9fc;
        border: 1px solid #eaecf4;
        border-radius: 0.35rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-lg-6">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-ticket-alt me-2 text-primary"></i>
                Ticket #{{ $ticket->id }}
            </h1>
            <p class="mb-0">{{ $ticket->subject }}</p>
            <p class="text-xs">From: {{ $ticket->store->name }}</p>
        </div>
        <div class="col-lg-6 text-end">
            <span class="badge bg-info me-2">Status: 
                @if($ticket->status == 'open')
                    Open
                @elseif($ticket->status == 'in_progress')
                    In Progress
                @elseif($ticket->status == 'waiting')
                    Waiting
                @elseif($ticket->status == 'resolved')
                    Resolved
                @elseif($ticket->status == 'closed')
                    Closed
                @endif
            </span>
            
            <span class="badge bg-primary me-2">Priority: 
                @if($ticket->priority == 'low')
                    Low
                @elseif($ticket->priority == 'medium')
                    Medium
                @elseif($ticket->priority == 'high')
                    High
                @elseif($ticket->priority == 'urgent')
                    Urgent
                @endif
            </span>
            
            <a href="{{ route('admin.support.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Tickets
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header p-3">
                    <h5 class="mb-0">Conversation</h5>
                </div>
                <div class="card-body">
                    <div class="conversation-area p-3">
                        @foreach($messages as $message)
                            <div class="message-container @if($message->is_admin) text-end @endif">
                                <div class="message-bubble @if($message->is_admin) message-admin ms-auto @else message-user me-auto @endif" style="max-width: 80%;">
                                    <div class="d-flex @if($message->is_admin) justify-content-end @else justify-content-between @endif align-items-center mb-2">
                                        <span class="fw-bold">
                                            @if($message->is_admin)
                                                Support Team
                                            @else
                                                User
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <p class="mb-2">{!! nl2br(e($message->message)) !!}</p>
                                    
                                    @if($message->attachments->count() > 0)
                                        <div class="attachments mt-2">
                                            <div class="text-muted small mb-1">Attachments:</div>
                                            @foreach($message->attachments as $attachment)
                                                <div class="attachment-item">
                                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank">
                                                        <i class="fas fa-paperclip me-1"></i>
                                                        {{ $attachment->file_name }}
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <div class="message-time">
                                        {{ $message->created_at->format('M d, Y g:i A') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <form method="POST" action="{{ route('admin.support.reply', $ticket->id) }}" enctype="multipart/form-data" class="mt-4">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label">Response</label>
                            <textarea class="form-control" name="message" rows="4" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Attachments (Optional)</label>
                                    <input type="file" class="form-control" name="attachments[]" multiple>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Update Status</label>
                                <select class="form-control" name="status" required>
                                    <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="waiting" {{ $ticket->status == 'waiting' ? 'selected' : '' }}>Waiting for Customer</option>
                                    <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group text-end">
                            <button type="submit" class="btn bg-gradient-primary">Send Response</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header p-3">
                    <h5 class="mb-0">Ticket Information</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Store:</span>
                        <span>{{ $ticket->store->name }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Created:</span>
                        <span>{{ $ticket->created_at->format('M d, Y g:i A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Last Updated:</span>
                        <span>{{ $ticket->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Category:</span>
                        <span>{{ ucfirst($ticket->category) }}</span>
                    </div>
                    
                    @if($ticket->status != 'closed')
                        <hr>
                        <form method="POST" action="{{ route('admin.support.reply', $ticket->id) }}">
                            @csrf
                            <input type="hidden" name="message" value="This ticket has been resolved and is now closed.">
                            <input type="hidden" name="status" value="closed">
                            <button type="submit" class="btn btn-outline-danger w-100 mb-2">
                                <i class="fas fa-times-circle me-1"></i> Close Ticket
                            </button>
                        </form>
                    @endif
                    
                    <hr>
                    
                </div>
            </div>
            
            <div class="card">
                <div class="card-header p-3">
                    <h5 class="mb-0">Internal Notes</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Add private notes about this customer or issue (not visible to customer)</p>
                    <textarea class="form-control mb-3" rows="3" placeholder="Add internal notes here..."></textarea>
                    <button class="btn btn-sm bg-gradient-dark w-100">Save Notes</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection