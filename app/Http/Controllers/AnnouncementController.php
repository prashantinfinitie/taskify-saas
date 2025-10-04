<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Announcement;
use App\Services\DeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AnnouncementController extends Controller
{
    protected $workspace;
    protected $user;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // fetch session and use it in entire class with constructor
            $this->workspace = Workspace::find(session()->get('workspace_id'));
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $announcements = Announcement::where('workspace_id', session()->get('workspace_id'))->get();
        return view('announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Merge the request data for 'all_workspace_users'
            $request->merge([
                'all_workspace_users' => $request->has('all_workspace_users') ? true : false,
            ]);

            // Validate the request data
            $validated = $request->validate([
                'title' => 'required|max:255',
                'content' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'priority' => 'in:low,medium,high',
                'all_workspace_users' => 'nullable|boolean',
                'selected_users' => 'array',
            ]);

            // Convert the date fields to the correct format using Carbon
            $start_date = Carbon::createFromFormat('d-m-Y', $validated['start_date'])->format('Y-m-d');
            $end_date = isset($validated['end_date']) ? Carbon::createFromFormat('d-m-Y', $validated['end_date'])->format('Y-m-d') : null;
            // Get the workspace from the session
            $workspace = Workspace::findOrFail(session()->get('workspace_id'));

            // Create the announcement
            $announcement = Announcement::create([
                'workspace_id' => $workspace->id,
                'created_by' => auth()->id(),
                'title' => $validated['title'],
                'content' => $validated['content'],
                'priority' => $validated['priority'] ?? 'low',
                'all_workspace_users' => $validated['all_workspace_users'] ?? false,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]);

            // Determine the recipients based on the 'all_workspace_users' flag
            if ($validated['all_workspace_users']) {
                // If broadcast, select all workspace users
                $recipients = $workspace->users;
            } else {
                // Otherwise, use selected users
                $recipients = User::whereIn('id', $validated['selected_users'] ?? [])->get();
            }

            // Attach recipients to the announcement
            $announcement->recipients()->attach(
                $recipients->pluck('id')->mapWithKeys(fn($id) => [
                    $id => ['is_read' => false]
                ])
            );

            // Send notifications if required (additional code for notifications can be added here)
            $notificationData = [
                'type' => 'announcement',
                'type_id' => $announcement->id,
                'type_title' => $announcement->title,

                'creator_first_name' => ucwords($announcement->creator->first_name),
                'creator_last_name' => ucwords($announcement->creator->last_name),
                'access_url' => 'announcements',
                'action' => 'assigned'
            ];
            $recipientsids = $recipients->pluck('id')->toArray();
            $recipients = array_merge(
                array_map(function ($recipientsids) {
                    return 'u_' . $recipientsids;
                }, $recipientsids)
            );

            processNotifications($notificationData, $recipients);

            // Return a success response
            return response()->json([
                'error' => false,
                'message' => 'Announcement created successfully',
                'announcement' => $announcement
            ]);
        } catch (\Exception $e) {
            dd($e);
            // Catch any exception and return a failure response with the error message
            return response()->json([
                'error' => true,
                'message' => 'Failed to create announcement: ' . $e->getMessage()
            ], 500); // Internal Server Error
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->selected_users = $announcement->recipients->pluck('id')->toArray();
        return response()->json(['error' => false, 'announcement' => $announcement]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        // Merge boolean flag for `all_workspace_users`
        $request->merge([
            'all_workspace_users' => $request->has('all_workspace_users') ? true : false,
        ]);

        // Validate the incoming request
        $validatedData = $request->validate([
            'id' => 'required|exists:announcements,id',
            'title' => 'required|max:255',
            'content' => 'required',
            'start_date' => 'required|date_format:d-m-Y',
            'end_date' => 'nullable|date_format:d-m-Y|after_or_equal:start_date',
            'priority' => 'in:low,medium,high',
            'all_workspace_users' => 'boolean',
            'selected_users' => 'nullable|array',
            'selected_users.*' => 'exists:users,id',
        ]);
        try {
            // Retrieve the announcement to update
            $announcement = Announcement::findOrFail($validatedData['id']);

            // Format dates to `Y-m-d` format
            $start_date = Carbon::createFromFormat('d-m-Y', $validatedData['start_date'])->format('Y-m-d');
            $end_date = isset($validatedData['end_date']) ? Carbon::createFromFormat('d-m-Y', $validatedData['end_date'])->format('Y-m-d') : null;

            // Update announcement details
            $announcement->update([
                'title' => $validatedData['title'],
                'content' => $validatedData['content'],
                'priority' => $validatedData['priority'] ?? 'low',
                'all_workspace_users' => $validatedData['all_workspace_users'],
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]);

            // Determine recipients
            if ($validatedData['all_workspace_users']) {
                // Select all workspace users if `all_workspace_users` is true
                $recipients = $announcement->workspace->users;
            } else {
                // Otherwise, use the selected users from the request
                $recipients = User::whereIn('id', $validatedData['selected_users'] ?? [])->get();
            }

            // Sync recipients with `is_read` flag set to false
            $announcement->recipients()->sync(
                $recipients->pluck('id')->mapWithKeys(fn($id) => [
                    $id => ['is_read' => false]
                ])
            );

            // Return a JSON response
            return response()->json([
                'error' => false,
                'message' => 'Announcement updated successfully',
                'announcement' => $announcement
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'error' => true,
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Handle general exceptions
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while updating the announcement.',
                'exception' => $e->getMessage(), // Optional: Remove in production
            ], 500);
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = DeletionService::delete(Announcement::class, $id, 'Announcement');
        return $response;
    }

    public function getEvents(Request $request)
    {
        // dd($request->all());
        // Ensure start and end dates are in proper format
        $start = Carbon::parse($request->startDate);
        $end = Carbon::parse($request->endDate);

        $query = Announcement::where('workspace_id', session()->get('workspace_id'))
            ->where(function ($q) use ($start, $end) {
                // Find announcements that fall within the date range or overlap
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end]);
            });

        // Get the announcements within the specified range
        $announcements = $query->get();

        // Return the events in the FullCalendar format

        $events = $announcements->map(function ($announcement) {
            return $announcement->toFullCalendarEvent();
        });

        return response()->json(data: $events);
    }

    /**
     * Mark all announcements as read.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function mark_all_as_read()
    {
        $announcements = $this->user->announcements()->get();

        foreach ($announcements as $announcement) {
            $this->user->announcements()->updateExistingPivot($announcement->id, ['read_at' => now(), 'is_read' => true]);
        }
        Session::flash('message', 'All notifications marked as read.');
        return response()->json(['error' => false]);
    }
    /**
     * Update the status of an announcement.
     *
     * This function is responsible for updating the status of a specific announcement,
     * marking it as read or performing any other necessary status change.
     *
     * @return void
     */

    public function update_status(Request $request)
    {

        try { // Mark all announcements as read
            $announcementId = $request->input('id');
            $needConfirm = $request->input('needConfirm') || false;
            // Find the notification
            $announcement =  $this->user->announcements()->findOrFail($announcementId);
            $readAt = isset($announcement->pivot->read_at) ? $announcement->pivot->read_at : $announcement->read_at;
            if ($needConfirm) {
                // Toggle the status
                if (is_null($readAt)) {
                    // If the notification is currently unread, mark it as read
                    $this->user->announcements()->updateExistingPivot($announcement->id, ['read_at' => now()]);
                    $message = 'Announcement marked as read successfully';
                } else {
                    // If the notification is currently read, mark it as unread
                    $this->user->announcements()->updateExistingPivot($announcement->id, ['read_at' => null]);
                    $message = 'Announcement marked as unread successfully';
                }

                // Return a response indicating success
                return response()->json(['error' => false, 'message' => $message]);
            } else {
                if (is_null($readAt)) {
                    $this->user->announcements()->updateExistingPivot($announcement->id, ['is_read' => true, 'read_at' => now()]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to update status: ' . $e);
            return response()->json(['error' => true, 'message' => 'Failed to update status: ' . $e->getMessage()], 500);
        }
    }
    /*************  ✨ Codeium Command ⭐  *************/
    /**
     * Return the count and HTML of unread announcements.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /******  6ade1fe1-da92-4185-a724-844d57e53926  *******/
    public function getUnreadAnnouncements()
    {
        // Fetch unread announcements count
        $unreadAnnouncementsCount = $this->user
            ->announcements()
            ->whereNull('announcement_user.read_at')
            ->where('announcement_user.is_read', false)
            ->count();

        // Fetch unread announcements with ordering
        $unreadAnnouncements = $this->user
            ->announcements()
            ->whereNull('announcement_user.read_at')
            ->where('announcement_user.is_read', false)
            ->orderBy('id', 'desc')
            ->take(3)
            ->get();

        // Render the HTML view with data
        $unreadAnnouncementsHtml = view('partials.unread_announcements')
            ->with('unreadAnnouncementsCount', $unreadAnnouncementsCount)
            ->with('unreadAnnouncements', $unreadAnnouncements)
            ->render();

        // Return JSON response
        return response()->json([
            'count' => $unreadAnnouncementsCount,
            'html' => $unreadAnnouncementsHtml,
        ]);
    }

}
