<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Workspace;
use Illuminate\Support\Str;
use App\Models\LeaveEditor;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;


class LeaveRequestController extends Controller
{
    protected $workspace;
    protected $user;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Detect if request expects JSON (API)
            if ($request->expectsJson()) {
                // Get from header for API requests
                $workspaceId = $request->header('workspace_id');
            } else {
                // Get from session for web requests
                $workspaceId = session()->get('workspace_id');
            }

            $this->workspace = Workspace::find(getWorkspaceId());
            $this->user = getAuthenticatedUser();

            return $next($request);
        });
    }

    public function index()
    {
        $leave_requests = is_admin_or_leave_editor() ? $this->workspace->leave_requests() : $this->user->leave_requests();
        $users = $this->workspace->users(true)->get();
        // dd($users);
        return view('leave_requests.list', ['leave_requests' => $leave_requests->count(), 'users' => $users, 'auth_user' => $this->user]);
    }
    /**
     * Create a Leave Request
     * @group leaverequest Managemant
     * This endpoint allows a user, admin, or leave editor to create a leave request.
     *
     * @bodyParam reason string required The reason for the leave. Example: Family function
     * @bodyParam from_date date required The start date of the leave in the format Y-m-d. Example: 2025-06-10
     * @bodyParam to_date date required The end date of the leave in the format Y-m-d. If partialLeave is on, this must match from_date. Example: 2025-06-12
     * @bodyParam partialLeave boolean optional If set to "on", indicates a partial day leave. Example: on
     * @bodyParam from_time string required_if:partialLeave,on The start time for a partial leave (24-hour format). Example: 10:00
     * @bodyParam to_time string required_if:partialLeave,on The end time for a partial leave (24-hour format). Example: 14:00
     * @bodyParam status string optional Only admins or leave editors can set status to 'approved' or 'rejected'. Default is 'pending'. Example: pending
     * @bodyParam leaveVisibleToAll boolean optional If set to "on", the leave is visible to all workspace users. Example: on
     * @bodyParam visible_to_ids array optional Required if leaveVisibleToAll is not set. An array of user IDs who can view the leave. Example: [3, 5, 7]
     * @bodyParam user_id integer optional Only admins or leave editors can create leave requests on behalf of another user. Example: 9
     * @bodyParam isApi boolean optional Indicates if this is an API request. Defaults to true. Example: true
     *@header workspace_id 2
     * @response 200 {
     *  "error": false,
     *  "message": "Leave request created successfully.",
     *  "id": 13,
     *  "type": "leave_request",
     *  "data": {
     *    "id": 13,
     *    "user_id": 9,
     *    "reason": "Family function",
     *    "from_date": "2025-06-10",
     *    "to_date": "2025-06-12",
     *    "from_time": null,
     *    "to_time": null,
     *    "status": "pending",
     *    "visible_to_all": true,
     *    ...
     *  }
     * }
     *
     * @response 422 {
     *  "message": "The given data was invalid.",
     *  "errors": {
     *    "reason": ["The reason field is required."],
     *    "from_date": ["The from date field is required."],
     *    "to_date": ["The to date field is required."]
     *  }
     * }
     *
     * @response 500 {
     *  "error": true,
     *  "message": "leave request culd not created",
     *  "error": "Exception message",
     *  "line": 125,
     *  "file": "app/Http/Controllers/LeaveRequestController.php"
     * }
     */

    public function api_store(Request $request)
    {
        $isApi = $request->get('isApi', true);


        try {
            $formFields = $request->validate([
                'reason' => ['required'],
                'from_date' => ['required', 'before_or_equal:to_date'],
                'to_date' => [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->input('partialLeave') == 'on' && $value !== $request->input('from_date')) {
                            $fail('For partial leave, the end date must be the same as the start date.');
                        }
                    },
                ],
                'from_time' => ['required_if:partialLeave,on'],
                'to_time' => ['required_if:partialLeave,on'],
                'status' => ['nullable'],
            ], [
                'from_time.required_if' => 'The from time field is required when partial leave is checked.',
                'to_time.required_if' => 'The to time field is required when partial leave is checked.',
            ]);

            if (!$this->user->hasRole('admin') && $request->input('status') === 'approved') {
                return response()->json([
                    'error' => true,
                    'message' => 'You cannot approve your own leave request.'
                ]);
            }

            $formFields['from_date'] = format_date($request->from_date, false, app('php_date_format'), 'Y-m-d');
            $formFields['to_date'] = format_date($request->to_date, false, app('php_date_format'), 'Y-m-d');
            $formFields['workspace_id'] = $this->workspace->id;
            $formFields['admin_id'] = getAdminIDByUserRole();

            if (is_admin_or_leave_editor() && $request->filled('status') && $request->input('status') !== 'pending') {
                $formFields['action_by'] = $this->user->id;
            }

            $formFields['user_id'] = is_admin_or_leave_editor() && $request->filled('user_id')
                ? $request->input('user_id')
                : $this->user->id;

            $formFields['visible_to_all'] = ($request->input('leaveVisibleToAll') === 'on') ? 1 : 0;

            $leaveRequest = LeaveRequest::create($formFields);

            if ($leaveRequest) {
                if (!$leaveRequest->visible_to_all) {
                    $visibleToUsers = $request->input('visible_to_ids', []);
                    $leaveRequest->visibleToUsers()->sync($visibleToUsers);
                }

                $leaveRequest->refresh();
                $fromDate = Carbon::parse($leaveRequest->from_date);
                $toDate = Carbon::parse($leaveRequest->to_date);

                if ($leaveRequest->from_time && $leaveRequest->to_time) {
                    $duration = 0;
                    while ($fromDate->lessThanOrEqualTo($toDate)) {
                        $fromDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $leaveRequest->from_time);
                        $toDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $leaveRequest->to_time);
                        $duration += $fromDateTime->diffInMinutes($toDateTime) / 60;
                        $fromDate->addDay();
                    }
                } else {
                    $duration = $fromDate->diffInDays($toDate) + 1;
                }

                $leaveType = $leaveRequest->from_time && $leaveRequest->to_time ? get_label('partial', 'Partial') : get_label('full', 'Full');

                $fromFormatted = $leaveRequest->from_time
                    ? format_date($leaveRequest->from_date . ' ' . $leaveRequest->from_time, true)
                    : format_date($leaveRequest->from_date);

                $toFormatted = $leaveRequest->to_time
                    ? format_date($leaveRequest->to_date . ' ' . $leaveRequest->to_time, true)
                    : format_date($leaveRequest->to_date);

                $durationFormatted = $leaveRequest->from_time
                    ? $duration . ' hour' . ($duration > 1 ? 's' : '')
                    : $duration . ' day' . ($duration > 1 ? 's' : '');

                $user = User::find($leaveRequest->user_id);

                $notificationData = [
                    'type' => 'leave_request_creation',
                    'type_id' => $leaveRequest->id,
                    'team_member_first_name' => $user->first_name,
                    'team_member_last_name' => $user->last_name,
                    'leave_type' => $leaveType,
                    'from' => $fromFormatted,
                    'to' => $toFormatted,
                    'duration' => $durationFormatted,
                    'reason' => $leaveRequest->reason,
                    'status' => ucfirst($leaveRequest->status),
                    'action' => 'created'
                ];

                $adminModelIds = Admin::where('id', getAdminIDByUserRole())->pluck('user_id')->toArray();
                $leaveEditorIds = DB::table('leave_editors')->pluck('user_id')->toArray();

                $recipients = array_merge(
                    array_map(fn($id) => 'u_' . $id, $adminModelIds),
                    array_map(fn($id) => 'u_' . $id, $leaveEditorIds)
                );

                processNotifications($notificationData, $recipients);

                if ($leaveRequest->status == 'approved') {
                    $appTimezone = config('app.timezone');
                    $currentDateTime = new \DateTime('now', new \DateTimeZone($appTimezone));
                    $leaveEndDate = new \DateTime($leaveRequest->to_date, new \DateTimeZone($appTimezone));

                    if ($leaveRequest->to_time) {
                        $leaveEndDate->setTime(
                            (int)substr($leaveRequest->to_time, 0, 2),
                            (int)substr($leaveRequest->to_time, 3, 2)
                        );
                    } else {
                        $leaveEndDate->setTime(23, 59, 59);
                    }

                    if ($currentDateTime < $leaveEndDate) {
                        $recipientTeamMembers = $leaveRequest->visible_to_all
                            ? $this->workspace->users->pluck('id')->toArray()
                            : array_merge(
                                $leaveEditorIds,
                                $adminModelIds,
                                $leaveRequest->visibleToUsers->pluck('id')->toArray()
                            );

                        $recipientTeamMembers = array_diff($recipientTeamMembers, [$leaveRequest->user_id]);
                        $recipientTeamMemberIds = array_map(fn($id) => 'u_' . $id, $recipientTeamMembers);

                        $alertData = [
                            'type' => 'team_member_on_leave_alert',
                            'type_id' => $leaveRequest->id,
                            'team_member_first_name' => $user->first_name,
                            'team_member_last_name' => $user->last_name,
                            'leave_type' => $leaveType,
                            'from' => $fromFormatted,
                            'to' => $toFormatted,
                            'duration' => $durationFormatted,
                            'reason' => $leaveRequest->reason,
                            'action' => 'team_member_on_leave_alert'
                        ];

                        processNotifications($alertData, $recipientTeamMemberIds);
                    }
                }

                return response()->json([
                    'error' => false,
                    'message' => 'Leave request created successfully.',
                    'id' => $leaveRequest->id,
                    'type' => 'leave_request',
                    'data' => formatLeaveRequest($leaveRequest),

                ]);
            }

            return response()->json([
                'error' => true,
                'message' => 'Leave request couldn\'t be created.'
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (Exception $e) {
            dd($e);
            return formatApiResponse(
                true,
                'leave request culd not created ',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            );
        }
    }
    public function store(Request $request)
    {
        $formFields = $request->validate([
            'reason' => ['required'],
            'from_date' => ['required', 'before_or_equal:to_date'],
            'to_date' => [
                'required',

                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('partialLeave') == 'on' && $value !== $request->input('from_date')) {
                        $fail('For partial leave, the end date must be the same as the start date.');
                    }
                },
            ],
            'from_time' => ['required_if:partialLeave,on'],
            'to_time' => ['required_if:partialLeave,on'],
            'status' => ['nullable'],
        ], [
            'from_time.required_if' => 'The from time field is required when partial leave is checked.',
            'to_time.required_if' => 'The to time field is required when partial leave is checked.',
        ]);
        if (!$this->user->hasRole('admin') && $request->input('status') && $request->filled('status') && $request->input('status') == 'approved') {
            return response()->json(['error' => true, 'message' => 'You cannot approve your own leave request.']);
        }
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $formFields['from_date'] = format_date($from_date, false, app('php_date_format'), 'Y-m-d');
        $formFields['to_date'] = format_date($to_date, false, app('php_date_format'), 'Y-m-d');
        if (is_admin_or_leave_editor() && $request->input('status') && $request->filled('status') && $request->input('status') != 'pending') {
            $formFields['action_by'] = $this->user->id;
        }
        $formFields['workspace_id'] = $this->workspace->id;
        $formFields['user_id'] = is_admin_or_leave_editor() && $request->filled('user_id') ? $request->input('user_id') : $this->user->id;
        $leaveVisibleToAll = $request->input('leaveVisibleToAll') && $request->filled('leaveVisibleToAll') && $request->input('leaveVisibleToAll') == 'on' ? 1 : 0;
        $formFields['visible_to_all'] = $leaveVisibleToAll;
        $formFields['admin_id'] = getAdminIDByUserRole();
        if ($lr = LeaveRequest::create($formFields)) {
            if ($leaveVisibleToAll == 0) {
                $visibleToUsers = $request->input('visible_to_ids', []);
                $lr->visibleToUsers()->sync($visibleToUsers);
            }
            $lr = LeaveRequest::find($lr->id);
            $fromDate = Carbon::parse($lr->from_date);
            $toDate = Carbon::parse($lr->to_date);
            $fromDateDayOfWeek = $fromDate->format('D');
            $toDateDayOfWeek = $toDate->format('D');
            if ($lr->from_time && $lr->to_time) {
                $duration = 0;
                // Loop through each day
                while ($fromDate->lessThanOrEqualTo($toDate)) {
                    // Create Carbon instances for the start and end times of the leave request for the current day
                    $fromDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $lr->from_time);
                    $toDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $lr->to_time);
                    // Calculate the duration for the current day and add it to the total duration
                    $duration += $fromDateTime->diffInMinutes($toDateTime) / 60; // Duration in hours
                    // Move to the next day
                    $fromDate->addDay();
                }
            } else {
                // Calculate the inclusive duration in days
                $duration = $fromDate->diffInDays($toDate) + 1;
            }
            $leaveType = $lr->from_time && $lr->to_time ? get_label('partial', 'Partial') : get_label('full', 'Full');
            $from = $fromDateDayOfWeek . ', ' . ($lr->from_time ? format_date($lr->from_date . ' ' . $lr->from_time, true, null, null, false) : format_date($lr->from_date));
            $to = $toDateDayOfWeek . ', ' . ($lr->to_time ? format_date($lr->to_date . ' ' . $lr->to_time, true, null, null, false) : format_date($lr->to_date));
            $duration = $lr->from_time && $lr->to_time ? $duration . ' hour' . ($duration > 1 ? 's' : '') : $duration . ' day' . ($duration > 1 ? 's' : '');
            // Fetch user details based on the user_id in the leave request
            $user = User::find($lr->user_id);
            // Prepare notification data
            $notificationData = [
                'type' => 'leave_request_creation',
                'type_id' => $lr->id,
                'team_member_first_name' => $user->first_name,
                'team_member_last_name' => $user->last_name,
                'leave_type' => $leaveType,
                'from' => $from,
                'to' => $to,
                'duration' => $duration,
                'reason' => $lr->reason,
                'status' => ucfirst($lr->status),
                'action' => 'created'
            ];
            // Determine recipients
            $adminModelIds = Admin::where('id', getAdminIDByUserRole())->pluck('user_id')->toArray();
            $leaveEditorIds = DB::table('leave_editors')
                ->pluck('user_id')
                ->toArray();
            // Combine admin model_ids and leave_editor_ids
            $adminIds = array_map(function ($modelId) {
                return 'u_' . $modelId;
            }, $adminModelIds);
            $leaveEditorIdsWithPrefix = array_map(function ($leaveEditorId) {
                return 'u_' . $leaveEditorId;
            }, $leaveEditorIds);
            // Combine admin and leave editor ids
            $recipients = array_merge($adminIds, $leaveEditorIdsWithPrefix);
            processNotifications($notificationData, $recipients);
            if ($lr->status == 'approved') {
                // Get the timezone from the application configuration
                $appTimezone = config('app.timezone');
                // Get current date and time with the application's timezone
                $currentDateTime = new \DateTime('now', new \DateTimeZone($appTimezone));
                // Combine to_date and to_time into a single DateTime object with the application's timezone
                $leaveEndDate = new \DateTime($lr->to_date, new \DateTimeZone($appTimezone));
                if ($lr->to_time) {
                    // If to_time is available, set the time part of the DateTime object
                    $leaveEndDate->setTime((int)substr($lr->to_time, 0, 2), (int)substr($lr->to_time, 3, 2));
                } else {
                    // If to_time is not available, set the end of the day
                    $leaveEndDate->setTime(23, 59, 59);
                }
                // Ensure both DateTime objects are in the same timezone
                $leaveEndDate->setTimezone(new \DateTimeZone($appTimezone));
                // Check if the leave end date and time have not passed
                if ($currentDateTime < $leaveEndDate) {
                    if ($lr->visible_to_all == 1) {
                        $recipientTeamMembers = $this->workspace->users->pluck('id')->toArray();
                    } else {
                        $recipientTeamMembers = $lr->visibleToUsers->pluck('id')->toArray();
                        $recipientTeamMembers = array_merge($adminModelIds, $leaveEditorIds, $recipientTeamMembers);
                    }
                    //Exclude requestee from alert
                    $recipientTeamMembers = array_diff($recipientTeamMembers, [$lr->user_id]);
                    $recipientTeamMemberIds = array_map(function ($userId) {
                        return 'u_' . $userId;
                    }, $recipientTeamMembers);
                    $notificationData = [
                        'type' => 'team_member_on_leave_alert',
                        'type_id' => $lr->id,
                        'team_member_first_name' => $user->first_name,
                        'team_member_last_name' => $user->last_name,
                        'leave_type' => $leaveType,
                        'from' => $from,
                        'to' => $to,
                        'duration' => $duration,
                        'reason' => $lr->reason,
                        'action' => 'team_member_on_leave_alert'
                    ];
                    processNotifications($notificationData, $recipientTeamMemberIds);
                }
            }
            return response()->json(['error' => false, 'message' => 'Leave request created successfully.', 'id' => $lr->id, 'type' => 'leave_request']);
        } else {
            return response()->json(['error' => true, 'message' => 'Leave request couldn\'t be created.']);
        }
    }

    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $user_ids = request('user_ids');
        $action_by_ids = request('action_by_ids');
        $types = request('types');
        $statuses = request('statuses');
        $start_date_from = (request('start_date_from')) ? request('start_date_from') : "";
        $start_date_to = (request('start_date_to')) ? request('start_date_to') : "";
        $end_date_from = (request('end_date_from')) ? request('end_date_from') : "";
        $end_date_to = (request('end_date_to')) ? request('end_date_to') : "";
        $where = ['workspace_id' => $this->workspace->id];
        if (!is_admin_or_leave_editor()) {
            // If the user is not an admin or leave editor, filter by user_id
            $where['user_id'] = $this->user->id;
        }
        $leave_requests = LeaveRequest::select(
            'leave_requests.*',
            'users.photo AS user_photo',
            DB::raw('CONCAT(users.first_name, " ", users.last_name) AS user_name'),
            DB::raw('CONCAT(action_users.first_name, " ", action_users.last_name) AS action_by_name')
        )
            ->leftJoin('users', 'leave_requests.user_id', '=', 'users.id')
            ->leftJoin('users AS action_users', 'leave_requests.action_by', '=', 'action_users.id');
        if (!empty($user_ids)) {
            $leave_requests = $leave_requests->whereIn('user_id', $user_ids);
        }
        if (!empty($action_by_ids)) {
            $leave_requests = $leave_requests->whereIn('action_by', $action_by_ids);
        }
        if (!empty($statuses)) {
            $leave_requests = $leave_requests->whereIn('leave_requests.status', $statuses);
        }
        if (!empty($types)) {
            $leave_requests = $leave_requests->where(function ($query) use ($types) {
                if (in_array('full', $types)) {
                    $query->orWhereNull('from_time')->whereNull('to_time');
                }
                if (in_array('partial', $types)) {
                    $query->orWhereNotNull('from_time')->whereNotNull('to_time');
                }
            });
        }
        if ($start_date_from && $start_date_to) {
            $leave_requests = $leave_requests->whereBetween('from_date', [$start_date_from, $start_date_to]);
        }
        if ($end_date_from && $end_date_to) {
            $leave_requests  = $leave_requests->whereBetween('to_date', [$end_date_from, $end_date_to]);
        }
        if ($search) {
            $leave_requests = $leave_requests->where(function ($query) use ($search) {
                $query->where('reason', 'like', '%' . $search . '%')
                    ->orWhere('leave_requests.id', 'like', '%' . $search . '%');
            });
        }
        $leave_requests->where($where);
        $total = $leave_requests->count();
        $isAdmin = $this->user->hasRole('admin');
        $isAdminOrLeaveEditor = is_admin_or_leave_editor();
        $leave_requests = $leave_requests->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($leave_request) use ($isAdmin, $isAdminOrLeaveEditor) {
                // Calculate the duration in hours if both from_time and to_time are provided
                $fromDate = Carbon::parse($leave_request->from_date);
                $toDate = Carbon::parse($leave_request->to_date);
                $fromDateDayOfWeek = $fromDate->format('D');
                $toDateDayOfWeek = $toDate->format('D');
                if ($leave_request->from_time && $leave_request->to_time) {
                    $duration = 0;
                    // Loop through each day
                    while ($fromDate->lessThanOrEqualTo($toDate)) {
                        // Create Carbon instances for the start and end times of the leave request for the current day
                        $fromDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $leave_request->from_time);
                        $toDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $leave_request->to_time);
                        // Calculate the duration for the current day and add it to the total duration
                        $duration += $fromDateTime->diffInMinutes($toDateTime) / 60; // Duration in hours
                        // Move to the next day
                        $fromDate->addDay();
                    }
                } else {
                    // Calculate the inclusive duration in days
                    $duration = $fromDate->diffInDays($toDate) + 1;
                }
                // Format "from_date" and "to_date" with labels
                $formattedDates = $duration > 1 ? format_date($leave_request->from_date) . ' ' . get_label('to', 'To') . ' ' . format_date($leave_request->to_date) : format_date($leave_request->from_date);
                $statusBadges = [
                    'pending' => '<span class="badge bg-warning">' . get_label('pending', 'Pending') . '</span>',
                    'approved' => '<span class="badge bg-success">' . get_label('approved', 'Approved') . '</span>',
                    'rejected' => '<span class="badge bg-danger">' . get_label('rejected', 'Rejected') . '</span>',
                ];
                $statusBadge = $statusBadges[$leave_request->status] ?? '';
                if ($leave_request->visible_to_all == 1) {
                    $visibleTo = 'All';
                } else {
                    $visibleTo = $leave_request->visibleToUsers->isEmpty()
                        ? '-'
                        : $leave_request->visibleToUsers->map(function ($user) {
                            $profileLink = route('users.show', ['id' => $user->id]);
                            return '<a href="' . $profileLink . '" target="_blank">' . $user->first_name . ' ' . $user->last_name . '</a>';
                        })->implode(', ');
                }
                $actions = '';
                if ($isAdmin || $leave_request->action_by === null) {
                    $actions .= '<a href="javascript:void(0);" class="edit-leave-request" data-bs-toggle="modal" data-bs-target="#edit_leave_request_modal" data-id=' . $leave_request->id . ' title=' . get_label('update', 'Update') . '><i class="bx bx-edit mx-1"></i></a>';
                }
                if ($isAdminOrLeaveEditor || $leave_request->status == 'pending') {
                    $actions .= '<button title=' . get_label('delete', 'Delete') . ' type="button" class="btn delete" data-id=' . $leave_request->id . ' data-type="leave-requests" data-table="lr_table">' .
                        '<i class="bx bx-trash text-danger mx-1"></i>' .
                        '</button>';
                }
                return [
                    'id' => $leave_request->id,
                    'user_name' => $leave_request->user_name . "<ul class='list-unstyled users-list m-0 avatar-group d-flex align-items-center'><a href='" . route('users.show', ['id' => $leave_request->user_id]) . "' target='_blank'><li class='avatar avatar-sm pull-up' title='{$leave_request->user_name}'>
            <img src='" . ($leave_request->user_photo ? asset('storage/' . $leave_request->user_photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle'>",
                    'action_by' => $leave_request->action_by_name,
                    'from_date' => $fromDateDayOfWeek . ', ' . ($leave_request->from_time ? format_date($leave_request->from_date . ' ' . $leave_request->from_time, true, null, null, false) : format_date($leave_request->from_date)),
                    'to_date' => $toDateDayOfWeek . ', ' . ($leave_request->to_time ? format_date($leave_request->to_date . ' ' . $leave_request->to_time, true, null, null, false) : format_date($leave_request->to_date)),
                    'type' => $leave_request->from_time && $leave_request->to_time ? '<span class="badge bg-info">' . get_label('partial', 'Partial') . '</span>' : '<span class="badge bg-primary">' . get_label('full', 'Full') . '</span>',
                    'duration' => $leave_request->from_time && $leave_request->to_time ? number_format($duration, 2) . ' hour' . ($duration > 1 ? 's' : '') : $duration . ' day' . ($duration > 1 ? 's' : ''),
                    'reason' => $leave_request->reason,
                    'status' => $statusBadge,
                    'visible_to' => $visibleTo,
                    'created_at' => format_date($leave_request->created_at, true),
                    'updated_at' => format_date($leave_request->updated_at, true),
                    'actions' => $actions ? $actions : '-'
                ];
            });
        return response()->json([
            "rows" => $leave_requests->items(),
            "total" => $total,
        ]);
    }
    /**
     * @group leaverequest Managemant
     *
     * List Leave Requests (all or by ID)
     *
     * This API returns either a paginated list of leave requests based on filters or a single leave request if an ID is provided.
     *
     * Requires authentication. Workspace must be set via header `workspace-id`.
     *
     * @queryParam isApi boolean Optional. Set to `true` for API mode. Default: true
     * @queryParam search string Optional. Search by leave reason or ID.
     * @queryParam sort string Optional. Column to sort by. Default: id
     * @queryParam order string Optional. Sort direction: ASC or DESC. Default: DESC
     * @queryParam user_ids array Optional. Filter by one or more user IDs.
     * @queryParam action_by_ids array Optional. Filter by action_by user IDs.
     * @queryParam types array Optional. Filter by types: full or partial.
     * @queryParam statuses array Optional. Filter by status: pending, approved, rejected.
     * @queryParam start_date_from date Optional. Start range for from_date filter.
     * @queryParam start_date_to date Optional. End range for from_date filter.
     * @queryParam end_date_from date Optional. Start range for to_date filter.
     * @queryParam end_date_to date Optional. End range for to_date filter.
     * @queryParam limit integer Optional. Results per page. Default: 10
     *@header workspace_id 2
     * @urlParam id int Optional. Leave request ID. If provided, returns only that leave request.
     *
     * @response 200 scenario="Single Leave Request Found" {
     *   "success": true,
     *   "message": "Leave request retrieved successfully.",
     *   "data": {
     *     "total": 1,
     *     "data": [
     *       {
     *         "id": 14,
     *         "user_name": "John Doe",
     *         "action_by": "Jane Smith",
     *         "from_date": "Mon, 2024-06-01",
     *         "to_date": "Tue, 2024-06-02",
     *         "type": "Full",
     *         "duration": "2 days",
     *         "reason": "Medical leave",
     *         "status": "<span class='badge bg-warning'>Pending</span>",
     *         "visible_to": "All",
     *         "created_at": "2024-05-15 10:30 AM",
     *         "updated_at": "2024-05-16 09:20 AM",
     *         "actions": "<a href=...>Edit</a> <button>Delete</button>"
     *       }
     *     ]
     *   }
     * }
     *
     * @response 200 scenario="List of Leave Requests" {
     *   "success": true,
     *   "message": "Leave requests retrieved successfully.",
     *   "data": {
     *     "total": 5,
     *     "data": [
     *       {
     *         "id": 1,
     *         "user_name": "John Doe",
     *         "action_by": "Jane Smith",
     *         "from_date": "Mon, 2024-06-01",
     *         "to_date": "Tue, 2024-06-02",
     *         "type": "Full",
     *         "duration": "2 days",
     *         "reason": "Annual Leave",
     *         "status": "<span class='badge bg-success'>Approved</span>",
     *         "visible_to": "All",
     *         "created_at": "2024-05-01 08:00 AM",
     *         "updated_at": "2024-05-02 09:00 AM",
     *         "actions": "<a href=...>Edit</a> <button>Delete</button>"
     *       }
     *     ]
     *   }
     * }
     *
     * @response 404 scenario="Leave request not found" {
     *   "success": false,
     *   "message": "Unable to retrieve leave requests.",
     *   "data": []
     * }
     *
     * @response 500 scenario="Server error or internal exception" {
     *   "success": false,
     *   "message": "Unable to retrieve leave requests.",
     *   "data": [],
     *   "error": "Call to undefined relationship [actionBy] on model [App\\Models\\LeaveRequest]."
     * }
     */

    public function listapi($id = null)
    {
        $isApi = request()->get('isApi', true); // default API mode

        try {
            if ($id) {
                $leaveRequest = LeaveRequest::with(['user', 'actionBy', 'visibleToUsers'])->findOrFail($id);
                return formatApiResponse(true, 'Leave request retrieved successfully.', [
                    'total' => 1,
                    'data' => [formatLeaveRequest($leaveRequest)]
                ]);
            }

            $search = request('search');
            $sort = request('sort', 'id');
            $order = request('order', 'DESC');
            $user_ids = request('user_ids');
            $action_by_ids = request('action_by_ids');
            $types = request('types');
            $statuses = request('statuses');
            $start_date_from = request('start_date_from');
            $start_date_to = request('start_date_to');
            $end_date_from = request('end_date_from');
            $end_date_to = request('end_date_to');

            $query = LeaveRequest::with(['user', 'actionBy', 'visibleToUsers'])
                ->where('workspace_id', $this->workspace->id);

            if (!is_admin_or_leave_editor()) {
                $query->where('user_id', $this->user->id);
            }

            if ($user_ids) {
                $query->whereIn('user_id', $user_ids);
            }

            if ($action_by_ids) {
                $query->whereIn('action_by', $action_by_ids);
            }

            if ($statuses) {
                $query->whereIn('status', $statuses);
            }

            if ($types) {
                $query->where(function ($q) use ($types) {
                    if (in_array('full', $types)) {
                        $q->orWhereNull('from_time')->whereNull('to_time');
                    }
                    if (in_array('partial', $types)) {
                        $q->orWhereNotNull('from_time')->whereNotNull('to_time');
                    }
                });
            }

            if ($start_date_from && $start_date_to) {
                $query->whereBetween('from_date', [$start_date_from, $start_date_to]);
            }

            if ($end_date_from && $end_date_to) {
                $query->whereBetween('to_date', [$end_date_from, $end_date_to]);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reason', 'like', '%' . $search . '%')
                        ->orWhere('id', 'like', '%' . $search . '%');
                });
            }

            $total = $query->count();

            $results = $query->orderBy($sort, $order)
                ->paginate(request('limit', 10))
                ->through(fn($leave) => formatLeaveRequest($leave));

            return formatApiResponse(true, 'Leave requests retrieved successfully.', [
                'total' => $total,
                'data' => $results->items()
            ]);
        } catch (\Exception $e) {
            dd($e);
            return formatApiResponse(false, 'Unable to retrieve leave requests.', [], 500, $e);
        }
    }

    public function get($id)
    {
        $lr = LeaveRequest::with('user')->findOrFail($id);
        // $lr = LeaveRequest::findOrFail($id);
        $visibleTo = $lr->visibleToUsers;
        return response()->json(['lr' => $lr, 'visibleTo' => $visibleTo]);
    }
    /**
     * Update Leave Request
     *
     * This endpoint allows authorized users to update an existing leave request.
     * Only admins or leave editors can change the status. Team members cannot approve their own leaves.
     * Leave requests already actioned (approved/rejected) can only be modified by an admin.
     *
     * @authenticated
     * @group leaverequest Managemant
     *
     * @bodyParam id integer required The ID of the leave request to update. Example: 12
     * @bodyParam reason string required Reason for leave. Example: Family emergency
     * @bodyParam from_date date required Start date of leave (in Y-m-d format). Must be before or equal to `to_date`. Example: 2025-06-10
     * @bodyParam to_date date required End date of leave (in Y-m-d format). Example: 2025-06-12
     * @bodyParam from_time string Optional. Required if partial leave is selected. Format: HH:MM. Example: 09:00
     * @bodyParam to_time string Optional. Required if partial leave is selected. Format: HH:MM. Example: 13:00
     * @bodyParam partialLeave string Optional. If "on", indicates it's a partial leave. Example: on
     * @bodyParam leaveVisibleToAll string Optional. If "on", the leave will be visible to all users. Example: on
     * @bodyParam visible_to_ids array Optional. List of user IDs who can view this leave (if leaveVisibleToAll is not set). Example: [2, 3, 4]
     * @bodyParam status string optional New status of the leave. Allowed values: pending, approved, rejected. Required if user is admin or leave editor. Example: approved
     *@header workspace_id 2
     * @response 200 {
     *   "error": false,
     *   "message": "Leave request updated successfully.",
     *   "data": {
     *     "id": 12,
     *     "data": {
     *       "id": 12,
     *       "user_id": 3,
     *       "reason": "Family emergency",
     *       "from_date": "2025-06-10",
     *       "to_date": "2025-06-12",
     *       "from_time": "09:00",
     *       "to_time": "13:00",
     *       "status": "approved",
     *       "action_by": 1,
     *       "visible_to_all": 1,
     *       "created_at": "2025-06-01T12:30:00.000000Z",
     *       "updated_at": "2025-06-06T10:45:00.000000Z"
     *     },
     *     "type": "leave_request"
     *   }
     * }
     *
     * @response 400 {
     *   "error": true,
     *   "message": "Missing or invalid input.",
     *   "details": {
     *     "from_date": [
     *       "The from date field is required."
     *     ]
     *   }
     * }
     *
     * @response 403 {
     *   "error": true,
     *   "message": "You cannot approve your own leave request."
     * }
     *
     * @response 403 {
     *   "error": true,
     *   "message": "Once actioned only admin can update leave request."
     * }
     *
     * @response 403 {
     *   "error": true,
     *   "message": "You cannot set the status to pending if it has already been approved or rejected."
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "Validation failed.",
     *   "details": {
     *     "status": [
     *       "The selected status is invalid."
     *     ]
     *   }
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while updating the leave request.",
     *   "details": "SQLSTATE[23000]: Integrity constraint violation..."
     * }
     */

    public function update(Request $request)
    {
        $isApi = $request->get('isApi', true);

        try {
            $isAdminOrLe = is_admin_or_leave_editor();

            $validatedData = $request->validate([
                'id' => 'required|exists:leave_requests,id',
                'reason' => ['required'],
                'from_date' => ['required', 'before_or_equal:to_date'],
                'to_date' => ['required'],
                'from_time' => ['required_if:partialLeave,on'],
                'to_time' => ['required_if:partialLeave,on'],
                'status' => $isAdminOrLe ? 'required|in:pending,approved,rejected' : 'nullable|in:pending,approved,rejected',
            ], [
                'from_time.required_if' => 'The from time field is required when partial leave is checked.',
                'to_time.required_if' => 'The to time field is required when partial leave is checked.',
            ]);

            $leaveRequest = LeaveRequest::findOrFail($validatedData['id']);
            $currentStatus = $leaveRequest->status;
            $newStatus = $validatedData['status'] ?? $currentStatus;

            if (!is_null($leaveRequest->action_by) && !$this->user->hasRole('admin')) {
                return formatApiResponse(false, 'Once actioned only admin can update leave request.', [], 403);
            }

            if (
                $leaveRequest->user_id == $this->user->id &&
                !$this->user->hasRole('admin') &&
                $request->filled('status') &&
                $request->input('status') == 'approved'
            ) {
                return formatApiResponse(false, 'You cannot approve your own leave request.', [], 403);
            }

            if (in_array($currentStatus, ['approved', 'rejected']) && $newStatus === 'pending') {
                return formatApiResponse(false, 'You cannot set the status to pending if it has already been approved or rejected.', [], 403);
            }

            $validatedData['from_date'] = format_date($validatedData['from_date'], false, app('php_date_format'), 'Y-m-d');
            $validatedData['to_date'] = format_date($validatedData['to_date'], false, app('php_date_format'), 'Y-m-d');

            if ($newStatus !== $currentStatus) {
                $validatedData['action_by'] = $this->user->id;
            }

            $validatedData['visible_to_all'] = $request->filled('leaveVisibleToAll') && $request->input('leaveVisibleToAll') == 'on' ? 1 : 0;

            if ($leaveRequest->update($validatedData)) {
                $leaveRequest = $leaveRequest->fresh();

                if ($validatedData['visible_to_all'] == 0) {
                    $leaveRequest->visibleToUsers()->sync($request->input('visible_to_ids', []));
                }

                if ($newStatus !== $currentStatus) {
                    $fromDate = Carbon::parse($leaveRequest->from_date);
                    $toDate = Carbon::parse($leaveRequest->to_date);

                    if ($leaveRequest->from_time && $leaveRequest->to_time) {
                        $duration = 0;
                        while ($fromDate->lte($toDate)) {
                            $fromDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $leaveRequest->from_time);
                            $toDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $leaveRequest->to_time);
                            $duration += $fromDateTime->diffInMinutes($toDateTime) / 60;
                            $fromDate->addDay();
                        }
                    } else {
                        $duration = $fromDate->diffInDays($toDate) + 1;
                    }

                    $leaveType = $leaveRequest->from_time ? get_label('partial', 'Partial') : get_label('full', 'Full');
                    $from = format_date($leaveRequest->from_date . ' ' . ($leaveRequest->from_time ?? ''), true);
                    $to = format_date($leaveRequest->to_date . ' ' . ($leaveRequest->to_time ?? ''), true);
                    $durationText = $leaveRequest->from_time ? "$duration hour" . ($duration > 1 ? 's' : '') : "$duration day" . ($duration > 1 ? 's' : '');

                    $user = User::find($leaveRequest->user_id);

                    $notificationData = [
                        'type' => 'leave_request_status_updation',
                        'type_id' => $leaveRequest->id,
                        'team_member_first_name' => $user->first_name,
                        'team_member_last_name' => $user->last_name,
                        'leave_type' => $leaveType,
                        'from' => $from,
                        'to' => $to,
                        'duration' => $durationText,
                        'reason' => $leaveRequest->reason,
                        'old_status' => ucfirst($currentStatus),
                        'new_status' => ucfirst($newStatus),
                        'action' => 'status_updated'
                    ];

                    $adminModelIds = DB::table('model_has_roles')->where('role_id', 1)->pluck('model_id')->toArray();
                    $leaveEditorIds = DB::table('leave_editors')->pluck('user_id')->toArray();
                    $recipients = array_merge(
                        array_map(fn($id) => 'u_' . $id, $adminModelIds),
                        array_map(fn($id) => 'u_' . $id, $leaveEditorIds),
                        ['u_' . $leaveRequest->user_id]
                    );

                    processNotifications($notificationData, $recipients);

                    if ($newStatus === 'approved') {
                        $appTz = config('app.timezone');
                        $now = new \DateTime('now', new \DateTimeZone($appTz));
                        $leaveEnd = new \DateTime($leaveRequest->to_date, new \DateTimeZone($appTz));
                        $leaveEnd->setTime(
                            $leaveRequest->to_time ? (int)substr($leaveRequest->to_time, 0, 2) : 23,
                            $leaveRequest->to_time ? (int)substr($leaveRequest->to_time, 3, 2) : 59
                        );

                        if ($now < $leaveEnd) {
                            $teamIds = $validatedData['visible_to_all']
                                ? $this->workspace->users->pluck('id')->toArray()
                                : array_merge($adminModelIds, $leaveEditorIds, $leaveRequest->visibleToUsers->pluck('id')->toArray());

                            $teamIds = array_diff($teamIds, [$leaveRequest->user_id]);
                            $teamUserIds = array_map(fn($id) => 'u_' . $id, $teamIds);

                            $alertData = [
                                'type' => 'team_member_on_leave_alert',
                                'type_id' => $leaveRequest->id,
                                'team_member_first_name' => $user->first_name,
                                'team_member_last_name' => $user->last_name,
                                'leave_type' => $leaveType,
                                'from' => $from,
                                'to' => $to,
                                'duration' => $durationText,
                                'reason' => $leaveRequest->reason,
                                'action' => 'team_member_on_leave_alert'
                            ];

                            processNotifications($alertData, $teamUserIds);
                        }
                    }
                }

                return formatApiResponse(false, 'Leave request updated successfully.', [
                    'id' => $leaveRequest->id,
                    'data' => formatLeaveRequest($leaveRequest)

                ]);
            }

            return formatApiResponse(false, 'Leave request could not be updated.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while updating the leave request.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update_editors(Request $request)
    {
        $userIds = $request->input('user_ids') ?? [];
        $currentLeaveEditorUserIds = LeaveEditor::pluck('user_id')->toArray();
        $usersToDetach = array_diff($currentLeaveEditorUserIds, $userIds);
        LeaveEditor::whereIn('user_id', $usersToDetach)->delete();
        foreach ($userIds as $assignedUserId) {
            // Check if a leave editor with the same user_id already exists
            $existingLeaveEditor = LeaveEditor::where('user_id', $assignedUserId)->first();
            if (!$existingLeaveEditor) {
                // Create a new LeaveEditor only if it doesn't exist
                $leaveEditor = new LeaveEditor();
                $leaveEditor->user_id = $assignedUserId;
                $leaveEditor->save();
            }
        }
        Session::flash('message', 'Leave editors updated successfully.');
        return response()->json(['error' => false]);
    }
    /**
     * Delete a leave request by ID.
     *
     * @group leaverequest Managemant
     *
     * @urlParam id int required The ID of the leave request to delete. Example: 1
     *@header workspace_id 2
     * @response 200 {
     *   "error": false,
     *   "message": "Leave request deleted successfully.",
     *   "id": 1,
     *   "type": "leave_request",
     *   "data": []
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "Leave request not found.",
     *   "data": []
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while deleting the leave request.",
     *   "data": []
     * }
     */

    public function destroy($id)
    {
        $isApi = request()->get('isApi', true); // Default to API response


        $leaveRequest = LeaveRequest::findOrFail($id);
        DeletionService::delete(LeaveRequest::class, $id, 'Leave request');
        return formatApiResponse(false, 'Leave request deleted successfully.', [
            'id' => $leaveRequest->id,
            'type' => 'leave_request',
            'data' => []

        ]);
    }




    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:leave_requests,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $deletedIds = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $deletedIds[] = $id;
            DeletionService::delete(LeaveRequest::class, $id, 'Leave request');
        }
        return response()->json(['error' => false, 'message' => 'Leave request(s) deleted successfully.', 'id' => $deletedIds, 'type' => 'leave_request']);
    }

    public function calendar_view()
    {
        return view('leave_requests.calendar_view');
    }

    public function get_calendar_data(Request $request)
    {
        // dd($request->all());
        // Parse date range with proper timezone handling
        $start = $request->query('date_from')
            ? format_date($request->query('date_from'), false, app('php_date_format'), 'Y-m-d')
            : Carbon::now()->startOfMonth();

        $end = $request->query('date_to')
            ? format_date($request->query('date_to'), false, app('php_date_format'), 'Y-m-d')
            : Carbon::now()->endOfMonth();

        // Retrieve leave requests based on user access
        $leaveRequestsQuery = isAdminOrHasAllDataAccess()
            ? $this->workspace->leave_requests()
            : $this->user->leave_requests();

        $statuses = $request->query('statuses', []);
        if (!is_array($statuses)) {
            $statuses = explode(',', $statuses);
        }
        if (!empty($statuses)) {
            $leaveRequestsQuery->whereIn('status', $statuses);
        }

        // Apply type filter
        $types = $request->query('types', []);
        if (!is_array($types)) {
            $types = explode(',', $types);
        }
        if (!empty($types)) {
            $leaveRequestsQuery->where(function ($query) use ($types) {
                if (in_array('full', $types)) {
                    $query->orWhereNull('from_time')->whereNull('to_time');
                }
                if (in_array('partial', $types)) {
                    $query->orWhereNotNull('from_time')->whereNotNull('to_time');
                }
            });
        }
        // dd($start, $end, $leaveRequestsQuery->get());
        // Apply date range filter
        $leave_requests = $leaveRequestsQuery->where(function ($query) use ($start, $end) {


            $query->whereBetween('from_date', [$start, $end])
                ->orWhereBetween('to_date', [$start, $end]);
        })->get();


        // Format leave request for FullCalendar
        $events = $leave_requests->map(function ($leave_request) {
            switch ($leave_request->status) {
                case 'approved':
                    $backgroundColor = '#4caf50';
                    $borderColor = '#4caf50';
                    $textColor = '#ffffff';
                    break;
                case 'pending':
                    $backgroundColor = '#ffeb3b';
                    $borderColor = '#ffeb3b';
                    $textColor = '#000000';
                    break;
                case 'rejected':
                    $backgroundColor = '#f44336';
                    $borderColor = '#f44336';
                    $textColor = '#ffffff';
                    break;
                default:
            }


            return [
                'id' => $leave_request->id,
                'title' => ucwords($leave_request->user->first_name . ' ' . $leave_request->user->last_name) . ' (' . ucwords($leave_request->status) . ')',
                'start' => $leave_request->from_date,
                'end' => $leave_request->to_date,
                'from_time' => $leave_request->from_time,
                'end_time' => $leave_request->to_time,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $borderColor,
                'textColor' => $textColor,
                'description' => "
            <strong>Reason:</strong> " . ucwords(Str::limit($leave_request->reason, 20, '....')) . "<br>
            <strong>Status:</strong> " . ucfirst($leave_request->status) . "<br>
           <strong>From:</strong> " . format_date($leave_request->from_date) . " at " . ($leave_request->from_time ? date('H:i', strtotime($leave_request->from_time)) : '00:00') . "<br>
<strong>To:</strong> " . format_date($leave_request->to_date) . " at " . ($leave_request->to_time ? date('H:i', strtotime($leave_request->to_time)) : '24:00'),
                'allDay' => false,
                'extendedProps' => [
                    'status' => $leave_request->status,
                ]
            ];
        });

        return response()->json($events);
    }

    public function get_leave_stats()
    {
        try {
            if (!$this->workspace || !$this->user) {
                return response()->json(['error' => true, 'message' => 'Missing workspace or user context.'], 400);
            }

            $query = isAdminOrHasAllDataAccess() ? $this->workspace->leave_requests() : $this->user->leave_requests();

            $pending = (clone $query)->where('status', 'pending')->count();
            $approved = (clone $query)->where('status', 'approved')->count();
            $rejected = (clone $query)->where('status', 'rejected')->count();
            $full = (clone $query)->whereNull('from_time')->whereNull('to_time')->count();
            $partial = (clone $query)->whereNotNull('from_time')->whereNotNull('to_time')->count();
            $total = $query->count();

            return response()->json([
                'pending' => $pending,
                'approved' => $approved,
                'rejected' => $rejected,
                'full' => $full,
                'partial' => $partial,
                'total' => $total,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Error fetching leave request stats.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
