<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\Store;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSupportController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::with('store')
            ->orderByDesc('created_at')
            ->paginate(15);
            
        $openCount = SupportTicket::whereIn('status', ['open', 'in_progress'])->count();
        $urgentCount = SupportTicket::where('priority', 'urgent')->whereNotIn('status', ['resolved', 'closed'])->count();
        
        return view('admin.support.index', compact('tickets', 'openCount', 'urgentCount'));
    }
    
    public function show($id)
    {
        $ticket = SupportTicket::with(['store', 'user', 'messages.user'])->findOrFail($id);
        $messages = $ticket->messages()->orderBy('created_at')->get();
        
        return view('admin.support.show', compact('ticket', 'messages'));
    }
    
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'status' => 'required|in:open,in_progress,waiting,resolved,closed',
            'attachments.*' => 'nullable|file|max:10240'
        ]);
        
        $ticket = SupportTicket::findOrFail($id);
        $ticket->update(['status' => $request->status]);
        
        // Create message with valid admin user ID from main database
        $message = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),  // Use the admin's ID
            'tenant_user_id' => null, // No tenant user for admin messages
            'message' => $request->message,
            'is_admin' => true
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
        
        // Send notification to tenant
        // Notification::send($ticket->user, new AdminTicketReply($ticket, $message));
        
        return redirect()->route('admin.support.show', $ticket->id)
            ->with('success', 'Reply sent successfully');
    }
}