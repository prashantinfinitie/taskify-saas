<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Admin;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Notifications\TicketCreatedForAdmin;
use App\Notifications\TicketReplyNotification;
use Illuminate\Validation\ValidationException;
use App\Notifications\TicketDeleteNotification;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketCreatedForSuperadmin;
use App\Notifications\TicketStatusUpdateNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Mailer\Exception\TransportException;

class SupportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check if the user has the 'superadmin' role
        if (auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('manager')) {
            // Fetch tickets or data specific to superadmins
            $tickets = Ticket::with('admin', 'replies')->get(); // Example: superadmins see all tickets
        }
        // Check if the user has the 'admin' role
        if (auth()->user()->hasRole('admin')) {
            // Fetch tickets or data specific to this admin
            $tickets = Ticket::where('admin_id', getAdminIdByUserRole())->with('replies')->get(); // Example: admins see their own tickets
        }
        return view('support.index', compact('tickets'));
        // Fallback for unauthorized users (optional)
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ticket_priorities = TicketPriority::all();
        return view('support.create', ['ticket_priorities' => $ticket_priorities]);
        //
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority_id' => 'required|exists:ticket_priorities,id',
            'media.*' => 'mimes:jpeg,png,jpg,gif,svg,pdf,doc,docx|max:2048',
        ]);
        try {
            // Check if email settings are configured
            if (!isEmailConfigured()) {
                return response()->json(['error' => true, 'message' => 'Email settings not configured.'], 422);
            }
            // Create the ticket
            $ticket = Ticket::create([
                'admin_id' => getAdminIdByUserRole(),
                'title' => $request->title,
                'description' => $request->description,
                'priority_id' => $request->priority_id,
            ]);
            // Handle file uploads
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $mediaPath = $file->store('ticket_media', 'public');
                    $ticket->media()->create(['media_path' => $mediaPath]);
                }
            }
            // Find superadmin and admin users
            $superAdmin = Role::where('name', 'superadmin')->first()->users()->first();
            $admin = Admin::findOrFail($ticket->admin_id)->user;
            // Try sending notifications
            try {
                // Notify the admin
                $admin->notify(new TicketCreatedForAdmin($ticket));
                // Notify the superadmin
                $superAdmin->notify(new TicketCreatedForSuperadmin($ticket, $admin));
            } catch (\Exception $e) {
                // Log email notification errors
                Log::error("Error sending ticket notification: " . $e->getMessage());
                return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
            }
            return response()->json(['message' => 'Ticket created successfully'], 200);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            Log::error("Error creating ticket: " . $e->getMessage());
            // Return a meaningful error message
            return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Fetch the ticket with its priority and replies
        $ticket = Ticket::with('priority', 'replies')->findOrFail($id);

        // Find the admin who created the ticket
        $createdBy = Admin::findOrFail($ticket->admin_id)->user;

        // Fetch the active subscription details for the ticket creator
        $subscriptionDetails = Subscription::where(['user_id' => $createdBy->id, 'status' => 'active'])->first();

        // Check if the current user is allowed to view the ticket
        if (auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('manager') || auth()->user()->id === $ticket->admin->user_id) {
            // If the user is authorized, return the view
            return view('support.view', compact('ticket', 'createdBy', 'subscriptionDetails'));
        }

        // If the user is not authorized, return a 403 Unauthorized response
        abort(403, 'Unauthorized access.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Check if the user has the 'superadmin' role
        if (!auth()->user()->hasRole('superadmin')) {
            return response()->json(['error' => true, 'message' => 'You do not have permission to delete this ticket.'], 200);
        }
        //Check if email is configured
        if (!isEmailConfigured()) {
            return response()->json(['error' => true, 'message' => 'Email settings are not configured.'], 200);
        }
        $ticket = Ticket::findOrFail($id);
        $ticketDetails = [
            'id' => $ticket->id,
            'title' => $ticket->title,
            'admin' => $ticket->admin->user->first_name . ' ' . $ticket->admin->user->last_name,
            'priority' => $ticket->priority->name,
            'description' => $ticket->description,
            'status' => $ticket->status,
            'user_id' => $ticket->admin->user_id,

        ];
        $ticket->admin->user->notify(new TicketDeleteNotification($ticketDetails));
        $ticket->delete();
        return response()->json(['error' => false,  'message' => 'Ticket deleted successfully'], 200);
    }
    public function storeReply(Request $request, $ticket_id)
    {
        try {
            // Validate email configuration
            if (!isEmailConfigured()) {
                return response()->json(['error' => true, 'message' => 'Email settings are not configured.'], 200);
            }

            // Validate the request
            $validated = $request->validate([
                'message' => 'required|string',
            ]);

            // Retrieve the ticket
            $ticket = Ticket::findOrFail($ticket_id);

            // Get the sender details
            $sender = auth()->user();
            $sendRole = $sender->roles->first()->name;

            // Create the reply
            $reply = new TicketReply();
            $reply->ticket_id = $ticket_id;
            $reply->sender_id = $sender->id;
            $reply->sender_role = $sendRole;
            $reply->message = $validated['message'];
            $reply->save();

            $senderName = ucfirst($sender->first_name . ' ' . $sender->last_name);

            // Determine the recipient based on the sender's role
            $recipient = $this->getRecipient($reply->sender_role, $reply->ticket->admin_id);

            // Send notification if the recipient exists
            if ($recipient) {
                $recipient->notify(new TicketReplyNotification($ticket, $reply, $senderName));
            }

            return response()->json(['error' => false, 'message' => 'Reply added successfully'], 200);
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => true, 'message' => 'Validation failed.', 'details' => $e->errors()], 422);
        } catch (ModelNotFoundException $e) {
            // Handle missing ticket
            return response()->json(['error' => true, 'message' => 'Ticket not found.'], 404);
        } catch (TransportException $e) {
            // Handle email configuration or transport issues
            return response()->json(['error' => true, 'message' => 'Failed to send email: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            // Handle unexpected errors
            return response()->json(['error' => true, 'message' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }

    protected function getRecipient($senderRole, $adminId)
    {
        if ($senderRole === 'admin') {
            // If sender is admin, notify superadmin
            return Role::where('name', 'superadmin')->first()->users()->first();
        } elseif ($senderRole === 'superadmin' || $senderRole === 'manager') {
            // If sender is superadmin, notify admin
            return Admin::findOrFail($adminId)->user;
        }
        return null;
    }
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validatedData = $request->validate([
            'status' => 'required|in:open,in_progress,closed',
        ]);

        // Check if the user has the required roles
        if (!auth()->user()->hasRole('superadmin') && !auth()->user()->hasRole('manager')) {
            return redirect()->back()->withErrors([
                'error' => 'Only superadmins and managers can update the status.'
            ]);
        }

        // Prevent reopening a closed ticket
        if ($ticket->status === 'closed') {
            return redirect()->back()->withErrors([
                'error' => 'Closed tickets cannot be reopened or changed.'
            ]);
        }

        // Check if email settings are configured
        if (!isEmailConfigured()) {
            return redirect()->back()->withErrors([
                'error' => 'Email settings are not configured.'
            ]);
        }

        $oldStatus = $ticket->status;
        $newStatus = $validatedData['status'];

        try {
            // Update the ticket status
            $ticket->status = $newStatus;
            $ticket->save();

            // Notify the admin assigned to the ticket
            if ($ticket->admin && $ticket->admin->user) {
                $ticket->admin->user->notify(new TicketStatusUpdateNotification($ticket, $oldStatus, $newStatus, getAuthenticatedUser()));
            }

            return redirect()->back()->with('message', 'Ticket status updated successfully.');
        } catch (\Throwable $exception) {
            // Log the error for debugging
            Log::error('Failed to update ticket status', [
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'error' => $exception->getMessage(),
            ]);

            return redirect()->back()->withErrors([
                'error' => 'An error occurred while updating the ticket status. Please try again later.'
            ]);
        }
    }

    public function list()
    {
        $search = request('search');
        $sort = request('sort') ? request('sort') : "id";
        $order = request('order') ? request('order') : "DESC";
        $limit = request('limit') ? request('limit') : 10;

        // Initialize query with a join on priorities to allow sorting by priority
        $tickets = Ticket::join('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
        ->select('tickets.*') // Select tickets columns, priority is already joined
        ->orderBy($sort === 'priority' ? 'ticket_priorities.name' : 'tickets.' . $sort, $order);

        // Limit tickets to the admin's view if they are not a superadmin
        if (!auth()->user()->hasRole('superadmin') && !auth()->user()->hasRole('manager')) {
            $adminId = getAdminIdByUserRole();
            $tickets->where('admin_id', $adminId);
        }

        // Apply search filters
        if ($search) {
            $tickets->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhereHas('priority', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('admin', function ($q) use ($search) {
                        $q->whereHas('user', function ($q) use ($search) {
                            $q->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%');
                        });
                    });
            });
        }

        // Get total before pagination
        $total = $tickets->count();

        // Apply pagination
        $tickets = $tickets
            ->paginate($limit)
            ->through(function ($ticket) {
                // Format status badges
                $status_formatted = '';
                switch ($ticket->status) {
                    case 'open':
                        $status_formatted = '<span class="badge bg-success">' . $ticket->status . '</span>';
                        break;
                    case 'in_progress':
                        $status_formatted = '<span class="badge bg-warning">' . $ticket->status . '</span>';
                        break;
                    case 'closed':
                        $status_formatted = '<span class="badge bg-danger">' . $ticket->status . '</span>';
                        break;
                    default:
                        $status_formatted = '<span class="badge bg-primary">' . $ticket->status . '</span>';
                        break;
                }

                // Format priority badges
                $priority_formatted = '';
                switch ($ticket->priority->name) {
                    case 'low':
                        $priority_formatted = '<span class="badge bg-success">' . $ticket->priority->name . '</span>';
                        break;
                    case 'high':
                        $priority_formatted = '<span class="badge bg-danger">' . $ticket->priority->name . '</span>';
                        break;
                    case 'medium':
                        $priority_formatted = '<span class="badge bg-warning">' . $ticket->priority->name . '</span>';
                        break;
                    default:
                        $priority_formatted = '<span class="badge bg-primary">' . $ticket->priority->name . '</span>';
                        break;
                }

            // Create actions based on user role
            $actions = '<a href="' . route('support.show', $ticket->id) . '"  title="' . get_label('view', 'View') . '">' .
            '<i class="bx bx-show-alt mx-1"></i></a>';
                if (auth()->user()->hasRole('superadmin')) {
                    $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete-ticket" data-id="' . $ticket->id . '" data-url="' . route('support.destroy', $ticket->id) . '">' .
                '<i class="bx bx-trash text-danger mx-1"></i></button>';
                }

                return [
                    'id' => $ticket->id,
                    'title' => $ticket->title,
                    'status' => $status_formatted,
                    'priority' => $priority_formatted,
                    'description' => $ticket->description,
                    'created_by' => ucfirst($ticket->admin->user->first_name . ' ' . $ticket->admin->user->last_name),
                    'created_at' => format_date($ticket->created_at, true),
                    'updated_at' => format_date($ticket->updated_at, true),
                    'actions' => $actions
                ];
            });

        // Return the tickets and total count
        return response()->json([
            "rows" => $tickets->items(),
            "total" => $total,
        ]);
    }
}
