<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Status;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Workspace;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Models\EstimatesInvoice;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Laravel\Sanctum\Sanctum;

class HomeController extends Controller
{
    protected $workspace;
    protected $user;
    protected $statuses;
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
         $this->statuses = Status::all();
    }

    public function index(Request $request)
    {

        $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects ?? [] : $this->user->projects ?? [];
        $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks ?? [] : $this->user->tasks() ?? [];
        $tasks = $tasks ? $tasks->count() : 0;
        $users = $this->workspace->users ?? [];
        $clients = $this->workspace->clients ?? [];
        $todos = $this->user->todos()->orderBy('id', 'desc')->paginate(5);
        $total_todos = $this->user->todos;
        $meetings = $this->user->meetings;
        if ($this->workspace) {
            $activities = $this->workspace->activity_logs()->orderBy('id', 'desc')->limit(10)->get();
        } else {
            $activities = collect(); // Return an empty collection to avoid errors
        }
        $statuses = Status::where("admin_id", getAdminIdByUserRole())->orWhereNull('admin_id')->get();
        return view('dashboard', ['users' => $users, 'clients' => $clients, 'projects' => $projects, 'tasks' => $tasks, 'todos' => $todos, 'total_todos' => $total_todos, 'meetings' => $meetings, 'auth_user' => $this->user, 'statuses' => $statuses, 'activities' => $activities]);
    }


   public function upcoming_birthdays()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "dob";
        $order = (request('order')) ? request('order') : "ASC";
        $upcoming_days = (int) request('upcoming_days', 30);
        $user_id = (request('user_id')) ? request('user_id') : "";

        $users = $this->workspace->users();

        // Calculate the current date
        $currentDate = today();
        $currentYear = $currentDate->format('Y');

        // Calculate the range for upcoming birthdays (e.g., 365 days from today)
        $upcomingDate = $currentDate->copy()->addDays($upcoming_days);

        $currentDateString = $currentDate->format('Y-m-d');
        $upcomingDateString = $upcomingDate->format('Y-m-d');

        $users = $users->whereRaw("DATE_ADD(DATE_FORMAT(dob, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(dob) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(dob, '%m-%d'), 1, 0) YEAR) BETWEEN ? AND ? AND DATEDIFF(DATE_ADD(DATE_FORMAT(dob, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(dob) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(dob, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) <= ?", [$currentDateString, $upcomingDateString, $upcoming_days])
            ->orderByRaw("DATEDIFF(DATE_ADD(DATE_FORMAT(dob, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(dob) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(dob, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) " . $order);
        // Search by full name (first name + last name)
        if (!empty($search)) {
            $users->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhere('dob', 'LIKE', "%$search%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }

        if (!empty($user_id)) {
            $users->where('users.id', $user_id);
        }

        $total = $users->count();
        // dd($total);

        $users = $users->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($user) use ($currentDate) {
                // Convert the 'dob' field to a DateTime object
                $birthdayDate = \Carbon\Carbon::createFromFormat('Y-m-d', $user->dob);

                // Set the year to the current year
                $birthdayDate->year = $currentDate->year;

                if ($birthdayDate->lt($currentDate)) {
                    // If the birthday has already passed this year, calculate for next year
                    $birthdayDate->year = $currentDate->year + 1;
                }

                // Calculate days left until the user's birthday
                $daysLeft = $currentDate->diffInDays($birthdayDate);

                $emoji = '';
                $label = '';

                if ($daysLeft === 0) {
                    $emoji = ' 🥳';
                    $label = ' <span class="badge bg-success">' . get_label('today', 'Today') . '</span>';
                } elseif ($daysLeft === 1) {
                    $label = ' <span class="badge bg-primary">' . get_label('tomorow', 'Tomorrow') . '</span>';
                } elseif ($daysLeft === 2) {
                    $label = ' <span class="badge bg-warning">' . get_label('day_after_tomorow', 'Day after tomorrow') . '</span>';
                }



                return [
                    'id' => $user->id,
                    'member' => $user->first_name . ' ' . $user->last_name . $emoji . "<ul class='list-unstyled users-list m-0 avatar-group d-flex align-items-center'><a href='".route('users.show' , ['id' =>  $user->id]) ."  ' target='_blank'><li class='avatar avatar-sm pull-up'  title='" . $user->first_name . " " . $user->last_name . "'>
                    <img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle'>",
                    'age' => $currentDate->diffInYears($birthdayDate),
                    'days_left' => $daysLeft,
                    'dob' => format_date($birthdayDate) . $label, // Format as needed
                ];
            });

        return response()->json([
            "rows" => $users->items(),
            "total" => $total,
        ]);
    }

/**
 * Get Upcoming Birthdays
 *
 * This endpoint retrieves a list of users within the current workspace whose birthdays are coming up within the next given number of days (default is 30).
 * It calculates the number of days left for each birthday and includes user details like name, photo, age, and profile link.
 * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @group Dashboard
 *
 * @authenticated
 *
 * @queryParam upcoming_days integer Optional. Number of upcoming days to look for birthdays. Defaults to 30. Example: 15
 * @queryParam isApi boolean Optional. Pass true to get formatted API response. Example: true
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Upcoming birthdays fetched successfully.",
 *   "data": {
 *     "data": [
 *       {
 *         "id": 5,
 *         "member": "John Doe",
 *         "dob": "1995-07-18",
 *         "days_left": 12,
 *         "age": 28,
 *         "photo": "http://example.com/storage/photos/user5.jpg",
 *         "profile_url": "http://example.com/users/5"
 *       }
 *     ],
 *     "total": 1
 *   }
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Internal Server Error: Something went wrong.",
 *   "data": [],
 *   "status_code": 500
 * }
 *
 * @return \Illuminate\Http\JsonResponse
 */


public function api_upcoming_birthdays(Request $request)
{
    $isApi = $request->get('isApi', false);

    try {
        $upcoming_days = (int) $request->input('upcoming_days', 30);
        $workspace = $this->workspace;

        if (!$workspace) {
            $message = 'Workspace not found.';
            return $isApi
                ? formatApiResponse(true, $message, [], 404)
                : response()->json(['error' => true, 'message' => $message], 404);
        }

        $now = today();
        $users = $workspace->users()->whereNotNull('dob')->get();

        $filtered = $users->filter(function ($user) use ($now, $upcoming_days) {
            try {
                $dob = \Carbon\Carbon::createFromFormat('Y-m-d', $user->dob);
            } catch (\Exception $e) {
                return false;
            }
            $birthday = $dob->copy()->year($now->year);
            if ($birthday->lt($now)) {
                $birthday->addYear();
            }
            return $now->diffInDays($birthday) <= $upcoming_days;
        });

        $result = $filtered->map(function ($user) use ($now) {
            $dob = \Carbon\Carbon::createFromFormat('Y-m-d', $user->dob);
            $birthday = $dob->copy()->year($now->year);
            if ($birthday->lt($now)) {
                $birthday->addYear();
            }
            $daysLeft = $now->diffInDays($birthday);

            return [
                'id' => $user->id,
                'member' => $user->first_name . ' ' . $user->last_name,
                'dob' => $user->dob,
                'days_left' => $daysLeft,
                'age' => $now->diffInYears($dob),
                'photo' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg'),
                'profile_url' => route('users.show', ['id' => $user->id]),
            ];
        })->values();

        if ($isApi) {
            return formatApiResponse(false, 'Upcoming birthdays fetched successfully.', [
                'total' => $result->count(),
                'data' => $result
            ]);
        } else {
            return response()->json([
                "total" => $result->count(),
                "rows" => $result,
            ]);
        }
    } catch (\Exception $e) {
        if ($isApi) {
            return formatApiResponse(true, 'Internal Server Error: ' . $e->getMessage(), [], 500);
        } else {
            return response()->json(['error' => true, 'message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }
}
    /**
     * Get Upcoming Work Anniversaries
     *@group Dashboard
     * Retrieves a paginated list of users who have work anniversaries (based on their date of joining) within a specified number of upcoming days.
     * This endpoint supports filtering, sorting, searching, and pagination.
     *
     *
     * @authenticated
     *
     * @queryParam search string Optional. Search term to filter users by first name, last name, full name, or date of joining. Example: John
     * @queryParam sort string Optional. Field to sort by. Default is "doj". Example: doj
     * @queryParam order string Optional. Sort order: ASC or DESC. Default is "ASC". Example: ASC
     * @queryParam upcoming_days integer Optional. Number of upcoming days to check. Default is 30. Example: 30
     * @queryParam user_id integer Optional. Filter by a specific user ID. Example: 15
     * @queryParam limit integer Optional. Number of results per page. Default is 15. Example: 10
     * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
     * @response 200 {
     *   "rows": [
     *     {
     *       "id": 1,
     *       "member": "Alice Smith 🥳<ul class='list-unstyled users-list m-0 avatar-group d-flex align-items-center'><a href='http://example.com/users/1' target='_blank'><li class='avatar avatar-sm pull-up' title='Alice Smith'><img src='http://example.com/storage/photos/alice.jpg' alt='Avatar' class='rounded-circle'>",
     *       "wa_date": "2025-05-19 <span class='badge bg-success'>Today</span>",
     *       "days_left": 0
     *     }
     *   ],
     *   "total": 25
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "order": ["The selected order is invalid. Must be ASC or DESC."],
     *     "sort": ["The selected sort field is invalid."],
     *     "upcoming_days": ["The upcoming_days must be an integer."],
     *     "limit": ["The limit must be a positive integer."]
     *   }
     * }
     *
     * @response 500 {
     *   "status": false,
     *   "message": "Internal server error. Please try again later."
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
public function api_upcoming_work_anniversaries(Request $request)
{
    try {
        $search = $request->get('search');
        // DD($search);
        $sort = $request->get('sort', 'doj');
        $order = strtolower($request->get('order', 'asc'));
        $upcoming_days = (int) $request->get('upcoming_days', 30);
        $user_id = $request->get('user_id');
        $page = max(1, (int) $request->get('page', 1));
        $limit = max(1, (int) $request->get('limit', 15));

        $workspace = $this->workspace;

        $today = \Carbon\Carbon::today()->startOfDay();
        $upcomingDate = $today->copy()->addDays($upcoming_days)->endOfDay();

        // Step 1: Build query
        $query = $workspace->users()->whereNotNull('doj');

        // Step 2: Apply search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $search = '%' . $search . '%';
                $q->where('first_name', 'like', $search)
                  ->orWhere('last_name', 'like', $search)
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$search]);
            });
        }

        // Step 3: Filter by user_id
        if ($user_id) {
            $query->where('users.id', $user_id);
        }

        // Step 4: Get matching users
        $users = $query->get();

        // Step 5: Filter by upcoming anniversary
        $filtered = $users->filter(function ($user) use ($today, $upcomingDate) {
            if (!$user->doj) return false;

            try {
                $doj = \Carbon\Carbon::parse($user->doj)->startOfDay();
                $anniversary = $doj->copy()->year($today->year);
                if ($anniversary->lt($today)) {
                    $anniversary->addYear();
                }

                return $anniversary->between($today, $upcomingDate);
            } catch (\Exception $e) {
                return false;
            }
        });

        // Step 6: Sort by anniversary date
        $sorted = $filtered->sortBy(function ($user) use ($today) {
            $doj = \Carbon\Carbon::parse($user->doj)->startOfDay();
            $anniversary = $doj->copy()->year($today->year);
            if ($anniversary->lt($today)) {
                $anniversary->addYear();
            }
            return $anniversary->timestamp;
        }, SORT_REGULAR, $order === 'desc');

        // Step 7: Paginate manually
        $total = $sorted->count();
        $paginated = $sorted->slice(($page - 1) * $limit, $limit)->values();

        // Step 8: Format response
        $today = \Carbon\Carbon::today();
        // dd($today); // Re-ensure same instance
        $data = $paginated->map(function ($user) use ($today) {
            $doj = \Carbon\Carbon::parse($user->doj)->startOfDay();
            // dd($doj);
            $anniversary = $doj->copy()->year($today->year);

            if ($anniversary->lt($today)) {
                $anniversary->addYear();
            }
            // dd($anniversary);
            $daysLeft = $today->diffInDays($anniversary);
            // dd($daysLeft);
            $label = '';
            $emoji = '';

            if ($daysLeft === 0) {
                $label = 'Today';
                $emoji = ' 🥳';
            } elseif ($daysLeft === 1) {
                $label = 'Tomorrow';
            } elseif ($daysLeft === 2) {
                $label = 'Day after tomorrow';
            }

            return [
                'id' => $user->id,
                'member' => $user->first_name . ' ' . $user->last_name . $emoji,
                'wa_date' => $anniversary->toDateString() . ($label ? " ($label)" : ''),
                'days_left' => $daysLeft,
                'photo_url' => $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg'),
                'profile_url' => route('users.show', ['id' => $user->id]),
            ];
        });

        return formatApiResponse(false, 'Upcoming work anniversaries fetched successfully.', [
            'total' => $total,
            // dd($data),
            'data' => $data,
            // dd($data),
        ]);
    } catch (\Exception $e) {
        return formatApiResponse(true, 'Something went wrong while fetching data.', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
}

   public function upcoming_work_anniversaries()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "doj";
        $order = (request('order')) ? request('order') : "ASC";
        $upcoming_days = (request('upcoming_days')) ? request('upcoming_days') : 30;
        $user_id = (request('user_id')) ? request('user_id') : "";
        $users = $this->workspace->users();

        $currentDate = today();
        $currentYear = $currentDate->format('Y');

        // Calculate the range for upcoming birthdays (e.g., 365 days from today)
        $upcomingDate = $currentDate->copy()->addDays($upcoming_days);

        $currentDateString = $currentDate->format('Y-m-d');
        $upcomingDateString = $upcomingDate->format('Y-m-d');

        $users = $users->whereRaw("DATE_ADD(DATE_FORMAT(doj, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(doj) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(doj, '%m-%d'), 1, 0) YEAR) BETWEEN ? AND ? AND DATEDIFF(DATE_ADD(DATE_FORMAT(doj, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(doj) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(doj, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) <= ?", [$currentDateString, $upcomingDateString, $upcoming_days])
            ->orderByRaw("DATEDIFF(DATE_ADD(DATE_FORMAT(doj, '%Y-%m-%d'), INTERVAL YEAR(CURRENT_DATE()) - YEAR(doj) + IF(DATE_FORMAT(CURRENT_DATE(), '%m-%d') > DATE_FORMAT(doj, '%m-%d'), 1, 0) YEAR), CURRENT_DATE()) " . $order);

        // Search by full name (first name + last name)
        if (!empty($search)) {
            $users->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhere('doj', 'LIKE', "%$search%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }
        if (!empty($user_id)) {
            $users->where('users.id', $user_id);
        }
        $total = $users->count();

        $users = $users->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($user) use ($currentDate) {
                // Convert the 'dob' field to a DateTime object
                $doj = \Carbon\Carbon::createFromFormat('Y-m-d', $user->doj);

                // Set the year to the current year
                $doj->year = $currentDate->year;

                if ($doj->lt($currentDate)) {
                    // If the birthday has already passed this year, calculate for next year
                    $doj->year = $currentDate->year + 1;
                }

                // Calculate days left until the user's birthday
                $daysLeft = $currentDate->diffInDays($doj);
                $label = '';
                $emoji = '';
                if ($daysLeft === 0) {
                    $emoji = ' 🥳';
                    $label = ' <span class="badge bg-success">' . get_label('today', 'Today') . '</span>';
                } elseif ($daysLeft === 1) {
                    $label = ' <span class="badge bg-primary">' . get_label('tomorow', 'Tomorrow') . '</span>';
                } elseif ($daysLeft === 2) {
                    $label = ' <span class="badge bg-warning">' . get_label('day_after_tomorow', 'Day after tomorrow') . '</span>';
                }


                return [
                    'id' => $user->id,
                    'member' => $user->first_name . ' ' . $user->last_name . $emoji . "<ul class='list-unstyled users-list m-0 avatar-group d-flex align-items-center'><a href='" . route('users.show' ,['id'=>$user->id]) . "' target='_blank'><li class='avatar avatar-sm pull-up'  title='" . $user->first_name . " " . $user->last_name . "'>
                    <img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle'>",
                    'wa_date' => format_date($doj) . $label, // Format as needed
                    'days_left' => $daysLeft,
                ];
            });

        return response()->json([
            "rows" => $users->items(),
            "total" => $total,
        ]);
    }


    /**
     * Get Members on Leave (Filtered & Paginated)
     *
     * Returns a paginated list of users who are currently on leave or scheduled to be on leave
     * within a specified number of upcoming days. Supports filtering by search term, sorting,
     * user ID, and respects permission-based visibility for the requesting user.
     *
     * @group Dashboard
     * @authenticated
     *
     * @queryParam search string Optional. Search by first name or last name. Example: Jane
     * @queryParam sort string Optional. Field to sort by. Default is `from_date`. Example: from_date
     * @queryParam order string Optional. Sort direction. Must be "ASC" or "DESC". Default is "ASC". Example: DESC
     * @queryParam upcoming_days integer Optional. Number of upcoming days to include. Default is 30. Example: 15
     * @queryParam user_id integer Optional. Filter by a specific user ID. Example: 5
     * @queryParam limit integer Optional. Number of records per page. Default is 15. Example: 10
     *@header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
     * @response 200 {
     *   "rows": [
     *     {
     *       "id": 12,
     *       "member": "John Doe <ul class='list-unstyled users-list ...'>...</ul>",
     *       "from_date": "Mon, May 20, 2025",
     *       "to_date": "Tue, May 21, 2025",
     *       "type": "<span class='badge bg-primary'>Full</span>",
     *       "duration": "2 days",
     *       "days_left": 1
     *     }
     *   ],
     *   "total": 15
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "order": ["The selected order is invalid. Allowed values are ASC or DESC."],
     *     "sort": ["The selected sort field is invalid."],
     *     "upcoming_days": ["The upcoming_days must be an integer."],
     *     "limit": ["The limit must be a positive integer."]
     *   }
     * }
     *
     * @response 500 {
     *   "status": false,
     *   "message": "Internal server error. Please try again later."
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */

public function members_on_leave(Request $request)
{
    try {
        $search = $request->get('search');
        $sort = $request->get('sort', 'from_date');
        $order = $request->get('order', 'ASC');
        $upcoming_days = (int) $request->get('upcoming_days', 30);
        $user_id = $request->get('user_id', '');
        $limit = min(max((int) $request->get('limit', 10), 1), 100);

        $currentDate = today();
        $upcomingDate = $currentDate->copy()->addDays($upcoming_days);
        $timezone = config('app.timezone');

        $leaveUsers = DB::table('leave_requests')
            ->selectRaw('*, leave_requests.user_id as UserId')
            ->leftJoin('users', 'leave_requests.user_id', '=', 'users.id')
            ->leftJoin('leave_request_visibility', 'leave_requests.id', '=', 'leave_request_visibility.leave_request_id')
            ->where(function ($query) use ($currentDate, $upcomingDate) {
                $query->where('from_date', '<=', $upcomingDate)
                    ->where('to_date', '>=', $currentDate);
            })
            ->where('leave_requests.status', 'approved')
            ->where('workspace_id', $this->workspace->id);

        if (!is_admin_or_leave_editor()) {
            $leaveUsers->where(function ($query) {
                $query->where('leave_requests.user_id', $this->user->id)
                    ->orWhere('leave_request_visibility.user_id', $this->user->id)
                    ->orWhere('leave_requests.visible_to_all', 1);
            });
        }

        if (!empty($search)) {
            $leaveUsers->where(function ($query) use ($search) {
                $query->where('first_name', 'LIKE', "%$search%")
                    ->orWhere('last_name', 'LIKE', "%$search%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
            });
        }

        if (!empty($user_id)) {
            $leaveUsers->where('leave_requests.user_id', $user_id);
        }

        $total = $leaveUsers->count();

        $leaveUsers = $leaveUsers->orderBy($sort, $order)
            ->paginate($limit)
            ->through(function ($user) use ($currentDate, $timezone) {
                $fromDateForDuration = \Carbon\Carbon::createFromFormat('Y-m-d', $user->from_date);
                $toDate = \Carbon\Carbon::createFromFormat('Y-m-d', $user->to_date);
                $fromDateForDiff = $fromDateForDuration->copy()->year($currentDate->year);

                $daysLeft = $currentDate->diffInDays($fromDateForDiff, false);
                if ($daysLeft < 0) {
                    $daysLeft = 0;
                }

                $currentTime = \Carbon\Carbon::now()->tz($timezone)->format('H:i:s');
                $label = '';

                if ($daysLeft === 0 && $user->from_time && $user->to_time && $user->from_time <= $currentTime && $user->to_time >= $currentTime) {
                    $label = ' <span class="badge bg-info">' . get_label('on_partial_leave', 'On Partial Leave') . '</span>';
                } elseif (($daysLeft === 0 && (!$user->from_time && !$user->to_time)) ||
                    ($daysLeft === 0 && $user->from_time <= $currentTime && $user->to_time >= $currentTime)) {
                    $label = ' <span class="badge bg-success">' . get_label('on_leave', 'On leave') . '</span>';
                } elseif ($daysLeft === 1) {
                    $langLabel = $user->from_time && $user->to_time
                        ? get_label('on_partial_leave_tomorrow', 'On partial leave from tomorrow')
                        : get_label('on_leave_tomorrow', 'On leave from tomorrow');
                    $label = ' <span class="badge bg-primary">' . $langLabel . '</span>';
                } elseif ($daysLeft === 2) {
                    $langLabel = $user->from_time && $user->to_time
                        ? get_label('on_partial_leave_day_after_tomorow', 'On partial leave from day after tomorrow')
                        : get_label('on_leave_day_after_tomorow', 'On leave from day after tomorrow');
                    $label = ' <span class="badge bg-warning">' . $langLabel . '</span>';
                }

                // Calculate duration
                if ($user->from_time && $user->to_time) {
                    $duration = 0;
                    $tempFromDate = $fromDateForDuration->copy();

                    while ($tempFromDate->lte($toDate)) {
                        $fromDateTime = \Carbon\Carbon::parse($tempFromDate->toDateString() . ' ' . $user->from_time);
                        $toDateTime = \Carbon\Carbon::parse($tempFromDate->toDateString() . ' ' . $user->to_time);
                        $diffMinutes = $fromDateTime->diffInMinutes($toDateTime);
                        if ($diffMinutes > 0) {
                            $duration += $diffMinutes / 60;
                        }
                        $tempFromDate->addDay();
                    }
                } else {
                    $duration = $fromDateForDuration->diffInDays($toDate) + 1;
                }

                $fromDateDayOfWeek = $fromDateForDuration->format('D');
                $toDateDayOfWeek = $toDate->format('D');

                return [
                    'id' => $user->UserId,
                    'member' => $user->first_name . ' ' . $user->last_name . ' ' . $label .
                        "<ul class='list-unstyled users-list m-0 avatar-group d-flex align-items-center'>
                            <a href='/users/profile/" . $user->UserId . "' target='_blank'>
                                <li class='avatar avatar-sm pull-up' title='" . $user->first_name . " " . $user->last_name . "'>
                                    <img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle'>
                                </li>
                            </a>
                        </ul>",
                    'from_date' => $fromDateDayOfWeek . ', ' . ($user->from_time ? format_date($user->from_date . ' ' . $user->from_time, true, null, null, false) : format_date($user->from_date)),
                    'to_date' => $toDateDayOfWeek . ', ' . ($user->to_time ? format_date($user->to_date . ' ' . $user->to_time, true, null, null, false) : format_date($user->to_date)),
                    'type' => $user->from_time && $user->to_time
                        ? '<span class="badge bg-info">' . get_label('partial', 'Partial') . '</span>'
                        : '<span class="badge bg-primary">' . get_label('full', 'Full') . '</span>',
                    'duration' => $user->from_time && $user->to_time
                        ? $duration . ' hour' . ($duration > 1 ? 's' : '')
                        : $duration . ' day' . ($duration > 1 ? 's' : ''),
                    'days_left' => $daysLeft,
                ];
            });

        return response()->json([
            "rows" => $leaveUsers->items(),
            "total" => $total,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => 'Internal Server Error: ' . $e->getMessage(),
        ], 500);
    }
}


    /**
     * Get Upcoming Birthdays in Calendar Format
     *
     * Returns a list of users whose birthdays fall within the specified date range.
     * Birthdays are adjusted to the current or next year based on the current date,
     * and formatted for calendar display with colors for styling.
     *
     * @group Dashboard
     * @authenticated
     *
     * @queryParam startDate date required Start date of the range (YYYY-MM-DD). Example: 2025-05-01
     * @queryParam endDate date required End date of the range (YYYY-MM-DD). Example: 2025-05-31
     *
     * @response 200 [
     *   {
     *     "userId": 1,
     *     "title": "Jane Doe's Birthday",
     *     "start": "2025-05-15",
     *     "backgroundColor": "#007bff",
     *     "borderColor": "#007bff",
     *     "textColor": "#ffffff"
     *   }
     * ]
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "startDate": ["The startDate field is required.", "The startDate is not a valid date."],
     *     "endDate": ["The endDate field is required.", "The endDate is not a valid date."]
     *   }
     * }
     *
     * @response 400 {
     *   "message": "Invalid date range. The endDate must be after the startDate."
     * }
     *
     * @response 500 {
     *   "status": false,
     *   "message": "Internal server error. Please try again later."
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function upcoming_birthdays_calendar(Request $request)
    {
        $startDate = Carbon::parse($request->startDate)->startOfDay();
        $endDate = Carbon::parse($request->endDate)->endOfDay();

        $users = $this->workspace->users()->get();
        $currentDate = today();

        $events = [];

        foreach ($users as $user) {
            if (!empty($user->dob)) {
                // Format the birthday date
                $birthdayDate = Carbon::createFromFormat('Y-m-d', $user->dob);

                // Set the year to the current year
                $birthdayDate->year = $currentDate->year;

                if ($birthdayDate->lt($currentDate)) {
                    // If the birthday has already passed this year, calculate for next year
                    $birthdayDate->year = $currentDate->year + 1;
                }

                $birthdayStartDate = $birthdayDate->copy()->startOfDay();
                $birthdayEndDate = $birthdayDate->copy()->endOfDay();

                // Check if the birthday falls within the requested date range
                if ($birthdayStartDate->between($startDate, $endDate)) {
                    // Prepare the event data
                    $event = [
                        'userId' => $user->id,
                        'title' => $user->first_name . ' ' . $user->last_name . '\'s Birthday',
                        'start' => $birthdayStartDate->format('Y-m-d'),
                        'backgroundColor' => '#007bff',
                        'borderColor' => '#007bff',
                        'textColor' => '#ffffff',
                    ];

                    // Add the event to the events array
                    $events[] = $event;
                }
            }
        }

        return response()->json($events);
    }
    /**
     * Get Upcoming Work Anniversaries (Calendar Format)
     *
     * Returns a list of users whose work anniversaries fall between the specified start and end dates.
     * The response is formatted for display in a calendar interface, including links to employee profiles.
     *
     * @group Dashboard
     * @authenticated
     *
     * @queryParam startDate date required The start date of the calendar range (YYYY-MM-DD). Example: 2025-05-01
     * @queryParam endDate date required The end date of the calendar range (YYYY-MM-DD). Example: 2025-05-31
     *
     * @response 200 [
     *   {
     *     "id": 1,
     *     "title": "🎉 John Doe's 5th Work Anniversary",
     *     "start": "2025-05-19",
     *     "end": "2025-05-19",
     *     "url": "/employee/1"
     *   },
     *   {
     *     "id": 2,
     *     "title": "🎉 Jane Smith's 3rd Work Anniversary",
     *     "start": "2025-05-22",
     *     "end": "2025-05-22",
     *     "url": "/employee/2"
     *   }
     * ]
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "startDate": ["The startDate field is required.", "The startDate is not a valid date."],
     *     "endDate": ["The endDate field is required.", "The endDate is not a valid date."]
     *   }
     * }
     *
     * @response 400 {
     *   "message": "Invalid date range. The endDate must be a date after startDate."
     * }
     *
     * @response 500 {
     *   "status": false,
     *   "message": "Internal server error. Please try again later."
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */


    public function upcoming_work_anniversaries_calendar(Request $request)
    {
        $startDate = Carbon::parse($request->startDate)->startOfDay();
        $endDate = Carbon::parse($request->endDate)->endOfDay();
        $users = $this->workspace->users()->get();

        // Calculate the current date
        $currentDate = today();

        $events = [];

        foreach ($users as $user) {
            if (!empty($user->doj)) {
                // Format the start date in the required format for FullCalendar
                $WADate = Carbon::createFromFormat('Y-m-d', $user->doj);

                // Set the anniversary date to the current year
                $WADate->year = $currentDate->year;

                if ($WADate->lt($currentDate)) {
                    // If the anniversary has already passed this year, calculate for next year
                    $WADate->year = $currentDate->year + 1;
                }

                $anniversaryDate = $WADate->copy();

                // Check if the anniversary falls within the requested date range
                if ($anniversaryDate->between($startDate, $endDate)) {
                    // Prepare the event data
                    $event = [
                        'userId' => $user->id,
                        'title' => $user->first_name . ' ' . $user->last_name . '\'s Work Anniversary',
                        'start' => $anniversaryDate->format('Y-m-d'),
                        'backgroundColor' => '#007bff',
                        'borderColor' => '#007bff',
                        'textColor' => '#ffffff',
                    ];

                    // Add the event to the events array
                    $events[] = $event;
                }
            }
        }

        return response()->json($events);
    }

    /**
     * Get Members on Leave (Calendar Format)
     *
     * Returns a list of approved leave requests for users within a specified date range.
     * The data is formatted for use in a calendar view. Users only see their own leave data
     * unless they have admin or leave editor privileges.
     *
     * @group Dashboard
     * @authenticated
     *
     * @queryParam startDate date required The start date of the calendar view (YYYY-MM-DD). Example: 2025-05-01
     * @queryParam endDate date required The end date of the calendar view (YYYY-MM-DD). Example: 2025-05-31
     *
     * @response 200 [
     *   {
     *     "userId": 1,
     *     "title": "John Doe - 09:00 AM to 05:00 PM",
     *     "start": "2025-05-19",
     *     "end": "2025-05-19",
     *     "startTime": "09:00:00",
     *     "endTime": "17:00:00",
     *     "backgroundColor": "#02C5EE",
     *     "borderColor": "#02C5EE",
     *     "textColor": "#ffffff"
     *   },
     *   {
     *     "userId": 2,
     *     "title": "Jane Smith",
     *     "start": "2025-05-22",
     *     "end": "2025-05-24",
     *     "startTime": null,
     *     "endTime": null,
     *     "backgroundColor": "#007bff",
     *     "borderColor": "#007bff",
     *     "textColor": "#ffffff"
     *   }
     * ]
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "startDate": ["The startDate field is required.", "The startDate is not a valid date."],
     *     "endDate": ["The endDate field is required.", "The endDate is not a valid date."]
     *   }
     * }
     *
     * @response 400 {
     *   "message": "Invalid date range. The endDate must be after startDate."
     * }
     *
     * @response 500 {
     *   "status": false,
     *   "message": "Internal server error. Please try again later."
     * }
     *
     * @return \Illuminate\Http\JsonResponse JSON response containing formatted leave request events for the calendar.
     */


    public function members_on_leave_calendar(Request $request)
    {
        $startDate = Carbon::parse($request->startDate)->startOfDay();
        $endDate = Carbon::parse($request->endDate)->endOfDay();
        $currentDate = today();

        $leaveRequests = DB::table('leave_requests')
            ->selectRaw('*, leave_requests.user_id as UserId')
            ->leftJoin('users', 'leave_requests.user_id', '=', 'users.id')
            ->leftJoin('leave_request_visibility', 'leave_requests.id', '=', 'leave_request_visibility.leave_request_id')
            ->where('to_date', '>=', $currentDate)
            ->where('leave_requests.status', '=', 'approved')
            ->where('workspace_id', '=', $this->workspace->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('from_date', '<=', $startDate)
                            ->where('to_date', '>=', $endDate);
                    });
            });
        // dd($leaveRequests);

        // Add condition to restrict results based on user roles
        if (!is_admin_or_leave_editor()) {
            $leaveRequests->where(function ($query) {
                $query->where('leave_requests.user_id', '=', $this->user->id)
                    ->orWhere('leave_request_visibility.user_id', '=', $this->user->id);
            });
        }

        $time_format = get_php_date_time_format(true);
        $time_format = str_replace(':s', '', $time_format);

        // Get leave requests and format for calendar
        $events = $leaveRequests->get()->map(function ($leave) use ($time_format) {
            $title = $leave->first_name . ' ' . $leave->last_name;
            if ($leave->from_time && $leave->to_time) {
                // If both start and end times are present, format them according to the desired format
                $formattedStartTime = \Carbon\Carbon::createFromFormat('H:i:s', $leave->from_time)->format($time_format);
                $formattedEndTime = \Carbon\Carbon::createFromFormat('H:i:s', $leave->to_time)->format($time_format);
                $title .= ' - ' . $formattedStartTime . ' to ' . $formattedEndTime;
                $backgroundColor = '#02C5EE';
            } else {
                $backgroundColor = '#007bff';
            }
            return [
                'userId' => $leave->UserId,
                'title' => $title,
                'start' => $leave->from_date,
                'end' => $leave->to_date,
                'startTime' => $leave->from_time,
                'endTime' => $leave->to_time,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $backgroundColor,
                'textColor' => '#ffffff'
            ];
        });

        return response()->json($events);
    }
    /**
     * Get Income vs Expense Data.
     *
     * Returns the total income (from fully paid invoices) and total expenses for a given date range.
     * Data access is based on the user's role. Admins or users with full access can view all data,
     * while others are restricted to their own records.
     *
     * @group Dashboard
     * @authenticated
     *
     * @queryParam start_date date optional The start date for filtering data (YYYY-MM-DD). Example: 2025-05-01
     * @queryParam end_date date optional The end date for filtering data (YYYY-MM-DD). Example: 2025-05-31
     *
     * @response 200 {
     *   "total_income": "12000.00",
     *   "total_expenses": "4500.00",
     *   "date_label": "May 1, 2025 - May 31, 2025",
     *   "currency_symbol": "$"
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "start_date": ["The start_date is not a valid date."],
     *     "end_date": ["The end_date is not a valid date."]
     *   }
     * }
     *
     * @response 400 {
     *   "message": "Invalid date range. The end_date must be after start_date."
     * }
     *
     * @response 500 {
     *   "status": false,
     *   "message": "Internal server error. Please try again later."
     * }
     *
     * @return \Illuminate\Http\JsonResponse JSON object containing total income, total expenses,
     *                                       the date range label, and the currency symbol.
     */


    public function income_vs_expense_data(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Determine whether the user has admin access or all data access
        $estimates_invoices = isAdminOrHasAllDataAccess() ?
            $this->workspace->estimates_invoices() :
            $this->user->estimates_invoices();

        // Start building the income query
        $totalIncomeQuery = $estimates_invoices
            ->where('status', 'fully_paid')
            ->where('type', 'invoice');

        // Apply date filtering if both start and end dates are provided
        if ($startDate && $endDate) {
            $totalIncomeQuery->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate]);
            });
        }

        // Calculate total income
        $totalIncome = $totalIncomeQuery->sum('final_total');

        // Start building the expenses query
        $expenses = $this->workspace->expenses();

        // If the user doesn't have admin access, apply user-based filtering to expenses
        if (!isAdminOrHasAllDataAccess()) {
            $expenses->where(function ($query) {
                $query->where('expenses.created_by', isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id)
                    ->orWhere('expenses.user_id', $this->user->id);
            });
        }

        // Apply date filtering to expenses if both start and end dates are provided
        if ($startDate && $endDate) {
            $expenses->whereBetween('expense_date', [$startDate, $endDate]);
        }

        // Calculate total expenses
        $totalExpenses = $expenses->sum('amount');

        // Format numbers to 2 decimal places
        $totalIncome = number_format($totalIncome, 2, '.', '');
        $totalExpenses = number_format($totalExpenses, 2, '.', '');
        $dateLabel = $startDate && $endDate
            ? format_date(Carbon::parse($startDate))  . ' - ' . format_date(Carbon::parse($endDate))
            : get_label('all_time', 'All Time');
        // Return the income and expenses as JSON
        return response()->json([
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'date_label' => $dateLabel,
            'currency_symbol' => get_settings('general_settings')['currency_symbol'],
        ]);
    }

    /**
 * Get Dashboard Data
 *
 * This endpoint returns a comprehensive dashboard summary for the authenticated user within the selected workspace.
 * It includes counts and detailed lists of users, clients, projects, tasks, to-dos, meetings, activities, and statuses.
 *
 * @group Dashboard
 *
 *@authenticated
 *@header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2

 *
 * @response 200 {
 *   "error": false,
 *   "message": "Dashboard data fetched successfully.",
 *   "data": {
 *     "counts": {
 *       "users_count": 5,
 *       "clients_count": 3,
 *       "projects_count": 8,
 *       "tasks_count": 22,
 *       "todos_count": 5,
 *       "meetings_count": 2,
 *       "statuses_count": 6,
 *       "activities_count": 10
 *     },
 *     "users": [...],
 *     "clients": [...],
 *     "projects": [...],
 *     "tasks": [...],
 *     "todos": [...],
 *     "total_todos": [...],
 *     "meetings": [...],
 *     "auth_user": {
 *       "id": 1,
 *       "first_name": "John",
 *       "last_name": "Doe",
 *       ...
 *     },
 *     "statuses": [...],
 *     "activities": [...]
 *   }
 * }
 *
 * @response 400 {
 *   "error": true,
 *   "message": "Missing or invalid workspace-id header.",
 *   "data": []
 * }
 *
 * @response 401 {
 *   "error": true,
 *   "message": "Unauthorized: User not authenticated.",
 *   "data": []
 * }
 *
 * @response 404 {
 *   "error": true,
 *   "message": "Workspace not found.",
 *   "data": []
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Something went wrong.",
 *   "data": {
 *     "line": 123,
 *     "file": "app/Http/Controllers/DashboardController.php",
 *     "error": "Exception message here"
 *   }
 * }
 *
 * @return \Illuminate\Http\JsonResponse
 */

    public function apiDashboard(Request $request)
    {
        $isApi = $request->get('isApi', true); // Default to true for API usage

        try {
            $user = auth('sanctum')->user(); // Explicitly using Sanctum

            if (!$user) {
                $message = 'Unauthorized: User not authenticated.';
                return $isApi
                    ? formatApiResponse(true, $message, [], 401)
                    : response()->json(['error' => true, 'message' => $message], 401);
            }

            $workspaceId = $request->header('workspace-id') ?? session('workspace_id');

            if (!$workspaceId) {
                $message = 'Missing or invalid workspace-id header.';
                return $isApi
                    ? formatApiResponse(true, $message, [], 400)
                    : response()->json(['error' => true, 'message' => $message], 400);
            }

            $workspace = Workspace::find($workspaceId);

            if (!$workspace) {
                $message = 'Workspace not found.';
                return $isApi
                    ? formatApiResponse(true, $message, [], 404)
                    : response()->json(['error' => true, 'message' => $message], 404);
            }

            $isAdmin = isAdminOrHasAllDataAccess();

            $projects = $isAdmin ? ($workspace->projects ?? []) : ($user->projects ?? []);
            $tasks = $isAdmin ? ($workspace->tasks ?? []) : ($user->tasks ?? []);
            $users = $workspace->users ?? [];
            $clients = $workspace->clients ?? [];

            $todos = $user->todos()->orderBy('id', 'desc')->paginate(5);
            $totalTodos = $user->todos;
            $meetings = $user->meetings;

            $activities = $workspace
                ? $workspace->activity_logs()->orderBy('id', 'desc')->limit(10)->get()
                : collect();

            $statuses = Status::where("admin_id", getAdminIdByUserRole())
                ->orWhereNull('admin_id')
                ->get();

            $data = [
                // Counts first
                'counts' => [
                    'users_count' => count($users),
                    'clients_count' => count($clients),
                    'projects_count' => count($projects),
                    'tasks_count' => $tasks ? $tasks->count() : 0,
                    'todos_count' => $totalTodos->count(),
                    'meetings_count' => $meetings->count(),
                    'statuses_count' => $statuses->count(),
                    'activities_count' => $activities->count(),
                ],


            ];

            return $isApi
                ? formatApiResponse(false, 'Dashboard data fetched successfully.', $data)
                : response()->json(['error' => false, 'message' => 'Dashboard data fetched successfully.', 'data' => $data]);
        } catch (\Exception $e) {
            $message = 'Something went wrong.';
            $errorData = [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'error' => $e->getMessage()
            ];

            return $isApi
                ? formatApiResponse(true, $message, $errorData, 500)
                : response()->json(['error' => true, 'message' => $message] + $errorData, 500);
        }
    }

     public function getStatistics()
    {
        try {
            // Define an array of colors
            $colors = [
                '#63ed7a',
                '#ffa426',
                '#fc544b',
                '#6777ef',
                '#FF00FF',
                '#53ff1a',
                '#ff3300',
                '#0000ff',
                '#00ffff',
                '#99ff33',
                '#003366',
                '#cc3300',
                '#ffcc00',
                '#ff9900',
                '#3333cc',
                '#ffff00',
                '#FF5733',
                '#33FF57',
                '#5733FF',
                '#FFFF33',
                '#A6A6A6',
                '#FF99FF',
                '#6699FF',
                '#666666',
                '#FF6600',
                '#9900CC',
                '#FF99CC',
                '#FFCC99',
                '#99CCFF',
                '#33CCCC',
                '#CCFFCC',
                '#99CC99',
                '#669999',
                '#CCCCFF',
                '#6666FF',
                '#FF6666',
                '#99CCCC',
                '#993366',
                '#339966',
                '#99CC00',
                '#CC6666',
                '#660033',
                '#CC99CC',
                '#CC3300',
                '#FFCCCC',
                '#6600CC',
                '#FFCC33',
                '#9933FF',
                '#33FF33',
                '#FFFF66',
                '#9933CC',
                '#3300FF',
                '#9999CC',
                '#0066FF',
                '#339900',
                '#666633',
                '#330033',
                '#FF9999',
                '#66FF33',
                '#6600FF',
                '#FF0033',
                '#009999',
                '#CC0000',
                '#999999',
                '#CC0000',
                '#CCCC00',
                '#00FF33',
                '#0066CC',
                '#66FF66',
                '#FF33FF',
                '#CC33CC',
                '#660099',
                '#663366',
                '#996666',
                '#6699CC',
                '#663399',
                '#9966CC',
                '#66CC66',
                '#0099CC',
                '#339999',
                '#00CCCC',
                '#CCCC99',
                '#FF9966',
                '#99FF00',
                '#66FF99',
                '#336666',
                '#00FF66',
                '#3366CC',
                '#CC00CC',
                '#00FF99',
                '#FF0000',
                '#00CCFF',
                '#000000',
                '#FFFFFF'
            ];

            // Initialize response data
            $statusCountsProjects = [];
            $statusCountsTasks = [];
            $total_projects_count = 0;
            $total_tasks_count = 0;
            $total_users_count = 0;
            $total_clients_count = 0;
            $total_todos_count = 0;
            $total_completed_todos_count = 0;
            $total_pending_todos_count = 0;
            $total_meetings_count = 0;

            // Fetch total counts
            if ($this->user->can('manage_projects')) {
                $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects ?? [] : $this->user->projects ?? [];
                $total_projects_count = $projects->count();
            }

            if ($this->user->can('manage_tasks')) {
                $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks ?? [] : $this->user->tasks() ?? [];
                $total_tasks_count = $tasks->count();
            }

            if ($this->user->can('manage_users')) {
                $users = $this->workspace->users ?? [];
                $total_users_count = count($users);
            }

            if ($this->user->can('manage_clients')) {
                $clients = $this->workspace->clients ?? [];
                $total_clients_count = count($clients);
            }

            $todos = $this->user->todos;
            $total_todos_count = $todos->count();
            $total_completed_todos_count = $todos->where('is_completed', true)->count();
            $total_pending_todos_count = $todos->where('is_completed', false)->count();

            if ($this->user->can('manage_meetings')) {
                $meetings = isAdminOrHasAllDataAccess() ? $this->workspace->meetings ?? [] : $this->user->meetings ?? [];
                $total_meetings_count = $meetings->count();
            }

          if ($this->user->can('manage_projects')) {
    foreach ($this->statuses as $status) {
        $projectCount = isAdminOrHasAllDataAccess()
            ? \App\Models\Project::where('workspace_id', $this->workspace->id)->where('status_id', $status->id)->count()
            : $this->user->projects()->where('status_id', $status->id)->count();

        $statusCountsProjects[] = [
            'id' => $status->id,
            'title' => $status->title,
            'color' => $status->color,
            'chart_color' => '0Xff' . strtoupper(ltrim($colors[array_rand($colors)], '#')),
            'total_projects' => $projectCount
        ];
    }
    usort($statusCountsProjects, fn($a, $b) => $b['total_projects'] <=> $a['total_projects']);
}

// Assign colors to status-wise tasks
if ($this->user->can('manage_tasks')) {
    foreach ($this->statuses as $status) {
        $taskCount = isAdminOrHasAllDataAccess()
            ? \App\Models\Task::where('workspace_id', $this->workspace->id)->where('status_id', $status->id)->count()
            : $this->user->tasks()->where('status_id', $status->id)->count();

        $statusCountsTasks[] = [
            'id' => $status->id,
            'title' => $status->title,
            'color' => $status->color,
            'chart_color' => '0Xff' . strtoupper(ltrim($colors[array_rand($colors)], '#')),
            'total_tasks' => $taskCount
        ];
    }
    usort($statusCountsTasks, fn($a, $b) => $b['total_tasks'] <=> $a['total_tasks']);
}

            // Return response
            return formatApiResponse(
                false,
                'Statistics retrieved successfully.',
                [
                    'data' => [
                        'total_projects' => $total_projects_count,
                        'total_tasks' => $total_tasks_count,
                        'total_users' => $total_users_count,
                        'total_clients' => $total_clients_count,
                        'total_meetings' => $total_meetings_count,
                        'total_todos' => $total_todos_count,
                        'completed_todos' => $total_completed_todos_count,
                        'pending_todos' => $total_pending_todos_count,
                        'status_wise_projects' => $statusCountsProjects,
                        'status_wise_tasks' => $statusCountsTasks
                    ]
                ]
            );

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while retrieving statistics: ' . $e->getMessage(),
            ], 500);
        }
    }
}
