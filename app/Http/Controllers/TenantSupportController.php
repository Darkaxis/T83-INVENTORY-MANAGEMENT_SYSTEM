<?php
// app/Http/Controllers/TenantSupportController.php
namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantSupportController extends Controller
{
    public function index()
    {
        $store = request()->store;
        $tickets = SupportTicket::where('store_id', $store->id)
            ->orderByDesc('created_at')
            ->paginate(10);
            
        return view('tenant.support.index', compact('tickets', 'store'));
    }
    
    public function create()
    {
        $store = request()->store;
        return view('tenant.support.create', compact('store'));
    }
    
    public function store(Request $request)
    {
        // Debug logging
        Log::info('Support ticket submission received', [
            'all_request' => $request->all(),
            'session_user' => session('tenant_user_id')
        ]);
        
        try {
            $validatedData = $request->validate([
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'category' => 'required|string',
                'priority' => 'required|in:low,medium,high,urgent',
                'attachments.*' => 'nullable|file|max:10240'
            ]);
            
            Log::info('Validation passed', $validatedData);
            
            $store = request()->store;
            
            // Get tenant user ID with fallback options
            $tenantUserId = session('tenant_user_id'); // Fallback to ID 1 for testing
            
            Log::info("Using tenant user ID: $tenantUserId for store: {$store->id}");
            
            // Create ticket without validation temporarily
            $ticket = SupportTicket::create([
                'store_id' => $store->id,
                'tenant_user_id' => $tenantUserId,
                'subject' => $request->subject,
                'category' => $request->category,
                'priority' => $request->priority,
                'status' => 'open'
            ]);
            
            Log::info('Ticket created', ['ticket_id' => $ticket->id]);
            
            // Continue with message creation...
            
            return redirect()
                ->route('tenant.support.index')
                ->with('success', 'Support ticket created successfully');
        }
        catch (\Exception $e) {
            Log::error('Error creating support ticket: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Error creating ticket: ' . $e->getMessage());
        }
    }
    
    public function show($id)
    {
        $store = request()->store;
        $ticket = SupportTicket::where('store_id', $store->id)
            ->where('id', $id)
            ->firstOrFail();
            
        $messages = $ticket->messages()->orderBy('created_at')->get();
        
        return view('tenant.support.show', compact('ticket', 'messages', 'store'));
    }
    
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240'
        ]);
        
        $store = request()->store;
        $ticket = SupportTicket::where('store_id', $store->id)
            ->where('id', $id)
            ->firstOrFail();
            
        // Get tenant user ID - this is from the tenant context
        $tenantUserId = session('tenant_user_id');
        
        // If ticket was closed, reopen it
        if ($ticket->status == 'closed' || $ticket->status == 'resolved') {
            $ticket->update(['status' => 'waiting']);
        }
        
        $message = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'tenant_user_id' => $tenantUserId,
            'message' => $request->message,
            'is_admin' => false
        ]);
        
        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments', 'public');
                
                TicketAttachment::create([
                    'message_id' => $message->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getMimeType()
                ]);
            }
        }
        
        // Notify admin about new reply
        // Notification::send(Admin::all(), new NewTicketReply($ticket, $message));
        
        return redirect()->route('tenant.support.show', $ticket->id)
            ->with('success', 'Reply added successfully');
    }
}