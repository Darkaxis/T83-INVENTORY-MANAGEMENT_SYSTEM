<!-- filepath: resources/views/tenant/support/show.blade.php -->
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
            <h1 class="h3 mb-0">Ticket #{{ $ticket->id }}</h1>
            <p class="mb-1">{{ $ticket->subject }}</p>
        </div>
        <div class="col-lg-6 text-end">
            <span class="badge bg-primary me-2">Status: 
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
            
            <span class="badge bg-info me-2">Priority: 
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
            
            <a href="{{ route('tenant.support.index') }}" class="btn btn-{{ $store->settings->theme_color ?? 'primary' }}">
                <i class="fas fa-arrow-left me-1"></i> Back to Tickets
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success shadow-sm border-start border-success border-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle text-success me-3 fa-2x"></i>
                <div>{{ session('success') }}</div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Conversation</h5>
                </div>
                <div class="card-body">
                    <div class="conversation-area p-3">
                        @foreach($messages as $message)
                            <div class="message-container @if($message->is_admin) text-end @endif">
                                <div class="message-bubble @if($message->is_admin) message-admin ms-auto @else message-user me-auto @endif" style="max-width: 80%;">
                                    <div class="d-flex @if($message->is_admin) justify-content-end @else justify-content-between @endif align-items-center mb-2">
                                        <span class="fw-bold text-{{ $message->is_admin ? 'primary' : 'dark' }}">
                                            @if($message->is_admin)
                                                Support Team
                                            @else
                                                You
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <p class="mb-2 @if($message->is_admin) text-end @endif">{!! nl2br(e($message->message)) !!}</p>
                                    
                                    @if($message->attachments->count() > 0)
                                        <div class="attachments mt-2 @if($message->is_admin) text-end @endif">
                                            <div class="text-muted small mb-1">Attachments:</div>
                                            @foreach($message->attachments as $attachment)
                                                <div class="attachment-item">
                                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" class="text-primary">
                                                        <i class="fas fa-paperclip me-1"></i>
                                                        {{ $attachment->file_name }}
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    
                                    <div class="message-time @if($message->is_admin) text-end @endif">
                                        {{ $message->created_at->format('M d, Y g:i A') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    @if($ticket->status != 'closed')
                        <form method="POST" action="{{ route('tenant.support.reply', $ticket->id) }}" enctype="multipart/form-data" class="mt-4">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label">Reply</label>
                                <textarea class="form-control" name="message" rows="4" required></textarea>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Attachments (Optional)</label>
                                <input type="file" class="form-control" name="attachments[]" multiple>
                                <small class="text-muted">You can upload multiple files. Max size: 10MB per file.</small>
                            </div>
                            <div class="form-group text-end">
                                <button type="submit" class="btn bg-gradient-primary">Send Reply</button>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-warning mt-4">
                            This ticket is closed. If you need further assistance, please create a new ticket.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Ticket Information</h5>
                </div>
                <div class="card-body">
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
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Submitted By:</span>
                        <span>You</span>
                    </div>
                    
                    @if($ticket->status != 'closed')
                        <hr>
                        <form method="POST" action="{{ route('tenant.support.reply', $ticket->id) }}">
                            @csrf
                            <input type="hidden" name="message" value="I consider this issue resolved. Please close this ticket.">
                            <button type="submit" class="btn btn-outline-success w-100">
                                <i class="fas fa-check-circle me-1"></i> Mark as Resolved
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection