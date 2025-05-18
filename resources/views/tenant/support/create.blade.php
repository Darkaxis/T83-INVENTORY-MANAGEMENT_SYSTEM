<!-- filepath: resources/views/tenant/support/create.blade.php -->
@extends('layouts.app')

@section('title', 'Create Support Ticket')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">Create Support Ticket</h1>
            <p class="mb-4">Submit a new support request to our team</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('tenant.support.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="card mb-4">
                    <div class="card-header p-3">
                        <h5 class="mb-0">Ticket Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="input-group input-group-outline my-3">
                                    <label class="form-label">Subject</label>
                                    <input type="text" class="form-control" name="subject" value="{{ old('subject') }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Category</label>
                                    <select class="form-control" name="category" required>
                                        <option value="" disabled selected>Select Category</option>
                                        <option value="technical" {{ old('category') == 'technical' ? 'selected' : '' }}>Technical Issue</option>
                                        <option value="billing" {{ old('category') == 'billing' ? 'selected' : '' }}>Billing Question</option>
                                        <option value="feature" {{ old('category') == 'feature' ? 'selected' : '' }}>Feature Request</option>
                                        <option value="account" {{ old('category') == 'account' ? 'selected' : '' }}>Account Related</option>
                                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Priority</label>
                                    <select class="form-control" name="priority" required>
                                        <option value="" disabled selected>Select Priority</option>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Message</label>
                                    <textarea class="form-control" name="message" rows="5" required>{{ old('message') }}</textarea>
                                    <small class="text-muted">Please provide as much detail as possible to help us assist you better.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Attachments (Optional)</label>
                                    <input type="file" class="form-control" name="attachments[]" multiple>
                                    <small class="text-muted">You can upload multiple files. Max size: 10MB per file.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12 text-end">
                        <a href="{{ route('tenant.support.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        
                        
                        <button type="submit" class="btn bg-gradient-primary">
                            
                            <i class="fas fa-paper-plane me-2"></i> Submit Ticket
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header p-3">
                    <h5 class="mb-0">Need Help?</h5>
                </div>
                <div class="card-body">
                    <p>Before submitting a ticket, check if your question is already answered in our resources:</p>
                    <ul>
                        <li><a href="#">Knowledge Base</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Video Tutorials</a></li>
                    </ul>
                    <hr>
                    <p>For urgent issues, contact our support team at:</p>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-phone me-2"></i>
                        <span>+123</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection