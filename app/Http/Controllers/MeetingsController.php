<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Client;
use App\Models\Meeting;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\UserClientPreference;
use Illuminate\Validation\ValidationException;

class MeetingsController extends Controller
{
    protected $workspace;
protected $user;

public function __construct()
{
    $this->middleware(function ($request, $next) {
        // Fetch workspace_id from header or session fallback
        $workspaceId = $request->header('workspace-id') ?? session()->get('workspace_id');
        $this->workspace = Workspace::find($workspaceId);

        $this->user = getAuthenticatedUser();
        return $next($request);
    });
}

    public function index()
    {
        $meetings = $this->user->meetings;
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        return view('meetings.meetings', compact('meetings', 'users', 'clients'));
    }

    public function create()
    {
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        $auth_user = $this->user;

        return view('meetings.create_meeting', compact('users', 'clients', 'auth_user'));
    }

    /**
 * Create a new meeting
 *
 * This endpoint creates a new meeting within the current workspace. It validates the meeting date/time, ensures the creator is part of the participant list, and sends notifications to all participants.
 *
 * @group Meeting Managemant
 * @authenticated
 *
 *@header workspace_id 2
 *
 * @bodyParam title string required The title of the meeting. Example: Project Kickoff
 * @bodyParam start_date date required The start date of the meeting (format: Y-m-d). Example: 2025-06-05
 * @bodyParam end_date date required The end date of the meeting (format: Y-m-d). Must be equal to or after start_date. Example: 2025-06-05
 * @bodyParam start_time string required The start time in 24-hour format (HH:mm). Example: 10:00
 * @bodyParam end_time string required The end time in 24-hour format (HH:mm). Must be after start_time if on the same day. Example: 11:30
 * @bodyParam user_ids array Optional array of internal user IDs to be added as participants. Example: [1, 2, 3]
 * @bodyParam client_ids array Optional array of client user IDs to be added as participants. Example: [5, 6]
 *
 * @response 200 scenario="Meeting successfully created" {
 *   "error": false,
 *   "message": "Meeting created successfully.",
 *   "data": {
 *     "id": 12,
 *     "data": {
 *       "id": 12,
 *       "title": "Project Kickoff",
 *       "start_date_time": "2025-06-05 10:00:00",
 *       "end_date_time": "2025-06-05 11:30:00",
 *       "workspace_id": 3,
 *       "admin_id": 1,
 *       ...
 *     }
 *   }
 * }
 *
 * @response 422 scenario="Validation error" {
 *   "error": true,
 *   "message": "Validation failed",
 *   "data": {
 *     "title": ["The title field is required."],
 *     "start_date": ["The start date is required."]
 *   }
 * }
 *
 * @response 400 scenario="Missing workspace or user context" {
 *   "error": true,
 *   "message": "Missing workspace or user context.",
 *   "data": []
 * }
 *
 * @response 500 scenario="Unexpected server error" {
 *   "error": true,
 *   "message": "Meeting couldn't be created.",
 *   "data": {
 *     "error": "SQLSTATE[...]: Some database error",
 *     "line": 102,
 *     "file": "app/Http/Controllers/MeetingController.php"
 *   }
 * }
 */

public function store(Request $request)
{
    $isApi = $request->get('isApi', true);

    try {
        if (!$this->workspace || !$this->user) {
            return formatApiResponse(true, 'Missing workspace or user context.', []);
        }

        $formFields = $request->validate([
            'title' => ['required'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required', 'after_or_equal:start_date'],
            'start_time' => ['required'],
            'end_time' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if (
                        $request->start_date === $request->end_date &&
                        $value < $request->start_time
                    ) {
                        $fail('End time must be after start time on the same day.');
                    }
                }
            ]
        ]);

        $formFields['start_date_time'] = format_date($request->start_date, false, app('php_date_format'), 'Y-m-d', false) . ' ' . $request->start_time;
        $formFields['end_date_time'] = format_date($request->end_date, false, app('php_date_format'), 'Y-m-d', false) . ' ' . $request->end_time;
        $formFields['workspace_id'] = $this->workspace->id;
        $formFields['user_id'] = $this->user->id;
        $formFields['admin_id'] = getAdminIDByUserRole();

        $userIds = $request->input('user_ids', []);
        $clientIds = $request->input('client_ids', []);

        // Ensure the creator is a participant
        if (Auth::guard('client')->check() && !in_array($this->user->id, $clientIds)) {
            array_unshift($clientIds, $this->user->id);
        } elseif (Auth::guard('web')->check() && !in_array($this->user->id, $userIds)) {
            array_unshift($userIds, $this->user->id);
        }

        $meeting = Meeting::create($formFields);
        $meeting->users()->attach($userIds);
        $meeting->clients()->attach($clientIds);

        // Notify participants
        $notification_data = [
            'type' => 'meeting',
            'type_id' => $meeting->id,
            'type_title' => $meeting->title,
            'action' => 'assigned',
            'title' => 'Added in a meeting',
            'message' => $this->user->first_name . ' ' . $this->user->last_name . ' added you in meeting: ' . $meeting->title . ', ID #' . $meeting->id . '.'
        ];

        $recipients = array_merge(
            array_map(fn($id) => 'u_' . $id, $userIds),
            array_map(fn($id) => 'c_' . $id, $clientIds)
        );

        processNotifications($notification_data, $recipients);

        return formatApiResponse(false, 'Meeting created successfully.', [
            'id' => $meeting->id,
            'data' => formatMeeting($meeting)
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return formatApiValidationError($isApi, $e->errors());
    } catch (\Exception $e) {
        return formatApiResponse(true, 'Meeting couldn\'t be created.', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
    }
}

    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $status = isset($_REQUEST['status']) && $_REQUEST['status'] !== '' ? $_REQUEST['status'] : "";
        $user_id = (request('user_id')) ? request('user_id') : "";
        $client_id = (request('client_id')) ? request('client_id') : "";
        $start_date_from = (request('start_date_from')) ? request('start_date_from') : "";
        $start_date_to = (request('start_date_to')) ? request('start_date_to') : "";
        $end_date_from = (request('end_date_from')) ? request('end_date_from') : "";
        $end_date_to = (request('end_date_to')) ? request('end_date_to') : "";
        $meetings = isAdminOrHasAllDataAccess() ? $this->workspace->meetings() : $this->user->meetings();
        if ($search) {
            $meetings = $meetings->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        if ($user_id) {
            $user = User::find($user_id);
            $meetings = $user->meetings();
        }
        if ($client_id) {
            $client = Client::find($client_id);
            $meetings = $client->meetings();
        }
        if ($start_date_from && $start_date_to) {
            $start_date_from = $start_date_from . ' 00:00:00';
            $start_date_to = $start_date_to . ' 23:59:59';
            $meetings = $meetings->whereBetween('start_date_time', [$start_date_from, $start_date_to]);
        }
        if ($end_date_from && $end_date_to) {
            $end_date_from = $end_date_from . ' 00:00:00';
            $end_date_to = $end_date_to . ' 23:59:59';
            $meetings  = $meetings->whereBetween('end_date_time', [$end_date_from, $end_date_to]);
        }
        if ($status) {
            if ($status === 'ongoing') {
                $meetings = $meetings->where('start_date_time', '<=', Carbon::now(config('app.timezone')))
                    ->where('end_date_time', '>=', Carbon::now(config('app.timezone')));
            } elseif ($status === 'yet_to_start') {
                $meetings = $meetings->where('start_date_time', '>', Carbon::now(config('app.timezone')));
            } elseif ($status === 'ended') {
                $meetings = $meetings->where('end_date_time', '<', Carbon::now(config('app.timezone')));
            }
        }
        $totalmeetings = $meetings->count();

        $canCreate = checkPermission('create_meetings');
        $canEdit = checkPermission('edit_meetings');
        $canDelete = checkPermission('delete_meetings');

        $currentDateTime = Carbon::now(config('app.timezone'));
        $meetings = $meetings->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($meeting) use ($canEdit, $canDelete, $canCreate, $currentDateTime) {

                $status = (($currentDateTime < \Carbon\Carbon::parse($meeting->start_date_time, config('app.timezone'))) ? 'Will start in ' . $currentDateTime->diff(\Carbon\Carbon::parse($meeting->start_date_time, config('app.timezone')))->format('%a days %H hours %I minutes %S seconds') : (($currentDateTime > \Carbon\Carbon::parse($meeting->end_date_time, config('app.timezone')) ? 'Ended before ' . \Carbon\Carbon::parse($meeting->end_date_time, config('app.timezone'))->diff($currentDateTime)->format('%a days %H hours %I minutes %S seconds') : 'Ongoing')));

                $actions = '';

                if ($canEdit) {
                    $actions .= '<a href="javascript:void(0);" class="edit-meeting" data-id="' . $meeting->id . '" title="' . get_label('update', 'Update') . '">' .
                        '<i class="bx bx-edit mx-1"></i>' .
                        '</a>';
                }

                if ($canDelete) {
                    $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $meeting->id . '" data-type="meetings" data-table="meetings_table">' .
                        '<i class="bx bx-trash text-danger mx-1"></i>' .
                        '</button>';
                }

                if ($canCreate) {
                    $actions .= '<a href="javascript:void(0);" class="duplicate" data-id="' . $meeting->id . '" data-title="' . $meeting->title . '" data-type="meetings" data-table="meetings_table" title="' . get_label('duplicate', 'Duplicate') . '">' .
                        '<i class="bx bx-copy text-warning mx-2"></i>' .
                        '</a>';
                }

                if ($status == 'Ongoing') {
                    $actions .= '<a href="/master-panel/meetings/join/' . $meeting->id . '" target="_blank" title="Join">' .
                        '<i class="bx bx-arrow-to-right text-success mx-3"></i>' .
                        '</a>';
                }

                $actions = $actions ?: '-';

                $userHtml = '';
                if (!empty($meeting->users) && count($meeting->users) > 0) {
                    $userHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                    foreach ($meeting->users as $user) {
                        $userHtml .= "<li class='avatar avatar-sm pull-up'><a href='/master-panel/users/profile/{$user->id}' target='_blank' title='{$user->first_name} {$user->last_name}'><img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                    }
                    if ($canEdit) {
                        $userHtml .= '<li title=' . get_label('update', 'Update') . '><a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-meeting update-users-clients" data-id="' . $meeting->id . '"><span class="bx bx-edit"></span></a></li>';
                    }
                    $userHtml .= '</ul>';
                } else {
                    $userHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
                    if ($canEdit) {
                        $userHtml .= '<a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-meeting update-users-clients" data-id="' . $meeting->id . '">' .
                            '<span class="bx bx-edit"></span>' .
                            '</a>';
                    }
                }

                $clientHtml = '';
                if (!empty($meeting->clients) && count($meeting->clients) > 0) {
                    $clientHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                    foreach ($meeting->clients as $client) {
                        $clientHtml .= "<li class='avatar avatar-sm pull-up'><a href='/master-panel/clients/profile/{$client->id}' target='_blank' title='{$client->first_name} {$client->last_name}'><img src='" . ($client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                    }
                    if ($canEdit) {
                        $clientHtml .= '<li title=' . get_label('update', 'Update') . '><a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-meeting update-users-clients" data-id="' . $meeting->id . '"><span class="bx bx-edit"></span></a></li>';
                    }
                    $clientHtml .= '</ul>';
                } else {
                    $clientHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
                    if ($canEdit) {
                        $clientHtml .= '<a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-meeting update-users-clients" data-id="' . $meeting->id . '">' .
                            '<span class="bx bx-edit"></span>' .
                            '</a>';
                    }
                }

                return [
                    'id' => $meeting->id,
                    'title' => $meeting->title,
                    'start_date_time' => format_date($meeting->start_date_time, true, null, null, false),
                    'end_date_time' => format_date($meeting->end_date_time, true, null, null, false),
                    'users' => $userHtml,
                    'clients' => $clientHtml,
                    'status' => $status,
                    'created_at' => format_date($meeting->created_at,  true),
                    'updated_at' => format_date($meeting->updated_at, true),
                    'actions' => $actions
                ];
            });
        return response()->json([
            "rows" => $meetings->items(),
            "total" => $totalmeetings,
        ]);
    }

    /**
 * List Meetings
 *
 * Retrieve a list of all meetings or a single meeting by ID, with support for search, filters, sort, and pagination.
 *
 * @group Meeting Managemant
 * @authenticated
 *
 * @urlParam id int Optional. The ID of the meeting to retrieve. Example: 5
 *
 * @queryParam search string Optional. Search by meeting title or ID. Example: team sync
 * @queryParam sort string Optional. Field to sort by. Default is id. Example: start_date_time
 * @queryParam order string Optional. Sort order (ASC or DESC). Default is DESC. Example: ASC
 * @queryParam limit int Optional. Number of results per page. Example: 10
 * @queryParam status string Optional. Filter by meeting status. Options: ongoing, yet_to_start, ended. Example: ongoing
 * @queryParam user_id int Optional. Filter meetings assigned to a specific user. Example: 2
 * @queryParam client_id int Optional. Filter meetings assigned to a specific client. Example: 8
 * @queryParam start_date_from date Optional. Start date filter from (Y-m-d). Example: 2025-06-01
 * @queryParam start_date_to date Optional. Start date filter to (Y-m-d). Example: 2025-06-30
 * @queryParam end_date_from date Optional. End date filter from (Y-m-d). Example: 2025-06-01
 * @queryParam end_date_to date Optional. End date filter to (Y-m-d). Example: 2025-06-30
 * @queryParam isApi boolean Optional. Default is true. Used to enable API-formatted responses. Example: true
 * @header workspace_id 2
 * @response 200 {
 *   "status": false,
 *   "message": "Meetings retrieved successfully.",
 *   "data": {
 *     "total": 1,
 *     "data": [
 *       {
 *         "id": 5,
 *         "title": "Client Update Meeting",
 *         "start_date_time": "2025-06-10 10:00:00",
 *         "end_date_time": "2025-06-10 11:00:00",
 *         "users": "<ul>...</ul>",
 *         "clients": "<ul>...</ul>",
 *         "status": "Ongoing",
 *         "created_at": "2025-06-01 09:30:00",
 *         "updated_at": "2025-06-01 09:45:00",
 *         "actions": "<a>...</a>"
 *       }
 *     ]
 *   }
 * }
 *
 * @response 400 {
 *   "status": true,
 *   "message": "Missing workspace or user context.",
 *   "data": []
 * }
 *
 * @response 404 {
 *   "status": true,
 *   "message": "Meeting not found.",
 *   "data": []
 * }
 *
 * @response 422 {
 *   "status": true,
 *   "message": "Validation failed.",
 *   "data": {
 *     "user_id": [
 *       "The selected user_id is invalid."
 *     ],
 *     "client_id": [
 *       "The selected client_id is invalid."
 *     ]
 *   }
 * }
 *
 * @response 500 {
 *   "status": true,
 *   "message": "An error occurred while retrieving meetings.",
 *   "data": {
 *     "error": "SQLSTATE[HY000]: General error...",
 *     "file": "/app/Http/Controllers/MeetingsController.php",
 *     "line": 75
 *   }
 * }
 */

      public function listapi($id = null)
{
    $isApi = request()->get('isApi', true);

    try {
        if (!$this->workspace || !$this->user) {
            return formatApiResponse(true, 'Missing workspace or user context.', []);
        }

        // Fetch a single meeting by ID
        if ($id) {
            $meeting = Meeting::with(['users', 'clients'])
                ->where('workspace_id', $this->workspace->id)
                ->findOrFail($id);

            return formatApiResponse(false, 'Meeting retrieved successfully.', [
                'total' => 1,
                'data' => [formatMeeting($meeting)]
            ]);
        }

        // Filters
        $search = request('search');
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $status = request('status');
        $user_id = request('user_id');
        $client_id = request('client_id');
        $start_date_from = request('start_date_from');
        $start_date_to = request('start_date_to');
        $end_date_from = request('end_date_from');
        $end_date_to = request('end_date_to');
        $per_page = request('per_page', 10);

        // Base query
        $meetings = isAdminOrHasAllDataAccess() ?
            Meeting::query()->where('workspace_id', $this->workspace->id) :
            $this->user->meetings();

        $meetings->with(['users', 'clients']);

        // Apply filters
        if ($search) {
            $meetings->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$search%");
            });
        }

        if ($user_id && $user = User::find($user_id)) {
            $meetings = $user->meetings()->where('workspace_id', $this->workspace->id);
        }

        if ($client_id && $client = Client::find($client_id)) {
            $meetings = $client->meetings()->where('workspace_id', $this->workspace->id);
        }

        if ($start_date_from && $start_date_to) {
            $meetings->whereBetween('start_date_time', [
                $start_date_from . ' 00:00:00',
                $start_date_to . ' 23:59:59'
            ]);
        }

        if ($end_date_from && $end_date_to) {
            $meetings->whereBetween('end_date_time', [
                $end_date_from . ' 00:00:00',
                $end_date_to . ' 23:59:59'
            ]);
        }

        if ($status) {
            $now = Carbon::now(config('app.timezone'));
            if ($status === 'ongoing') {
                $meetings->where('start_date_time', '<=', $now)
                         ->where('end_date_time', '>=', $now);
            } elseif ($status === 'yet_to_start') {
                $meetings->where('start_date_time', '>', $now);
            } elseif ($status === 'ended') {
                $meetings->where('end_date_time', '<', $now);
            }
        }

        $total = $meetings->count();

        // Pagination + formatting
        $meetings = $meetings->orderBy($sort, $order)
            ->paginate($per_page)
            ->through(fn($meeting) => formatMeeting($meeting));

        return formatApiResponse(false, 'Meetings retrieved successfully.', [
            'total' => $total,
            'data' => $meetings->items()
        ]);
    } catch (\Exception $e) {
        return formatApiResponse(true, 'Error retrieving meetings.', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);
    }
}


    public function edit($id)
    {
        $meeting = Meeting::findOrFail($id);
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        return view('meetings.update_meeting', compact('meeting', 'users', 'clients'));
    }
/**
 * Update a Meeting
 *
 * Update the details of an existing meeting. Also supports assigning users and clients to the meeting, and notifies newly assigned participants.
 *
 * @group Meeting Managemant
 * @authenticated
 *
 * @bodyParam id int required The ID of the meeting to update. Example: 7
 * @bodyParam title string required The title of the meeting. Example: Project Planning
 * @bodyParam start_date date required Start date of the meeting (Y-m-d). Must be before or equal to end_date. Example: 2025-06-08
 * @bodyParam end_date date required End date of the meeting (Y-m-d). Must be after or equal to start_date. Example: 2025-06-08
 * @bodyParam start_time string required Start time in 24-hour format (H:i:s). Example: 10:00:00
 * @bodyParam end_time string required End time in 24-hour format (H:i:s). Must be after start_time if dates are the same. Example: 11:30:00
 * @bodyParam user_ids array Optional. An array of user IDs to assign to the meeting. Example: [1, 4, 5]
 * @bodyParam client_ids array Optional. An array of client IDs to assign to the meeting. Example: [2, 8]
 * @bodyParam isApi boolean Optional. Use true to get a standardized API response. Default is false. Example: true
 * @header workspace_id 2
 * @response 200 {
 *   "status": false,
 *   "message": "meeting updated successfully.",
 *   "data": {
 *     "id": 7,
 *     "data": {
 *       "id": 7,
 *       "title": "Project Planning",
 *       "start_date_time": "2025-06-08 10:00:00",
 *       "end_date_time": "2025-06-08 11:30:00",
 *       "users": [...],
 *       "clients": [...],
 *       ...
 *     }
 *   }
 * }
 *
 * @response 422 {
 *   "status": true,
 *   "message": "Validation failed.",
 *   "data": {
 *     "title": ["The title field is required."],
 *     "start_date": ["The start date field is required."],
 *     "end_date": ["The end date must be after or equal to start date."],
 *     "end_time": ["End time must be after start time on the same day."]
 *   }
 * }
 *
 * @response 404 {
 *   "status": true,
 *   "message": "Meeting not found.",
 *   "data": []
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "An error occurred while updating the meeting."
 * }
 */

    public function update(Request $request)
    {
         $isApi = request()->get('isApi', false);

        $formFields = $request->validate([
            'title' => ['required'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required', 'after_or_equal:start_date'],
            'start_time' => ['required'],
            'end_time' => ['required', function ($attribute, $value, $fail) use ($request) {
                if (
                    $request->start_date === $request->end_date &&
                    $value < $request->start_time
                ) {
                    $fail('End time must be after start time on the same day.');
                }
            }]
        ]);
        try{
        $id = $request->input('id');
        $start_date = $request->input('start_date');
        $start_time = $request->input('start_time');
        $end_date = $request->input('end_date');
        $end_time = $request->input('end_time');
                $formFields['start_date'] = $formFields['start_date'];
$formFields['end_date'] = $formFields['end_date'];


        $userIds = $request->input('user_ids') ?? [];

        $clientIds = $request->input('client_ids') ?? [];
        $meeting = Meeting::findOrFail($id);
        // Set creator as a participant automatically

        if (User::where('id', $meeting->user_id)->exists() && !in_array($meeting->user_id, $userIds)) {
            array_splice($userIds, 0, 0, $meeting->user_id);
        } elseif (Client::where('id', $meeting->user_id)->exists() && !in_array($meeting->user_id, $clientIds)) {
            array_splice($clientIds, 0, 0, $meeting->user_id);
        }
        // Get current list of users and clients associated with the workspace
        $existingUserIds = $meeting->users->pluck('id')->toArray();
        $existingClientIds = $meeting->clients->pluck('id')->toArray();
        $meeting->update($formFields);
        $meeting->users()->sync($userIds);
        $meeting->clients()->sync($clientIds);

        // Exclude old users and clients from receiving notification
        $userIds = array_diff($userIds, $existingUserIds);
        $clientIds = array_diff($clientIds, $existingClientIds);

        // Prepare notification data
        $notification_data = [
            'type' => 'meeting',
            'type_id' => $id,
            'type_title' => $meeting->title,
            'action' => 'assigned',
            'title' => 'Added in a meeting',
            'message' => $this->user->first_name . ' ' . $this->user->last_name . ' added you in meeting: ' . $meeting->title . ', ID #' . $id . '.'
        ];

        // Combine user and client IDs for notification recipients
        $recipients = array_merge(
            array_map(function ($userId) {
                return 'u_' . $userId;
            }, $userIds),
            array_map(function ($clientId) {
                return 'c_' . $clientId;
            }, $clientIds)
        );
        // Process notifications
        processNotifications($notification_data, $recipients);
        Session::flash('message', 'Meeting updated successfully.');
       return formatApiResponse(
                    false,
                    'meeting updated successfully.',
                    [
                        'id' => $meeting->id,
                        'data' =>formatMeeting($meeting)
                    ]
                );
    }  catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            dd($e);
            // Handle any unexpected errors
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while updating the meeting.'
            ], 500);
        }
    }

/**
 * Delete a Meeting
 *
 * This endpoint allows you to delete a meeting by its ID. Only users with proper permissions can delete meetings.
 *@group Meeting Managemant
 * @urlParam id int required The ID of the meeting to delete. Example: 15
 * @header workspace_id 2
 * @response 200 {
 *   "success": true,
 *   "message": "Meeting deleted successfully.",
 *   "data": {
 *     "id": 15,
 * "data":[]
 *   }
 * }
 *
 * @response 404 {
 *   "error": true,
 *   "message": "Meeting not found."
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "An error occurred while deleting the meeting."
 * }
 */

   public function destroy($id)
{
    $isApi = request()->get('isApi', false);

    try {
        $meeting = Meeting::findOrFail($id);

        $response = DeletionService::delete(Meeting::class, $id, 'Meeting');

        return formatApiResponse(
            $isApi,
            'Meeting deleted successfully.',
            [
                'id' => $id,
                'data' =>[]
            ]
        );
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'error' => true,
            'message' => 'Meeting not found.'
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => 'An error occurred while deleting the meeting.'
        ], 500);
    }
}


    public function destroy_multiple(Request $request)
    {

        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:meetings,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedMeetings = [];
        $deletedMeetingTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $meeting = Meeting::find($id);
            if ($meeting) {
                $deletedMeetings[] = $id;
                $deletedMeetingTitles[] = $meeting->title;
                DeletionService::delete(Meeting::class, $id, 'Meeting');
            }
        }

        return response()->json(['error' => false,
        'message' => 'Meetings(s) deleted successfully.',
         'id' => $deletedMeetings,
         'titles' => $deletedMeetingTitles]);
    }

    public function join($id)
    {

        $meeting = Meeting::findOrFail($id);
        $currentDateTime = Carbon::now(config('app.timezone'));
        if ($currentDateTime < $meeting->start_date_time) {
            return redirect(route('meetings.index'))->with('error', 'Meeting is yet to start');
        } elseif ($currentDateTime > $meeting->end_date_time) {
            return redirect(route('meetings.index'))->with('error', 'Meeting has been ended');
        } else {
            if ($meeting->users->contains($this->user->id) || isAdminOrHasAllDataAccess()) {
                $is_meeting_admin =  $this->user->id == $meeting['user_id'];
                $meeting_id = $meeting['id'];
                $room_name = $meeting['title'];
                $user_email =  $this->user->email;
                $user_display_name =  $this->user->first_name . ' ' .  $this->user->last_name;
                return view('meetings.join_meeting', compact('is_meeting_admin', 'meeting_id', 'room_name', 'user_email', 'user_display_name'));
            } else {
                return redirect(route('meetings.index'))->with('error', 'You are not authorized to join this meeting');
            }
        }
    }

    public function duplicate($id)
    {
        // Define the related tables for this meeting
        $relatedTables = ['users', 'clients']; // Include related tables as needed

        // Use the general duplicateRecord function
        $title = (request()->has('title') && !empty(trim(request()->title))) ? request()->title : '';
        $duplicateMeeting = duplicateRecord(Meeting::class, $id, $relatedTables, $title);
        if (!$duplicateMeeting) {
            return response()->json(['error' => true, 'message' => 'Meeting duplication failed.']);
        }
        if (request()->has('reload') && request()->input('reload') === 'true') {
            Session::flash('message', 'Meeting duplicated successfully.');
        }
        return response()->json(['error' => false, 'message' => 'Meeting duplicated successfully.', 'id' => $id]);
    }
    public function get($id)
    {
        $meeting = Meeting::with('users', 'clients')->findOrFail($id);

        $meeting->start_date = \Carbon\Carbon::parse($meeting->start_date_time)->format('Y-m-d');
        $meeting->start_time = \Carbon\Carbon::parse($meeting->start_date_time)->format('H:i:s');
        $meeting->end_date = \Carbon\Carbon::parse($meeting->end_date_time)->format('Y-m-d');
        $meeting->end_time = \Carbon\Carbon::parse($meeting->end_date_time)->format('H:i:s');

        return response()->json(['error' => false, 'meeting' => $meeting]);
    }

    public function calendar_view()
    {
        $users = User::all();
        $auth_user = auth()->user();

        return view('meetings.calendar_view', compact('users', 'auth_user'));
    }


   public function get_calendar_data(Request $request)
{
    // Parse date range with proper timezone handling
    $php_date_format = app('php_date_format');
    $start = $request->query('date_from')
        ? Carbon::createFromFormat($php_date_format, $request->query('date_from'), config('app.timezone'))
        : Carbon::now(config('app.timezone'))->startOfMonth();
    $end = $request->query('date_to')
        ? Carbon::createFromFormat($php_date_format, $request->query('date_to'), config('app.timezone'))
        : Carbon::now(config('app.timezone'))->endOfMonth();

    // Retrieve meetings based on user access
    $meetingsQuery = isAdminOrHasAllDataAccess()
        ? $this->workspace->meetings()
        : $this->user->meetings();

    // Apply date range filter
    $meetingsQuery->where(function ($query) use ($start, $end) {
        $query->whereBetween('start_date_time', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->orWhereBetween('end_date_time', [$start->toDateTimeString(), $end->toDateTimeString()]);
    });

    // Apply status filter
    $statuses = $request->query('status', []);
    if (!is_array($statuses)) {
        $statuses = explode(',', $statuses); // Handle if statuses are passed as a comma-separated string
    }
    
    if (!empty($statuses)) {
        $currentDateTime = Carbon::now(config('app.timezone'));
        $meetingsQuery->where(function ($query) use ($statuses, $currentDateTime) {
            foreach ($statuses as $status) {
                if ($status === 'ongoing') {
                    $query->orWhere(function ($q) use ($currentDateTime) {
                        $q->where('start_date_time', '<=', $currentDateTime)
                          ->where('end_date_time', '>=', $currentDateTime);
                    });
                } elseif ($status === 'yet_to_start') {
                    $query->orWhere('start_date_time', '>', $currentDateTime);
                } elseif ($status === 'ended') {
                    $query->orWhere('end_date_time', '<', $currentDateTime);
                }
            }
        });
    }

    // Fetch the meetings
    $meetings = $meetingsQuery->get();

    // Current time for status calculations
    $currentDateTime = Carbon::now(config('app.timezone'));

    // Format meetings for FullCalendar
    $events = $meetings->map(function ($meeting) use ($currentDateTime) {
        $startTime = Carbon::parse($meeting->start_date_time, config('app.timezone'));
        $endTime = Carbon::parse($meeting->end_date_time, config('app.timezone'));

        // Determine meeting status and styling
        if ($currentDateTime < $startTime) {
            $status = 'Upcoming';
            $backgroundColor = '#9BAFFF'; // Blue
            $borderColor = '#0056B3';
            $textColor = '#000000';
            $description = 'Starts in ' . $this->formatTimeRemaining($currentDateTime->diff($startTime));
        } elseif ($currentDateTime > $endTime) {
            $status = 'Ended';
            $backgroundColor = '#FF8080'; // Red
            $borderColor = '#495057';
            $textColor = '#000000';
            $description = 'Ended ' . $this->formatTimeRemaining($endTime->diff($currentDateTime)) . ' ago';
        } else {
            $status = 'Ongoing';
            $backgroundColor = '#A0E4A3'; // Green
            $borderColor = '#1E7E34';
            $textColor = '#000000';
            $description = 'Currently in progress';
        }

        return [
            'id' => $meeting->id,
            'title' => $meeting->title . ' (' . $status . ')',
            'start' => $startTime->toIso8601String(),
            'end' => $endTime->toIso8601String(),
            'url' => route('meetings.join', ['id' => $meeting->id]),
            'backgroundColor' => $backgroundColor,
            'borderColor' => $borderColor,
            'textColor' => $textColor,
            'description' => $description,
            'allDay' => $meeting->is_all_day ?? false,
            'extendedProps' => [
                'status' => $status,
                'organizer' => $meeting->organizer->name ?? 'Unknown',
                'location' => $meeting->location ?? null,
            ]
        ];
    });

    return response()->json($events);
}

// In MeetingsController.php
public function get_meetings_stats()
{
    try {
        if (!$this->workspace || !$this->user) {
            return response()->json(['error' => true, 'message' => 'Missing workspace or user context.'], 400);
        }

        $now = Carbon::now(config('app.timezone'));
        $query = isAdminOrHasAllDataAccess() ? $this->workspace->meetings() : $this->user->meetings();

        $ongoing = (clone $query)->where('start_date_time', '<=', $now)->where('end_date_time', '>=', $now)->count();
        $upcoming = (clone $query)->where('start_date_time', '>', $now)->count();
        $completed = (clone $query)->where('end_date_time', '<', $now)->count();
        $total = $query->count();

        return response()->json([
            'ongoing' => $ongoing,
            'upcoming' => $upcoming,
            'completed' => $completed,
            'total' => $total,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => 'Error fetching meetings stats.',
            'details' => $e->getMessage()
        ], 500);
    }
}
    private function formatTimeRemaining(\DateInterval $interval)
    {
        $parts = [];

        if ($interval->d > 0) {
            $parts[] = $interval->d . ' day' . ($interval->d > 1 ? 's' : '');
        }

        if ($interval->h > 0) {
            $parts[] = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '');
        }

        if ($interval->i > 0) {
            $parts[] = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
        }

        // Only show seconds if less than an hour remains
        if (empty($parts) || ($interval->d == 0 && $interval->h == 0)) {
            $parts[] = $interval->s . ' second' . ($interval->s > 1 ? 's' : '');
        }

        return implode(', ', $parts);
    }
    public function saveViewPreference(Request $request)
    {
        $view = $request->input('view');
        $prefix = isClient() ? 'c_' : 'u_';
        if (
            UserClientPreference::updateOrCreate(
                ['user_id' => $prefix . $this->user->id, 'table_name' => 'meetings'],
                ['default_view' => $view]
            )
        ) {
            return response()->json(['error' => false, 'message' => 'Default View Set Successfully.']);
        } else {
            return response()->json(['error' => true, 'message' => 'Something Went Wrong.']);
        }
    }
}
