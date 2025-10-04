<?php

namespace App\Http\Controllers;

use PDO;
use Exception;
use Carbon\Carbon;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Status;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Priority;
use App\Models\TaskList;
use App\Models\Workspace;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\CommentAttachment;
use App\Models\CustomField;
use App\Services\DeletionService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\UserClientPreference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
//  use App\Http\Controllers\Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Request as FacadesRequest;


class TasksController extends Controller
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
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id = '')
    {
        $project = (object)[];
        if ($id) {
            $project = Project::findOrFail($id);
            $tasks = $project->tasks;
            $toSelectTaskUsers = $project->users;
        } else {
            $toSelectTaskUsers = $this->workspace->users;
            $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks : $this->user->tasks();
        }
        $tasks = $tasks->count();
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects : $this->user->projects;
        $customFields = CustomField::where('module', 'task')->get();
        $taskLists = TaskList::all();
        // dd($this->workspace->admin_id);
        return view('tasks.tasks', ['project' => $project, 'tasks' => $tasks, 'users' => $users, 'clients' => $clients, 'projects' => $projects, 'toSelectTaskUsers' => $toSelectTaskUsers, 'customFields' => $customFields,'taskLists'=> $taskLists]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id = '')
    {
        $project = (object)[];
        $projects = [];
        if ($id) {
            $project = Project::find($id);
            $users = $project->users;
        } else {
            $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects : $this->user->projects;
            $users = $this->workspace->users;
        }
        $statuses = Status::where('admin_id', getAdminIdByUserRole())->orWhere(function ($query) {
            $query->whereNull('admin_id')
                ->where('is_default', 1);
        })
            ->get();
        return view('tasks.create_task', ['project' => $project, 'projects' => $projects, 'users' => $users, 'statuses' => $statuses]);
    }
    /**
     * Create a new task
     *
     * This endpoint allows you to create a new task within a workspace and assign it to users.
     * It supports additional features like setting reminders and recurring schedules.
     *
     * @group Task Management
     *
     * @bodyParam title string required The title of the task. Example: Create new onboarding flow
     * @bodyParam status_id integer required The ID of the task status. Example: 1
     * @bodyParam start_date date required The start date in `YYYY-MM-DD` format. Example: 2025-06-01
     * @bodyParam due_date date required The due date in `YYYY-MM-DD` format. Example: 2025-06-06
     * @bodyParam description string The description of the task. Example: Implement onboarding UI and logic.
     * @bodyParam project integer required The ID of the project to which the task belongs. Example: 15
     * @bodyParam priority_id integer The ID of the priority level. Must exist in priorities table. Example: 2
     * @bodyParam note string Optional note for the task. Example: Coordinate with HR and DevOps.
     * @bodyParam billing_type string The billing type (none, billable, non-billable). Example: billable
     * @bodyParam completion_percentage integer required Completion in steps of 10. One of: 0,10,...,100. Example: 0
     * @bodyParam users_id array User IDs assigned to the task. Example: [2, 3]
     * @bodyParam enable_reminder string Enable reminders. Must be 'on' if enabled. Example: on
     * @bodyParam frequency_type string Frequency of reminder (daily, weekly, monthly). Example: weekly
     * @bodyParam day_of_week integer Day of the week for reminders (1 = Monday). Example: 2
     * @bodyParam day_of_month integer Day of the month for reminders. Example: 15
     * @bodyParam time_of_day string Time of day for reminder (HH:MM). Example: 09:00
     * @bodyParam enable_recurring_task string Enable recurring task. Must be 'on' if enabled. Example: on
     * @bodyParam recurrence_frequency string Frequency (daily, weekly, monthly, yearly). Example: monthly
     * @bodyParam recurrence_day_of_week integer Day of the week for recurrence. Example: 3
     * @bodyParam recurrence_day_of_month integer Day of the month for recurrence. Example: 10
     * @bodyParam recurrence_month_of_year integer Month of the year for recurrence. Example: 6
     * @bodyParam recurrence_starts_from date Date from which recurrence starts. Must be today or future. Example: 2025-06-03
     * @bodyParam recurrence_occurrences integer Number of occurrences. Example: 5
     * @bodyParam task_list_id integer The ID of the task list (if any). Must exist in task_lists table. Example: 1
     * @header workspace_id 2
     * @response 200 scenario="Task created successfully" {
     *   "error": false,
     *   "message": "Task created successfully.",
     *   "id": 28,
     *   "type": "task",
     *   "parent_id": 15,
     *   "parent_type": "project",
     *   "Data": {
     *     "id": 28,
     *     "workspace_id": 2,
     *     "title": "Create new onboarding flow",
     *     "status": "Open",
     *     "status_id": 1,
     *     "priority": "low",
     *     "priority_id": 2,
     *     "users": [
     *       {
     *         "id": 2,
     *         "first_name": "herry",
     *         "last_name": "porter",
     *         "email": "admin@gmail.com",
     *         "photo": "http://localhost:8000/storage/photos/no-image.jpg"
     *       },
     *       {
     *         "id": 3,
     *         "first_name": "John",
     *         "last_name": "Doe",
     *         "email": "admin2@gmail.com",
     *         "photo": "http://localhost:8000/storage/photos/no-image.jpg"
     *       }
     *     ],
     *     "user_id": [2, 3],
     *     "clients": [
     *       {
     *         "id": 1,
     *         "first_name": "jerry",
     *         "last_name": "ginny",
     *         "email": "jg@gmail.com",
     *         "photo": "http://localhost:8000/storage/photos/gqHsvgmDBCbtf843SRYx31e6Zl51amPZY8eG05FB.jpg"
     *       }
     *     ],
     *     "start_date": "2015-01-01",
     *     "due_date": "2025-06-06",
     *     "project": "New Project Title",
     *     "project_id": 15,
     *     "description": "Implement onboarding UI and logic.",
     *     "note": "Coordinate with HR and DevOps.",
     *     "favorite": 0,
     *     "client_can_discuss": null,
     *     "created_at": "2025-06-03",
     *     "updated_at": "2025-06-03",
     *     "enable_reminder": 1,
     *     "last_reminder_sent": null,
     *     "frequency_type": "weekly",
     *     "day_of_week": 2,
     *     "day_of_month": 15,
     *     "time_of_day": "09:00:00",
     *     "enable_recurring_task": 1,
     *     "recurrence_frequency": "monthly",
     *     "recurrence_day_of_week": 3,
     *     "recurrence_day_of_month": 10,
     *     "recurrence_month_of_year": 6,
     *     "recurrence_starts_from": "2025-06-03",
     *     "recurrence_occurrences": 5,
     *     "completed_occurrences": null,
     *     "billing_type": "billable",
     *     "completion_percentage": 0,
     *     "task_list_id": null
     *   }
     * }
     *
     * @response 422 scenario="Validation error" {
     *   "error": true,
     *   "message": "Invalid date format. Please use yyyy-mm-dd.",
     *   "exception": "InvalidArgumentException message here..."
     * }
     *
     * @response 500 scenario="Unexpected server error" {
     *   "error": true,
     *   "message": "An error occurred while creating the task. SQLSTATE[23000]: Integrity constraint violation: 1452..."
     * }
     */

    public function store(Request $request)
    {
        $isApi = $request->get('isApi', false);

        try {
            $adminId = getAdminIdByUserRole();

            $formFields = $request->validate([
                'title' => ['required'],
                'status_id' => ['required'],
                'start_date' => ['required'],
                'due_date' => ['required'],
                'description' => ['nullable'],
                'project' => ['required'],
                'priority_id' => 'nullable|exists:priorities,id',
                'parent_id'=>'nullable',
                'note' => 'nullable|string',
                'billing_type' => 'nullable|in:none,billable,non-billable',
                'completion_percentage' => ['required', 'integer', 'min:0', 'max:100', 'in:0,10,20,30,40,50,60,70,80,90,100'],
                'enable_reminder' => 'nullable|in:on',
                'frequency_type' => 'nullable|in:daily,weekly,monthly',
                'day_of_week' => 'nullable|integer|between:1,7',
                'day_of_month' => 'nullable|integer|between:1,31',
                'time_of_day' => 'nullable|date_format:H:i',
                'enable_recurring_task' => 'nullable|in:on',
                'recurrence_frequency' => 'nullable|in:daily,weekly,monthly,yearly',
                'recurrence_day_of_week' => 'nullable|integer|min:1|max:7',
                'recurrence_day_of_month' => 'nullable|integer|min:1|max:31',
                'recurrence_month_of_year' => 'nullable|integer|min:1|max:12',
                'recurrence_starts_from' => 'nullable|date|after_or_equal:today',
                'recurrence_occurrences' => 'nullable|integer|min:1',
                'task_list_id' => 'nullable|exists:task_lists,id',
            ]);

            $status = Status::findOrFail($request->input('status_id'));


            if (!canSetStatus($status)) {
                return response()->json(['error' => true, 'message' => 'You are not authorized to set this status.']);
            }

            $project_id = $request->input('project');
            $start_date = $request->input('start_date');
            $due_date = $request->input('due_date');
            $recurrence_starts_from = $request->input('recurrence_starts_from');

            try {
                if ($isApi) {
                    $formFields['start_date'] = Carbon::createFromFormat('Y-m-d', $start_date)->format('Y-m-d');
                    $formFields['due_date'] = Carbon::createFromFormat('Y-m-d', $due_date)->format('Y-m-d');
                    $formFields['recurrence_starts_from'] = $recurrence_starts_from
                        ? Carbon::createFromFormat('Y-m-d', $recurrence_starts_from)->format('Y-m-d')
                        : null;
                } else {
                    $formFields['start_date'] = format_date($start_date, false, app('php_date_format'), 'Y-m-d');
                    $formFields['due_date'] = format_date($due_date, false, app('php_date_format'), 'Y-m-d');
                    $formFields['recurrence_starts_from'] = $recurrence_starts_from
                        ? format_date($recurrence_starts_from, false, app('php_date_format'), 'Y-m-d')
                        : null;
                }
            } catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => 'Invalid date format. Please use yyyy-mm-dd.',
                    'exception' => $e->getMessage()
                ], 422);
            }

            $formFields['admin_id'] = $adminId;
            $formFields['workspace_id'] = getWorkspaceId();
            $formFields['created_by'] = $this->user->id;
            $formFields['project_id'] = $project_id;
            $formFields['parent_id'] = $request->input('parent_id');

            $userIds = $request->input('users_id', []);

            $new_task = Task::create($formFields);
            $task_id = $new_task->id;
            $task = Task::find($task_id);
            $task->users()->attach($userIds, ['admin_id' => $adminId]);


            if ($request->has('custom_fields')) {
                foreach ($request->input('custom_fields') as $fieldId => $value) {
                    // Handle checkbox arrays
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }

                    $task->customFields()->create([
                        'custom_field_id' => $fieldId,
                        'value' => $value
                    ]);
                }
            }

            $task->statusTimelines()->create([
                'status' => $status->title,
                'new_color' => $status->color,
                'previous_status' => '-',
                'changed_at' => now(),
            ]);

            if ($formFields['enable_reminder'] ?? false) {
                $task->reminders()->create([
                    'frequency_type' => $formFields['frequency_type'],
                    'day_of_week' => $formFields['day_of_week'],
                    'day_of_month' => $formFields['day_of_month'],
                    'time_of_day' => $formFields['time_of_day'],
                ]);
            }

            if ($formFields['enable_recurring_task'] ?? false) {
                $task->recurringTask()->create([
                    'frequency' => $formFields['recurrence_frequency'],
                    'day_of_week' => $formFields['recurrence_day_of_week'],
                    'day_of_month' => $formFields['recurrence_day_of_month'],
                    'month_of_year' => $formFields['recurrence_month_of_year'],
                    'starts_from' => $formFields['recurrence_starts_from'],
                    'number_of_occurrences' => $formFields['recurrence_occurrences'],
                ]);
            }

            $notification_data = [
                'type' => 'task',
                'type_id' => $task_id,
                'type_title' => $task->title,
                'access_url' => 'tasks/information/' . $task->id,
                'action' => 'assigned',
                'title' => 'New task assigned',
                'message' => $this->user->first_name . ' ' . $this->user->last_name . ' assigned you new task : ' . $task->title . ', ID #' . $task_id . '.'
            ];

            $recipients = !empty($userIds) ? array_map(fn($userId) => 'u_' . $userId, $userIds) : [];
            processNotifications($notification_data, $recipients);
            // dd(processNotifications($notification_data, $recipients ));

            return response()->json([
                'error' => false,
                'message' => 'Task created successfully.',
                'id' => $new_task->id,
                'type' => 'task',
                'parent_id' => $project_id,
                'parent_type' => 'project',
                'data' => formatTask($task)
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while creating the task. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id)
    {   
        $task = Task::findOrFail($id);
        // dd($task->id);
        $toSelectTaskUsers = $this->workspace->users;
        $taskLists = TaskList::all();
        $customFields = CustomField::where('module', 'task')->get();
        return view('tasks.task_information', ['task' => $task, 'auth_user' => $this->user , 'taskLists' => $taskLists ,'customFields' => $customFields ],compact('toSelectTaskUsers'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id)
    {
        $task = Task::findOrFail($id);
        $project = $task->project;
        $users = $task->project->users;
        $task_users = $task->users;
        $statuses = Status::where("admin_id", getAdminIdByUserRole())->get();
        $tags = Tag::where('admin_id', getAdminIdByUserRole());
        return view('tasks.update_task', ["project" => $project, "task" => $task, "users" => $users, "task_users" => $task_users, 'statuses' => $statuses, 'tags' => $tags]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Update an existing task.
     * @group Task Management
     * This API endpoint updates a task with given details including reminders and recurring configurations.
     * It handles:
     * - Status change tracking with status timelines.
     * - Optional reminder creation or update.
     * - Optional recurring task creation or update.
     * - User reassignment with notification dispatching.
     *
     * @bodyParam id integer required The ID of the task to update. Example: 25
     * @bodyParam title string required The title of the task. Example: Test Task Title
     * @bodyParam description string Optional description for the task. Example: This is a test task description.
     * @bodyParam status_id integer required The status ID associated with the task. Example: 15
     * @bodyParam priority_id integer nullable The priority ID associated with the task. Example: 4
     * @bodyParam start_date date required Start date of the task (must be before or equal to due_date). Example: 2025-06-01
     * @bodyParam due_date date required Due date of the task. Example: 2025-06-10
     * @bodyParam note string nullable Optional notes related to the task.
     * @bodyParam billing_type string nullable Must be one of: none, billable, non-billable. Example: billable
     * @bodyParam completion_percentage integer required Completion in steps of 10. Must be between 0 and 100. Example: 0
     * @bodyParam user_id array Optional. Array of user IDs to assign to the task. Example: [2, 3]
     *
     * @bodyParam enable_reminder string Optional. Pass "on" to enable reminders. Example: on
     * @bodyParam frequency_type string Optional. Reminder frequency. One of: daily, weekly, monthly. Example: weekly
     * @bodyParam day_of_week integer Nullable. Day of the week if frequency is weekly (1=Monday ... 7=Sunday). Example: 3
     * @bodyParam day_of_month integer Nullable. Day of the month if frequency is monthly. Example: 15
     * @bodyParam time_of_day string Nullable. Time for the reminder (HH:MM format). Example: 09:00
     *
     * @bodyParam enable_recurring_task string Optional. Pass "on" to enable recurring tasks. Example: on
     * @bodyParam recurrence_frequency string Optional. One of: daily, weekly, monthly, yearly. Example: monthly
     * @bodyParam recurrence_day_of_week integer Nullable. Used if recurrence_frequency is weekly. Example: 5
     * @bodyParam recurrence_day_of_month integer Nullable. Used if recurrence_frequency is monthly. Example: 10
     * @bodyParam recurrence_month_of_year integer Nullable. Used if recurrence_frequency is yearly. Example: 6
     * @bodyParam recurrence_starts_from date Nullable. Start date for recurring tasks. Must be today or future. Example: 2025-06-13
     * @bodyParam recurrence_occurrences integer Nullable. Number of occurrences for recurrence. Example: 12
     *
     * @header workspace_id 2
     * @authenticated
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Task updated successfully.",
     *   "data": {
     *     "id": 25,
     *     "parent_id": 2,
     *     "parent_type": "project",
     *     "data": {
     *       "id": 25,
     *       "title": "Test Task Title",
     *       "status_id": 15,
     *       "priority_id": 4,
     *       "completion_percentage": 0,
     *       ...
     *     }
     *   }
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": {
     *     "title": [
     *       "The title field is required."
     *     ],
     *     "start_date": [
     *       "The start date must be before or equal to due date."
     *     ]
     *   }
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while updating the task. [Error Details]"
     * }
     */



    public function update(Request $request)
    {

        $isApi = $request->get('isApi', false);
        try {
            $formFields = $request->validate([
                'id' => 'required|exists:tasks,id',
                'title' => ['required'],
                'status_id' => ['required'],
                'priority_id' => ['nullable'],
                'start_date' => ['required', 'before_or_equal:due_date'],
                'due_date' => ['required'],
                'description' => ['nullable'],
                'note' => ['nullable'],
                'billing_type' => 'nullable|in:none,billable,non-billable',
                'completion_percentage' => ['required', 'integer', 'min:0', 'max:100', 'in:0,10,20,30,40,50,60,70,80,90,100'],
                'enable_reminder' => 'nullable|in:on', // Validation for reminder toggle
                'frequency_type' => 'nullable|in:daily,weekly,monthly',
                'day_of_week' => 'nullable|integer|between:1,7',
                'day_of_month' => 'nullable|integer|between:1,31',
                'time_of_day' => 'nullable|date_format:H:i',
                'enable_recurring_task' => 'nullable|in:on', // Validation for recurring task
                'recurrence_frequency' => 'nullable|in:daily,weekly,monthly,yearly',
                'recurrence_day_of_week' => 'nullable|integer|min:1|max:7',
                'recurrence_day_of_month' => 'nullable|integer|min:1|max:31',
                'recurrence_month_of_year' => 'nullable|integer|min:1|max:12',
                'recurrence_starts_from' => 'nullable|date|after_or_equal:today',
                'recurrence_occurrences' => 'nullable|integer|min:1',
                'task_list_id' => 'nullable|exists:task_lists,id',
            ]);
            $status = Status::findOrFail($request->input('status_id'));
            $id = $request->input('id');
            $task = Task::findOrFail($id);
            $currentStatusId = $task->status_id;
            // Check if the status has changed
            if ($currentStatusId != $request->input('status_id')) {
                $status = Status::findOrFail($request->input('status_id'));
                if (!canSetStatus($status)) {
                    return response()->json(['error' => true, 'message' => 'You are not authorized to set this status.']);
                }
                $oldStatus = Status::findOrFail($currentStatusId);
                $task->statusTimelines()->create([
                    'status' => $status->title,
                    'new_color' => $status->color,
                    'previous_status' => $oldStatus->title,
                    'old_color' => $oldStatus->color,
                    'changed_at' => now()
                ]);
            }

            if ($request->has('custom_fields')) {
                foreach ($request->custom_fields as $field_id => $value) {
                    // Handle checkboxes (arrays)
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }

                    // Find existing custom field value or create new
                    $fieldValue = $task->customFields()
                        ->where('custom_field_id', $field_id)
                        ->first();

                    if ($fieldValue) {
                        $fieldValue->update(['value' => $value]);
                    } else {
                        $task->customFields()->create([
                            'custom_field_id' => $field_id,
                            'value' => $value
                        ]);
                    }
                }
            }

            // Check Task Reminder is On than add the reminder
            if (isset($formFields['enable_reminder']) && $formFields['enable_reminder'] == "on") {
                // Check if reminder exists
                $reminder = $task->reminders()->first();
                if ($reminder) {
                    // Update existing reminder
                    $reminder->update([
                        'frequency_type' => $formFields['frequency_type'],
                        'day_of_week' => $formFields['frequency_type'] == 'weekly' ? $formFields['day_of_week'] : null,
                        'day_of_month' => $formFields['frequency_type'] == 'monthly' ? $formFields['day_of_month'] : null,
                        'time_of_day' => $formFields['time_of_day'],
                        'is_active' => 1
                    ]);
                } else {
                    // Create new reminder
                    $task->reminders()->create([
                        'frequency_type' => $formFields['frequency_type'],
                        'day_of_week' => $formFields['frequency_type'] == 'weekly' ? $formFields['day_of_week'] : null,
                        'day_of_month' => $formFields['frequency_type'] == 'monthly' ? $formFields['day_of_month'] : null,
                        'time_of_day' => $formFields['time_of_day'],
                        'is_active' => 1
                    ]);
                }
            } else {
                // If reminder is turned off, either delete or deactivate the reminder
                $reminder = $task->reminders()->first();
                if ($reminder) {
                    //Deactivate the reminder
                    $reminder->update(['is_active' => 0]);
                }
            }
            if (isset($formFields['enable_recurring_task']) && $formFields['enable_recurring_task'] == "on") {
                // Check if recurring task exists
                $recurringTask = $task->recurringTask()->first();
                if ($recurringTask) {
                    // Update existing recurring task
                    $recurringTask->update([
                        'frequency' => $formFields['recurrence_frequency'],
                        'day_of_week' =>  $formFields['recurrence_day_of_week'],
                        'day_of_month' =>  $formFields['recurrence_day_of_month'],
                        'month_of_year' => $formFields['recurrence_month_of_year'],
                        'starts_from' => $formFields['recurrence_starts_from'],
                        'number_of_occurrences' => $formFields['recurrence_occurrences'],
                        'is_active' => 1
                    ]);
                } else {
                    // Create new recurring task
                    $task->recurringTask()->create([
                        'frequency' => $formFields['recurrence_frequency'],
                        'day_of_week' => $formFields['recurrence_frequency'] == 'weekly' ? $formFields['recurrence_day_of_week'] : null,
                        'day_of_month' => $formFields['recurrence_frequency'] == 'monthly' ? $formFields['recurrence_day_of_month'] : null,
                        'month_of_year' => $formFields['recurrence_frequency'] == 'yearly' ? $formFields['recurrence_month_of_year'] : null,
                        'starts_from' => $formFields['recurrence_starts_from'],
                        'number_of_occurrences' => $formFields['recurrence_occurrences'],
                    ]);
                }
            } else {
                // If recurring task is turned off, either delete or deactivate the recurring task
                $recurringTask = $task->recurringTask()->first();
                if ($recurringTask) {
                    //Deactivate the reminder
                    $recurringTask->update(['is_active' => 0]);
                }
            }
            $start_date = $request->input('start_date');
            $due_date = $request->input('due_date');
            $formFields['start_date'] = format_date($start_date, false, app('php_date_format'), 'Y-m-d');
            $formFields['due_date'] = format_date($due_date, false, app('php_date_format'), 'Y-m-d');
            $userIds = $request->input('user_id', []);
            $task = Task::findOrFail($id);
            $task->update($formFields);
            // Get the current users associated with the task
            $currentUsers = $task->users->pluck('id')->toArray();
            $currentClients = $task->project->clients->pluck('id')->toArray();
            // Sync the users for the task
            $task->users()->sync($userIds);
            // Get the new users associated with the task
            $newUsers = array_diff($userIds, $currentUsers);
            // Prepare notification data for new users
            $notification_data = [
                'type' => 'task',
                'type_id' => $id,
                'type_title' => $task->title,
                'access_url' => 'tasks/information/' . $task->id,
                'action' => 'assigned',
                'title' => 'Task updated',
                'message' => $this->user->first_name . ' ' . $this->user->last_name . ' assigned you new task : ' . $task->title . ', ID #' . $id . '.'
            ];
            // Notify only the new users
            $recipients = array_map(function ($userId) {
                return 'u_' . $userId;
            }, $newUsers);
            // Process notifications for new users
            processNotifications($notification_data, $recipients);
            if ($currentStatusId != $request->input('status_id')) {
                $currentStatus = Status::findOrFail($currentStatusId);
                $newStatus = Status::findOrFail($request->input('status_id'));
                $notification_data = [
                    'type' => 'task_status_updation',
                    'type_id' => $id,
                    'type_title' => $task->title,
                    'updater_first_name' => $this->user->first_name,
                    'updater_last_name' => $this->user->last_name,
                    'old_status' => $currentStatus->title,
                    'new_status' => $newStatus->title,
                    'access_url' => 'tasks/information/' . $id,
                    'action' => 'status_updated',
                    'title' => 'Task status updated',
                    'message' => $this->user->first_name . ' ' . $this->user->last_name . ' has updated the status of task : ' . $task->title . ', ID #' . $id . ' from ' . $currentStatus->title . ' to ' . $newStatus->title
                ];
                $currentRecipients = array_merge(
                    array_map(function ($userId) {
                        return 'u_' . $userId;
                    }, $currentUsers),
                    array_map(function ($clientId) {
                        return 'c_' . $clientId;
                    }, $currentClients)
                );
                processNotifications($notification_data, $currentRecipients);
            }

            $task = $task->fresh();
            //
            return formatApiResponse(
                false,
                'Task updated successfully.',
                [
                    'id' => $task->id,
                    'parent_id' => $task->project->id,
                    'parent_type' => 'project',
                    'data' => formatTask($task),
                    // dd($task)
                ]
            );
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while updating  the task.' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Delete a specific task by ID.
     *@group Task Management
     * This endpoint deletes a task along with its associated comments and their attachments.
     * All attachments are deleted from the `public` storage disk.
     * Uses the DeletionService to handle the final removal process.
     * @header workspace_id 2
     * @urlParam id integer required The ID of the task to delete. Example: 23
     *
     * @response 200 scenario="Task deleted successfully" {
     *   "error": false,
     *   "message": "Task deleted successfully.",
     *   "data": {
     *     "id": 23,
     *     "title": "Update client onboarding flow",
     *     "parent_id": 2,
     *     "parent_type": "project",
     *     "data": []
     *   }
     * }
     *
     * @response 404 scenario="Task not found" {
     *   "error": true,
     *   "message": "Task not found.",
     *   "data": []
     * }
     *
     * @response 500 scenario="Unexpected error" {
     *   "error": true,
     *   "message": "An unexpected error occurred while deleting the task.",
     *   "data": []
     * }
     */



    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return formatApiResponse(
                true,
                'Task not found.',
                []
            );
        }

        // Delete attachments from storage and their records
        $comments = $task->comments()->with('attachments')->get();
        $comments->each(function ($comment) {
            $comment->attachments->each(function ($attachment) {
                Storage::disk('public')->delete($attachment->file_path);
                $attachment->delete();
            });
        });

        // Force delete comments
        $task->comments()->forceDelete();

        // Use your deletion service to delete the task
        DeletionService::delete(Task::class, $id, 'Task');

        return formatApiResponse(
            false,
            'Task deleted successfully.',
            [
                'id' => $id,
                'title' => $task->title,
                'parent_id' => $task->project_id,
                'parent_type' => 'project',
                'data' => []
            ]
        );
    }


    /**
     * Delete multiple tasks
     *
     * This endpoint deletes multiple tasks by their IDs. All associated comments and attachments are also permanently removed.
     *
     * @group Task Management
     *
     * @bodyParam ids array required An array of task IDs to be deleted. Example: [101, 102, 103]
     * @bodyParam ids.* integer The ID of an individual task to delete. Must exist in the tasks table.
     * @header workspace_id 2
     * @response 200 {
     *   "error": false,
     *   "message": "Task(s) deleted successfully.",
     *   "id": [101, 102],
     *   "titles": ["Task One", "Task Two"],
     *   "parent_id": [5, 6],
     *   "parent_type": "project"
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "ids.0": ["The selected ids.0 is invalid."]
     *   }
     * }
     */

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:tasks,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $deletedTasks = [];
        $deletedTaskTitles = [];
        $parentIds = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $task = Task::find($id);
            $comments = $task->comments()->with('attachments')->get();
            $comments->each(function ($comment) {
                $comment->attachments->each(function ($attachment) {
                    Storage::disk('public')->delete($attachment->file_path);
                    $attachment->delete();
                });
            });
            $task->comments()->forceDelete();
            if ($task) {
                $deletedTaskTitles[] = $task->title;
                DeletionService::delete(Task::class, $id, 'Task');
                $deletedTasks[] = $id;
                $parentIds[] = $task->project_id;
            }
        }
        return response()->json(['error' => false, 'message' => 'Task(s) deleted successfully.', 'id' => $deletedTasks, 'titles' => $deletedTaskTitles, 'parent_id' => $parentIds, 'parent_type' => 'project']);
    }

    public function list($id = '')
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $status_ids = request('status_ids', []);
        $priority_ids = request('priority_ids', []);
        $user_ids = request('user_ids', []);
        $client_ids = request('client_ids', []);
        $project_ids = request('project_ids', []);
        $start_date_from = (request('task_start_date_from')) ? trim(request('task_start_date_from')) : "";
        $start_date_to = (request('task_start_date_to')) ? trim(request('task_start_date_to')) : "";
        $end_date_from = (request('task_end_date_from')) ? trim(request('task_end_date_from')) : "";
        $end_date_to = (request('task_end_date_to')) ? trim(request('task_end_date_to')) : "";
        $labelNote = get_label('note', 'Note');
        $customFields = CustomField::where('module', 'task')->get();
        $task_parent_id = request('task_parent_id');

        $where = [];
        if ($id) {
            $id = explode('_', $id);
            $belongs_to = $id[0];
            $belongs_to_id = $id[1];
            if ($belongs_to == 'project') {
                $project = Project::find($belongs_to_id);
                $tasks = $project->tasks();
            } else {
                $userOrClient = $belongs_to == 'user' ? User::find($belongs_to_id) : Client::find($belongs_to_id);
                $tasks = isAdminOrHasAllDataAccess($belongs_to, $belongs_to_id) ? $this->workspace->tasks() : $userOrClient->tasks();
            }
        } else {
            $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks() : $this->user->tasks();
        }
        if (!empty($user_ids)) {
            $taskIds = DB::table('task_user')
                ->whereIn('user_id', $user_ids)
                ->pluck('task_id')
                ->toArray();
            $tasks = $tasks->whereIn('id', $taskIds);
        }
        if (!empty($client_ids)) {
            $projectIds = DB::table('client_project')
                ->whereIn('client_id', $client_ids)
                ->pluck('project_id')
                ->toArray();
            $tasks = $tasks->whereIn('project_id', $projectIds);
        }
        if (!empty($project_ids)) {
            $tasks->whereIn('project_id', $project_ids);
        }
        if (!empty($status_ids)) {
            $tasks->whereIn('status_id', $status_ids);
        }
        if (!empty($priority_ids)) {
            $tasks->whereIn('priority_id', $priority_ids);
        }
        if ($start_date_from && $start_date_to) {
            $tasks->whereBetween('start_date', [$start_date_from, $start_date_to]);
        }
        if ($end_date_from && $end_date_to) {
            $tasks->whereBetween('due_date', [$end_date_from, $end_date_to]);
        }
        if ($search) {
            $tasks = $tasks->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        // Apply where clause to $tasks
        $tasks = $tasks->where($where);

        if ($task_parent_id) {
            // Fetch only subtasks for given parent
            $tasks = $tasks->where('parent_id', $task_parent_id);
        } else {
            // Fetch only parent tasks (exclude subtasks)
            $tasks = $tasks->whereNull('parent_id');
        }

        // Count total tasks before pagination
        $totaltasks = $tasks->count();
        $canCreate = checkPermission('create_tasks');
        $canEdit = checkPermission('edit_tasks');
        $canDelete = checkPermission('delete_tasks');
        $statuses = Status::where('admin_id', getAdminIDByUserRole())->orWhere(function ($query) {
            $query->whereNull('admin_id')
                ->where('is_default', 1);
        })->get();
        $priorities = Priority::where('admin_id', getAdminIDByUserRole())->get();

        // Paginate tasks and format them - NOTICE THE ADDED $customFields HERE
        $tasks = $tasks->orderBy($sort, $order)->paginate(request('limit'))->through(function ($task) use ($statuses, $priorities, $canEdit, $canDelete, $canCreate, $labelNote, $customFields) {
            $statusOptions = '';
            foreach ($statuses as $status) {
                $disabled = canSetStatus($status)  ? '' : 'disabled';
                $selected = $task->status_id == $status->id ? 'selected' : '';
                $statusOptions .= "<option value='{$status->id}' class='badge bg-label-{$status->color}' {$selected} {$disabled}>{$status->title}</option>";
            }
            $priorityOptions = '';
            foreach ($priorities as $priority) {
                $selectedPriority = $task->priority_id == $priority->id ? 'selected' : '';
                $priorityOptions .= "<option value='{$priority->id}' class='badge bg-label-{$priority->color}' {$selectedPriority}>{$priority->title}</option>";
            }
            $actions = '';
            if ($canEdit) {
                $actions .= '<a href="javascript:void(0);" class="edit-task" data-id="' . $task->id . '" title="' . get_label('update', 'Update') . '">' .
                    '<i class="bx bx-edit mx-1"></i>' .
                    '</a>';
            }
            if ($canDelete) {
                $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $task->id . '" data-type="tasks" data-table="task_table">' .
                    '<i class="bx bx-trash text-danger mx-1"></i>' .
                    '</button>';
            }
            if ($canCreate) {
                $actions .= '<a href="javascript:void(0);" class="duplicate" data-id="' . $task->id . '" data-title="' . $task->title . '" data-type="tasks" data-table="task_table" title="' . get_label('duplicate', 'Duplicate') . '">' .
                    '<i class="bx bx-copy text-warning mx-2"></i>' .
                    '</a>';
            }
            $actions .= '<a href="javascript:void(0);" class="quick-view" data-id="' . $task->id . '" title="' . get_label('quick_view', 'Quick View') . '">' .
                '<i class="bx bx-info-circle mx-3"></i>' .
                '</a>';
            $actions = $actions ?: '-';
            $userHtml = '';
            if (!empty($task->users) && count($task->users) > 0) {
                $userHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                foreach ($task->users as $user) {
                    $userHtml .= "<li class='avatar avatar-sm pull-up'><a href='" . route('users.show', ['id' => $user->id]) . "' target='_blank' title='{$user->first_name} {$user->last_name}'><img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                }
                if ($canEdit) {
                    $userHtml .= '<li title=' . get_label('update', 'Update') . '><a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-task update-users-clients" data-id="' . $task->id . '"><span class="bx bx-edit"></span></a></li>';
                }
                $userHtml .= '</ul>';
            } else {
                $userHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
                if ($canEdit) {
                    $userHtml .= '<a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-task update-users-clients" data-id="' . $task->id . '">' .
                        '<span class="bx bx-edit"></span>' .
                        '</a>';
                }
            }
            $clientHtml = '';
            if (!empty($task->project->clients) && count($task->project->clients) > 0) {
                $clientHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                foreach ($task->project->clients as $client) {
                    $clientHtml .= "<li class='avatar avatar-sm pull-up'><a href='" . route('clients.profile', ['id' => $client->id]) . "' target='_blank' title='{$client->first_name} {$client->last_name}'><img src='" . ($client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                }
                $clientHtml .= '</ul>';
            } else {
                $clientHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
            }
            
            $rows = [
                'id' => $task->id,
                'title' => "<a href='" . route('tasks.info', ['id' => $task->id]) . "' target='_blank' title='" . strip_tags($task->description) . "'>
                        <strong>{$task->title}</strong>
                    </a>",
                'project_id' => "<a href='" . route('projects.info', ['id' => $task->project->id]) . "' target='_blank' title='" . strip_tags($task->project->description) . "'>
                        <strong>{$task->project->title}</strong>
                    </a>
                    <a href='javascript:void(0);' class='mx-2'>
                        <i class='bx " . ($task->project->is_favorite ? 'bxs' : 'bx') . "-star favorite-icon text-warning'
                           data-favorite='{$task->project->is_favorite}'
                           data-id='{$task->project->id}'
                           title='" . ($task->project->is_favorite ? get_label('remove_favorite', 'Click to remove from favorite') : get_label('add_favorite', 'Click to mark as favorite')) . "'>
                        </i>
                    </a>
                    <a href='" . route('tasks.info', ['id' => $task->id]) . "#navs-top-discussions'  target='_blank'  class='mx-2'>
                        <i class='bx bx-message-rounded-dots text-danger' data-bs-toggle='tooltip' data-bs-placement='right' title='" . get_label('discussions', 'Discussions') . "'></i>
                    </a>",
                'users' => $userHtml,
                'clients' => $clientHtml,
                'start_date' => format_date($task->start_date),
                'end_date' => format_date($task->due_date),
                'status_id' => "<div class='d-flex align-items-center'><select class='form-select form-select-sm select-bg-label-{$task->status->color}' id='statusSelect' data-id='{$task->id}' data-original-status-id='{$task->status->id}' data-original-color-class='select-bg-label-{$task->status->color}' data-type='task'>{$statusOptions}</select> " . (!empty($task->note) ? "
                            <span class='ms-2' data-bs-toggle='tooltip' title='{$labelNote}:{$task->note}'> <i class='bx bxs-notepad text-primary'></i></span>" : "") . " </div>
                    ",
                'priority_id' => "<select class='form-select form-select-sm select-bg-label-" . ($task->priority ? $task->priority->color : 'secondary') . "' id='prioritySelect' data-id='{$task->id}' data-original-priority-id='" . ($task->priority ? $task->priority->id : '') . "' data-original-color-class='select-bg-label-" . ($task->priority ? $task->priority->color : 'secondary') . "' data-type='task'>{$priorityOptions}</select>",
                'created_at' => format_date($task->created_at, true),
                'updated_at' => format_date($task->updated_at, true),
                'billing_type' => ucwords($task->billing_type),
                'actions' => $actions
            ];

            foreach ($customFields as $customField) {

                $customFieldValue = $task->customFields()
                    ->where('custom_field_id', $customField->id)
                    ->value('value');

                if ($customField->field_type === 'date' && $customFieldValue) {
                    try {
                        $customFieldValue = \Carbon\Carbon::parse($customFieldValue);
                        $customFieldValue = format_date($customFieldValue, false);
                    } catch (\Exception $e) {

                        $customFieldValue = '-';
                    }
                }

                $rows['custom_field_' . $customField->id] = $customFieldValue ?? '-';
            }

            return $rows;
        });

        // Return JSON response with formatted tasks and total count
        return response()->json([
            "rows" => $tasks->items(),
            "total" => $totaltasks,
        ]);
    }
    public function dragula($id = '')
    {
        $project = (object)[];
        $projects = [];
        if ($id) {
            $project = Project::findOrFail($id);
            $tasks = isAdminOrHasAllDataAccess() ? $project->tasks : $this->user->project_tasks($id);
            $toSelectTaskUsers = $project->users;
        } else {
            $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects : $this->user->projects;
            $toSelectTaskUsers = $this->workspace->users;
            $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks : $this->user->tasks()->get();
        }
        if (request()->has('status')) {
            $tasks = $tasks->where('status_id', request()->status);
        }
        if (request()->has('project')) {
            $project = Project::findOrFail(request()->project);
            $tasks = $tasks->where('project_id', request()->project);
            $toSelectTaskUsers = $project->users;
        }
        $total_tasks = $tasks->count();
        return view('tasks.board_view', ['project' => $project, 'tasks' => $tasks, 'total_tasks' => $total_tasks, 'projects' => $projects, 'toSelectTaskUsers' => $toSelectTaskUsers]);
    }

    /**
     * Update the status of a task.
     * @group Task status and performance
     *
     * This endpoint allows you to update the status of a task by providing the task ID and the new status ID.
     * It logs the status change in the task's status timeline and notifies assigned users and clients.
     *
     * @bodyParam id integer required The ID of the task to update. Example: 33
     * @bodyParam statusId integer required The new status ID to set on the task. Example: 4
     * @header workspace_id 2
     * @queryParam isApi boolean Optional. Set to true if calling from API to get a structured API response. Example: true
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Status updated successfully.",
     *   "id": "33",
     *   "type": "task",
     *   "activity_message": "herry porter updated task status from Approved to Completed",
     *   "data": {
     *     "id": 33,
     *     "workspace_id": 2,
     *     "title": "Test Task Title",
     *     "status": "Completed",
     *     "status_id": 4,
     *     "priority": "low",
     *     "priority_id": 2,
     *     "users": [
     *       {
     *         "id": 2,
     *         "first_name": "herry",
     *         "last_name": "porter",
     *         "email": "admin@gmail.com",
     *         "photo": "http://localhost:8000/storage/photos/no-image.jpg"
     *       }
     *     ],
     *     "start_date": "2025-06-01",
     *     "due_date": "2025-06-10",
     *     "project": "favorite project",
     *     "project_id": 2,
     *     "description": "This is a test task description."
     *   }
     * }
     *
     * @response 403 {
     *   "error": true,
     *   "message": "You are not authorized to set this status."
     * }
     *
     * @response 404 {
     *   "message": "No query results for model [App\\Models\\Task] 999"
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "Task status couldn't updated."
     * }
     */


    public function update_status(Request $request)
    {
        $request->validate([
            'id' => ['required'],
            'statusId' => ['required']
        ]);
        $id = $request->id;
        $statusId = $request->statusId;
        $status = Status::findOrFail($statusId);
        if (canSetStatus($status)) {
            $task = Task::findOrFail($id);
            $oldStatus = $task->status_id;
            $currentStatus = optional($task->status)->title ?? 'Unknown';
            $task->status_id = $statusId;
            $task->note = $request->note;
            $oldStatus = Status::findOrFail($oldStatus);
            $newStatus = Status::findOrFail($statusId);
            $task->statusTimelines()->create([
                'status' => $newStatus->title,
                'new_color' => $newStatus->color,
                'previous_status' => $oldStatus->title,
                'old_color' => $oldStatus->color,
                'changed_at' => now(),
            ]);
            if ($task->save()) {
                $task = $task->fresh();
                $newStatus = $task->status->title;
                $notification_data = [
                    'type' => 'task_status_updation',
                    'type_id' => $id,
                    'type_title' => $task->title,
                    'updater_first_name' => $this->user->first_name,
                    'updater_last_name' => $this->user->last_name,
                    'old_status' => $currentStatus,
                    'new_status' => $newStatus,
                    'access_url' => 'tasks/information/' . $id,
                    'action' => 'status_updated'
                ];
                $userIds = $task->users->pluck('id')->toArray();
                $clientIds = $task->project->clients->pluck('id')->toArray();
                $recipients = array_merge(
                    array_map(function ($userId) {
                        return 'u_' . $userId;
                    }, $userIds),
                    array_map(function ($clientId) {
                        return 'c_' . $clientId;
                    }, $clientIds)
                );
                processNotifications($notification_data, $recipients);
                return formatApiResponse(
                    false,
                    'Status updated successfully.',
                    [
                        'id' => $id,
                        'type' => 'task',
                        'activity_message' => trim($this->user->first_name) . ' ' . trim($this->user->last_name) . ' updated task status from ' . trim($currentStatus) . ' to ' . trim($newStatus),
                        'data' => formatTask($task)
                    ]
                );
            } else {
                return response()->json(['error' => true, 'message' => 'Task status couldn\'t updated.']);
            }
        } else {
            return response()->json(['error' => true, 'message' => 'You are not authorized to set this status.']);
        }
    }
    /**
     * Duplicate a task.
     *
     * This endpoint allows you to duplicate an existing task. You can optionally set a custom title for the duplicated task.
     * Related data such as assigned users will also be duplicated.
     *@group Task Management
     * @urlParam id integer required The ID of the task to duplicate. Example: 12
     * @header workspace_id 2
     * @bodyParam title string optional A new title for the duplicated task. If not provided, the system will use a default naming convention. Example: Copy of Design Review Task
     * @bodyParam reload string optional Set to "true" if you want to trigger a session flash message (usually used for reloading UI). Example: true
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Task duplicated successfully.",
     *   "id": 12,
     *   "parent_id": 5,
     *   "parent_type": "project"
     * }
     *
     * @response 400 {
     *   "error": true,
     *   "message": "Task duplication failed."
     * }
     */

    public function duplicate($id)
    {
        // Define the related tables for this meeting
        $relatedTables = ['users']; // Include related tables as needed
        // Use the general duplicateRecord function
        $title = (request()->has('title') && !empty(trim(request()->title))) ? request()->title : '';
        $duplicate = duplicateRecord(Task::class, $id, $relatedTables, $title);
        if (!$duplicate) {
            return response()->json(['error' => true, 'message' => 'Task duplication failed.']);
        }
        if (request()->has('reload') && request()->input('reload') === 'true') {
            Session::flash('message', 'Task duplicated successfully.');
        }
        return response()->json(['error' => false, 'message' => 'Task duplicated successfully.', 'id' => $id, 'parent_id' => $duplicate->project->id, 'parent_type' => 'project']);
    }


    /**
     * Upload media files to a task.
     * @group Task Media
     * Upload one or more media files to an existing task using its ID. The uploaded files will be stored
     * in the `task-media` media collection using Spatie MediaLibrary. This endpoint accepts multiple files.
     * @header workspace_id 2
     * @bodyParam id integer required The ID of the task to which the files should be uploaded. Example: 25
     * @bodyParam media_files file[] required The media files to upload. Must be provided as an array of files.
     *
     * @response 200 scenario="Success" {
     *   "error": false,
     *   "message": "File(s) uploaded successfully.",
     *   "id": [15, 16],
     *   "type": "media",
     *   "parent_type": "task",
     *   "parent_id": 25
     * }
     *
     * @response 422 scenario="Missing or invalid task ID" {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "id": ["The selected id is invalid."]
     *   }
     * }
     *
     * @response 200 scenario="No files uploaded" {
     *   "error": true,
     *   "message": "No file(s) chosen."
     * }
     */


    public function upload_media(Request $request)
    {

        $validatedData = $request->validate([
            'id' => 'integer|exists:tasks,id'
        ]);
        $mediaIds = [];
        if ($request->hasFile('media_files')) {
            $task = Task::find($validatedData['id']);
            $mediaFiles = $request->file('media_files');
            foreach ($mediaFiles as $mediaFile) {
                $mediaItem = $task->addMedia($mediaFile)
                    ->sanitizingFileName(function ($fileName) {
                        // Replace special characters and spaces with hyphens
                        return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                    })
                    ->toMediaCollection('task-media');
                $mediaIds[] = $mediaItem->id;
            }
            Session::flash('message', 'File(s) uploaded successfully.');
            return response()->json([
                'error' => false,
                'message' => 'File(s) uploaded successfully.',
                'id' => $mediaIds,
                //  dd($mediaIds),
                'type' => 'media',
                'parent_type' => 'task',
                'parent_id' => $task->id
            ]);
        } else {
            Session::flash('error', 'No file(s) chosen.');
            return response()->json(['error' => true, 'message' => 'No file(s) chosen.']);
        }
    }
    /**
     * Get task media list.
     *@group Task Media
     * Returns a list of media files associated with a specific task. Supports optional searching and sorting.
     * @header workspace_id 2
     * @urlParam id integer required The ID of the task to get media for. Example: 25
     * @queryParam search string Optional search term to filter by file name, ID, or creation date. Example: image
     * @queryParam sort string Field to sort by. Default is `id`. Example: file_name
     * @queryParam order string Sorting direction: `asc` or `desc`. Default is `desc`. Example: asc
     *
     * @response 200 {
     *   "rows": [
     *     {
     *       "id": 16,
     *       "file": "<a href=\"http://localhost:8000/storage/task-media/hmgoepprod.jpg\" data-lightbox=\"task-media\"><img src=\"http://localhost:8000/storage/task-media/hmgoepprod.jpg\" alt=\"hmgoepprod.jpg\" width=\"50\"></a>",
     *       "file_name": "hmgoepprod.jpg",
     *       "file_size": "67.54 KB",
     *       "created_at": "2025-06-04",
     *       "updated_at": "2025-06-04",
     *       "actions": "<a href=\"http://localhost:8000/storage/task-media/hmgoepprod.jpg\" title=\"Download\" download><i class=\"bx bx-download bx-sm\"></i></a><button title=\"Delete\" type=\"button\" class=\"btn delete\" data-id=\"16\" data-type=\"task-media\" data-table=\"task_media_table\"><i class=\"bx bx-trash text-danger\"></i></button>"
     *     }
     *   ],
     *   "total": 2
     * }
     */

    public function get_media($id)
    {
        $search = request('search');
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');

        $task = Task::findOrFail($id);
        $media = $task->getMedia('task-media');

        // Filter by search term
        if ($search) {
            $media = $media->filter(function ($mediaItem) use ($search) {
                return (
                    stripos((string) $mediaItem->id, $search) !== false ||
                    stripos($mediaItem->file_name, $search) !== false ||
                    stripos($mediaItem->created_at->format('Y-m-d'), $search) !== false
                );
            });
        }

        // Sort BEFORE mapping
        $media = ($order === 'asc')
            ? $media->sortBy($sort)
            : $media->sortByDesc($sort);

        $canDelete = checkPermission('delete_media');

        $formattedMedia = $media->map(function ($mediaItem) use ($canDelete) {
            $fileUrl = $mediaItem->getFullUrl();
            $fileExtension = pathinfo($fileUrl, PATHINFO_EXTENSION);
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
            $isImage = in_array(strtolower($fileExtension), $imageExtensions);

            $fileHtml = $isImage
                ? '<a href="' . $fileUrl . '" data-lightbox="task-media"><img src="' . $fileUrl . '" alt="' . $mediaItem->file_name . '" width="50"></a>'
                : '<a href="' . $fileUrl . '" title="Download">' . $mediaItem->file_name . '</a>';

            $actions = '<a href="' . $fileUrl . '" title="Download" download><i class="bx bx-download bx-sm"></i></a>';
            if ($canDelete) {
                $actions .= '<button title="Delete" type="button" class="btn delete" data-id="' . $mediaItem->id . '" data-type="task-media" data-table="task_media_table"><i class="bx bx-trash text-danger"></i></button>';
            }

            return [
                'id' => $mediaItem->id,
                'file' => $fileHtml,
                'file_name' => $mediaItem->file_name,
                'file_size' => formatSize($mediaItem->size),
                'created_at' => format_date($mediaItem->created_at),
                'updated_at' => format_date($mediaItem->updated_at),
                'actions' => $actions,
            ];
        });

        return response()->json([
            'rows' => $formattedMedia->values()->toArray(),
            'total' => $formattedMedia->count(),
        ]);
    }

    /**
     * Delete a media file from a task.
     *@group Task Media
     * Deletes a specific media file associated with a task using its media ID. This removes the file from both the database and the storage disk.
     * @header workspace_id 2
     * @urlParam mediaId int required The ID of the media file to delete. Example: 45
     *
     * @response 200 {
     *   "error": false,
     *   "message": "File deleted successfully.",
     *   "id": 45,
     *   "title": "example-file.pdf",
     *   "parent_id": 23,
     *   "type": "media",
     *   "parent_type": "task"
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "File not found."
     * }
     */

    public function delete_media($mediaId)
    {
        $mediaItem = Media::find($mediaId);
        // dd($mediaItem);
        if (!$mediaItem) {
            // Handle case where media item is not found
            return response()->json(['error' => true, 'message' => 'File not found.']);
        }
        // Delete media item from the database and disk
        $mediaItem->delete();
        return response()->json(['error' => false, 'message' => 'File deleted successfully.', 'id' => $mediaId, 'title' => $mediaItem->file_name, 'parent_id' => $mediaItem->model_id,  'type' => 'media', 'parent_type' => 'task']);
    }
    public function delete_multiple_media(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:media,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        // dd($ids);
        $deletedIds = [];
        $deletedTitles = [];
        $parentIds = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $media = Media::find($id);
            // dd($media);
            if ($media) {
                $deletedIds[] = $id;
                $deletedTitles[] = $media->file_name;
                $parentIds[] = $media->model_id;
                $media->delete();
            }
        }
        return response()->json(['error' => false, 'message' => 'Files(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles, 'parent_id' => $parentIds, 'type' => 'media', 'parent_type' => 'task']);
    }
    public function get($id)
    {
        $task = Task::with([
            'users',
            'reminders',
            'recurringTask',
            'customFields.customField' // Load custom fields and their definitions
        ])->findOrFail($id);

        $project = $task->project()->with('users')->firstOrFail();

        // Format custom fields for easier frontend usage
        $formattedCustomFields = [];
        foreach ($task->customFields as $fieldable) {
            // dd($fieldable->customField);
            $formattedCustomFields[$fieldable->custom_field_id] = [
                'field_id' => $fieldable->custom_field_id,
                'field_label' => $fieldable->customField->field_label,
                'field_type' => $fieldable->customField->field_type,
                'value' => $fieldable->value
            ];
        }
        // dd($formattedCustomFields);
        $task->formatted_custom_fields = $formattedCustomFields;

        return response()->json([
            'error' => false,
            'task' => $task,
            'project' => $project
        ]);
    }

    /**
     * Save User's Default Task View Preference
     *@group Task status and performance
     * Stores the default task view (e.g., board, list, calendar) for the currently authenticated user or client.
     * This preference determines how tasks are displayed by default in the UI.
     * @header workspace_id 2
     * Requires the user to be authenticated.
     *
     * @bodyParam view string required The view preference to set. Example: board
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Default View Set Successfully."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "view": [
     *       "The view field is required."
     *     ]
     *   }
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     */

    public function saveViewPreference(Request $request)
    {
        $view = $request->input('view');
        $prefix = isClient() ? 'c_' : 'u_';
        UserClientPreference::updateOrCreate(
            ['user_id' => $prefix . $this->user->id, 'table_name' => 'tasks'],
            ['default_view' => $view]
        );
        return response()->json(['error' => false, 'message' => 'Default View Set Successfully.']);
    }
    /**
     * Update Task Priority
     *@group Task status and performance
     * This endpoint updates the priority of a specific task. You can provide a new priority or remove it by passing `null` or `0`.
     *
     * @urlParam id integer optional The ID of the task. If provided in the URL, it doesn't need to be in the body. Example: 25
     * @header workspace_id 2
     * @bodyParam id integer required The ID of the task to update. Required if not provided in the URL. Example: 25
     * @bodyParam priorityId integer nullable The new priority ID. Use `null` or `0` to remove the priority. Example: 1
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Priority updated successfully.",
     *   "id": 25,
     *   "type": "task",
     *   "activity_message": "herry porter updated task priority from low to high",
     *   "data": {
     *     "id": 25,
     *     "workspace_id": 2,
     *     "title": "Test Task Title",
     *     "status": "Approved",
     *     "status_id": 8,
     *     "priority": "high",
     *     "priority_id": 1,
     *     "users": [
     *       {
     *         "id": 2,
     *         "first_name": "herry",
     *         "last_name": "porter",
     *         "email": "admin@gmail.com",
     *         "photo": "http://localhost:8000/storage/photos/no-image.jpg"
     *       },
     *       {
     *         "id": 3,
     *         "first_name": "John",
     *         "last_name": "Doe",
     *         "email": "admin2@gmail.com",
     *         "photo": "http://localhost:8000/storage/photos/no-image.jpg"
     *       }
     *     ],
     *     "user_id": [2, 3],
     *     "clients": [],
     *     "start_date": "2025-06-01",
     *     "due_date": "2025-06-10",
     *     "project": "favorite project",
     *     "project_id": 2,
     *     "description": "This is a test task description.",
     *     "note": "Optional note about the task.",
     *     "favorite": 0,
     *     "client_can_discuss": null,
     *     "created_at": "2025-05-28",
     *     "updated_at": "2025-06-03",
     *     "enable_reminder": 1,
     *     "last_reminder_sent": null,
     *     "frequency_type": "weekly",
     *     "day_of_week": 3,
     *     "time_of_day": "14:30:00",
     *     "enable_recurring_task": 1,
     *     "recurrence_frequency": "monthly",
     *     "recurrence_day_of_month": 15,
     *     "recurrence_starts_from": "2025-06-01",
     *     "recurrence_occurrences": 6,
     *     "billing_type": "billable",
     *     "completion_percentage": 0,
     *     "task_list_id": null
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "id": ["The id field is required."],
     *     "priorityId": ["The selected priorityId is invalid."]
     *   }
     * }
     *
     * @response 400 {
     *   "error": true,
     *   "message": "No priority change detected."
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "Priority couldn’t be updated."
     * }
     */

    public function update_priority(Request $request, $id = null)
    {
        $isApi = request()->get('isApi', false);

        if ($id) {
            $request->merge(['id' => $id]);
        }

        if ($request->input('priorityId') == 0) {
            $request->merge(['priorityId' => null]);
        }

        $rules = [
            'id' => 'required|exists:tasks,id',
            'priorityId' => 'nullable|exists:priorities,id'
        ];

        try {
            $request->validate($rules);

            $id = $request->id;
            $priorityId = $request->priorityId;
            $task = Task::findOrFail($id);

            if ($task->priority_id != $priorityId) {
                $currentPriority = $task->priority ? $task->priority->title : '-';
                $task->priority_id = $priorityId;

                if ($task->save()) {
                    $task = $task->fresh();
                    $newPriority = $task->priority ? $task->priority->title : '-';

                    $message = trim($this->user->first_name) . ' ' . trim($this->user->last_name) .
                        ' updated task priority from ' . trim($currentPriority) .
                        ' to ' . trim($newPriority);

                    return formatApiResponse(
                        false,
                        'Priority updated successfully.',
                        [
                            'id' => $id,
                            'type' => 'task',
                            'activity_message' => $message,
                            'data' => formatTask($task)
                        ]
                    );
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => 'Priority couldn\'t updated.'
                    ]);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'No priority change detected.'
                ]);
            }
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Priority couldn\'t be updated.'
            ], 500);
        }
    }

    // calendar view
    public function calendar_view()
    {
        $project_id = request('id') ?? null;
        $projects = $this->workspace->projects;
        $tasks = $this->workspace->tasks;
        $totalTasks = $tasks->count();
        return view('tasks.calendar_view', compact('projects', 'project_id' ,'totalTasks'));
    }

    /**
     * Get calendar tasks data for a workspace.
     *
     * Retrieves tasks for the specified workspace filtered by an optional date range and project ID.
     * The tasks are formatted for use with FullCalendar.
     *
     * @group Task Celender
     *
     * @urlParam workspaceId integer required The ID of the workspace to fetch tasks from.
     *
     * @queryParam start string optional Start date for filtering tasks (format: YYYY-MM-DD).
     * @queryParam end string optional End date for filtering tasks (format: YYYY-MM-DD).
     * @queryParam project_id integer optional Project ID to filter tasks by project.
     * @header workspace_id 2
     * @response 200 [
     *   {
     *     "id": 25,
     *     "tasks_info_url": "http://localhost:8000/master-panel/tasks/information/25",
     *     "title": "Test Task Title",
     *     "start": "2025-06-01",
     *     "end": "2025-06-10",
     *     "backgroundColor": "#a0e4a3",
     *     "borderColor": "#ffffff",
     *     "textColor": "#000000"
     *   },
     *   {
     *     "id": 28,
     *     "tasks_info_url": "http://localhost:8000/master-panel/tasks/information/28",
     *     "title": "Create new onboarding flow",
     *     "start": "2015-01-01",
     *     "end": "2025-06-06",
     *     "backgroundColor": "#a0e4a3",
     *     "borderColor": "#ffffff",
     *     "textColor": "#000000"
     *   }
     * ]
     *
     * @response 404 {
     *   "error": true,
     *   "message": "Workspace not found."
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "Something went wrong: <error_message>"
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @param int $workspaceId
     * @return \Illuminate\Http\JsonResponse
     */


    public function get_calendar_data(Request $request)
{
    // Get the start, end, project_id, statuses, and priorities from the request
    $start = $request->query('start');
    $end = $request->query('end');
    $project_id = $request->query('project_id');
    $statuses = $request->query('statuses', []); 
    $priorities = $request->query('priorities', []);

    // Fetch tasks based on user permissions and date range
    $tasksQuery = isAdminOrHasAllDataAccess()
        ? $this->workspace->tasks()
        : $this->user->tasks();

    // Filter tasks based on the date range
    $tasksQuery->where(function ($query) use ($start, $end) {
        $query->whereBetween('start_date', [$start, $end])
              ->orWhereBetween('due_date', [$start, $end]);
    });

    // Apply project_id filter if provided
    if ($project_id) {
        $tasksQuery->where('project_id', $project_id);
    }

    // Apply status filter if statuses are provided
    if (!empty($statuses)) {
        $tasksQuery->whereIn('status_id', $statuses);
    }

    // Apply priority filter if priorities are provided
    if (!empty($priorities)) {
        $tasksQuery->whereIn('priority_id', $priorities);
    }

    // Retrieve the tasks as a collection
    $tasks = $tasksQuery->get();

    // Format the tasks for FullCalendar
    $events = $tasks->map(function ($task) {
        $backgroundColor = '#5ab0ff'; // Lighter default blue

        // Set the background color based on the task status
        switch ($task->status->color) {
            case 'primary':
                $backgroundColor = '#9bafff'; // Lighter primary blue
                break;
            case 'success':
                $backgroundColor = '#a0e4a3'; // Lighter green
                break;
            case 'danger':
                $backgroundColor = '#ff6b5c'; // Lighter red
                break;
            case 'warning':
                $backgroundColor = '#ffca66'; // Lighter yellow
                break;
            case 'info':
                $backgroundColor = '#6ed4f0'; // Lighter blue
                break;
            case 'secondary':
                $backgroundColor = '#aab0b8'; // Lighter grey
                break;
            case 'dark':
                $backgroundColor = '#4f5b67'; // Lighter dark grey
                break;
            case 'light':
                $backgroundColor = '#ffffff'; // Already light
                break;
            default:
                $backgroundColor = '#5ab0ff'; // Lighter default blue
        }

        return [
            'id' => $task->id,
            'tasks_info_url' => route('tasks.info', ['id' => $task->id]),
            'title' => $task->title,
            'start' => $task->start_date,
            'end' => Carbon::parse($task->due_date)->format('Y-m-d'), // Add a day to end date
            'backgroundColor' => $backgroundColor,
            'borderColor' => '#ffffff',
            'textColor' => '#000000',
        ];
    });

    return response()->json($events);
}
    /**
     * Add a comment to a model (e.g., task, project).
     * @group Task Comments
     * This endpoint allows an authenticated user to add a comment to a specific model
     * such as a Task, Project, or any commentable entity. It also supports mentions
     * (e.g., @username) and file attachments (e.g., PNG, PDF).
     *
     * @bodyParam model_type string required The fully qualified model class name. Example: App\Models\Task
     * @bodyParam model_id integer required The ID of the model to comment on. Example: 25
     * @bodyParam content string required The comment content. Mentions like @username will be parsed. Example: This is a test comment mentioning @john_doe
     * @bodyParam parent_id integer nullable The ID of the parent comment (for replies). Example: 5
     * @bodyParam attachments[] file optional Optional file attachments (JPG, PNG, PDF, etc). No-example
     * @header workspace_id 2
     * @response 200 {
     *   "success": true,
     *   "message": "Comment Added Successfully",
     *   "comment": {
     *     "id": 20,
     *     "content": "This is a test comment mentioning @john_doe",
     *     "user": {
     *       "id": null,
     *       "name": null,
     *       "email": null
     *     },
     *     "attachments": [],
     *     "parent_id": null,
     *     "created_at": "2025-06-04 06:05:24",
     *     "created_human": "1 second ago"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "model_type": ["The model_type field is required."],
     *     "model_id": ["The model_id field is required."],
     *     "content": ["The content field is required."]
     *   }
     * }
     */


    public function comments(Request $request)
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'content' => 'required|string',
            'parent_id' => 'nullable|integer|exists:comments,id',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,xlsx,txt,docx|max:2048', // Add more file types and size limits if needed
        ]);
        list($processedContent, $mentionedUserIds) = replaceUserMentionsWithLinks($request->content);
        $comment = Comment::with('user')->create([
            'commentable_type' => $request->model_type,
            'commentable_id' => $request->model_id,
            'content' => $processedContent,
            'user_id' => auth()->id(), // Associate with authenticated user
            'parent_id' => $request->parent_id, // Set the parent_id for replies
        ]);
        $directoryPath = storage_path('app/public/comment_attachments');
        // Create the directory with permissions if it does not exist
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true); // 0755 for directories
        }
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('public/comment_attachments');
                $path = str_replace('public/', '', $path);
                CommentAttachment::create([
                    'comment_id' => $comment->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }
        sendMentionNotification($comment, $mentionedUserIds, session()->get('workspace_id'), auth()->id());
        return response()->json([
            'success' => true,
            'comment' => $comment->load('attachments'),
            'message' => get_label('comment_added_successfully', 'Comment Added Successfully'),
            'user' => $comment->user,
            'created_at' => $comment->created_at->diffForHumans() // Send human-readable date
        ]);
    }

    /**
     * get comments
     *
     * This endpoint allows an authenticated user to add a comment to any commentable model (e.g., Task, Project).
     * Supports file attachments and user mentions within the comment content.
     *@group Task Comments
     * @bodyParam model_type string required The fully qualified class name of the model being commented on (e.g., App\Models\Task). Example: App\Models\Task
     * @bodyParam model_id integer required The ID of the model instance being commented on. Example: 12
     * @bodyParam content string required The content of the comment. Mentions (e.g., @username) will be converted to user links. Example: This is a test comment with @johndoe mentioned.
     * @bodyParam parent_id integer nullable The ID of the parent comment if this is a reply. Example: 5
     * @bodyParam attachments file[] Optional array of files to attach to the comment (jpg, jpeg, png, pdf, xlsx, txt, docx, max 2MB each).
     * @header workspace_id 2
     * @response 200 {
     *   "success": true,
     *   "comment": {
     *     "id": 20,
     *     "commentable_type": "App\\Models\\Task",
     *     "commentable_id": 12,
     *     "content": "This is a test comment with <a href='/user/profile/5'>@johndoe</a>",
     *     "user_id": 3,
     *     "parent_id": null,
     *     "created_at": "2025-05-28T12:45:00.000000Z",
     *     "updated_at": "2025-05-28T12:45:00.000000Z",
     *     "attachments": [
     *       {
     *         "id": 1,
     *         "comment_id": 20,
     *         "file_name": "screenshot.png",
     *         "file_path": "comment_attachments/screenshot.png",
     *         "file_type": "image/png"
     *       }
     *     ]
     *   },
     *   "message": "Comment Added Successfully",
     *   "user": {
     *     "id": 3,
     *     "name": "John Doe",
     *     ...
     *   },
     *   "created_at": "just now"
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "content": ["The content field is required."]
     *   }
     * }
     */

    public function get_comment(Request $request, $id)
    {
        $comment = Comment::with('attachments')->findOrFail($id);
        // dd($comment);
        return response()->json([
            'comment' => $comment,
        ]);
    }
    /**
     * Update a comment's content.
     *@group Task Comments
     * This endpoint updates the content of an existing comment. It also detects user mentions
     * (e.g., @username) in the updated content and sends mention notifications accordingly.
     *
     * @bodyParam comment_id integer required The ID of the comment to update. Example: 15
     * @bodyParam content string required The updated content of the comment. User mentions using @username will be parsed and linked. Example: Updated comment mentioning @janedoe.
     * @header workspace_id 2
     * @response 200 {
     *   "error": false,
     *   "message": "Comment updated successfully.",
     *   "id": 15,
     *   "type": "project"
     * }
     *
     * @response 400 {
     *   "error": true,
     *   "message": "Comment couldn't updated."
     * }
     */

    public function update_comment(Request $request)
    {
        $request->validate([
            'comment_id' => ['required'],
            'content' => 'required|string',
        ]);
        list($processedContent, $mentionedUserIds) = replaceUserMentionsWithLinks($request->content);
        $id = $request->comment_id;
        $comment = Comment::findOrFail($id);
        $comment->content = $processedContent;
        if ($comment->save()) {
            sendMentionNotification($comment, $mentionedUserIds, session()->get('workspace_id'), auth()->id());
            return response()->json(['error' => false, 'message' => 'Comment updated successfully.', 'id' => $id, 'type' => 'project']);
        } else {
            return response()->json(['error' => true, 'message' => 'Comment couldn\'t updated.']);
        }
    }
    /**
     * Permanently delete a comment and its attachments.
     *@group Task Comments
     * This endpoint deletes a comment by its ID, including all of its associated attachments
     * (files uploaded with the comment). Files will be removed from storage as well.
     *
     * @bodyParam comment_id integer required The ID of the comment to delete. Example: 12
     * @header workspace_id 2
     * @response 200 {
     *   "error": false,
     *   "message": "Comment deleted successfully.",
     *   "id": 12,
     *   "type": "project"
     * }
     *
     * @response 400 {
     *   "error": true,
     *   "message": "Comment couldn't deleted."
     * }
     */

    public function destroy_comment(Request $request)
    {
        $request->validate([
            'comment_id' => ['required'],
        ]);
        $id = $request->comment_id;
        $comment = Comment::findOrFail($id);
        $attachments = $comment->attachments;
        foreach ($attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();
        }
        if ($comment->forceDelete()) {
            return response()->json(['error' => false, 'message' => 'Comment deleted successfully.', 'id' => $id, 'type' => 'project']);
        } else {
            return response()->json(['error' => true, 'message' => 'Comment couldn\'t deleted.']);
        }
    }
    public function search_projects(Request $request)
    {
        $query = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;
        $projects = $this->workspace->projects();
        // If there is no query, return the first set of projects
        $projects = $projects->when($query, function ($queryBuilder) use ($query) {
            $queryBuilder->where('title', 'like', '%' . $query . '%');
        })
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'title']);
        // Prepare response for Select2
        $results = $projects->map(function ($project) {
            return ['id' => $project->id, 'text' => ucwords($project->title)];
        });
        // Flag for more results
        $pagination = ['more' => $projects->count() === $perPage];
        return response()->json([
            'items' => $results,
            'pagination' => $pagination
        ]);
    }


    public function search_tasks(Request $request)
    {
        $query = $request->input('q'); // Search term
        $projectId = $request->input('project_id'); // Get project ID from query
        $page = $request->input('page', 1);
        $perPage = 10;

        // Fetch tasks that belong to the given project ID
        $tasks = Task::where('project_id', $projectId)
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where('title', 'like', '%' . $query . '%');
            })
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'title']);

        // Prepare response for Select2
        $results = $tasks->map(function ($task) {
            return ['id' => $task->id, 'text' => ucwords($task->title)];
        });

        // Return response
        return response()->json([
            'items' => $results,
            'pagination' => ['more' => $tasks->count() === $perPage]
        ]);
    }


    public function group_by_task_list(Request $request)
    {

        try {
            $page = $request->get('page', 1);
            $perPage = 10;  // Number of task lists per page
            $taskLists = TaskList::with([
                'tasks' => function ($query) {
                    $query->with('users');
                }
            ])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            if ($request->ajax()) {
                return response()->json([
                    'html' => view('components.group-task-list', compact('taskLists'))->render(),
                    'hasMorePages' => $taskLists->hasMorePages()
                ]);
            }

            $toSelectTaskUsers = $this->workspace->users;
            $tasks = isAdminOrHasAllDataAccess() ? $this->workspace->tasks : $this->user->tasks();
            $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects : $this->user->projects;

            return view('tasks.group_by_task_lists', compact('taskLists', 'projects', 'tasks', 'toSelectTaskUsers'));
        } catch (\Exception $e) {
            // dd($e);

            return response()->json(['error' => true, 'message' => $e->getMessage()]);
        }
    }
    /**
     * @group Task Management
     *
     * List or Get Task(s)
     *
     * This endpoint returns a paginated list of tasks, or a single task if an ID is provided.
     * It supports advanced filtering, searching, sorting, and eager loading of related entities
     * such as users, project, status, priority, reminders, and recurring task details.
     *
     * If the ID is numeric, it returns a single formatted task object.
     * If the ID follows the format `user_{id}` or `project_{id}`, it filters tasks belonging
     * to the specified user or project.
     *
     * @urlParam id int|string Optional. Numeric task ID to fetch a single task, or `user_{id}`, `project_{id}` to filter by user/project. Example: 25
     *
     * @queryParam isApi boolean Optional. Indicate if it's an API call. Default: false
     * @queryParam search string Optional. Search tasks by title or ID. Example: Design
     * @queryParam sort string Optional. Field to sort by. Default: id. Example: title
     * @queryParam order string Optional. Sorting order: ASC or DESC. Default: DESC
     * @queryParam status_ids[] int Optional. Filter tasks by status ID(s). Example: 1
     * @queryParam priority_ids[] int Optional. Filter tasks by priority ID(s). Example: 2
     * @queryParam user_ids[] int Optional. Filter tasks assigned to these user ID(s). Example: 3
     * @queryParam client_ids[] int Optional. Filter tasks linked to clients via project(s). Example: 5
     * @queryParam project_ids[] int Optional. Filter tasks by project ID(s). Example: 2
     * @queryParam task_start_date_from date Optional. Filter tasks starting from this date. Format: Y-m-d. Example: 2025-06-01
     * @queryParam task_start_date_to date Optional. Filter tasks starting up to this date. Format: Y-m-d. Example: 2025-06-30
     * @queryParam task_end_date_from date Optional. Filter tasks due from this date. Format: Y-m-d. Example: 2025-06-05
     * @queryParam task_end_date_to date Optional. Filter tasks due up to this date. Format: Y-m-d. Example: 2025-06-20
     * @queryParam limit int Optional. Number of results per page. Default: 10. Example: 20
     * @header workspace_id 2
     * @response 200 scenario="Single task response" {
     *  "id": 25,
     *  "workspace_id": 2,
     *  "title": "Test Task Title",
     *  "status": "Open",
     *  "status_id": 1,
     *  "priority": "low",
     *  "priority_id": 2,
     *  "users": [
     *    {
     *      "id": 2,
     *      "first_name": "herry",
     *      "last_name": "porter",
     *      "email": "admin@gmail.com",
     *      "photo": "http://localhost:8000/storage/photos/no-image.jpg"
     *    }
     *  ],
     *  "user_id": [2],
     *  "clients": [],
     *  "start_date": "2025-06-01",
     *  "due_date": "2025-06-10",
     *  "project": "favorite project",
     *  "project_id": 2,
     *  "description": "This is a test task description.",
     *  "note": "Optional note about the task.",
     *  "favorite": 0,
     *  "client_can_discuss": null,
     *  "created_at": "2025-05-28",
     *  "updated_at": "2025-05-28",
     *  "enable_reminder": 1,
     *  "last_reminder_sent": null,
     *  "frequency_type": "weekly",
     *  "day_of_week": 3,
     *  "day_of_month": null,
     *  "time_of_day": "14:30:00",
     *  "enable_recurring_task": 1,
     *  "recurrence_frequency": "monthly",
     *  "recurrence_day_of_week": null,
     *  "recurrence_day_of_month": 15,
     *  "recurrence_month_of_year": null,
     *  "recurrence_starts_from": "2025-06-01",
     *  "recurrence_occurrences": 6,
     *  "completed_occurrences": null,
     *  "billing_type": "billable",
     *  "completion_percentage": 0,
     *  "task_list_id": null
     * }
     *
     * @response 200 scenario="Paginated task list" {
     *   "status": false,
     *   "message": "Tasks retrieved successfully.",
     *   "data": {
     *     "total": 25,
     *     "data": [
     *       {
     *         "id": 25,
     *         "workspace_id": 2,
     *         "title": "Test Task Title",
     *         "status": "Open",
     *         ...
     *       }
     *     ]
     *   }
     * }
     *
     * @response 404 scenario="Task not found" {
     *   "message": "Task not found"
     * }
     *
     * @response 404 scenario="Project or User not found" {
     *   "message": "Project not found"
     * }
     */

    public function listapi(Request $request, $id = null)
    {

        $isApi = $request->get('isApi', false);
        try {
            $search = request('search');
            $sort = request('sort', 'id');
            $order = request('order', 'DESC');
            $status_ids = request('status_ids', []);
            $priority_ids = request('priority_ids', []);
            $user_ids = request('user_ids', []);
            $client_ids = request('client_ids', []);
            $project_ids = request('project_ids', []);
            $start_date_from = trim(request('task_start_date_from', ''));
            $start_date_to = trim(request('task_start_date_to', ''));
            $end_date_from = trim(request('task_end_date_from', ''));
            $end_date_to = trim(request('task_end_date_to', ''));


            if (is_numeric($id)) {
                $task = Task::with(['users', 'project.clients', 'status', 'priority'])->find($id);

                if (!$task) {
                    return response()->json(['message' => 'Task not found'], 404);
                }

                return formatTask($task);
            }


            if ($id) {
                $parts = explode('_', $id);
                $belongs_to = $parts[0] ?? null;
                $belongs_to_id = $parts[1] ?? null;

                if ($belongs_to === 'project') {
                    $project = Project::find($belongs_to_id);
                    if (!$project) {
                        return response()->json(['message' => 'Project not found'], 404);
                    }
                    $tasks = $project->tasks();
                } else {
                    $userOrClient = $belongs_to === 'user'
                        ? User::find($belongs_to_id)
                        : Client::find($belongs_to_id);

                    if (!$userOrClient) {
                        return response()->json(['message' => ucfirst($belongs_to) . ' not found'], 404);
                    }

                    $tasks = isAdminOrHasAllDataAccess($belongs_to, $belongs_to_id)
                        ? ($this->workspace ? $this->workspace->tasks() : Task::query())
                        : $userOrClient->tasks();
                }
            } else {
                $tasks = isAdminOrHasAllDataAccess()
                    ? ($this->workspace ? $this->workspace->tasks() : Task::query())
                    : ($this->user ? $this->user->tasks() : Task::query());
            }

            // Filtering
            if (!empty($user_ids)) {
                $taskIds = DB::table('task_user')
                    ->whereIn('user_id', $user_ids)
                    ->pluck('task_id')
                    ->toArray();
                $tasks = $tasks->whereIn('id', $taskIds);
            }

            if (!empty($client_ids)) {
                $projectIds = DB::table('client_project')
                    ->whereIn('client_id', $client_ids)
                    ->pluck('project_id')
                    ->toArray();
                $tasks = $tasks->whereIn('project_id', $projectIds);
            }

            if (!empty($project_ids)) {
                $tasks->whereIn('project_id', $project_ids);
            }

            if (!empty($status_ids)) {
                $tasks->whereIn('status_id', $status_ids);
            }

            if (!empty($priority_ids)) {
                $tasks->whereIn('priority_id', $priority_ids);
            }

            if ($start_date_from && $start_date_to) {
                $tasks->whereBetween('start_date', [$start_date_from, $start_date_to]);
            }

            if ($end_date_from && $end_date_to) {
                $tasks->whereBetween('due_date', [$end_date_from, $end_date_to]);
            }

            if ($search) {
                $tasks->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('id', 'like', '%' . $search . '%');
                });
            }

            $total = $tasks->count();

            $tasks = Task::with(['status', 'priority', 'users', 'project.clients']) // eager load to prevent N+1
                ->orderBy($sort, $order)
                ->paginate(request('per_page', 10))
                ->through(function ($task) {
                    return formatTask($task);
                });

            return formatApiResponse(
                false,
                'Tasks retrieved successfully.',
                [
                    'total' => $total,
                    'data' => $tasks->items(), // already formatted
                ]
            );
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (Exception $e) {
            return formatApiResponse(
                true,
                'project culd not created ',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            );
        }
    }
    public function updateStatus($id, $newStatus)
    {
        $status = Status::findOrFail($newStatus);
        if (canSetStatus($status)) {
            $task = Task::findOrFail($id);
            $current_status = $task->status->title;
            $task->status_id = $newStatus;
            if ($task->save()) {
                $task->refresh();
                $new_status = $task->status->title;
                $notification_data = [
                    'type' => 'task_status_updation',
                    'type_id' => $id,
                    'type_title' => $task->title,
                    'updater_first_name' => $this->user->first_name,
                    'updater_last_name' => $this->user->last_name,
                    'old_status' => $current_status,
                    'new_status' => $new_status,
                    'access_url' => 'tasks/information/' . $id,
                    'action' => 'status_updated'
                ];
                $userIds = $task->users->pluck('id')->toArray();
                $clientIds = $task->project->clients->pluck('id')->toArray();
                $recipients = array_merge(
                    array_map(function ($userId) {
                        return 'u_' . $userId;
                    }, $userIds),
                    array_map(function ($clientId) {
                        return 'c_' . $clientId;
                    }, $clientIds)
                );
                processNotifications($notification_data, $recipients);
                return response()->json(['error' => false, 'message' => 'Task status updated successfully.', 'id' => $id, 'activity_message' => trim($this->user->first_name) . ' ' . trim($this->user->last_name) . ' updated task status from ' . trim($current_status) . ' to ' . trim($new_status)]);
            } else {
                return response()->json(['error' => true, 'message' => 'Task status couldn\'t updated.']);
            }
        } else {
            return response()->json(['error' => true, 'message' => 'You are not authorized to set this status.']);
        }
    }

 
}
