<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Workspace;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class WorkspacesController extends Controller
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
    public function index()
    {
        $workspaces = Workspace::all();
        $adminId = getAdminIdByUserRole();
        $admin = Admin::with('user', 'teamMembers.user')->find($adminId);

        $users = $admin->teamMembers;
        $toSelectWorkspaceUsers = $admin->teamMembers;
        $clients = Client::where('admin_id', $adminId)->get();
        $toSelectWorkspaceClients = Client::where('admin_id', $adminId)->get();
        return view('workspaces.workspaces', compact('workspaces', 'users', 'clients', 'admin', 'toSelectWorkspaceClients', 'toSelectWorkspaceUsers'));
    }
    public function create()
    {
        $adminId = getAdminIdByUserRole();
        $admin = Admin::with('user', 'teamMembers.user')->find($adminId);

        $users = User::all();
        $clients = Client::where('admin_id', $adminId)->get();
        $auth_user = $this->user;

        return view('workspaces.create_workspace', compact('users', 'clients', 'auth_user', 'admin'));
    }

    /**
 * Create a new workspace
 *@group Workspace Managemant
 * This endpoint allows authenticated users to create a new workspace. It automatically associates users and clients,
 * sets the workspace as primary (if requested), and logs activity. Notifications are sent to the participants.
 *
 * @bodyParam title string required The name of the workspace. Example: Design Team
 * @bodyParam user_ids array List of user IDs to attach to the workspace. Example: [3, 5]
 * @bodyParam client_ids array List of client IDs to attach to the workspace. Example: [101, 102]
 * @bodyParam primaryWorkspace boolean Indicates if this workspace should be set as the primary workspace. Example: true
 * @bodyParam isApi boolean Optional flag to return API-formatted response. Example: true
 * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 {
 *   "error": false,
 *   "message": "Workspace created successfully.",
 *   "id": 438,
 *   "data": {
 *     "id": 438,
 *     "title": "Design Team",
 *     "is_primary": true,
 *     "users": [
 *       {
 *         "id": 7,
 *         "first_name": "Madhavan",
 *         "last_name": "Vaidya",
 *         "photo": "https://yourdomain.com/storage/photos/user.png"
 *       }
 *     ],
 *     "clients": [
 *       {
 *         "id": 103,
 *         "first_name": "Test",
 *         "last_name": "Test",
 *         "photo": "https://yourdomain.com/storage/photos/no-image.jpg"
 *       }
 *     ],
 *     "created_at": "07-08-2024 14:38:51",
 *     "updated_at": "07-08-2024 14:38:51"
 *   }
 * }
 *
 * @response 422 {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "title": [
 *       "The title field is required."
 *     ]
 *   }
 * }
 */
public function store(Request $request)
    {
          $isApi = request()->get('isApi', false);
           try {
        $adminId = null;
        if (Auth::guard('web')->check() && $this->user->hasRole('admin')) {
            $admin = Admin::where('user_id', $this->user->id)->first();
            if ($admin) {
                $adminId = $admin->id;
            }
        }

        $formFields = $request->validate([
            'title' => ['required']
        ]);

        $formFields['user_id'] = $this->user->id;
        $formFields['admin_id'] = $adminId;
        $userIds = $request->input('user_ids') ?? [];
        $clientIds = $request->input('client_ids') ?? [];

        // Set creator as a participant automatically

        if (Auth::guard('client')->check() && !in_array($this->user->id, $clientIds)) {
            array_splice($clientIds, 0, 0, $this->user->id);
        } else if (Auth::guard('web')->check() && !in_array($this->user->id, $userIds)) {
            array_splice($userIds, 0, 0, $this->user->id);
        }
        $primaryWorkspace = isAdminOrHasAllDataAccess() && $request->input('primaryWorkspace') && $request->filled('primaryWorkspace') && $request->input('primaryWorkspace') == 'on' ? 1 : 0;

        $formFields['is_primary'] = $primaryWorkspace;

        // Create new workspace

        $new_workspace = Workspace::create($formFields);
        if ($primaryWorkspace) {
            // Set all other workspaces to non-primary
            Workspace::where('id', '!=', $new_workspace->id)->update(['is_primary' => 0]);
        }
        $workspace_id = $new_workspace->id;
        if ($this->workspace == null) {
            session()->put('workspace_id', $workspace_id);
        }
        $workspace = Workspace::find($workspace_id);
        // Attach users and clients to the workspace
        $workspace->users()->attach($userIds, ['admin_id' => $adminId]);
        $workspace->clients()->attach($clientIds, ['admin_id' => $adminId]);

        //Create activity log
        $activityLogData = [
            'workspace_id' => $workspace_id,
            'admin_id' => $adminId,
            'actor_id' => $this->user->id,
            'actor_type' => 'user',
            'type_id' => $workspace_id,
            'type' => 'workspace',
            'activity' => 'created',
            'message' => $this->user->name . ' created workspace ' . $new_workspace->title,
        ];

        ActivityLog::create($activityLogData);
        $notification_data = [
            'type' => 'workspace',
            'type_id' => $workspace_id,
            'type_title' => $workspace->title,
            'action' => 'assigned',
            'title' => 'Added in a workspace',
            'message' => $this->user->first_name . ' ' . $this->user->last_name . ' added you in workspace: ' . $workspace->title . ', ID #' . $workspace_id . '.'

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

            // Return JSON response with workspace ID
            return formatApiResponse(
                false,
                'Workspace created successfully.',
                [
                    'id' => $workspace_id,
                    'data' => formatWorkspace($workspace)
                ]
            );
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while creating the workspace.'
            ], 500);
        }
    }

    public function get($id)
    {
        $workspace = Workspace::with('users', 'clients')->findOrFail($id);
        return response()->json(['error' => false, 'workspace' => $workspace]);
    }
    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $user_id = (request('user_id')) ? request('user_id') : "";
        $client_id = (request('client_id')) ? request('client_id') : "";

        $workspaces = isAdminOrHasAllDataAccess() ? $this->workspace : $this->user->workspaces();
        // dd(getAdminIDByUserRole());


        if ($user_id) {
            $user = User::find($user_id);
            $workspaces = $user->workspaces();
        }
        if ($client_id) {
            $client = Client::find($client_id);
            $workspaces = $client->workspaces();
        }
        $workspaces = $workspaces->when($search, function ($query) use ($search) {
            return $query->where('title', 'like', '%' . $search . '%')
                ->orWhere('id', 'like', '%' . $search . '%');
        });
        $workspaces->where('workspaces.admin_id', getAdminIDByUserRole());
        $totalworkspaces = $workspaces->count();

        $canCreate = checkPermission('create_workspaces');
        $canEdit = checkPermission('edit_workspaces');
        $canDelete = checkPermission('delete_workspaces');

        $workspaces = $workspaces->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($workspace) use ($canEdit, $canDelete, $canCreate) {

                $actions = '';

                if ($canEdit) {
                    $actions .= '<a href="javascript:void(0);" class="edit-workspace" data-id="' . $workspace->id . '" title="' . get_label('update', 'Update') . '">' .
                        '<i class="bx bx-edit mx-1"></i>' .
                        '</a>';
                }

                if ($canDelete) {
                    $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $workspace->id . '" data-type="workspaces">' .
                '<i class="bx bx-trash text-danger mx-1"></i>' .
                '</button>';
                }

                if ($canCreate) {
                    $actions .= '<a href="javascript:void(0);" class="duplicate" data-id="' . $workspace->id . '" data-title="' . $workspace->title . '" data-type="workspaces" title="' . get_label('duplicate', 'Duplicate') . '">' .
                        '<i class="bx bx-copy text-warning mx-2"></i>' .
                        '</a>';
                }

                $actions = $actions ?: '-';

                $userHtml = '';
                if (!empty($workspace->users) && count($workspace->users) > 0) {
                    $userHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                    foreach ($workspace->users as $user) {
                        $userHtml .= "<li class='avatar avatar-sm pull-up'><a href='/users/profile/{$user->id}' target='_blank' title='{$user->first_name} {$user->last_name}'><img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                    }
                    if ($canEdit) {
                        $userHtml .= '<li title=' . get_label('update', 'Update') . '><a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-workspace update-users-clients" data-id="' . $workspace->id . '"><span class="bx bx-edit"></span></a></li>';
                    }
                    $userHtml .= '</ul>';
                } else {
                    $userHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
                    if ($canEdit) {
                        $userHtml .= '<a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-workspace update-users-clients" data-id="' . $workspace->id . '">' .
                            '<span class="bx bx-edit"></span>' .
                            '</a>';
                    }
                }

                $clientHtml = '';
                if (!empty($workspace->clients) && count($workspace->clients) > 0) {
                    $clientHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                    foreach ($workspace->clients as $client) {
                        $clientHtml .= "<li class='avatar avatar-sm pull-up'><a href='/clients/profile/{$client->id}' target='_blank' title='{$client->first_name} {$client->last_name}'><img src='" . ($client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                    }
                    if ($canEdit) {
                        $clientHtml .= '<li title=' . get_label('update', 'Update') . '><a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-workspace update-users-clients" data-id="' . $workspace->id . '"><span class="bx bx-edit"></span></a></li>';
                    }
                    $clientHtml .= '</ul>';
                } else {
                    $clientHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
                    if ($canEdit) {
                        $clientHtml .= '<a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-workspace update-users-clients" data-id="' . $workspace->id . '">' .
                            '<span class="bx bx-edit"></span>' .
                            '</a>';
                    }
                }
                return [
                    'id' => $workspace->id,
                'title' => '<a href="workspaces/switch/' . $workspace->id . '">' . $workspace->title . '</a>' . ($workspace->is_primary ? ' <span class="badge bg-success">' . get_label('primary', 'Primary') . '</span>' : ''),
                'users' => $userHtml,
                'clients' => $clientHtml,
                'created_at' => format_date($workspace->created_at, true),
                'updated_at' => format_date($workspace->updated_at, true),
                'actions' => $actions
                ];
            });

        return response()->json([
            "rows" => $workspaces->items(),
            "total" => $totalworkspaces,
        ]);
    }
/**
 * Get a list of workspaces or a specific workspace.
 *@group Workspace Managemant
 * This endpoint retrieves all workspaces associated with the currently authenticated admin,
 * including their related users and clients. It supports filtering by search, sorting, and pagination.
 * If an ID is provided, it returns the details of that specific workspace.
 *
 * @urlParam id integer optional The ID of the workspace to retrieve.
 * @queryParam search string optional Search by workspace title or ID. Example: first
 * @queryParam sort string optional Column to sort by. Default is "id". Example: title
 * @queryParam order string optional Sort direction ("ASC" or "DESC"). Default is "DESC". Example: ASC
 * @queryParam limit integer optional Number of results per page. Default is 10. Example: 20
 * @queryParam isApi boolean optional Set true if called from API. Default is true.
* @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 {
 *  "error": false,
 *  "message": "Workspaces retrieved successfully.",
 *  "total": 2,
 *  "data": [
 *      {
 *          "id": 1,
 *          "title": "Marketing Team",
 *          "is_primary": true,
 *          "users": [
 *              {
 *                  "id": 2,
 *                  "first_name": "Jane",
 *                  "last_name": "Doe",
 *                  "photo": "http://localhost:8000/storage/photos/no-image.jpg"
 *              }
 *          ],
 *          "clients": [
 *              {
 *                  "id": 5,
 *                  "first_name": "Client",
 *                  "last_name": "One",
 *                  "photo": "http://localhost:8000/storage/photos/no-image.jpg"
 *              }
 *          ],
 *          "created_at": "2025-05-19 09:53:37",
 *          "updated_at": "2025-06-05 11:35:18"
 *      }
 *  ]
 * }
 *
 * @response 200 {
 *  "error": false,
 *  "message": "Workspace retrieved successfully.",
 *  "data": {
 *      "id": 2,
 *      "title": "Design Department",
 *      "is_primary": false,
 *      "users": [...],
 *      "clients": [...],
 *      "created_at": "2025-05-19 09:53:37",
 *      "updated_at": "2025-06-05 11:35:18"
 *  }
 * }
 *
 * @response 404 {
 *  "error": true,
 *  "message": "No query results for model [App\\Models\\Workspace] 999.",
 *  "data": []
 * }
 *
 * @response 500 {
 *  "error": true,
 *  "message": "Internal Server Error",
 *  "data": []
 * }
 */

 public function listapi($id = null)
{
    $isApi = request()->get('isApi', true);

    try {
        $query = Workspace::with(['users', 'clients'])->where('admin_id', getAdminIdByUserRole());



        if ($id) {
            $workspace = $query->findOrFail($id);
            return formatApiResponse(
                false,
                'Workspace retrieved successfully.',
                formatWorkspace($workspace)
            );
        }

        $search = request('search');
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $limit = request('limit', 10); // default limit

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$search%");
            });
        }

        $total = $query->count();

        $workspaces = $query->orderBy($sort, $order)
            ->paginate($limit)
            ->getCollection()
            ->map(function ($workspace) {
                return formatWorkspace($workspace);
            });

        return formatApiResponse(
            false,
            'Workspaces retrieved successfully.',
            [
                'total' => $total,
                'data' => $workspaces
            ]
        );
    } catch (\Exception $e) {
        return formatApiResponse(true, $e->getMessage(), []);
    }
}



    public function edit($id)
    {
        $workspace = Workspace::findOrFail($id);
        $admin = Admin::with('user', 'teamMembers.user')->find(getAdminIdByUserRole());
        $clients = Client::where('admin_id', getAdminIdByUserRole())->get();
        return view('workspaces.update_workspace', compact('workspace', 'clients', 'admin'));
    }
/**
 * Update an existing workspace
 *@group Workspace Managemant
 * Updates the details of an existing workspace such as title, primary status,
 * assigned users, and clients. It also handles updating notifications for newly added members.
 *
 * @bodyParam id integer required The ID of the workspace. Example: 12
 * @bodyParam title string required The updated title of the workspace. Example: Design Team
 * @bodyParam user_ids array optional List of user IDs to associate with the workspace. Example: [1, 3]
 * @bodyParam client_ids array optional List of client IDs to associate with the workspace. Example: [101, 104]
 * @bodyParam primaryWorkspace boolean optional Whether this workspace is set as the primary one. Example: true
  * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 {
 *    "error": false,
 *    "id": 12,
 *    "message": "Workspace updated successfully.",
 *    "data": {
 *      "id": 12,
 *      "title": "Design Team",
 *      "is_primary": true,
 *      "users": [
 *        {
 *          "id": 3,
 *          "first_name": "Alice",
 *          "last_name": "Doe",
 *          "photo": "https://example.com/photos/user3.jpg"
 *        }
 *      ],
 *      "clients": [
 *        {
 *          "id": 101,
 *          "first_name": "Bob",
 *          "last_name": "Client",
 *          "photo": "https://example.com/photos/client101.jpg"
 *        }
 *      ],
 *      "created_at": "07-08-2024 14:38:51",
 *      "updated_at": "07-08-2024 15:10:02"
 *    }
 * }
 *
 * @response 422 {
 *    "error": true,
 *    "message": "Validation failed.",
 *    "errors": {
 *      "title": [
 *        "The title field is required."
 *      ],
 *      "id": [
 *        "The selected id is invalid."
 *      ]
 *    }
 * }
 */

 public function update(Request $request)
    {
         $isApi = request()->get('isApi', false);
        $formFields = $request->validate([
            'id' => 'required|exists:workspaces,id',
            'title' => ['required']
        ]);
        $id = $request->input('id');
        $workspace = Workspace::findOrFail($id);
        $userIds = $request->input('user_ids') ?? [];
        $clientIds = $request->input('client_ids') ?? [];
        // Set creator as a participant automatically
        if (User::where('id', $workspace->user_id)->exists() && !in_array($workspace->user_id, $userIds)) {
            array_splice($userIds, 0, 0, $workspace->user_id);
        } elseif (Client::where('id', $workspace->user_id)->exists() && !in_array($workspace->user_id, $clientIds)) {
            array_splice($clientIds, 0, 0, $workspace->user_id);
        }
        $existingUserIds = $workspace->users->pluck('id')->toArray();
        $existingClientIds = $workspace->clients->pluck('id')->toArray();
        if (isAdminOrHasAllDataAccess()) {
            if ($request->has('primaryWorkspace')) {
                $primaryWorkspace = $request->boolean('primaryWorkspace', false) ? 1 : 0;
                $formFields['is_primary'] = $primaryWorkspace;
            } else {
                $primaryWorkspace = 0;
            }
        } else {
            $primaryWorkspace = $workspace->is_primary;
        }
        $workspace->update($formFields);
        if ($primaryWorkspace) {
            // Set all other workspaces to non-primary
            Workspace::where('id', '!=', $workspace->id)->update(['is_primary' => 0]);
        }
        $workspace->users()->sync($userIds);
        $workspace->clients()->sync($clientIds);
        $userIds = array_diff($userIds, $existingUserIds);
        $clientIds = array_diff($clientIds, $existingClientIds);
        // Prepare notification data
        $notification_data = [
            'type' => 'workspace',
            'type_id' => $id,
            'type_title' => $workspace->title,
            'action' => 'assigned',
            'title' => 'Added in a workspace',
            'message' => $this->user->first_name . ' ' . $this->user->last_name . ' added you in workspace: ' . $workspace->title . ', ID #' . $id . '.'
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
        Session::flash('message', 'Workspace updated successfully.');
        return response()->json(['error' => false,
        'message' => 'Workspace updated successfully.',
        'id' => $id,
          'data' => formatWorkspace($workspace->fresh(['users', 'clients']))
        ]);
    }
/**
 *
 *
 * Delete a workspace
 *@group Workspace Managemant
 * This API deletes a workspace by its ID, if it's not the currently active workspace.
 *
 * @urlParam id integer required The ID of the workspace. Example: 12
 * @header  Authorization  Bearer 40|dbscqcapUOVnO7g5bKWLIJ2H2zBM0CBUH218XxaNf548c4f1
 * @header Accept application/json
 * @header workspace_id 2
 * @response 200 {
*"error": false,
*   "message": "Workspace deleted successfully.",
*    "id": "3",
*    "title": "Design Team Workspace",
*    "data": []
*}
 *
 * @response 400 {
 *   "error": true,
 *   "message": "Cannot delete the currently active workspace."
 * }
 *
 * @response 404 {
 *   "error": true,
 *   "message": "Workspace not found or already deleted."
 * }
 */

   public function destroy($id)
{
    $isApi = request()->get('isApi', true);

    try {
        // Prevent deletion of currently active workspace
        if ($this->workspace && $this->workspace->id == $id) {
            return formatApiResponse(
                true,
                'Cannot delete the currently active workspace.',
                [],
                400
            );
        }

        $workspace = Workspace::find($id);

        if (!$workspace) {
            return formatApiResponse(
                true,
                'Workspace not found or already deleted.',
                [],
                404
            );
        }

        // Perform deletion using service
        $response = DeletionService::delete(Workspace::class, $id, 'Workspace');

        return $isApi
            ? $response
            : redirect()->back()->with('message', 'Workspace deleted successfully.');
    } catch (\Exception $e) {
        return formatApiResponse(
            true,
            'Something went wrong while deleting the workspace.',
            ['exception' => $e->getMessage()],
            500
        );
    }
}

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:workspaces,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedWorkspaces = [];
        $deletedWorkspaceTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $workspace = Workspace::find($id);
            if ($workspace) {
                $deletedWorkspaces[] = $id;
                $deletedWorkspaceTitles[] = $workspace->title;
                DeletionService::delete(Workspace::class, $id, 'Workspace');
            }
        }

        return response()->json(['error' => false, 'message' => 'Workspace(s) deleted successfully.', 'id' => $deletedWorkspaces, 'titles' => $deletedWorkspaceTitles]);
    }

    public function switch($id)
    {
        if (Workspace::findOrFail($id)) {
            session()->put('workspace_id', $id);
            return back()->with('message', 'Workspace changed successfully.');
        } else {
            return back()->with('error', 'Workspace not found.');
        }
    }

    public function remove_participant()
    {
        $workspace = Workspace::findOrFail(session()->get('workspace_id'));
        if ($this->user->hasRole('client')) {
            $workspace->clients()->detach($this->user->id);
        } else {
            $workspace->users()->detach($this->user->id);
        }
        $workspace_id = isset($this->user->workspaces[0]['id']) && !empty($this->user->workspaces[0]['id']) ? $this->user->workspaces[0]['id'] : 0;
        $data = ['workspace_id' => $workspace_id];
        session()->put($data);
        Session::flash('message', 'Removed from workspace successfully.');
        return response()->json(['error' => false]);
    }

    public function duplicate(Request $request, $id)
    {

        $options = $request->input('options') ?? [];
        // dd($options);
         // Normalize options to always be an array
        if (!is_array($options)) {
            $options = explode(',', $options); // Split string into an array by commas if necessary
        }
        // Ensure default duplication of users and clients
        $defaultOptions = ['users', 'clients'];
        $options = array_merge($defaultOptions, $options);

        // Validation: Tasks can only be selected if Projects is selected
        if (in_array('tasks', $options) && !in_array('projects', $options)) {
            return response()->json(['error' => true, 'message' => 'Tasks can only be duplicated if Projects is selected.']);
        }
        $allowedOptions = ['projects', 'project_tasks', 'meetings', 'todos', 'notes', 'users', 'clients'];
        $relatedTables = array_intersect($options, $allowedOptions);

        // Use the general duplicateRecord function
        $title = (request()->has('title') && !empty(trim(request()->title))) ? request()->title : '';
        $duplicate = duplicateRecord(Workspace::class, $id, $relatedTables, $title);
        if (!$duplicate) {
            return response()->json(['error' => true, 'message' => 'Workspace duplication failed.']);
        }
        $workspace = Workspace::find($duplicate->id);
        $workspace->update(['is_primary' => 0]);
        return response()->json(['error' => false, 'message' => 'Workspace duplicated successfully.', 'id' => $id]);
    }
}
