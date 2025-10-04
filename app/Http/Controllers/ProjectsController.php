<?php

namespace App\Http\Controllers;

use App\Http\Middleware\IsApi;
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
use App\Models\Milestone;
use App\Models\Workspace;
use App\Models\ProjectUser;
use Illuminate\Http\Request;
use App\Models\ProjectClient;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CommentAttachment;
use App\Models\CustomField;
use App\Services\DeletionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserClientPreference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use Illuminate\Support\Facades\Request as FacadesRequest;

class ProjectsController extends Controller
{
  protected $workspace;
protected $user;

public function __construct()
{
    $this->middleware(function ($request, $next) {
        // Use helper function to get workspace ID
        $workspaceId = getWorkspaceId();
        // dd($workspaceId);
        $this->workspace = Workspace::find($workspaceId);
        // dd($this->workspace);

        $this->user = getAuthenticatedUser();

        return $next($request);
    });
}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $type = null)
    {
        $status = isset($_REQUEST['status']) && $_REQUEST['status'] !== '' ? $_REQUEST['status'] : "";
        $selectedTags = (request('tags')) ? request('tags') : [];
        $where = [];
        if ($status != '') {
            $where['status_id'] = $status;
        }
        $is_favorite = 0;
        if ($type === 'favorite') {
            $where['is_favorite'] = 1;
            $is_favorite = 1;
        }
        $sort = (request('sort')) ? request('sort') : "id";
        $order = 'desc';
        if ($sort == 'newest') {
            $sort = 'created_at';
            $order = 'desc';
        } elseif ($sort == 'oldest') {
            $sort = 'created_at';
            $order = 'asc';
        } elseif ($sort == 'recently-updated') {
            $sort = 'updated_at';
            $order = 'desc';
        } elseif ($sort == 'earliest-updated') {
            $sort = 'updated_at';
            $order = 'asc';
        }
        $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects() : $this->user->projects();
        $projects->where($where);
        if (!empty($selectedTags)) {
            $projects->whereHas('tags', function ($q) use ($selectedTags) {
                $q->whereIn('tags.id', $selectedTags);
            });
        }
        $projects = $projects->orderBy($sort, $order)->paginate(6);
        $statuses = Status::where("admin_id", getAdminIdByUserRole())->orWhereNull('admin_id')->get();
        $tags = Tag::where('admin_id', getAdminIdByUserRole())->orWhereNull('admin_id')->get();
        $customFields = CustomField::where('module', 'project')->get();
        // dd($customFields);
        return view('projects.grid_view', ['projects' => $projects, 'auth_user' => $this->user, 'selectedTags' => $selectedTags, 'is_favorite' => $is_favorite, 'statuses' => $statuses, 'tags' => $tags, 'customFields' => $customFields  ]);
    }
    public function kanban_view(Request $request, $type = null)
    {
        $status = isset($_REQUEST['status']) && $_REQUEST['status'] !== '' ? $_REQUEST['status'] : "";
        $selectedTags = (request('tags')) ? request('tags') : [];
        $where = [];
        if ($status != '') {
            $where['status_id'] = $status;
        }
        $is_favorite = 0;
        if ($type === 'favorite') {
            $where['is_favorite'] = 1;
            $is_favorite = 1;
        }
        $sort = (request('sort')) ? request('sort') : "id";
        $order = 'desc';
        if ($sort == 'newest') {
            $sort = 'created_at';
            $order = 'desc';
        } elseif ($sort == 'oldest') {
            $sort = 'created_at';
            $order = 'asc';
        } elseif ($sort == 'recently-updated') {
            $sort = 'updated_at';
            $order = 'desc';
        } elseif ($sort == 'earliest-updated') {
            $sort = 'updated_at';
            $order = 'asc';
        }
        $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects() : $this->user->projects();
        $projects->where($where);
        if (!empty($selectedTags)) {
            $projects->whereHas('tags', function ($q) use ($selectedTags) {
                $q->whereIn('tags.id', $selectedTags);
            });
        }
        $projects = $projects->orderBy($sort, $order)->get();
        $statuses = Status::where("admin_id", getAdminIdByUserRole())->orWhereNull('admin_id')->get();
        $tags = Tag::where('admin_id', getAdminIdByUserRole())->orWhereNull('admin_id')->get();
        $customFields = CustomField::where('module', 'project')->get();
        return view('projects.kanban', ['projects' => $projects, 'auth_user' => $this->user, 'selectedTags' => $selectedTags, 'is_favorite' => $is_favorite, 'statuses' => $statuses, 'tags' => $tags, 'customFields' => $customFields ]);
    }
    public function list_view(Request $request, $type = null)
    {
        // Use query builder for flexible filtering
        $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects() : $this->user->projects();
        // Filter for favorites if needed
        if ($type === 'favorite') {
            $projects = $projects->where('is_favorite', 1);
        }
        // Retrieve the projects as a collection
        $projects = $projects->get();
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        $customFields = CustomField::where('module', 'project')->get();
        $is_favorites = $type === 'favorite' ? 1 : 0;
        return view('projects.projects', [
            'projects' => $projects,
            'users' => $users,
            'clients' => $clients,
            'is_favorites' => $is_favorites,
            'customFields' => $customFields,
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        $adminId = getAdminIdByUserRole();
        $statuses = Status::where('admin_id', $adminId)
            ->orWhere(function ($query) {
                $query->whereNull('admin_id')
                    ->where('is_default', 1);
            })->get();
        $tags = Tag::where('admin_id', $adminId)
            ->get();
         $customFields = CustomField::all();    

        return view('projects.create_project', ['users' => $users, 'clients' => $clients, 'auth_user' => $this->user, 'statuses' => $statuses, 'tags' => $tags, 'customFields' => $customFields]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

/**
 *
 *
 * Create a new project
 * @group Project Managemant
 * This endpoint allows creating a new project along with assigning users, clients, and tags.
 * The user must be authorized to set the selected status.
 *
 * @bodyParam title string required The title of the project. Example: Website Redesign
 * @bodyParam status_id int required The status ID for the project. Must exist in statuses table. Example: 1
 * @bodyParam priority_id int The priority ID. Must exist in priorities table. Example: 4
 * @bodyParam start_date string The project start date in `Y-m-d` format. Must be before or equal to `end_date`. Example: 2025-05-01
 * @bodyParam end_date string The project end date in `Y-m-d` format. Must be after or equal to `start_date`. Example: 2025-05-31
 * @bodyParam budget string The budget amount (formatted string or numeric). Example: 5000
 * @bodyParam task_accessibility string required Must be either `project_users` or `assigned_users`. Example: project_users
 * @bodyParam description string Project description (optional). Example: A complete redesign of the company website.
 * @bodyParam note string Internal note (optional). Example: Client prefers Figma for designs.
 * @bodyParam enable_tasks_time_entries boolean Whether time entries are enabled. Example: true
  * @bodyParam user_id int[] required Array of user IDs to assign. Example: [1, 2, 3]
 * @bodyParam client_id int[] required Array of client IDs to assign. Example: [1, 43]
 * @bodyParam tag_ids int[] required Array of tag IDs to attach. Example: [1]

 * @bodyParam isApi boolean Optional flag to determine API-specific behavior. Example: true
 * @bodyParam workspace_id int Workspace Id . Must exist in wprkspaces table . example:2
 *

 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 scenario="Success" {
 *   "error": false,
 *   "message": "Project created successfully.",
 *   "id": 85,
 *   "data": {
 *     "id": 85,
 *     "title": "Website Redesign",
 *     "task_count": 0,
 *     "status": "Open",
 *     "status_id": 1,
 *     "priority": "high",
 *     "priority_id": 1,
 *     "users": [
 *       {
 *         "id": 1,
 *         "first_name": "super",
 *         "last_name": "Admin",
 *         "email": "superadmin@gmail.com",
 *         "photo": "http://localhost:8000/storage/photos/no-image.jpg"
 *       }
 *     ],
 *     "user_id": [1, 2, 3],
 *     "clients": [
 *       {
 *         "id": 1,
 *         "first_name": "jerry",
 *         "last_name": "ginny",
 *         "email": "jg@gmail.com",
 *         "photo": "http://localhost:8000/storage/photos/sample.jpg"
 *       }
 *     ],
 *     "client_id": [1, 28],
 *     "tags": [
 *       {
 *         "id": 1,
 *         "title": ".first tag"
 *       }
 *     ],
 *     "tag_ids": [1],
 *     "start_date": "2025-05-01",
 *     "end_date": "2025-05-31",
 *     "budget": "5000",
 *     "task_accessibility": "project_users",
 *     "description": "A complete redesign of the company website.",
 *     "note": "Client prefers Figma for designs.",
 *     "favorite": false,
 *     "client_can_discuss": null,
 *     "created_at": "2025-05-30",
 *     "updated_at": "2025-05-30"
 *   }
 * }
 *
 * @response 422 scenario="Validation errors" {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "title": ["The title field is required."],
 *     "status_id": ["The status field is required."],
 *     "start_date": ["The start date must be before end date."],
 *     "budget": ["The budget format is invalid."]
 *   }
 * }
 *
 * @response 403 scenario="Unauthorized status change" {
 *   "error": true,
 *   "message": "You are not authorized to set this status.",
 *   "data": [],
 *   "code": 403
 * }
 *
 * @response 500 scenario="Unexpected server error" {
 *   "error": true,
 *   "message": "Something went wrong while creating the project.",
 *   "code": 500
 * }
 */





public function store(Request $request)
{
    $workspaceId = $request->header('workspace_id') ?? session()->get('workspace_id');
    // dd($workspaceId);
    $this->workspace = Workspace::find($workspaceId);
    $this->user = getAuthenticatedUser();

    if (!$this->workspace) {
        return response()->json(['error' => true, 'message' => 'Missing or invalid workspace.'], 400);
    }

    $isApi = $request->get('isApi', false);

    try {
        $adminId = getAdminIdByUserRole();
        $formFields = $request->validate([
            'title' => ['required'],
            'status_id' => ['required'],
            'priority_id' => ['nullable'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required'],
            'budget' => ['nullable', 'regex:/^\d+(\.\d+)?$/'],
            'task_accessibility' => ['required'],
            'description' => ['nullable'],
            'note' => ['nullable'],
            'enable_tasks_time_entries' => 'boolean',
        ], [
            'status_id.required' => 'The status field is required.'
        ]);

        $status = Status::findOrFail($request->input('status_id'));

        if (!canSetStatus($status)) {
            return response()->json(['error' => true, 'message' => 'You are not authorized to set this status.']);
        }

        // $formFields['start_date'] = format_date($request->input('start_date'), false, app('php_date_format'), 'Y-m-d');
        // $formFields['end_date'] = format_date($request->input('end_date'), false, app('php_date_format'), 'Y-m-d');
        $start_date = $request->input('start_date');
                $end_date = $request->input('end_date');
                if ($start_date) {
                    $formFields['start_date'] = format_date($start_date, false, $isApi ? 'Y-m-d' : app('php_date_format'), 'Y-m-d');
                }
                if ($end_date) {
                    $formFields['end_date'] = format_date($end_date, false, $isApi ? 'Y-m-d' : app('php_date_format'), 'Y-m-d');
                }
        $formFields['admin_id'] = $adminId;
        $formFields['workspace_id'] = $this->workspace->id;
        $formFields['created_by'] = $this->user->id;

        $new_project = Project::create($formFields);

        $userIds = $request->input('user_id') ?? [];
        $clientIds = $request->input('client_id') ?? [];
        $tagIds = $request->input('tag_ids') ?? [];

        // Add creator to participants
        if (Auth::guard('client')->check() && !in_array($this->user->id, $clientIds)) {
            array_unshift($clientIds, $this->user->id);
        } elseif (Auth::guard('web')->check() && !in_array($this->user->id, $userIds)) {
            array_unshift($userIds, $this->user->id);
        }

        $project = Project::find($new_project->id);
        $project->users()->attach($userIds);
        $project->clients()->attach($clientIds);
        $project->tags()->attach($tagIds);

        $project->statusTimelines()->create([
            'status' => $status->title,
            'new_color' => $status->color,
            'previous_status' => '-',
            'changed_at' => now(),
        ]);
        if ($request->has('custom_fields')) {
                foreach ($request->custom_fields as $field_id => $value) {
                    // Handle checkboxes (arrays)
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }

                    $project->customFieldValues()->create([
                        'custom_field_id' => $field_id,
                        'value' => $value
                    ]);
                }
            }

        // Notification
        $notification_data = [
            'type' => 'project',
            'type_id' => $project->id,
            'type_title' => $project->title,
            'access_url' => 'projects/information/' . $project->id,
            'action' => 'assigned',
            'workspace_id' => $this->workspace->id,
            // dd($this->workspace->id)
        ];

        $recipients = array_merge(
            array_map(fn($id) => 'u_' . $id, $userIds),
            array_map(fn($id) => 'c_' . $id, $clientIds)
        );

        processNotifications($notification_data, $recipients);

        if ($isApi) {
            return formatApiResponse(false, 'Project created successfully.', [
                // 'id' => $project->id,
                'data' => formatProject($project),
            ]);
        }

        return response()->json([
            'error' => false,
            'id' => $project->id,
            'message' => 'Project created successfully.'
        ]);
    } catch (ValidationException $e) {
        // dd($e);
        return formatApiValidationError($isApi, $e->errors());
    } catch (Exception $e) {
        return formatApiResponse(true, 'Project could not be created.', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);
    }
}


  /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = Project::findOrFail($id);
        $projectTags = $project->tags;
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        $types = getControllerNames();
        $statuses = Status::where("admin_id", getAdminIdByUserRole())->get();
        $toSelectTaskUsers = $project->users;
        $comments = $project->comments;
        $customFields = CustomField::where('module', 'project')->get();
        return view('projects.project_information', ['project' => $project, 'projectTags' => $projectTags, 'users' => $users, 'clients' => $clients, 'types' => $types, 'auth_user' => $this->user, 'statuses' => $statuses, 'toSelectTaskUsers' => $toSelectTaskUsers, 'comments' => $comments, 'customFields' => $customFields ]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        $adminId = getAdminIdByUserRole();
        $statuses = Status::where("admin_id", getAdminIdByUserRole())->get();
        $tags = Tag::where('admin_id', $adminId)->get();

        $customFields = CustomField::where('module', 'project')->get();
         // Prepare custom field values for the view
        $customFieldValues = [];
        foreach ($project->customFieldValues as $fieldValue) {
            $customFieldValues[$fieldValue->custom_field_id] = $fieldValue->value;
        }

        return view('projects.update_project', ["project" => $project, "users" => $users, "clients" => $clients, 'statuses' => $statuses, 'tags' => $tags,  'customFields' => $customFields, 'customFieldValues' => $customFieldValues ]);
    }

    /**
 * Update an existing project.
 *@group Project Managemant
 * This endpoint updates an existing project by its ID, including title, dates, users, clients, tags, and status.
 * It also handles syncing assigned users, clients, and tags with the project, logs status change timelines, and dispatches notifications.
 *
 * @authenticated
 *
 * @header workspace_id int required The ID of the workspace to which the project belongs.
 * @queryParam isApi boolean Optional. Set to true if you want API formatted response. Example: true
 *
 * @bodyParam id int required The ID of the project to update. Example: 111
 * @bodyParam title string required The title of the project. Example: "Website Redesign"
 * @bodyParam status_id int required The ID of the status to assign. Example: 1
 * @bodyParam priority_id int The ID of the priority to assign. Nullable. Example: 4
 * @bodyParam budget int The budget allocated to the project. Nullable. Example: 5000
 * @bodyParam start_date date required The start date of the project. Must be before or equal to end_date. Format: Y-m-d. Example: 2025-05-01
 * @bodyParam end_date date required The end date of the project. Format: Y-m-d. Example: 2025-05-31
 * @bodyParam task_accessibility string required The task accessibility setting. Example: project_users
 * @bodyParam description string A brief description of the project. Nullable. Example: "A complete redesign of the company website."
 * @bodyParam note string Additional notes for the project. Nullable. Example: "Client prefers Figma for designs."
 * @bodyParam user_id int[] required Array of user IDs to assign. Example: [1, 2, 3]
 * @bodyParam client_id int[] required Array of client IDs to assign. Example: [1, 43]
 * @bodyParam tag_ids int[] required Array of tag IDs to attach. Example: [1]
 * @bodyParam enable_tasks_time_entries boolean Whether to enable time entries on tasks. Example: true

 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 scenario="Success" {
 *   "error": false,
 *   "message": "Project updated successfully.",
 *   "id": 111,
 *   "data": {
 *     "id": 111,
 *     "title": "updated",
 *     "task_count": 0,
 *     "status": "Open",
 *     "status_id": 1,
 *     "priority": "r",
 *     "priority_id": 4,
 *     "users": [
 *       {
 *         "id": 2,
 *         "first_name": "herry",
 *         "last_name": "porter",
 *         "email": "admin@gmail.com",
 *         "photo": "http://localhost:8000/storage/photos/no-image.jpg"
 *       }
 *     ],
 *     "user_id": [2],
 *     "clients": [],
 *     "client_id": [],
 *     "tags": [
 *       {
 *         "id": 1,
 *         "title": "first tag"
 *       }
 *     ],
 *     "tag_ids": [1],
 *     "start_date": "2025-05-01",
 *     "end_date": "2025-05-31",
 *     "budget": "5000",
 *     "task_accessibility": "project_users",
 *     "description": "A complete redesign of the company website.",
 *     "note": "Client prefers Figma for designs.",
 *     "favorite": 0,
 *     "client_can_discuss": null,
 *     "created_at": "2025-06-09",
 *     "updated_at": "2025-06-09"
 *   }
 * }
 *
 * @response 422 scenario="Validation failed" {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "title": ["The title field is required."]
 *   }
 * }
 *
 * @response 500 scenario="Unexpected error" {
 *   "error": true,
 *   "message": "An error occurred while updating the project."
 * }
 */

public function update(Request $request)
    {
         $isApi = $request->get('isApi', false);

    // try {
        $formFields = $request->validate([
            'id' => 'required|exists:projects,id',
            'title' => ['required'],
            'status_id' => ['required'],
            'priority_id' => ['nullable'],
            'budget' => ['nullable', 'integer'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required'],
            'task_accessibility' => ['required'],
            'description' => ['nullable'],
            'note' => ['nullable'],
            'user_id' => ['array'], // Ensuring these are arrays
            'client_id' => ['array'],
            'tag_ids' => ['array'],
            'enable_tasks_time_entries' =>  'boolean',
        ]);
        $id = $formFields['id'];
        $project = Project::findOrFail($id);
        $currentStatusId = $project->status_id;
        $workspace = $project->workspace;

        if ($currentStatusId != $formFields['status_id']) {
            $status = Status::findOrFail($formFields['status_id']);
            if (!canSetStatus($status)) {
                return response()->json(['error' => true, 'message' => 'You are not authorized to set this status.']);
            }
            // Status Time Storing
            $oldStatus = Status::findOrFail($currentStatusId);
            $newStatus = Status::findOrFail($formFields['status_id']);
            $project->statusTimelines()->create([
                'status' => $newStatus->title,
                'new_color' => $newStatus->color,
                'previous_status' => $oldStatus->title,
                'old_color' => $oldStatus->color,
                'changed_at' => now(),
            ]);
        }
        // Format dates
        // $formFields['start_date'] = format_date($formFields['start_date'], false, app('php_date_format'), 'Y-m-d');
        // $formFields['end_date'] = format_date($formFields['end_date'], false, app('php_date_format'), 'Y-m-d');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
                if ($start_date) {
                    $formFields['start_date'] = format_date($start_date, false, $isApi ? 'Y-m-d' : app('php_date_format'), 'Y-m-d');
                }
                if ($end_date) {
                    $formFields['end_date'] = format_date($end_date, false, $isApi ? 'Y-m-d' : app('php_date_format'), 'Y-m-d');
                }
        // Remove user_id and client_id from $formFields to prevent updating directly on the projects table
        unset($formFields['user_id'], $formFields['client_id']);
        // Retrieve user and client IDs, defaulting to empty arrays
        $userIds = $request->input('user_id', []);
        $clientIds = $request->input('client_id', []);
        $tagIds = $request->input('tag_ids', []);
        // Automatically set creator as a participant if not already included
        $creatorId = $project->created_by;
        if (!in_array($creatorId, $userIds) && User::find($creatorId)) {
            array_unshift($userIds, $creatorId);
        } elseif (!in_array($creatorId, $clientIds) && Client::find($creatorId)) {
            array_unshift($clientIds, $creatorId);
        }
        // Update the project details
        $project->update($formFields);
        // Use workspace relations to ensure only workspace users and clients are synced
        $workspaceUserIds = $workspace->users->pluck('id')->toArray();
        $workspaceClientIds = $workspace->clients->pluck('id')->toArray();
        // Sync only valid workspace users and clients
        $validUserIds = array_intersect($userIds, $workspaceUserIds);
        $validClientIds = array_intersect($clientIds, $workspaceClientIds);
        // Sync relationships with users, clients, and tags
        $project->users()->sync($validUserIds);
        $project->clients()->sync($validClientIds);
        $project->tags()->sync($tagIds);

        // Update custom field values
        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $field_id => $value) {
                // Handle checkboxes (arrays)
                if (is_array($value)) {
                    $value = json_encode($value);
                }

                // Find existing custom field value or create new
                $fieldValue = $project->customFieldValues()
                    ->where('custom_field_id', $field_id)
                    ->first();

                if ($fieldValue) {
                    $fieldValue->update(['value' => $value]);
                } else {
                    $project->customFieldValues()->create([
                        'custom_field_id' => $field_id,
                        'value' => $value
                    ]);
                }
            }
        }
        
        // Prepare notification data
        $notificationData = [
            'type' => 'project',
            'type_id' => $project->id,
            'type_title' => $project->title,
            'access_url' => 'projects/information/' . $project->id,
            'action' => 'assigned',
            'title' => 'Project Updated',
            'message' => $this->user->first_name . ' ' . $this->user->last_name . ' assigned you new project: ' . $project->title . ', ID #' . $project->id . '.'
        ];
        // Determine recipients
        $recipients = array_merge(
            array_map(fn($userId) => 'u_' . $userId, $validUserIds),
            array_map(fn($clientId) => 'c_' . $clientId, $validClientIds)
        );
        // Process notifications
        processNotifications($notificationData, $recipients);
         $project = $project->fresh();
            return formatApiResponse(
                false,
                'Project updated successfully.',
                [
                    // 'id' => $project->id,
                    'data' => formatProject($project)
                ]
            );
        // } catch (ValidationException $e) {
        //     return formatApiValidationError($isApi, $e->errors());
        // } catch (\Exception $e) {
        //     // Handle any unexpected errors
        //     return response()->json([
        //         'error' => true,
        //         'message' => 'An error occurred while updating the project.'
        //     ], 500);
        // }
    }

    public function get($projectId)
    {
        $project = Project::findOrFail($projectId);
        $users = $project->users()->get();
        $clients = $project->clients()->get();
        $tags = $project->tags()->get();
        $workspace_users = $this->workspace->users;
        $workspace_clients = $this->workspace->clients;
        $task_lists = $project->taskLists;

        $customFields = CustomField::where('module', 'project')->get();
          // Prepare custom field values for the view
        $customFieldValues = [];
        foreach ($project->customFieldValues as $fieldValue) {
            $customFieldValues[$fieldValue->custom_field_id] = $fieldValue->value;
        }

        return response()->json(['error' => false, 'project' => $project, 'users' => $users, 'clients' => $clients, 'workspace_users' => $workspace_users, 'workspace_clients' => $workspace_clients, 'tags' => $tags, 'task_lists' => $task_lists, 'customFields' => $customFields, 'customFieldValues' => $customFieldValues ]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

/**
 * Delete a project.
 *
 * This endpoint deletes a project by its ID. It also removes all associated comments and their attachments.
 * Files are permanently removed from the public storage disk.
 *@group Project Managemant
 * @urlParam id integer required The ID of the project to delete. Example: 85

 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 {
 *   "error": false,
 *   "message": "Project deleted successfully.",
 *   "id": "85",
 *   "title": "this is updated",
 *   "data": []
 * }
 *
 * @response 404 scenario="Project not found" {
 *   "error": true,
 *   "message": "Project not found.",
 *   "data": []
 * }
 *
 * @response 500 scenario="Unexpected error" {
 *   "error": true,
 *   "message": "An unexpected error occurred while deleting the project.",
 *   "exception": "Exception message",
 *   "line": 123,
 *   "file": "path/to/file"
 * }
 */


    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        if($project){
        // Get all attachments before deletion
        $comments = $project->comments()->with('attachments')->get();
        // Delete all files using public disk
        $comments->each(function ($comment) {
            $comment->attachments->each(function ($attachment) {
                Storage::disk('public')->delete($attachment->file_path);
                $attachment->delete();
            });
        });
        $project->comments()->forceDelete();
        $response = DeletionService::delete(Project::class, $id, 'Project');
        return $response;
    }
else {
            return formatApiResponse(
                true,
                'Project not found.',
                []
            );
        }
     }


    /**
 * Delete multiple projects.
 *
 * This endpoint allows you to delete multiple projects by providing their IDs.
 * All related comments and attachments will also be permanently deleted.
 *@group Project Managemant
 * @bodyParam ids array required An array of project IDs to delete. Example: [1, 2, 3]
 * @bodyParam ids.* integer required Each ID must exist in the projects table.

 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 {
 *   "error": false,
 *   "message": "Project(s) deleted successfully.",
 *   "ids": [1, 2, 3],
 *   "titles": ["Project A", "Project B", "Project C"]
 * }
 * @response 422 {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "ids": ["The ids field is required."]
 *   }
 * }
 * @authenticated
 */

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:projects,id' // Ensure each ID in 'ids' is an integer and exists in the 'projects' table
        ]);
        $ids = $validatedData['ids'];
        $deletedProjectTitles = [];
        // Retrieve all projects by the given IDs
        $projects = Project::whereIn('id', $ids)->get();
        if ($projects->isEmpty()) {
            return response()->json(['error' => true, 'message' => 'No projects found to delete.']);
        }
        // Collect project titles and delete all associated comments in bulk
        foreach ($projects as $project) {
            $deletedProjectTitles[] = $project->title;
            $comments = $project->comments()->with('attachments')->get();
            // Delete all files using public disk
            $comments->each(function ($comment) {
                $comment->attachments->each(function ($attachment) {
                    Storage::disk('public')->delete($attachment->file_path);
                    $attachment->delete();
                });
            });
            $project->comments()->forceDelete();
        }
        // Bulk delete associated comments for all projects
        // Bulk delete projects using the DeletionService
        foreach ($ids as $id) {
            DeletionService::delete(Project::class, $id, 'Project');
        }
        return response()->json(['error' => false, 'message' => 'Project(s) deleted successfully.', 'ids' => $ids, 'titles' => $deletedProjectTitles]);
    }
     public function list(Request $request, $id = '', $type = '')
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $status = isset($_REQUEST['status']) && $_REQUEST['status'] !== '' ? $_REQUEST['status'] : "";
        $user_id = (request('user_id')) ? request('user_id') : "";
        $client_id = (request('client_id')) ? request('client_id') : "";
        $start_date_from = (request('project_start_date_from')) ? request('project_start_date_from') : "";
        $start_date_to = (request('project_start_date_to')) ? request('project_start_date_to') : "";
        $end_date_from = (request('project_end_date_from')) ? request('project_end_date_from') : "";
        $end_date_to = (request('project_end_date_to')) ? request('project_end_date_to') : "";
        $is_favorites = (request('is_favorites')) ? request('is_favorites') : "";
        $where = [];
        if ($status != '') {
            $where['status_id'] = $status;
        }
        if ($is_favorites) {
            $where['is_favorite'] = 1;
        }
        if ($id) {
            $id = explode('_', $id);
            $belongs_to = $id[0];
            $belongs_to_id = $id[1];
            $userOrClient = $belongs_to == 'user' ? User::find($belongs_to_id) : Client::find($belongs_to_id);
            $projects = isAdminOrHasAllDataAccess($belongs_to, $belongs_to_id) ? $this->workspace->projects() : $userOrClient->projects();
        } else {
            $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects() : $this->user->projects();
        }
        if ($user_id) {
            $user = User::find($user_id);
            $projects = $user->projects();
        }
        if ($client_id) {
            $client = Client::find($client_id);
            $projects = $client->projects();
        }
        if ($start_date_from && $start_date_to) {
            $projects->whereBetween('start_date', [$start_date_from, $start_date_to]);
        }
        if ($end_date_from && $end_date_to) {
            $projects->whereBetween('end_date', [$end_date_from, $end_date_to]);
        }
        $projects->when($search, function ($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%')
                ->orWhere('id', 'like', '%' . $search . '%');
        });
        $projects->where($where);
        $totalprojects = $projects->count();
        $canCreate = checkPermission('create_projects');
        $canEdit = checkPermission('edit_projects');
        $canDelete = checkPermission('delete_projects');
        $statuses = Status::where('admin_id', getAdminIDByUserRole())
            ->orWhere(function ($query) {
                $query->whereNull('admin_id')
                    ->where('is_default', 1);
            })->get();
        $priorities = Priority::where('admin_id', getAdminIDByUserRole())->get();
        $labelNote = get_label('note', 'Note');

        $customFields = CustomField::where('module', 'project')->get();

        $projects = $projects->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(
                function ($project) use ($statuses, $priorities, $canEdit, $canDelete, $canCreate, $labelNote, $customFields) {
                    $statusOptions = '';
                    foreach ($statuses as $status) {
                        // Determine if the option should be disabled
                        $disabled = canSetStatus($status) ? '' : 'disabled';
                        // Render the option with appropriate attributes
                        $selected = $project->status_id == $status->id ? 'selected' : '';
                        $statusOptions .= "<option value='{$status->id}' class='badge bg-label-$status->color' $selected $disabled>$status->title</option>";
                    }
                    $priorityOptions = "";
                    foreach ($priorities as $priority) {
                        $selected = $project->priority_id == $priority->id ? 'selected' : '';
                        $priorityOptions .= "<option value='{$priority->id}' class='badge bg-label-$priority->color' $selected>$priority->title</option>";
                    }
                    $actions = '';
                    if ($canEdit) {
                        $actions .= '<a href="javascript:void(0);" class="edit-project" data-id="' . $project->id . '" title="' . get_label('update', 'Update') . '">' .
                            '<i class="bx bx-edit mx-1"></i>' .
                            '</a>';
                    }
                    if ($canDelete) {
                        $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $project->id . '" data-type="projects" data-table="projects_table">' .
                            '<i class="bx bx-trash text-danger mx-1"></i>' .
                            '</button>';
                    }
                    if ($canCreate) {
                        $actions .= '<a href="javascript:void(0);" class="duplicate" data-id="' . $project->id . '" data-title="' . $project->title . '" data-type="projects" data-table="projects_table" title="' . get_label('duplicate', 'Duplicate') . '">' .
                            '<i class="bx bx-copy text-warning mx-2"></i>' .
                            '</a>';
                    }
                    $actions .= '<a href="javascript:void(0);" class="quick-view" data-id="' . $project->id . '" data-type="project" title="' . get_label('quick_view', 'Quick View') . '">' .
                        '<i class="bx bx-info-circle mx-3"></i>' .
                        '</a>';
                    $actions = $actions ?: '-';
                    $userHtml = '';
                    if (!empty($project->users) && count($project->users) > 0) {
                        $userHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                        foreach ($project->users as $user) {
                            $userHtml .= "<li class='avatar avatar-sm pull-up'><a href='" . route('users.show', ['id' => $user->id]) . "' target='_blank' title='{$user->first_name} {$user->last_name}'><img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                        }
                        if ($canEdit) {
                            $userHtml .= '<li title=' . get_label('update', 'Update') . '><a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients" data-id="' . $project->id . '"><span class="bx bx-edit"></span></a></li>';
                        }
                        $userHtml .= '</ul>';
                    } else {
                        $userHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
                        if ($canEdit) {
                            $userHtml .= '<a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients" data-id="' . $project->id . '">' .
                                '<span class="bx bx-edit"></span>' .
                                '</a>';
                        }
                    }
                    $clientHtml = '';
                    if (!empty($project->clients) && count($project->clients) > 0) {
                        $clientHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                        foreach ($project->clients as $client) {
                            $clientHtml .= "<li class='avatar avatar-sm pull-up'><a href='" . route('clients.profile', ['id' => $client->id]) . "' target='_blank' title='{$client->first_name} {$client->last_name}'><img src='" . ($client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                        }
                        if ($canEdit) {
                            $clientHtml .= '<li title=' . get_label('update', 'Update') . '><a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients" data-id="' . $project->id . '"><span class="bx bx-edit"></span></a></li>';
                        }
                        $clientHtml .= '</ul>';
                    } else {
                        $clientHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
                        if ($canEdit) {
                            $clientHtml .= '<a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients" data-id="' . $project->id . '">' .
                                '<span class="bx bx-edit"></span>' .
                                '</a>';
                        }
                    }
                    $tagHtml = '';
                    foreach ($project->tags as $tag) {
                        $tagHtml .= "<span class='badge bg-label-{$tag->color}'>{$tag->title}</span> ";
                    }
                    $description = \Illuminate\Support\Str::limit(strip_tags($project->description), 25);
                    $row = [
                        'id' => $project->id,
                        'title' => "<a href='" . route('projects.info', ['id' => $project->id]) . "' target='_blank' title=' {$description}'><strong>{$project->title}</strong></a> <a href='javascript:void(0);' class='mx-2'><i class='bx " . ($project->is_favorite ? 'bxs' : 'bx') . "-star favorite-icon text-warning' data-favorite='{$project->is_favorite}' data-id='{$project->id}' title='" . ($project->is_favorite ? get_label('remove_favorite', 'Click to remove from favorite') : get_label('add_favorite', 'Click to mark as favorite')) . "'></i></a>
                <a href='" . route('projects.info', ['id' => $project->id]) . "#navs-top-discussions'  target='_blank'  class='mx-2'>
                <i class='bx bx-message-rounded-dots text-danger' data-bs-toggle='tooltip' data-bs-placement='right' title='" . get_label('discussions', 'Discussions') . "'></i>
                </a>",
                        'users' => $userHtml,
                        'clients' => $clientHtml,
                        'start_date' => format_date($project->start_date),
                        'end_date' => format_date($project->end_date),
                        'budget' => !empty($project->budget) && $project->budget !== null ? format_currency($project->budget) : '-',
                        'status_id' => "
                       <div class='d-flex align-items-center'>
                         <select class='form-select form-select-sm select-bg-label-{$project->status->color}'
                            id='statusSelect' data-id='{$project->id}' data-original-status-id='{$project->status->id}' data-original-color-class='select-bg-label-{$project->status->color}'> {$statusOptions} </select>
                              " . (!empty($project->note) ? "
                            <span class='ms-2' data-bs-toggle='tooltip' title='{$labelNote}:{$project->note}'> <i class='bx bxs-notepad text-primary'></i></span>" : "") .
                            " </div>
                            ",
                        'priority_id' => "<select class='form-select form-select-sm select-bg-label-" . ($project->priority ? $project->priority->color : 'secondary') . "' id='prioritySelect' data-id='{$project->id}' data-original-priority-id='" . ($project->priority ? $project->priority->id : '') . "' data-original-color-class='select-bg-label-" . ($project->priority ? $project->priority->color : 'secondary') . "'>{$priorityOptions}</select>",
                        'task_accessibility' => get_label($project->task_accessibility, ucwords(str_replace("_", " ", $project->task_accessibility))),
                        'tags' => $tagHtml ?: ' - ',
                        'created_at' => format_date($project->created_at, true),
                        'updated_at' => format_date($project->updated_at, true),
                        'tasks_count' => $project->tasks()->count(),
                        'actions' => $actions
                    ];
                    // Add custom field values dynamically
                    foreach ($customFields as $customField) {
                        $customFieldValue = $project->customFieldValues()
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
                    
                        $row['custom_field_' . $customField->id] = $customFieldValue ?? '-';
                    }
                    
                    

                    return $row;
                }
            );

        return response()->json([
            "rows" => $projects->items(),
            "total" => $totalprojects,
        ]);
    }

/**
 * Get Project(s)
 *
 * Fetch a single project by ID or a list of projects with optional filters.
 *@group Project Managemant
 * This endpoint retrieves one specific project if an ID is provided, or returns a paginated list of projects
 * based on applied filters such as status, users, clients, date range, search terms, and favorite flag.
 *
 * @authenticated
 *
 * @urlParam id int optional The ID of the project to retrieve. If provided, other filters are ignored. Example: 23
 *
 * @queryParam search string optional Search by project title, description, or ID. Example: redesign
 * @queryParam sort string optional Column to sort by. Default is `created_at`. Example: title
 * @queryParam order string optional Sort order. Accepts `asc` or `desc`. Default is `desc`. Example: asc
 * @queryParam limit int optional Number of results per page. Default is 10. Example: 5
 * @queryParam offset int optional Offset for pagination. Default is 0. Example: 10
 * @queryParam status int optional Filter by status ID. Example: 1
 * @queryParam user_id int optional Filter by user ID (assigned user). Example: 2
 * @queryParam client_id int optional Filter by client ID. Example: 1
 * @queryParam project_start_date_from date optional Filter by project start date from (YYYY-MM-DD). Example: 2025-01-01
 * @queryParam project_start_date_to date optional Filter by project start date to (YYYY-MM-DD). Example: 2025-12-31
 * @queryParam project_end_date_from date optional Filter by project end date from (YYYY-MM-DD). Example: 2025-01-01
 * @queryParam project_end_date_to date optional Filter by project end date to (YYYY-MM-DD). Example: 2025-12-31
 * @queryParam is_favorites boolean optional Filter for favorite projects. Accepts 1 or 0. Example: 1

 * @header Accept application/json
 * @header workspace-id 2
 * @response 200 scenario="Single project found" {
 *   "error": false,
 *   "message": "Project retrieved successfully",
 *   "total": 1,
 *   "data": [
 *     {
 *       "id": 23,
 *       "title": "this is A projects",
 *       "task_count": 0,
 *       "status": "Open",
 *       "status_id": 1,
 *       "priority": "low",
 *       "priority_id": 2,
 *       "users": [
 *         {
 *           "id": 2,
 *           "first_name": "herry",
 *           "last_name": "porter",
 *           "email": "admin@gmail.com",
 *           "photo": "http://localhost:8000/storage/photos/no-image.jpg"
 *         }
 *       ],
 *       "user_id": [2],
 *       "clients": [
 *         {
 *           "id": 1,
 *           "first_name": "jerry",
 *           "last_name": "ginny",
 *           "email": "jg@gmail.com",
 *           "photo": "http://localhost:8000/storage/photos/gqHsvgmDBCbtf843SRYx31e6Zl51amPZY8eG05FB.jpg"
 *         }
 *       ],
 *       "client_id": [1],
 *       "tags": [
 *         {
 *           "id": 1,
 *           "title": ".first tag"
 *         }
 *       ],
 *       "tag_ids": [1],
 *       "start_date": "2025-05-20",
 *       "end_date": "2025-05-25",
 *       "budget": "5000.00",
 *       "task_accessibility": "private",
 *       "description": "Project description here...",
 *       "note": "Internal note",
 *       "favorite": false,
 *       "client_can_discuss": null,
 *       "created_at": "2025-05-20",
 *       "updated_at": "2025-05-20"
 *     }
 *   ]
 * }
 *
 * @response 404 scenario="Project not found" {
 *   "error": true,
 *   "message": "Project not found",
 *   "total": 0,
 *   "data": []
 * }
 *
 * @response 500 scenario="Unexpected error" {
 *   "error": true,
 *   "message": "Lead Couldn't Created.",
 *   "error": "Some error message",
 *   "line": 143,
 *   "file": "/app/Http/Controllers/ProjectController.php"
 * }
 */



public function apiList(Request $request, $id = null)
{
     $isApi = $request->get('isApi', false);
     if (!$this->workspace) {
        return formatApiResponse(
            true,
            'The workspace you selected is invalid.',
            // [
            //     'total' => 0,
            //     'data' => []
            // ]
        );
    }
    try {
        // Input filters
        $search = $request->input('search', '');
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        $per_page = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $status = $request->input('status');
        $user_id = $request->input('user_id');
        $client_id = $request->input('client_id');
        $start_date_from = $request->input('project_start_date_from');
        $start_date_to = $request->input('project_start_date_to');
        $end_date_from = $request->input('project_end_date_from');
        $end_date_to = $request->input('project_end_date_to');
        $is_favorites = $request->input('is_favorites');

        // Single project fetch
        if ($id) {
            $project = Project::with(['status', 'priority', 'tags', 'users', 'clients', 'tasks'])->find($id);

            if (!$project) {
                return formatApiResponse(
                    true,
                    'Project not found',
                    [
                        'total' => 0,
                        'data' => []
                    ]
                );
            }

            return formatApiResponse(
                false,
                'Project retrieved successfully',
                [
                    'total' => 1,
                    'data' => [formatProject($project)]
                ]
            );
        } else {
            // Build query
            $query = Project::with(['status', 'priority', 'tags', 'users', 'clients', 'tasks']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%")
                        ->orWhere('id', 'like', "%$search%");
                });
            }

            if ($status !== null) {
                $query->where('status_id', $status);
            }

            if ($is_favorites) {
                $query->where('is_favorite', 1);
            }

            if ($user_id) {
                $query->whereHas('users', fn($q) => $q->where('users.id', $user_id));
            }

            if ($client_id) {
                $query->whereHas('clients', fn($q) => $q->where('clients.id', $client_id));
            }

            if ($start_date_from && $start_date_to) {
                $query->whereBetween('start_date', [$start_date_from, $start_date_to]);
            }

            if ($end_date_from && $end_date_to) {
                $query->whereBetween('end_date', [$end_date_from, $end_date_to]);
            }

            // Get total and fetch data
            $total = $query->count();

            $projects = $query->orderBy($sort, $order)
                ->paginate($per_page);

            $formattedProjects = $projects->map(fn($project) => formatProject($project));
            
            return formatApiResponse(
                false,
                'Projects retrieved successfully.',
                [
                    'total' => $total,
                    'data' => $formattedProjects
                ]
            );
            
        }
    } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (Exception $e) {
            return formatApiResponse(
                true,
                'Lead Couldn\'t Created.',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            );
        }
}



    /**
     * Update the favorite status of a project.
     *
     * This endpoint updates whether a project is marked as a favorite or not. The user must be authenticated to perform this action.
     *
     * @authenticated
     *
     * @group Project Managemant
     *
     * @urlParam id int required The ID of the project to update.
     * @bodyParam is_favorite int required Indicates whether the project is a favorite. Use 1 for true and 0 for false.
     * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
     * @response 200 {
     * "error": false,
     * "message": "Project favorite status updated successfully",
     * "data": {
     * "id": 438,
     * "title": "Res Test",
     * "status": "Default",
     * "priority": "dsfdsf",
     * "users": [
     * {
     * "id": 7,
     * "first_name": "Madhavan",
     * "last_name": "Vaidya",
     * "photo": "https://test-taskify.infinitietech.com/storage/photos/yxNYBlFLALdLomrL0JzUY2USPLILL9Ocr16j4n2o.png"
     * }
     * ],
     * "clients": [
     * {
     * "id": 103,
     * "first_name": "Test",
     * "last_name": "Test",
     * "photo": "https://test-taskify.infinitietech.com/storage/photos/no-image.jpg"
     * }
     * ],
     * "tags": [
     * {
     * "id": 45,
     * "title": "Tag from update project"
     * }
     * ],
     * "start_date": null,
     * "end_date": null,
     * "budget": "1000.00",
     * "task_accessibility": "assigned_users",
     * "description": null,
     * "note": null,
     * "favorite": 1,
     * "created_at": "07-08-2024 14:38:51",
     * "updated_at": "12-08-2024 13:36:10"
     * }
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "Validation errors occurred",
     *   "errors": {
     *     "is_favorite": [
     *       "The is favorite field must be either 0 or 1."
     *     ]
     *   }
     * }
     *
     * @response 200 {
     *   "error": true,
     *   "message": "Project not found",
     *   "data": []
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while updating the favorite status."
     * }
     */

    public function update_favorite(Request $request, $id)
    {
        $isApi = request()->get('isApi', false);
        try {
        $project = Project::find($id);
        // DD($project);
        if (!$project) {
            return response()->json(['error' => true, 'message' => 'Project not found']);
        }
        $isFavorite = $request->input('is_favorite');
        // DD($isFavorite);
        // Update the project's favorite status
        $project->is_favorite = $isFavorite;
        $project->save();
        // dd($project);
          return formatApiResponse(
                false,
                'Project favorite status updated successfully',
                ['data' => formatProject($project)]
            );
     } catch (ValidationException $e) {
             // dd($e->errors());
             return formatApiValidationError($isApi, $e->errors());
         } catch (\Exception $e) {
             dd($e->getMessage());
             // Handle any unexpected errors
             return response()->json([
             'error' => true,
                 'message' => 'An error occurred while updating the project favorite status.'
             ], 500);
         }
     }




    /**
 * Duplicate a project.
 *@group Project Managemant
 * This endpoint duplicates a project and its related data such as users, clients, tasks, and tags.
 * Optionally, a new title can be provided for the duplicated project.
 *
 * @urlParam id integer required The ID of the project to duplicate. Example: 12
 * @queryParam title string Optional. A new title for the duplicated project. Example: New Project Copy
 * @queryParam reload boolean Optional. If true, flashes a session message. Example: true
 * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 {
 *   "error": false,
 *   "message": "Project duplicated successfully.",
 *   "id": 12
 * }
 * @response 400 {
 *   "error": true,
 *   "message": "Project duplication failed."
 * }
 * @authenticated
 */

    public function duplicate($id)
    {
        // Define the related tables for this meeting
        $relatedTables = ['users', 'clients', 'tasks', 'tags']; // Include related tables as needed
        // Use the general duplicateRecord function
        $title = (request()->has('title') && !empty(trim(request()->title))) ? request()->title : '';
        $duplicate = duplicateRecord(Project::class, $id, $relatedTables, $title);
        if (!$duplicate) {
            return response()->json(['error' => true, 'message' => 'Project duplication failed.']);
        }
        if (request()->has('reload') && request()->input('reload') === 'true') {
            Session::flash('message', 'Project duplicated successfully.');
        }
        return response()->json(['error' => false, 'message' => 'Project duplicated successfully.', 'id' => $id]);
    }
   /**
 * Upload media files to a specific project.
 *
 * This endpoint allows uploading one or multiple media files associated with a project.
 *@group Project Media
 * @bodyParam id integer required The ID of the project to attach media to. Example: 15
 * @bodyParam media_files[] file required One or more files to upload (multipart/form-data).
 * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 {
 *   "error": false,
 *   "message": "Media uploaded successfully.",
 *   "id": [6, 7],
 *   "data": [
 *     {
 *       "id": 6,
 *       "name": "maxresdefault",
 *       "file_name": "maxresdefault.jpg",
 *       "file_size": 72106,
 *       "file_type": "image/jpeg",
 *       "created_at": "2025-06-02",
 *       "updated_at": "2025-06-02"
 *     },
 *     {
 *       "id": 7,
 *       "name": "screenshot",
 *       "file_name": "screenshot.png",
 *       "file_size": 45000,
 *       "file_type": "image/png",
 *       "created_at": "2025-06-02",
 *       "updated_at": "2025-06-02"
 *     }
 *   ]
 * }
 *
 * @response 422 {
 *   "error": true,
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "media_files.0": ["The media files.0 may not be greater than 2048 kilobytes."],
 *     "id": ["The selected id is invalid."]
 *   }
 * }
 *
 * @response 400 {
 *   "error": true,
 *   "message": "No file(s) chosen."
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Project could not be created.",
 *   "error": "Detailed exception message here",
 *   "line": 123,
 *   "file": "/path/to/file.php"
 * }
 *
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\JsonResponse
 */


    public function upload_media(Request $request)
{
    $isApi = request()->get('isApi', false);

    try {
        $maxFileSizeBytes = config('media-library.max_file_size');
        $maxFileSizeKb = (int) ($maxFileSizeBytes / 1024);

        $validatedData = $request->validate([
            'id' => ['required', 'integer', 'exists:projects,id'],
            'media_files.*' => "file|max:$maxFileSizeKb"
        ]);

        $mediaIds = [];
        $formattedMedia = [];

        if ($request->hasFile('media_files')) {
            $project = Project::find($validatedData['id']);
            $mediaFiles = $request->file('media_files');

            foreach ($mediaFiles as $mediaFile) {
                $mediaItem = $project->addMedia($mediaFile)
                    ->sanitizingFileName(function ($fileName) {
                        return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                    })
                    ->toMediaCollection('project-media');

                $mediaIds[] = $mediaItem->id;
                $formattedMedia[] = formateMedia($mediaItem);
            }

            if ($isApi) {
                return formatApiResponse(false, 'Media uploaded successfully.', [
                    'id' => $mediaIds,
                    'data' => $formattedMedia
                ]);
            }

            Session::flash('message', 'File(s) uploaded successfully.');
            return response()->json([
                'error' => false,
                'message' => 'File(s) uploaded successfully.',
                'id' => $mediaIds,
                'type' => 'media',
                'parent_type' => 'project',
                'parent_id' => $project->id
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'No file(s) chosen.'
            ]);
        }
    } catch (ValidationException $e) {
        return formatApiValidationError($isApi, $e->errors());
    } catch (Exception $e) {
        return formatApiResponse(true, 'Project could not be created.', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);
    }
}
/**
 * Get project media files
 *
 * Retrieves all media files uploaded to a specific project. Supports sorting and filtering. Returns a formatted list of media with file URL, preview, and action buttons.
 *@group Project Media
 * @urlParam id integer required The ID of the project. Example: 1
 * @queryParam search string Optional. Search term to filter media by ID, file name, or creation date. Example: report
 * @queryParam sort string Optional. Field to sort by (e.g., id, file_name). Default: id. Example: file_name
 * @queryParam order string Optional. Sorting order: `asc` or `desc`. Default: desc. Example: asc
 * @queryParam isApi boolean Optional. When true, returns a formatted API response instead of JSON table structure. Example: true
 * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 scenario="Success - API response"
 * {
 *   "error": false,
 *   "message": "Media retrieved successfully.",
 *   "data": [
 *     {
 *       "id": 4,
 *       "file": "<a href=\"http://localhost:8000/storage/project-media/images.jpg\" data-lightbox=\"project-media\"> <img src=\"http://localhost:8000/storage/project-media/images.jpg\" alt=\"images.jpg\" width=\"50\"></a>",
 *       "file_name": "images.jpg",
 *       "file_size": "11.89 KB",
 *       "created_at": "2025-06-02",
 *       "updated_at": "2025-06-02",
 *       "actions": [
 *         "<a href=\"http://localhost:8000/storage/project-media/images.jpg\" title=Download download><i class=\"bx bx-download bx-sm\"></i></a><button title=Delete type=\"button\" class=\"btn delete\" data-id=\"4\" data-type=\"project-media\" data-table=\"project_media_table\"><i class=\"bx bx-trash text-danger\"></i></button>"
 *       ]
 *     }
 *   ]
 * }
 *
 * @response 200 scenario="Success - Non-API JSON table response"
 * {
 *   "rows": [
 *     {
 *       "id": 4,
 *       "file": "<a href=\"http://localhost:8000/storage/project-media/images.jpg\" data-lightbox=\"project-media\"> <img src=\"http://localhost:8000/storage/project-media/images.jpg\" alt=\"images.jpg\" width=\"50\"></a>",
 *       "file_name": "images.jpg",
 *       "file_size": "11.89 KB",
 *       "created_at": "2025-06-02",
 *       "updated_at": "2025-06-02",
 *       "actions": [
 *         "<a href=\"http://localhost:8000/storage/project-media/images.jpg\" title=Download download><i class=\"bx bx-download bx-sm\"></i></a><button title=Delete type=\"button" class="btn delete" data-id="4" data-type="project-media" data-table="project_media_table"><i class="bx bx-trash text-danger"></i></button>"
 *       ]
 *     }
 *   ],
 *   "total": 1
 * }
 *
 * @response 404 scenario="Project not found"
 * {
 *   "message": "No query results for model [App\\Models\\Project] 99"
 * }
 */



  public function get_media($id)
{
    $isApi = request()->get('isApi', false);
    $search = request('search');
    $sort = request('sort') ?? "id";
    $order = request('order') ?? "DESC";

    $project = Project::findOrFail($id);
    $media = $project->getMedia('project-media');
    // dd($media); // Debug media collection

    //    dd('Media count: ' . $media->count(), $media->toArray()); // Debug media count and dump raw


    // Skip filtering temporarily for debug

    if ($search) {
        $media = $media->filter(function ($mediaItem) use ($search) {
            return (
                stripos((string)$mediaItem->id, $search) !== false ||
                stripos($mediaItem->file_name, $search) !== false ||
                stripos($mediaItem->created_at->format('Y-m-d'), $search) !== false
            );
        });
    }


    $formattedMedia = $media->map(function ($mediaItem) {
        $isPublicDisk = $mediaItem->disk == 'public' ? 1 : 0;
        $fileUrl = $isPublicDisk
            ? asset('storage/project-media/' . $mediaItem->file_name)
            : $mediaItem->getFullUrl();

        return [
            'id' => $mediaItem->id,
            'file' => '<a href="' . $fileUrl . '" data-lightbox="project-media"> <img src="' . $fileUrl . '" alt="' . $mediaItem->file_name . '" width="50"></a>',
            'file_name' => $mediaItem->file_name,
            'file_size' => formatSize($mediaItem->size),
            'created_at' => format_date($mediaItem->created_at),
            'updated_at' => format_date($mediaItem->updated_at),
            'actions' => [
                '<a href="' . $fileUrl . '" title=' . get_label('download', 'Download') . ' download>' .
                    '<i class="bx bx-download bx-sm"></i>' .
                    '</a>' .
                    '<button title=' . get_label('delete', 'Delete') . ' type="button" class="btn delete" data-id="' . $mediaItem->id . '" data-type="project-media" data-table="project_media_table">' .
                    '<i class="bx bx-trash text-danger"></i>' .
                    '</button>'
            ],
        ];
    });

    if ($order == 'asc') {
        $formattedMedia = $formattedMedia->sortBy($sort);
    } else {
        $formattedMedia = $formattedMedia->sortByDesc($sort);
    }

    if ($isApi) {
        return formatApiResponse(false, 'Media retrieved successfully.', [
            'data' => $formattedMedia->values()->toArray(),
        ]);
    }

    return response()->json([
        'rows' => $formattedMedia->values()->toArray(),
        'total' => $formattedMedia->count(),
    ]);
}

    /**
 * Delete a single media file by ID.
 *
 * Deletes a media file record and its associated file from storage.
 *@group Project Media
 * @urlParam mediaId int required The ID of the media file to delete. Example: 101
 * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 {
 *   "error": false,
 *   "message": "File deleted successfully.",
 *   "id": 101,
 *   "title": "example.jpg",
 *   "parent_id": 15,
 *   "type": "media",
 *   "parent_type": "project"
 * }
 *
 * @response 404 scenario="Media Not Found" {
 *   "error": true,
 *   "message": "File not found."
 * }
 */
    public function delete_media($mediaId)
{
    $isApi = request()->get('isApi', false);

    try {
        $mediaItem = Media::find($mediaId);
        // dd($mediaItem);

        if (!$mediaItem) {
            $message = 'File not found.';
            return $isApi
                ? formatApiResponse(true, $message)
                : response()->json(['error' => true, 'message' => $message]);
        }

        $fileName = $mediaItem->file_name;
        $parentId = $mediaItem->model_id;

        // Delete the media file
        $mediaItem->delete();

        $successData = [
            'id' => $mediaId,
            'title' => $fileName,
            'parent_id' => $parentId,
            'type' => 'media',
            'parent_type' => 'project'
        ];

        return $isApi
            ? formatApiResponse(false, 'Media deleted successfully.', $successData)
            : response()->json(['error' => false, 'message' => 'File deleted successfully.'] + $successData);

    } catch (Exception $e) {
        $message = 'An error occurred while deleting the media file.';

        return $isApi
            ? formatApiResponse(true, $message, [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()            ])
            : response()->json(['error' => true, 'message' => $message]);
    }
}

    /**
 * Delete multiple media files by their IDs.
 *
 * Accepts an array of media IDs to delete multiple media files in a single request.
 *@group Project Media
 * @bodyParam ids array required Array of media IDs to delete. Example: [101, 102, 103]
 * @bodyParam ids.* integer required Each media ID must exist in the media table.
 * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 {
 *   "error": false,
 *   "message": "Files(s) deleted successfully.",
 *   "id": [101, 102],
 *   "titles": ["example1.jpg", "example2.png"],
 *   "parent_id": [15, 15],
 *   "type": "media",
 *   "parent_type": "project"
 * }
 *
 * @response 422 scenario="Validation Error" {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "ids": [
 *       "The ids field is required."
 *     ],
 *     "ids.0": [
 *       "The selected ids.0 is invalid."
 *     ]
 *   }
 * }
 */
    public function delete_multiple_media(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:media,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        $parentIds = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $media = Media::find($id);
            if ($media) {
                $deletedIds[] = $id;
                $deletedTitles[] = $media->file_name;
                $parentIds[] = $media->model_id;
                $media->delete();
            }
        }
        return response()->json(['error' => false, 'message' => 'Files(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles, 'parent_id' => $parentIds, 'type' => 'media', 'parent_type' => 'project']);
    }
  /**
 * Create a new milestone for a project
 *
 * This endpoint allows users to create a milestone under a specific project.
 *
 * @group Project Milestones
 *
 * @header workspace_id 2
 *
 * @bodyParam project_id int required The ID of the project the milestone belongs to. Example: 5
 * @bodyParam title string required The title of the milestone. Example: Final Design Review
 * @bodyParam status string required The status of the milestone. Must be one of: incomplete, complete, pending. Example: incomplete
 * @bodyParam start_date string required The start date of the milestone in the current PHP date format. Must be before or equal to end_date. Example: 2025-06-10
 * @bodyParam end_date string required The end date of the milestone. Example: 2025-06-20
 * @bodyParam cost string required The cost of the milestone. Example: 2000.50
 * @bodyParam description string The description of the milestone (optional). Example: All screens finalized and approved by client.
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Milestone created successfully.",
 *   "data": {
 *     "id": 12,
 *     "type": "milestone",
 *     "parent_type": "project",
 *     "parent_id": 5
 *   }
 * }
 *
 * @response 422 {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "project_id": ["The project_id field is required."],
 *     "title": ["The title field is required."]
 *   }
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Milestone couldn't be created: Milestone creation failed due to mass assignment or DB error."
 * }
 */

public function store_milestone(Request $request)
{
    $isApi = request()->get('isApi', false);

    $formFields = $request->validate([
        'project_id' => ['required', 'exists:projects,id'],
        'title' => ['required', 'string', 'max:255'],
        'status' => ['required', 'in:incomplete,complete,pending'],
                   'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required'],
        'cost' => ['required', 'regex:/^\d+(\.\d+)?$/'],
        'description' => ['nullable', 'string'],
    ]);
    // dd($formFields);
    try {
        // Format dates for DB
          $start_date = $request->input('start_date');
        //   dd($start_date);
        $end_date = $request->input('end_date');
  $formFields['start_date'] = format_date($start_date, false, app('php_date_format'), 'Y-m-d');
        $formFields['end_date'] = format_date($end_date, false, app('php_date_format'), 'Y-m-d');
        // dd($formFields['start_date'], $formFields['end_date']);

        $formFields['workspace_id'] = $this->workspace->id;
        // dd($formFields['workspace_id']);
        $formFields['created_by'] = isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id;
// dd($formFields['created_by']);
        // Create milestone
        $milestone = Milestone::create($formFields);

        if (!$milestone) {
            // If creation failed, throw exception to handle below
            throw new \Exception('Milestone creation failed due to mass assignment or DB error.');
        }

        // Return successful API response
        return formatApiResponse(false, 'Milestone created successfully.', [
            'id' => $milestone->id,
            'type' => 'milestone',
            'parent_type' => 'project',
            'parent_id' => $milestone->project_id,
        ]);
    } catch (ValidationException $e) {
        dd($e->errors());
        return formatApiValidationError($isApi, $e->errors());
    } catch (\Exception $e) {
        // dd($e);
        // Handle any unexpected errors
        if ($isApi) {
            return formatApiResponse(true, 'Milestone couldn\'t be created: ' . $e->getMessage());
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Milestone couldn\'t be created: ' . $e->getMessage(),
            ], 500);
        }
    }
}

/**
 * Get milestone(s) (single or list)
 * @group Project Milestones
 * This API returns either a single milestone (if an `id` is provided) or a paginated list of milestones.
 * It supports filtering by title, description, status, and date ranges. Sorting and pagination are also supported.
 *
 * @urlParam id integer optional The ID of the milestone to retrieve. If provided, other filters are ignored. Example: 3
 *
 * @queryParam search string optional A keyword to search by milestone title, description, or ID. Example: Review
 * @queryParam status string optional Filter by milestone status. Example: complete
 * @queryParam start_date_from date optional Filter milestones starting from this date (Y-m-d). Example: 2025-06-01
 * @queryParam start_date_to date optional Filter milestones starting up to this date (Y-m-d). Example: 2025-06-30
 * @queryParam end_date_from date optional Filter milestones ending from this date (Y-m-d). Example: 2025-07-01
 * @queryParam end_date_to date optional Filter milestones ending up to this date (Y-m-d). Example: 2025-07-31
 * @queryParam sort string optional Field to sort by. Defaults to `id`. Example: title
 * @queryParam order string optional Sort direction (`asc` or `desc`). Defaults to `desc`. Example: asc
 * @queryParam limit integer optional Number of records per page. Defaults to 10. Example: 20
 *
 * @header workspace_id integer required The ID of the workspace context. Example: 2
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Milestones retrieved successfully.",
 *   "data": [
 *     {
 *       "id": 3,
 *       "title": "Final Review",
 *       "status": "complete",
 *       "start_date": "2025-06-01",
 *       "end_date": "2025-06-15",
 *       "cost": "1500.00",
 *       "progress": 100,
 *       "description": "Final phase of project delivery."
 *     }
 *   ],
 *   "total": 1,
 *   "page": 1,
 *   "limit": 10
 * }
 *
 * @response 404 {
 *   "error": true,
 *   "message": "Milestone not found.",
 *   "data": []
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Error: Unexpected exception message",
 *   "data": {
 *     "line": 123,
 *     "file": "path/to/file.php"
 *   }
 * }
 */


    public function api_milestones($id = null)
{
    $isApi = request()->get('isApi', false);

    try {
        if ($id) {
            // Single milestone
            $milestone = Milestone::find($id);

            if (!$milestone) {
                return formatApiResponse(true, 'Milestone not found', [], 404);
            }

            return formatApiResponse(false, 'Milestone retrieved successfully', [
                'data' => $milestone->toArray()
            ]);
        } else {
            // List of milestones with optional filters
            $query = Milestone::query();

            // Optional filtering
            if ($search = request('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhere('id', 'like', '%' . $search . '%');
                });
            }

            if ($status = request('status')) {
                $query->where('status', $status);
            }

            if ($startFrom = request('start_date_from') and $startTo = request('start_date_to')) {
                $query->whereBetween('start_date', [$startFrom, $startTo]);
            }

            if ($endFrom = request('end_date_from') and $endTo = request('end_date_to')) {
                $query->whereBetween('end_date', [$endFrom, $endTo]);
            }

            // Sorting
            $sort = request('sort', 'id');
            $order = request('order', 'desc');
            $query->orderBy($sort, $order);

            // Pagination
            $limit = (int) request('limit', 10);
            $milestones = $query->paginate($limit);

            return formatApiResponse(false, 'Milestones retrieved successfully.', [
                'data' => $milestones->items(),
                'total' => $milestones->total(),
                'page' => $milestones->currentPage(),
                'limit' => $milestones->perPage()
            ]);
        }
    } catch (\Exception $e) {
        return formatApiResponse(true, 'Error: ' . $e->getMessage(), [
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
}

    public function get_milestones($id)
    {
        $project = Project::findOrFail($id);
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $status = isset($_REQUEST['status']) && $_REQUEST['status'] !== '' ? $_REQUEST['status'] : "";
        $start_date_from = (request('start_date_from')) ? request('start_date_from') : "";
        $start_date_to = (request('start_date_to')) ? request('start_date_to') : "";
        $end_date_from = (request('end_date_from')) ? request('end_date_from') : "";
        $end_date_to = (request('end_date_to')) ? request('end_date_to') : "";
        $milestones =  $project->milestones();
        if ($search) {
            $milestones = $milestones->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhere('cost', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        if ($start_date_from && $start_date_to) {
            $milestones = $milestones->whereBetween('start_date', [$start_date_from, $start_date_to]);
        }
        if ($end_date_from && $end_date_to) {
            $milestones  = $milestones->whereBetween('end_date', [$end_date_from, $end_date_to]);
        }
        if ($status) {
            $milestones  = $milestones->where('status', $status);
        }
        $total = $milestones->count();
        $milestones = $milestones->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($milestone) {
                if (strpos($milestone->created_by, 'u_') === 0) {
                    // The ID corresponds to a user
                    $creator = User::find(substr($milestone->created_by, 2)); // Remove the 'u_' prefix
                } elseif (strpos($milestone->created_by, 'c_') === 0) {
                    // The ID corresponds to a client
                    $creator = Client::find(substr($milestone->created_by, 2)); // Remove the 'c_' prefix
                }
                if ($creator !== null) {
                    $creator = $creator->first_name . ' ' . $creator->last_name;
                } else {
                    $creator = '-';
                }
                $statusBadge = '';
                if ($milestone->status == 'incomplete') {
                    $statusBadge = '<span class="badge bg-danger">' . get_label('incomplete', 'Incomplete') . '</span>';
                } elseif ($milestone->status == 'complete') {
                    $statusBadge = '<span class="badge bg-success">' . get_label('complete', 'Complete') . '</span>';
                }
                $progress = '<div class="demo-vertical-spacing">
                <div class="progress">
                  <div class="progress-bar" role="progressbar" style="width: ' . $milestone->progress . '%" aria-valuenow="' . $milestone->progress .
                    '" aria-valuemin="0" aria-valuemax="100">
                  </div>
                </div>
              </div> <h6 class="mt-2">' . $milestone->progress . '%</h6>';
                return [
                    'id' => $milestone->id,
                    'title' => $milestone->title,
                    'status' => $statusBadge,
                    'progress' => $progress,
                    'cost' => format_currency($milestone->cost),
                    'start_date' => format_date($milestone->start_date),
                    'end_date' => format_date($milestone->end_date),
                    'created_by' => $creator,
                    'description' => $milestone->description,
                    'created_at' => format_date($milestone->created_at),
                    'updated_at' => format_date($milestone->updated_at),
                ];
            });
        return response()->json([
            "rows" => $milestones->items(),
            "total" => $total,
        ]);
    }


        public function get_milestone($id)
    {
        $ms = Milestone::findOrFail($id);
        return response()->json(['ms' => $ms]);
    }


/**
 * Update an existing milestone.
 *@group Project Milestones
 * This endpoint updates the details of a specific milestone including title, status, dates,
 * cost, progress, and an optional description. The milestone is identified by its `id` which must be
 * passed as a request parameter.
 *
 * @bodyParam id integer required The ID of the milestone to update. Example: 5
 * @bodyParam title string required The title of the milestone. Example: Final Review
 * @bodyParam status string required The current status of the milestone. Example: complete
 * @bodyParam start_date date required The start date in `d-m-Y` format. Must be before or equal to end_date. Example: 01-06-2025
 * @bodyParam end_date date required The end date in `d-m-Y` format. Example: 15-06-2025
 * @bodyParam cost float required The estimated cost (numbers or decimal). Example: 2000.50
 * @bodyParam progress integer required Progress of the milestone in percentage (0–100). Example: 80
 * @bodyParam description string nullable Optional description of the milestone. Example: Final review and delivery milestone.
 *
 * @header workspace_id integer required The ID of the workspace context. Example: 2
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Milestone updated successfully.",
 *   "id": 5,
 *   "type": "milestone",
 *   "parent_type": "project",
 *   "parent_id": 12
 * }
 *
 * @response 422 {
 *   "error": true,
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "title": ["The title field is required."]
 *   }
 * }
 *
 * @response 400 {
 *   "error": true,
 *   "message": "Invalid date format.",
 *   "exception": "InvalidArgumentException"
 * }
 */


    public function update_milestone(Request $request)
    {
        $formFields = $request->validate([
            'title' => ['required'],
            'status' => ['required'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required'],
            'cost' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'progress' => ['required'],
            'description' => ['nullable'],
        ]);
        // dd($formFields);
      try {
    $formFields['start_date'] = Carbon::createFromFormat('d-m-Y', trim($request->input('start_date')))->format('Y-m-d');
    $formFields['end_date'] = Carbon::createFromFormat('d-m-Y', trim($request->input('end_date')))->format('Y-m-d');
} catch (\Exception $e) {
    return response()->json([
        'error' => true,
        'message' => 'Invalid date format.',
        'exception' => $e->getMessage()
    ]);
}

        // DD($formFields);
        $ms = Milestone::findOrFail($request->id);
        // dd($ms);
        // dd($ms->update($formFields));
        if ($ms->update($formFields)) {
            return response()->json(['error' => false, 'message' => 'Milestone updated successfully.',
             'id' => $ms->id,
             'type' => 'milestone',
             'title' => $ms->title,
              'status' => $ms->status,


              'parent_type' => 'project',
              'parent_id' => $ms->project_id]);
        } else {
            return response()->json(['error' => true, 'message' => 'Milestone couldn\'t updated.']);
        }
    }
    /**
 * Delete a specific milestone.
 *
 * This endpoint deletes a single milestone by its ID. The milestone must exist. Once deleted, a confirmation message with related metadata is returned.
 *@group Project Milestones
 * @urlParam id int required The ID of the milestone to delete. Example: 3
 * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 {
 *   "error": false,
 *   "message": "Milestone deleted successfully.",
 *   "id": 3,
 *   "title": "Design Phase",
 *   "type": "milestone",
 *   "parent_type": "project",
 *   "parent_id": 7
 * }
 *
 * @response 404 {
 *   "message": "No query results for model [App\\Models\\Milestone] 99"
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "An unexpected error occurred while deleting the milestone."
 * }
 */

    public function destroy_milestone($id)
    {
        $ms = Milestone::findOrFail($id);
        DeletionService::delete(Milestone::class, $id, 'Milestone');
        return response()->json(['error' => false, 'message' => 'Milestone deleted successfully.', 'id' => $id, 'title' => $ms->title, 'type' => 'milestone', 'parent_type' => 'project', 'parent_id' => $ms->project_id]);
    }
    public function delete_multiple_milestones(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:milestones,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $ms = Milestone::findOrFail($id);
            $deletedIds[] = $id;
            $deletedTitles[] = $ms->title;
            $parentIds[] = $ms->project_id;
            DeletionService::delete(Milestone::class, $id, 'Milestone');
        }
        return response()->json(['error' => false, 'message' => 'Milestone(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles, 'type' => 'milestone', 'parent_type' => 'project', 'parent_id' => $parentIds]);
    }
    /**
 * Save the user's default view preference for projects.
 *
 * This endpoint allows the authenticated user or client to set their preferred default view (e.g., kanban, list, or calendar) for how projects are displayed in the UI.
 * The view preference is stored in the `user_client_preferences` table.
 *@group Project status and priority
 * @authenticated
 * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @bodyParam view string required The preferred default view type. Valid options might include "kanban", "list", or "calendar". Example: kanban
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Default View Set Successfully."
 * }
 *
 * @response 400 {
 *   "error": true,
 *   "message": "Something Went Wrong."
 * }
 *
 * @response 422 {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "view": ["The view field is required."]
 *   }
 * }
 */

    public function saveViewPreference(Request $request)
    {
        $view = $request->input('view');
        $prefix = isClient() ? 'c_' : 'u_';
        if (UserClientPreference::updateOrCreate(
            ['user_id' => $prefix . $this->user->id, 'table_name' => 'projects'],
            ['default_view' => $view]
        )) {
            return response()->json(['error' => false, 'message' => 'Default View Set Successfully.']);
        } else {
            return response()->json(['error' => true, 'message' => 'Something Went Wrong.']);
        }
    }

   /**
 * Update the status of a project.
 *@group Project status and priority
 * This endpoint updates the status of a specified project.
 * The status change is recorded in the status timeline,
 * and notifications are sent to related users and clients.
 *
 * You can include an optional `note` with the status update.
 *
 * If `isApi` request parameter is true, response will use
 * the standardized API response format.
 *
 * @group Project status and priority
 *
 * @bodyParam id int required The ID of the project to update. Example: 438
 * @bodyParam statusId int required The ID of the new status to set. Example: 5
 * @bodyParam note string Optional note about the status update.
 * @bodyParam isApi bool Optional flag to specify if the request is API (true or false). Defaults to false.
 * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 {
 *   "error": false,
 *   "message": "Status updated successfully.",
 *   "id": 438,
 *   "type": "project",
 *   "old_status": "Default",
 *   "new_status": "Completed",
 *   "activity_message": "John Doe updated project status from Default to Completed",
 *   "data": {
 *     "id": 438,
 *     "title": "Res Test",
 *     "status": "Completed",
 *     "priority": "High",
 *     "users": [
 *       {
 *         "id": 7,
 *         "first_name": "John",
 *         "last_name": "Doe",
 *         "photo": "https://example.com/photos/johndoe.png"
 *       }
 *     ],
 *     "clients": [
 *       {
 *         "id": 103,
 *         "first_name": "Client",
 *         "last_name": "Name",
 *         "photo": "https://example.com/photos/no-image.jpg"
 *       }
 *     ],
 *     "tags": [
 *       {
 *         "id": 45,
 *         "title": "Important"
 *       }
 *     ],
 *     "start_date": "07-08-2024 14:38:51",
 *     "end_date": "12-08-2024 13:49:33",
 *     "budget": "1000.00",
 *     "task_accessibility": "assigned_users",
 *     "description": null,
 *     "note": "Project on track",
 *     "favorite": 1,
 *     "created_at": "07-08-2024 14:38:51",
 *     "updated_at": "12-08-2024 13:49:33"
 *   }
 * }
 *
 * @response 403 {
 *   "error": true,
 *   "message": "You are not authorized to set this status."
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Status couldn't be updated."
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Error: Exception message here",
 *   "line": 123,
 *   "file": "/path/to/file.php"
 * }
 */

 public function update_status(Request $request)
{
    $isApi = $request->get('isApi', false);

    $request->validate([
        'id' => ['required', 'exists:projects,id'],
        'statusId' => ['required', 'exists:statuses,id']
    ]);

    try {
        $id = $request->id;
        $statusId = $request->statusId;

        $status = Status::findOrFail($statusId);

        if (!canSetStatus($status)) {
            return $isApi
                ? formatApiResponse(true, 'You are not authorized to set this status.', [], 403)
                : response()->json(['error' => true, 'message' => 'You are not authorized to set this status.'], 403);
        }

        $project = Project::findOrFail($id);

        $oldStatus = Status::findOrFail($project->status_id);
        $currentStatus = $oldStatus->title;

        $project->status_id = $statusId;
        $project->note = $request->note;

        $newStatus = Status::findOrFail($statusId);

        $project->statusTimelines()->create([
            'status' => $newStatus->title,
            'new_color' => $newStatus->color,
            'previous_status' => $oldStatus->title,
            'old_color' => $oldStatus->color,
            'changed_at' => now(),
        ]);

        if ($project->save()) {
            $project = $project->fresh(['users', 'clients', 'tags', 'status']);

            $activityMessage = $this->user->first_name . ' ' . $this->user->last_name .
                " updated project status from {$currentStatus} to {$newStatus->title}";

            $responseData = [
                'id' => $id,
                'type' => 'project',
                'old_status' => $currentStatus,
                'new_status' => $newStatus->title,
                'activity_message' => $activityMessage,

                // Use helper to format full project data:
                'data' => formatProject($project),
            ];

            if ($isApi) {
                return formatApiResponse(false, 'Status updated successfully.', $responseData);
            } else {
                return response()->json(array_merge(['error' => false, 'message' => 'Status updated successfully.'], $responseData));
            }
        } else {
            $msg = 'Status couldn\'t be updated.';
            return $isApi
                ? formatApiResponse(true, $msg, [], 500)
                : response()->json(['error' => true, 'message' => $msg], 500);
        }
    } catch (\Exception $e) {
        $errorMessage = 'Error: ' . $e->getMessage();
        if ($isApi) {
            return formatApiResponse(true, $errorMessage, [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        } else {
            return response()->json([
                'error' => true,
                'message' => $errorMessage,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
}



/**
 * Update the priority of a project.
 * @group Project status and priority
 * This endpoint allows updating the priority of a specified project.
 * The request must include the project ID and optionally the new priority ID.
 * It returns the updated project details along with an activity message.
 *
 * @group Project Management
 *
 * @bodyParam id int required The ID of the project to update. Example: 123
 * @bodyParam priorityId int|null The ID of the new priority to set. Pass null to reset. Example: 5
 * @bodyParam note string|null Optional note related to the priority update. Example: "Urgent priority needed"
 * @bodyParam isApi bool Optional flag (true/false) indicating if the request expects an API-formatted response. Default is false.
 * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 {
 *   "error": false,
 *   "message": "Priority updated successfully.",
 *   "id": 123,
 *   "type": "project",
 *   "old_priority": "Medium",
 *   "new_priority": "High",
 *   "activity_message": "John Doe updated project priority from Medium to High",
 *   "data": {
 *     // Detailed formatted project data as returned by formatProject helper
 *   }
 * }
 *
 * @response 403 {
 *   "error": true,
 *   "message": "You are not authorized to update this priority."
 * }
 *
 * @response 422 {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "id": ["The selected id is invalid."],
 *     "priorityId": ["The selected priority id is invalid."]
 *   }
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Error: Exception message here",
 *   "line": 45,
 *   "file": "/path/to/file.php"
 * }
 */

    public function update_priority(Request $request)
{
    $isApi = $request->get('isApi', false);

    $request->validate([
        'id' => ['required', 'exists:projects,id'],
        'priorityId' => ['nullable', 'exists:priorities,id']
    ]);

    try {
        $id = $request->id;
        $priorityId = $request->priorityId;

        $project = Project::findOrFail($id);

        $currentPriority = $project->priority ? $project->priority->title : 'Default';

        $project->priority_id = $priorityId;
        $project->note = $request->note;

        if ($project->save()) {
            $project = $project->fresh(['users', 'clients', 'tags', 'priority']);

            $newPriority = $project->priority ? $project->priority->title : 'Default';

            $activityMessage = $this->user->first_name . ' ' . $this->user->last_name .
                " updated project priority from {$currentPriority} to {$newPriority}";

            $responseData = [
                'id' => $id,
                'type' => 'project',
                'old_priority' => $currentPriority,
                'new_priority' => $newPriority,
                'activity_message' => $activityMessage,
                'data' => formatProject($project),  // your helper for detailed project formatting
            ];

            if ($isApi) {
                return formatApiResponse(false, 'Priority updated successfully.', $responseData);
            } else {
                return response()->json(array_merge(['error' => false, 'message' => 'Priority updated successfully.'], $responseData));
            }
        } else {
            $msg = 'Priority couldn\'t be updated.';
            return $isApi
                ? formatApiResponse(true, $msg, [], 500)
                : response()->json(['error' => true, 'message' => $msg], 500);
        }
    } catch (\Exception $e) {
        $errorMessage = 'Error: ' . $e->getMessage();
        if ($isApi) {
            return formatApiResponse(true, $errorMessage, [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        } else {
            return response()->json([
                'error' => true,
                'message' => $errorMessage,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
}
/**
 * Add a comment.
 *
 * This endpoint allows the authenticated user to post a comment on any model (like a project or task)
 * using polymorphic relationships. It supports file attachments (images, PDFs, documents)
 * and also handles user mentions (e.g., @username), sending notifications to mentioned users.
 *
 * @group Project Comments
 *
 * @bodyParam model_type string required The fully qualified model class name. Example: App\\Models\\Project
 * @bodyParam model_id int required The ID of the model being commented on. Example: 14
 * @bodyParam content string required The comment content. Mentions like "@john" are supported. Example: This is a comment with a mention to @jane.
 * @bodyParam parent_id int Optional. The ID of the parent comment (for replies). Example: 5
 * @bodyParam attachments file[] Optional. Files to attach with the comment (jpg, jpeg, png, pdf, xlsx, txt, docx). Max size: 2MB per file.
 *
 * @response 200 {
 *   "success": true,
 *   "message": "Comment Added Successfully",
 *   "comment": {
 *     "id": 21,
 *     "commentable_type": "App\\Models\\Project",
 *     "commentable_id": 14,
 *     "content": "This is a comment with a mention to <a href='/users/5'>@jane</a>",
 *     "user_id": 1,
 *     "parent_id": null,
 *     "created_at": "2025-06-12T10:31:02.000000Z",
 *     "updated_at": "2025-06-12T10:31:02.000000Z",
 *     "user": {
 *        "id": 1,
 *        "first_name": "John",
 *        "last_name": "Doe",
 *        "email": "john@example.com"
 *     },
 *     "attachments": [
 *       {
 *         "id": 1,
 *         "comment_id": 21,
 *         "file_name": "screenshot.png",
 *         "file_path": "comment_attachments/screenshot.png",
 *         "file_type": "image/png"
 *       }
 *     ]
 *   }
 * }
 *
 * @response 422 {
 *   "success": false,
 *   "message": "Validation failed.",
 *   "errors": {
 *     "model_type": ["The model_type field is required."],
 *     "content": ["The content field is required."]
 *   }
 * }
 *
 * @response 500 {
 *   "success": false,
 *   "message": "An error occurred: [error details]"
 * }
     */
public function comments(Request $request)
{
    $isApi = $request->get('isApi', false);

    try {
        // Validate request
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'content' => 'required|string',
            'parent_id' => 'nullable|integer|exists:comments,id',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,xlsx,txt,docx|max:2048',
        ]);

        // Process mentions
        list($processedContent, $mentionedUserIds) = replaceUserMentionsWithLinks($request->content);


        $comment = Comment::with('user')->create([
            'commentable_type' => $request->model_type,
            'commentable_id' => $request->model_id,
            'content' => $processedContent,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
        ]);

        // Ensure attachment directory exists
        $directoryPath = storage_path('app/public/comment_attachments');
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }

        // Handle file attachments
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

        // Notify mentioned users
        sendMentionNotification($comment, $mentionedUserIds, session()->get('workspace_id'), auth()->id());

        // Return response based on mode
        if ($isApi) {
            return response()->json([
                'success' => true,
                'message' => get_label('comment_added_successfully', 'Comment Added Successfully'),
                'comment' => formatComments($comment),
            ]);
        } else {
            return response()->json([
                'success' => true,
                'comment' => $comment->load('attachments'),
                'message' => get_label('comment_added_successfully', 'Comment Added Successfully'),
                'user' => $comment->user,
                'created_at' => $comment->created_at->diffForHumans(),
            ]);
        }
    } catch (\Illuminate\Validation\ValidationException $e) {
        $errors = $e->errors();
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $errors,
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Get  comment by ID.
 *
 * This endpoint retrieves the details of a specific comment, including any attachments associated with it.
 *
 *  @group Project Comments
 *
 * @urlParam id integer required The ID of the comment to retrieve. Example: 21
 *
 * @response 200 {
 *   "comment": {
 *     "id": 21,
 *     "commentable_type": "App\\Models\\Project",
 *     "commentable_id": 14,
 *     "content": "This is a comment with mention to <a href='/users/5'>@jane</a>",
 *     "user_id": 1,
 *     "parent_id": null,
 *     "created_at": "2025-06-12T10:31:02.000000Z",
 *     "updated_at": "2025-06-12T10:31:02.000000Z",
 *     "attachments": [
 *       {
 *         "id": 1,
 *         "comment_id": 21,
 *         "file_name": "report.pdf",
 *         "file_path": "comment_attachments/report.pdf",
 *         "file_type": "application/pdf"
 *       }
 *     ]
 *   }
 * }
 *
 * @response 404 {
 *   "message": "No query results for model [App\\Models\\Comment] 99"
 * }
 */

   public function get_comment(Request $request, $id)
{
    $isApi = $request->get('isApi', true); // default to true for API
// dd($id);
    try {
        $comment = Comment::with('attachments')->findOrFail($id);
        // dd($comment);
        $formattedComment = formatComments($comment);

        return $isApi
            ? response()->json([
                'success' => true,
                'message' => get_label('comment_fetched_successfully', 'Comment fetched successfully'),
                'comment' => $formattedComment,
            ])
            : $formattedComment;
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage(),
        ], 500);
    }
}

   /**
 * Update a comment
 *
 * This endpoint updates the content of an existing comment. It also handles user mention parsing and sends notifications to mentioned users.
 *
 *  @group Project Comments
 *
 * @bodyParam comment_id int required The ID of the comment to update. Example: 12
 * @bodyParam content string required The new content of the comment. Mentions can be included using @username format. Example: "Updated comment with mention to @john"
 * @bodyParam isApi boolean Optional flag to determine if it's an API request. Example: true
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Comment updated successfully.",
 *   "id": 12,
 *   "type": "project"
 * }
 *
 * @response 422 {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "comment_id": [
 *       "The comment_id field is required."
 *     ],
 *     "content": [
 *       "The content field is required."
 *     ]
 *   }
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Internal Server Error"
 * }
 */


        public function update_comment(Request $request)
        {
            $is_Api = $request->get('isApi', false);
            // try{
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
                 $formattedComment = formatComments($comment);
                return response()->json(['error' => false, 'message' => 'Comment updated successfully.', 'id' => $id,
               'comment' => $formattedComment,
                 'type' => 'project']);
            } else {
                return response()->json(['error' => true, 'message' => 'Comment couldn\'t updated.']);
            }
        // }catch (\Exception $e) {
        //         if ($is_Api) {
        //             return formatApiResponse(true, 'Internal Server Error', [], 500);
        //         } else {
        //             return response()->json(['error' => true, 'message' => 'Internal Server Error'], 500);
        //         }
        //     }
        }
/**
 * Delete a comment
 *
 * This endpoint permanently deletes a comment and all of its associated attachments from the storage.
 *
 * @group Project Comments
 *
 * @bodyParam comment_id int required The ID of the comment to delete. Example: 15
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Comment deleted successfully.",
 *   "id": 15,
 *   "type": "project"
 * }
 *
 * @response 422 {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "comment_id": [
 *       "The comment_id field is required."
 *     ]
 *   }
 * }
 *
 * @response 404 {
 *   "message": "No query results for model [App\\Models\\Comment] 15"
 * }
 *
 * @response 500 {
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
        // dd($id);
        $comment = Comment::findOrFail($id);
        $attachments = $comment->attachments;
        // dd($attachments);
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
    public function destroy_comment_attachment($id)
    {
        $attachment = CommentAttachment::findOrFail($id);
        // dd($attachment);
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
        return response()->json(['error' => false, 'message' => 'Attachment deleted successfully.']);
    }
    public function gantt_chart()
    {
        $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects : $this->user->projects;
        // This method now only returns the view, without preloading data
        return view('projects.gantt-chart-view', compact('projects'));
    }
    public function fetch_gantt_data(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects : $this->user->projects;
        // Filter projects based on the date range
        $filteredProjects = $projects->filter(function ($project) use ($startDate, $endDate) {
            $projectStart = Carbon::parse($project->start_date);
            $projectEnd = Carbon::parse($project->end_date);
            return ($projectStart->between($startDate, $endDate) ||
                $projectEnd->between($startDate, $endDate) ||
                ($projectStart->lte($startDate) && $projectEnd->gte($endDate)));
        });
        // Load the tasks for each project
        $filteredProjects->load('tasks');
        return response()->json($filteredProjects->values());
    }
    // public function fetch_gantt_data(Request $request)
    // {
    //     $startDate = $request->input('start_date');
    //     $endDate = $request->input('end_date');
    //     $projects = isAdminOrHasAllDataAccess()
    //         ? $this->workspace->projects()->with(['tasks' => function ($query) use ($startDate, $endDate) {
    //             $query->whereBetween('start_date', [$startDate, $endDate])
    //                 ->orWhereBetween('due_date', [$startDate, $endDate]);
    //         }])->whereBetween('start_date', [$startDate, $endDate])
    //         ->orWhereBetween('end_date', [$startDate, $endDate])
    //         ->get()
    //         : $this->user->projects()->with(['tasks' => function ($query) use ($startDate, $endDate) {
    //             $query->whereBetween('start_date', [$startDate, $endDate])
    //                 ->orWhereBetween('due_date', [$startDate, $endDate]);
    //         }])->whereBetween('start_date', [$startDate, $endDate])
    //         ->orWhereBetween('end_date', [$startDate, $endDate])
    //         ->get();
    //     return response()->json($projects);
    // }
    public function update_module_dates(Request $request)
    {
        // First, validate the input format for module and date fields
        $request->validate([
            'module' => 'required|array',
            'module.type' => 'required|string|in:project,task',
            'module.id' => 'required|integer',
            'start_date' => 'required|string', // Temporarily validate as a string
            'end_date' => 'required|string',   // Temporarily validate as a string
        ], [
            'module.required' => 'The module is required. Please specify if it is a project or task.',
            'module.type.required' => 'The type must be either "project" or "task".',
            'module.type.in' => 'The module type must be either "project" or "task".',
            'module.id.required' => 'The module ID is required.',
            'module.id.integer' => 'The module ID must be a valid integer.',
            'start_date.required' => 'The start date is required.',
            'end_date.required' => 'The end date is required.',
        ]);
        // Extract module and date strings from the request
        $module = $request->input('module');
        $startDateString = $request->input('start_date');
        $endDateString = $request->input('end_date');
        // Attempt to parse dates using the parseDate helper method
        $startDate = $this->parseDate($startDateString);
        $endDate = $this->parseDate($endDateString);
        // Validate the parsed dates to ensure they are valid
        $request->validate([
            'start_date' => ['required', function ($attribute, $value, $fail) use ($startDate) {
                if (!$startDate) {
                    $fail('The start date format is invalid. Please provide a valid date.');
                }
            }],
            'end_date' => ['required', function ($attribute, $value, $fail) use ($endDate, $startDate) {
                if (!$endDate) {
                    $fail('The end date format is invalid. Please provide a valid date.');
                } elseif ($endDate < $startDate) {
                    $fail('The end date must be after or equal to the start date.');
                }
            }],
        ]);
        // Handle project or task based on the provided module type
        if ($module['type'] === 'project') {
            $project = Project::find($module['id']);
            if ($project) {
                $project->start_date = $startDate;
                $project->end_date = $endDate;
                $project->save(); // Save the project dates
                return response()->json(['error' => false, 'message' => 'Project dates updated successfully.']);
            } else {
                return response()->json(['error' => true, 'message' => 'Project not found.']);
            }
        } elseif ($module['type'] === 'task') {
            $task = Task::find($module['id']);
            if ($task) {
                $task->start_date = $startDate;
                $task->due_date = $endDate;
                $task->save(); // Save the task dates
                return response()->json(['error' => false, 'message' => 'Task dates updated successfully.']);
            } else {
                return response()->json(['error' => true, 'message' => 'Task not found.']);
            }
        } else {
            return response()->json(['error' => true, 'message' => 'Unknown module type.']);
        }
    }
    /**
     * Helper function to parse a date string
     *
     * @param string $dateString
     * @return Carbon|null
     */
    protected function parseDate($dateString)
    {
        // Remove timezone abbreviation and parse the date
        $dateString = preg_replace('/\s\([^)]+\)$/', '', $dateString);
        try {
            $date = Carbon::parse($dateString);
            return $date->format('Y-m-d'); // Format to 'YYYY-MM-DD'
        } catch (\Exception $e) {
            return null;
        }
    }
    // Updated mind_map function
    public function mind_map(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $mindMapData = $this->getMindMapData($project);
        return view('projects.mind_map', compact('mindMapData', 'project'));
    }
    private function getMindMapData($project)
    {
        $mindMapData = [
            'meta' => [
                'name' => $project->title,
                'author' => $project->created_by,
                'version' => '1.0'
            ],
            'format' => 'node_tree', // Specify format if required by your jsMind version
            'data' => [
                'id' => 'project_' . $project->id,
                'topic' => $project->title,
                'isroot' => true,
                'level' => 1,
                'children' => [
                    [
                        'id' => 'tasks',
                        'topic' => 'Tasks',
                        'level' => 2,
                        'children' => $project->tasks->map(function ($task) {
                            return [
                                'id' => 'task_' . $task->id,
                                'topic' => $task->title,
                                'data' => [
                                    'media' => $task->media->map(function ($mediaItem) {
                                        $isPublicDisk = $mediaItem->disk == 'public' ? 1 : 0;
                                        $fileUrl = $isPublicDisk
                                            ? asset('storage/project-media/' . $mediaItem->file_name)
                                            : $mediaItem->getFullUrl();
                                        return $fileUrl;
                                    })->toArray()
                                ]
                            ];
                        })->toArray()
                    ],
                    // [
                    //     'id' => 'comments',
                    //     'topic' => 'Comments',
                    //     'children' => $project->comments->map(function ($comment) {
                    //         return [
                    //             'id' => 'comment_' . $comment->id,
                    //             'topic' => $comment->content,
                    //             'children' => $comment->children->map(function ($reply) {
                    //                 return [
                    //                     'id' => 'reply_' . $reply->id,
                    //                     'topic' => $reply->content
                    //                 ];
                    //             })->toArray()
                    //         ];
                    //     })->toArray()
                    // ],
                    // [
                    //     'id' => 'milestones',
                    //     'topic' => 'Milestones',
                    //     'children' => $project->milestones->map(function ($milestone) {
                    //         return [
                    //             'id' => 'milestone_' . $milestone->id,
                    //             'topic' => $milestone->title
                    //         ];
                    //     })->toArray()
                    // ],
                    [
                        'id' => 'media',
                        'topic' => 'Media',
                        'children' => $project->media->map(function ($mediaItem) {
                            $isPublicDisk = $mediaItem->disk == 'public' ? 1 : 0;
                            $fileUrl = $isPublicDisk
                                ? asset('storage/project-media/' . $mediaItem->file_name)
                                : $mediaItem->getFullUrl();
                            return [
                                'id' => 'media_' . $mediaItem->id,
                                'topic' => $mediaItem->file_name,
                                'data' => [
                                    'url' => $fileUrl
                                ]
                            ];
                        })->toArray()
                    ],
                    [
                        'id' => 'users',
                        'topic' => 'Users',
                        'children' => $project->users->map(function ($user) {
                            return [
                                'id' => 'user_' . $user->id,
                                'topic' => $user->first_name . ' ' . $user->last_name
                            ];
                        })->toArray()
                    ],
                    [
                        'id' => 'clients',
                        'topic' => 'Clients',
                        'children' => $project->clients->map(function ($client) {
                            return [
                                'id' => 'client_' . $client->id,
                                'topic' => $client->first_name . ' ' . $client->last_name
                            ];
                        })->toArray()
                    ]
                ]
            ]
        ];
        return $mindMapData;
    }
    public function export_mindmap(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $imageData = $request->input('imageData');
        // Generate PDF
        $pdf = PDF::loadView('projects.pdf_mind_map', compact('imageData', 'project'));
        return $pdf->download('mind_map_' . $project->id . '.pdf');
    }
    public function get_users(Request $request)
    {
        // Get mention_id and mention_type from the request
        $mentionId = $request->get('mention_id');
        $mentionType = $request->get('mention_type');
        $query = $request->get('search', '');
        // dd($mentionId, $mentionType, $query);
        // Initialize users query
        $users = User::query();
        // Apply relationship based on mention_type
        switch ($mentionType) {
            case 'project':
                $users->whereHas('projects', function ($q) use ($mentionId) {
                    $q->where('projects.id', $mentionId);
                });
                break;
            case 'task':
                $users->whereHas('tasks', function ($q) use ($mentionId) {
                    $q->where('tasks.id', $mentionId);
                });
                break;
            case 'workspace':
                $users->whereHas('workspaces', function ($q) use ($mentionId) {
                    $q->where('workspaces.id', $mentionId);
                });
                break;
            default:
                return response()->json(['error' => 'Invalid mention_type'], 400);
        }
        // Apply search filter for first_name
        $users->where('first_name', 'like', '%' . $query . '%');
        // Fetch and map users
        $users = $users->get(['id', 'first_name', 'last_name'])->map(function ($user) {
            return [
                'key' => $user->id,
                'value' => $user->first_name . ' ' . $user->last_name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ];
        });
        // Return the users as JSON
        return response()->json($users);
    }

    // calendar view
    public function calendar_view()
    {
        $is_favorites = 0;
        $customFields = CustomField::where('module', 'project')->get();
        return view('projects.calendar_view', compact('is_favorites','customFields'));
    }

public function get_calendar_data(Request $request)
{
    $php_date_format = app('php_date_format');
    
    // Get date range from request
    $start = $request->query('date_from')
        ? Carbon::createFromFormat('Y-m-d', $request->query('date_from'), config('app.timezone'))
        : Carbon::now(config('app.timezone'))->startOfMonth();
        
    $end = $request->query('date_to')
        ? Carbon::createFromFormat('Y-m-d', $request->query('date_to'), config('app.timezone'))
        : Carbon::now(config('app.timezone'))->endOfMonth();

    // Get custom date filters if provided (these take precedence)
    $customStart = $request->query('custom_date_from') 
        ? Carbon::createFromFormat('Y-m-d', $request->query('custom_date_from'), config('app.timezone'))
        : null;
        
    $customEnd = $request->query('custom_date_to')
        ? Carbon::createFromFormat('Y-m-d', $request->query('custom_date_to'), config('app.timezone'))
        : null;

    // Use custom dates if provided, otherwise use calendar view dates
    $filterStart = $customStart ?: $start;
    $filterEnd = $customEnd ?: $end;

    // Get filter parameters
    $statusIds = $request->query('status_ids', []);
    $priorityIds = $request->query('priority_ids', []);
    
    // Convert comma-separated string to array if needed
    if (is_string($statusIds)) {
        $statusIds = explode(',', $statusIds);
    }
    if (is_string($priorityIds)) {
        $priorityIds = explode(',', $priorityIds);
    }

    // Base query
    $projectsQuery = isAdminOrHasAllDataAccess() 
        ? $this->workspace->projects() 
        : $this->user->projects();

    // Apply date range filter
    if ($filterStart && $filterEnd) {
        $projectsQuery->where(function ($query) use ($filterStart, $filterEnd) {
            $query->whereBetween('start_date', [$filterStart, $filterEnd])
                ->orWhereBetween('end_date', [$filterStart, $filterEnd])
                ->orWhere(function ($subQuery) use ($filterStart, $filterEnd) {
                    // Include projects that span across the date range
                    $subQuery->where('start_date', '<=', $filterStart)
                             ->where('end_date', '>=', $filterEnd);
                });
        });
    }

    // Apply status filter
    if (!empty($statusIds) && !in_array('', $statusIds)) {
        $projectsQuery->whereIn('status_id', $statusIds);
    }

    // Apply priority filter
    if (!empty($priorityIds) && !in_array('', $priorityIds)) {
        $projectsQuery->whereIn('priority_id', $priorityIds);
    }

    // Get projects with relationships
    $projects = $projectsQuery->with(['status', 'priority', 'users', 'clients'])->get();

    // Format projects for FullCalendar
    $events = $projects->map(function ($project) {
        $backgroundColor = '#007bff';
        
        // Set background color based on project status
        switch ($project->status->color) {
            case 'primary':
                $backgroundColor = '#9bafff';
                break;
            case 'success':
                $backgroundColor = '#a0e4a3';
                break;
            case 'danger':
                $backgroundColor = '#ff6b5c';
                break;
            case 'warning':
                $backgroundColor = '#ffca66';
                break;
            case 'info':
                $backgroundColor = '#6ed4f0';
                break;
            case 'secondary':
                $backgroundColor = '#aab0b8';
                break;
            case 'dark':
                $backgroundColor = '#4f5b67';
                break;
            case 'light':
                $backgroundColor = '#ffffff';
                break;
            default:
                $backgroundColor = '#5ab0ff';
        }

        // Create title with date information
        $title = $project->title;
        
        // Add status and priority info to title for better context
        $statusInfo = ' [' . $project->status->title . ']';
        if ($project->priority) {
            $statusInfo .= ' (' . $project->priority->title . ')';
        }
        
        // Format dates for display
        $startDateFormatted = format_date($project->start_date);
        if ($project->end_date != $project->start_date) {
            $title .= ' : ' . $startDateFormatted . ' ' . get_label('to', 'to') . ' ' . format_date($project->end_date);
        } else {
            $title .= ' : ' . $startDateFormatted;
        }
        
        $title .= $statusInfo;

        return [
            'id' => $project->id,
            'project_info_url' => route('projects.info', ['id' => $project->id]),
            'title' => $title,
            'start' => $project->start_date,
            'end' => Carbon::parse($project->end_date)->addDay()->format('Y-m-d'), // Add day for FullCalendar end date
            'backgroundColor' => $backgroundColor,
            'borderColor' => '#ffffff',
            'textColor' => '#000000',
            'extendedProps' => [
                'status' => $project->status->title,
                'priority' => $project->priority ? $project->priority->title : '',
                'status_color' => $project->status->color,
                'priority_color' => $project->priority ? $project->priority->color : '',
                'description' => $project->description,
                'budget' => $project->budget,
                'users_count' => $project->users->count(),
                'clients_count' => $project->clients->count(),
            ]
        ];
    });

    return response()->json($events);
}

}

