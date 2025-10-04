<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Workspace;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\DeletionService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class NotificationsController extends Controller
{
    protected $workspace;
    protected $user;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // fetch session and use it in entire class with constructor
             $workspaceId = getWorkspaceId();
        $this->workspace = Workspace::find($workspaceId);
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $types = [
            'project',
            'task',
            'workspace',
            'meeting',
            'leave_request',
            'project_comment_mention',
            'task_comment_mention',
            'announcement',
            'project_issue',
            'task_reminder',
            'recurring_task',

            // Add more types as needed
        ];
        $notifications_count = $this->user->notifications()->count();
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;

        return view('notifications.list', ['notifications_count' => $notifications_count, 'users' => $users, 'clients' => $clients, 'types' => $types]);
    }

    public function mark_all_as_read()
    {
        $notifications = $this->user->notifications()->get();

        foreach ($notifications as $notification) {
            $this->user->notifications()->updateExistingPivot($notification->id, ['read_at' => now()]);
        }
        Session::flash('message', 'All notifications marked as read.');
        return response()->json(['error' => false]);
    }



    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $status = (request('status')) ? request('status') : "";
        $type = (request('type')) ? request('type') : "";
        $user_id = (request('user_id')) ? request('user_id') : "";
        $client_id = (request('client_id')) ? request('client_id') : "";
        if ($user_id && isAdminOrHasAllDataAccess()) {
            $user = User::findOrFail($user_id);
            $notifications = $user->notifications();
        } elseif ($client_id && isAdminOrHasAllDataAccess()) {
            $client = Client::findOrFail($client_id);
            $notifications = $client->notifications();
        } else {
            $notifications = isAdminOrHasAllDataAccess() ? $this->workspace->notifications() : $this->user->notifications();
        }
        if ($search) {
            $notifications = $notifications->where(function ($query) use ($search) {
                $query->where('id', 'like', '%' . $search . '%')
                    ->orWhere('title', 'like', '%' . $search . '%')
                    ->orWhere('message', 'like', '%' . $search . '%');
            });
        }

        // Check if the logged-in user is a user or a client
        if (isClient()) {
            $pivotTable = 'client_notifications';
        } else {
            $pivotTable = 'notification_user';
        }

        if ($status === "read") {
            $notifications = $notifications->where(function ($query) use ($pivotTable) {
                $query->whereNotNull("{$pivotTable}.read_at");
            });
        } elseif ($status === "unread") {
            $notifications = $notifications->where(function ($query) use ($pivotTable) {
                $query->whereNull("{$pivotTable}.read_at");
            });
        }

        if ($type) {
            $notifications = $notifications->where('type', $type);
        }

        $total = $notifications->count();


        $notifications = $notifications->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($notification) {
                // Construct the base URL based on the notification type
                $baseUrl = '';
            // dd($notification);
                if ($notification->type == 'project') {
                $baseUrl = '/master-panel/projects/information/' . $notification->type_id;
                } else if ($notification->type == 'task') {
                $baseUrl = '/master-panel/tasks/information/' . $notification->type_id;
            } else if ($notification->type == 'workspace') {
                $baseUrl = '/master-panel/workspaces/';
            } else if ($notification->type == 'meeting') {
                $baseUrl = '/master-panel/meetings';
            } else if ($notification->type == 'leave_request') {
                $baseUrl = '/master-panel/leave-requests';
            } else if ($notification->type == 'project_comment_mention') {
                $baseUrl = '/master-panel/projects/information/' . $notification->type_id;
            } else if ($notification->type == 'task_comment_mention') {
                $baseUrl = '/master-panel/tasks/information/' . $notification->type_id;
            } elseif ($notification->type == 'announcement') {
                $baseUrl = '/master-panel/announcements';
            } elseif ($notification->type == 'project_issue') {
                $baseUrl = '/master-panel/projects';
            } elseif ($notification->type == 'task_reminder' || $notification->type == 'recurring_task') {
                $baseUrl = '/master-panel/tasks/information/' . $notification->type_id;
            }
                $readAt = isset($notification->pivot->read_at) ? $notification->pivot->read_at : $notification->read_at;
                $markAsAction = is_null($readAt) ? get_label('mark_as_read', 'Mark as read') : get_label('mark_as_unread', 'Mark as unread');
                $iconClass = is_null($readAt) ? 'bx bx-check text-secondary mx-1' : 'bx bx-check-double text-success mx-1';

                // Check if the notification is assigned to the currently logged-in user or client
                $isAssignedToCurrentUser = $notification->users->contains('id', $this->user->id) || $notification->clients->contains('id', $this->user->id);

                // Construct the HTML for the mark as read/unread action only if the notification is assigned to the current user
                if ($isAssignedToCurrentUser) {
                    $actionsHtml = '<a href="javascript:void(0)" data-id="' . $notification->id . '" data-needconfirm="true" title="' . $markAsAction . '" class="card-link update-notification-status"><i class="' . $iconClass . '"></i></a>';
                } else {
                    // If the notification is not assigned to the current user, do not display mark as read/unread option
                    $actionsHtml = '';
                }

                $statusBadge = is_null($readAt) ? '<span class="badge bg-danger">' . get_label('unread', 'Unread') . '</span>' : '<span class="badge bg-success">' . get_label('read', 'Read') . '</span>';

                // Append view and delete options
                $actionsHtml .= '<a href="' . $baseUrl . '" title="' . get_label('view', 'View') . '" class="card-link update-notification-status" data-id="' . $notification->id . '"><i class="bx bx-info-circle mx-1"></i></a>' .
                    '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $notification->id . '" data-type="notifications">' .
                    '<i class="bx bx-trash text-danger mx-1"></i>' .
                    '</button>';

                return [
                    'id' => $notification->id,
                    'title' => $notification->title . '<br><span class="text-muted">' . $notification->created_at->diffForHumans() . ' (' . format_date($notification->created_at, true) . ')' . '</span>',
                    'users' => $notification->users,
                    'clients' => $notification->clients,

                    'type_id' => $notification->type_id,
                    'message' => $notification->message,
                    'status' => $statusBadge,
                'type' => ucwords(str_replace('_', ' ', $notification->type)),
                    'read_at' => format_date($readAt, true),
                    'created_at' => format_date($notification->created_at, true),
                    'updated_at' => format_date($notification->updated_at, true),
                    'actions' => $actionsHtml,
                ];
            });

        foreach ($notifications->items() as $notification => $collection) {
            foreach ($collection['clients'] as $i => $client) {
                $collection['clients'][$i] = "<a href='/clients/profile/" . $client->id . "' target='_blank'><li class='avatar avatar-sm pull-up'  title='" . $client['first_name'] . " " . $client['last_name'] . "'>
                    <img src='" . ($client['photo'] ? asset('storage/' . $client['photo']) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' />
                    </li></a>";
            };
        }

        foreach ($notifications->items() as $notification => $collection) {
            foreach ($collection['users'] as $i => $user) {
                $collection['users'][$i] = "<a href='/users/profile/" . $user->id . "' target='_blank'><li class='avatar avatar-sm pull-up'  title='" . $user['first_name'] . " " . $user['last_name'] . "'>
                    <img src='" . ($user['photo'] ? asset('storage/' . $user['photo']) : asset('storage/photos/no-image.jpg')) . "' class='rounded-circle' />
                    </li></a>";
            };
        }

        return response()->json([
            "rows" => $notifications->items(),
            "total" => $total,
        ]);
    }

    /**
     * List notifications (API)
     *
     * @group Notifications
     *
     * List notifications for the authenticated user or specified user/client.
     *
     * @queryParam search string Optional search term. Example: project
     * @queryParam sort string Field to sort by. Default: id. Example: id
     * @queryParam order string Sort order (ASC|DESC). Default: DESC. Example: DESC
     * @queryParam status string Filter by read/unread. Example: unread
     * @queryParam type string Filter by notification type. Example: project
     * @queryParam user_id integer Filter by user ID (admin only). Example: 1
     * @queryParam client_id integer Filter by client ID (admin only). Example: 2
     * @queryParam limit integer Results per page. Example: 10
     *
     * @response 200 {
     *   "rows": [
     *     {
     *       "id": 1,
     *       "title": "Title",
     *       "users": [],
     *       "clients": [],
     *       "type_id": 1,
     *       "message": "Message text",
     *       "type": "Project",
     *       "read_at": "2024-06-27 10:00:00",
     *       "created_at": "2024-06-27 10:00:00",
     *       "updated_at": "2024-06-27 10:00:00"
     *     }
     *   ],
     *   "total": 1
     * }
     */
    public function apiList(Request $request, $id = '')
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $status = $request->input('status', '');
        $user_id = $request->input('user_id', '');
        $client_id = $request->input('client_id', '');
        $type = $request->input('type', '');
        $notificationType = $request->input('notification_type', '');
        $limit = $request->input('limit', 10); // default limit
        $offset = $request->input('offset', 0); // default offset

        if ($id) {
            $notification = Notification::find($id);
            if (!$notification) {
                return formatApiResponse(
                    false,
                    'Notification not found',
                    [
                        'total' => 0,
                        'data' => []
                    ]
                );
            } else {
                return formatApiResponse(
                    false,
                    'Notification retrieved successfully',
                    [
                        'total' => 1,
                        'data' => [formatNotification($notification)]
                    ]
                );
            }
        } else {
            $pivotTable = getGuardName() == 'client' ? 'client_notifications' : 'notification_user';
            if ($user_id && isAdminOrHasAllDataAccess()) {
                $pivotTable = 'notification_user';
                $user = User::find($user_id);
                if (!$user) {
                    return formatApiResponse(
                        false,
                        'User not found',
                        [
                            'total' => 0,
                            'data' => []
                        ]
                    );
                }
                $notificationsQuery = $user->notifications();
            } elseif ($client_id && isAdminOrHasAllDataAccess()) {
                $pivotTable = 'client_notifications';
                $client = Client::find($client_id);
                if (!$client) {
                    return formatApiResponse(
                        false,
                        'Client not found',
                        [
                            'total' => 0,
                            'data' => []
                        ]
                    );
                }
                $notificationsQuery = $client->notifications();
            } elseif (isAdminOrHasAllDataAccess() && $this->workspace && $this->workspace instanceof \App\Models\Workspace) {
                $notificationsQuery = $this->workspace->notifications();
            } elseif ($this->user && $this->user instanceof \App\Models\User) {
                $notificationsQuery = $this->user->notifications();
            } else {
                // If no valid user/workspace, return empty
                return formatApiResponse(
                    false,
                    'No notifications found',
                    [
                        'total' => 0,
                        'data' => []
                    ]
                );
            }

            if ($search) {
                $notificationsQuery->where(function ($query) use ($search) {
                    $query->where('id', 'like', '%' . $search . '%')
                        ->orWhere('title', 'like', '%' . $search . '%')
                        ->orWhere('message', 'like', '%' . $search . '%');
                });
            }

            if ($status === "read") {
                $notificationsQuery->where(function ($query) use ($pivotTable) {
                    $query->whereNotNull("{$pivotTable}.read_at");
                });
            } elseif ($status === "unread") {
                $notificationsQuery->where(function ($query) use ($pivotTable) {
                    $query->whereNull("{$pivotTable}.read_at");
                });
            }

            if ($notificationType) {
                if ($notificationType === 'system') {
                    $notificationsQuery->where("{$pivotTable}.is_system", 1);
                } elseif ($notificationType === 'push') {
                    $notificationsQuery->where("{$pivotTable}.is_push", 1);
                }
            }
            if (!empty($type)) {
                $notificationsQuery->where('type', $type);
            }
            if ($sort === "status") {
                $notificationsQuery->orderBy(function ($query) use ($pivotTable) {
                    return $query->selectRaw("CASE WHEN {$pivotTable}.read_at IS NULL THEN 0 ELSE 1 END");
                }, $order);
            } else {
                $notificationsQuery->orderBy($sort, $order);
            }

            $total = $notificationsQuery->count();

            $notifications = $notificationsQuery->skip($offset)
                ->take($limit)
                ->get();

            if ($notifications->isEmpty()) {
                return formatApiResponse(
                    false,
                    'Notifications not found',
                    [
                        'total' => 0,
                        'data' => []
                    ]
                );
            }

            $data = $notifications->map(function ($notification) {
                // Define formatNotification function to format notification data
                return formatNotification($notification);
            });

            return formatApiResponse(
                false,
                'Notifications retrieved successfully',
                [
                    'total' => $total,
                    'data' => $data
                ]
            );
        }
    }
       /**
     * Remove the specified notification.
     *
     * This endpoint deletes a notification based on the provided ID. The user must be authenticated to perform this action.
     *
     * @authenticated
     *
     * @group Notifications
     *
     * @urlParam id int required The ID of the notification to be deleted. Example: 1
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Notification deleted successfully.",
     *   "data": []
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "Notification not found.",
     *   "data": []
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while deleting the notification."
     * }
     */

    public function destroy($id)
    {
        try {
            // Find the notification
            $notification = Notification::find($id);
            if ($notification) {
                // Detach the notification from all users
                $notification->users()->detach();

                // Detach the notification from all clients
                $notification->clients()->detach();

                // If the notification is no longer associated with any users or clients, delete it
                if ($notification->users()->count() === 0 && $notification->clients()->count() === 0) {
                    $notification->delete();
                }

                return formatApiResponse(
                    false,
                    'Notification deleted successfully.',
                    [
                        'id' => $notification->id,
                        'data' => []
                    ]
                );
            } else {
                return formatApiResponse(
                    true,
                    'Notification not found.',
                    []
                );
            }
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'An error occurred while deleting the notification.'], 500);
        }
    }



    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:notifications,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $notification = Notification::findOrFail($id);
            $this->user->notifications()->detach($notification);

            // Check if the notification is still associated with any users or clients
            if ($notification->users()->count() === 0 && $notification->clients()->count() === 0) {
                // If not associated with any users or clients, delete the notification
                $notification->delete();
            }
        }

        return response()->json(['error' => false, 'message' => 'Notification(s) deleted successfully.']);
    }

    public function update_status(Request $request)
    {
        $notificationId = $request->input('id');
        $needConfirm = $request->input('needConfirm') || false;
        // Find the notification
        $notification =  $this->user->notifications()->findOrFail($notificationId);
        $readAt = isset($notification->pivot->read_at) ? $notification->pivot->read_at : $notification->read_at;
        if ($needConfirm) {
            // Toggle the status
            if (is_null($readAt)) {
                // If the notification is currently unread, mark it as read
                $this->user->notifications()->updateExistingPivot($notification->id, ['read_at' => now()]);
                $message = 'Notification marked as read successfully';
            } else {
                // If the notification is currently read, mark it as unread
                $this->user->notifications()->updateExistingPivot($notification->id, ['read_at' => null]);
                $message = 'Notification marked as unread successfully';
            }

            // Return a response indicating success
            return response()->json(['error' => false, 'message' => $message]);
        } else {
            if (is_null($readAt)) {
                $this->user->notifications()->updateExistingPivot($notification->id, ['read_at' => now()]);
            }
        }
    }

    public function getUnreadNotifications()
    {
        $unreadNotificationsCount = $this->user->notifications->where('pivot.read_at', null)->count();
        $unreadNotifications = $this->user->notifications()
        
            ->wherePivot('read_at', null)
            ->getQuery()
            ->orderBy('id', 'desc')
            ->take(3)
            ->get();
        $unreadNotificationsHtml = view('partials.unread_notifications')
            ->with('unreadNotificationsCount', $unreadNotificationsCount)
            // dd($unreadNotifications);
            ->with('unreadNotifications', $unreadNotifications)
            ->render();

        // Return JSON response with count and HTML
        return response()->json([
            'count' => $unreadNotificationsCount,
            // dd($unreadNotificationsHtml),
            'html' => $unreadNotificationsHtml
        ]);
    }

    /**
     * Mark all notifications as read (API)
     *
     * Marks all notifications for the authenticated user as read and returns the updated notifications using the formatNotification helper.
     *
     * @group Notifications
     *
     * @authenticated
     *
     * @response 200 {
     *   "error": false,
     *   "message": "All notifications marked as read.",
     *   "data": [
     *     { "id": 1, "title": "...", ...notification fields... }
     *   ]
     * }
     *
     * @response 401 {
     *   "error": true,
     *   "message": "Unauthenticated."
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while marking notifications as read."
     * }
     */
    public function markAllAsReadApi(Request $request)
    {
        try {
            $notifications = $this->user->notifications()->get();
            $updated = [];
            foreach ($notifications as $notification) {
                $this->user->notifications()->updateExistingPivot($notification->id, ['read_at' => now()]);
                $notification->pivot->read_at = now(); // update in-memory for formatting
                $updated[] = formatNotification($notification);
            }
            return response()->json([
                'error' => false,
                'message' => 'All notifications marked as read.',
                'data' => $updated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while marking notifications as read.'
            ], 500);
        }
    }
    
}
