<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Support\Facades\Session;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('status.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('status.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


     /**
 * Create a New Status
 *
 * This endpoint creates a new status entry with a unique slug and assigns roles that have access to it.
 *
 * @group Status Management
 *
 * @bodyParam title string required The name of the status. Example: Approved
 * @bodyParam color string required The Bootstrap color class (without `bg-` prefix). Example: success
 * @bodyParam role_ids array Optional list of role IDs to associate with the status. Example: [1, 2]
 *
 * @response 200 scenario="Successful creation" {
 *   "error": false,
 *   "message": "Status created successfully.",
 *   "id": 5,
 *   "status": {
 *     "id": 5,
 *     "title": "Approved",
 *     "color": "success",
 *     "slug": "approved",
 *     "admin_id": 1,
 *     "created_at": "2025-05-28T12:34:56.000000Z",
 *     "updated_at": "2025-05-28T12:34:56.000000Z"
 *   }
 * }
 *
 * @response 422 scenario="Validation failed" {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "title": ["The title field is required."],
 *     "color": ["The color field is required."]
 *   }
 * }
 *
 * @response 500 scenario="Creation failed due to internal error" {
 *   "error": true,
 *   "message": "Status couldn't created."
 * }
 */

    public function store(Request $request)
    {
        $adminId = getAdminIdByUserRole();
        $formFields = $request->validate([
            'title' => ['required'],
            'color' => ['required']
        ]);
        $slug = generateUniqueSlug($request->title, Status::class);
        $formFields['slug'] = $slug;
        $formFields['admin_id'] = $adminId;

        $roleIds = $request->input('role_ids');
        if ($status = Status::create($formFields)) {
            $status->roles()->attach($roleIds);
            return response()->json([
            'error' => false,
            'message' => 'Status created successfully.',
            'id' => $status->id?? 0,
            'status' => $status?? ''
        ]);
        } else {
            return response()->json(['error' => true, 'message' => 'Status couldn\'t created.']);
        }
    }

  public function list()
{

    $search = request('search');
    $sort = (request('sort')) ? request('sort') : "id";
    $order = (request('order')) ? request('order') : "DESC";
    $status = Status::orderBy($sort, $order);
    $adminId = getAdminIdByUserRole();
    $status->where('admin_id', $adminId);

    if ($search) {
        $status = $status->where(function ($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%')
                ->orWhere('id', 'like', '%' . $search . '%');
        });
    }

    $total = $status->count();
    // dd($total);
    $status = $status
        ->paginate(request("limit"))
        ->through(function ($status) {
            $roles = $status->roles->pluck('name')->map(function ($roleName) {
                return ucfirst($roleName);
            })->implode(', ');

            return [
                'id' => $status->id,
                'title' => $status->title,
                'roles_has_access' => $roles ?: ' - ',
                'color' => '<span class="badge bg-' . $status->color . '">' . $status->title . '</span>',
                'created_at' => format_date($status->created_at, true),
                'updated_at' => format_date($status->updated_at, true),
            ];
        });

    return response()->json([
        "rows" => $status->items(),
        "total" => $total,
    ]);
}


    public function get($id)
    {
        $status = Status::findOrFail($id);
        $roles = $status->roles()->pluck('id')->toArray();
        return response()->json(['status' => $status, 'roles' => $roles]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
 * Update an Existing Status
 *
 * This endpoint updates the title, color, and associated roles of an existing status.
 *
 * @group Status Management
 *
 * @bodyParam id integer required The ID of the status to update. Example: 5
 * @bodyParam title string required The updated title of the status. Example: Rejected
 * @bodyParam color string required The updated color class (without `bg-` prefix). Example: danger
 * @bodyParam role_ids array Optional array of role IDs to sync with the status. Example: [1, 3]
 *
 * @response 200 scenario="Successful update" {
 *   "error": false,
 *   "message": "Status updated successfully.",
 *   "id": 5
 * }
 *
 * @response 422 scenario="Validation failed" {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "id": ["The id field is required."],
 *     "title": ["The title field is required."],
 *     "color": ["The color field is required."]
 *   }
 * }
 *
 * @response 404 scenario="Status not found" {
 *   "message": "No query results for model [App\\Models\\Status] 99"
 * }
 *
 * @response 500 scenario="Update failed due to internal error" {
 *   "error": true,
 *   "message": "Status couldn't updated."
 * }
 */

    public function update(Request $request)
    {
        $formFields = $request->validate([
            'id' => ['required'],
            'title' => ['required'],
            'color' => ['required']
        ]);
        $slug = generateUniqueSlug($request->title, Status::class, $request->id);
        $formFields['slug'] = $slug;
        $status = Status::findOrFail($request->id);

        if ($status->update($formFields)) {
            $roleIds = $request->input('role_ids');
            $status->roles()->sync($roleIds);
            return response()->json(['error' => false, 'message' => 'Status updated successfully.',
            'id' => $status->id?? 0,
            'title' =>$status->title?? '',
            'color'=>$status->color?? '',
        ]);
        } else {
            return response()->json(['error' => true, 'message' => 'Status couldn\'t updated.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
 * Delete a Status
 *
 * This endpoint deletes a status if it is not associated with any project or task.
 *
 * @group Status Management
 *
 * @urlParam id integer required The ID of the status to delete. Example: 5
 *
 * @response 200 scenario="Successful deletion" {
 *   "error": false,
 *   "message": "Status deleted successfully."
 * }
 *
 * @response 403 scenario="Status associated with project or task" {
 *   "error": true,
 *   "message": "Status can't be deleted.It is associated with a project or task."
 * }
 *
 * @response 404 scenario="Status not found" {
 *   "message": "No query results for model [App\\Models\\Status] 99"
 * }
 *
 * @response 500 scenario="Deletion failed due to server error" {
 *   "error": true,
 *   "message": "Something went wrong while deleting the status."
 * }
 */

    public function destroy($id)
    {
        $status = Status::findOrFail($id);
        if ($status->projects()->count() > 0 ||  $status->tasks()->count() > 0) {

            return response()->json(['error' => true, 'message' => 'Status can\'t be deleted.It is associated with a project or task.']);
        } else {

            $response = DeletionService::delete(Status::class, $id, 'Status');
            return $response;
        }
    }
/**
 * Delete Multiple Statuses
 *
 * Deletes multiple statuses by their IDs if they are not associated with any project or task.
 *
 * @group Status Management
 *
 * @bodyParam ids array required Array of status IDs to delete. Each ID must exist in the statuses table. Example: [1, 2, 3]
 * @bodyParam ids.* integer required Individual status ID. Example: 1
 *
 * @response 200 scenario="Successful deletion" {
 *   "error": false,
 *   "message": "Status(es) deleted successfully.",
 *   "id": [1, 2],
 *   "titles": ["Approved", "Pending"]
 * }
 *
 * @response 422 scenario="Validation failed" {
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "ids": ["The ids field is required."],
 *     "ids.0": ["The selected ids.0 is invalid."]
 *   }
 * }
 *
 * @response 403 scenario="Status associated with project or task" {
 *   "error": true,
 *   "message": "Status can't be deleted.It is associated with a project"
 * }
 *
 * @response 404 scenario="Status not found" {
 *   "message": "No query results for model [App\\Models\\Status] 99"
 * }
 *
 * @response 500 scenario="Deletion failed due to server error" {
 *   "error": true,
 *   "message": "Something went wrong while deleting the statuses."
 * }
 */

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:statuses,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $status = Status::findOrFail($id);
            if ($status->projects()->count() > 0 ||  $status->tasks()->count() > 0) {
                return response()->json(['error' => true, 'message' => 'Status can\'t be deleted.It is associated with a project']);
            } else {
                $deletedIds[] = $id;
                $deletedTitles[] = $status->title;
                DeletionService::delete(Status::class, $id, 'Status');
            }
        }
        return response()->json(['error' => false, 'message' => 'Status(es) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles]);
    }
    public function search(Request $request)
    {
        $query = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;

        // If there is no query, return the first set of statuses
        $statuses = Status::where('admin_id', getAdminIdByUserRole())
            ->orWhere(function ($query) {
                $query->whereNull('admin_id')
                    ->where('is_default', 1);
            })
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where('title', 'like', '%' . $query . '%');
            })
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'title']);

        $statuses = $statuses->unique('id');
        // Prepare response for Select2
        $results = $statuses->map(function ($status) {
            return ['id' => $status->id, 'text' => $status->title];
        });

        // Flag for more results
        $pagination = ['more' => $statuses->count() === $perPage];

        return response()->json([
            'items' => $results,
            'pagination' => $pagination
        ]);
    }
    /**
 * Get Status List or a Single Status
 *
 * This endpoint allows you to:
 * - Retrieve a list of all status records.
 * - Retrieve a specific status record by providing its ID.
 *
 * @group Status Management
 *
 * @urlParam id int Optional. The ID of the status you want to retrieve. Example: 3
 *
 * @response 200 scenario="Fetch all statuses" {
 *   "rows": [
 *     {
 *       "id": 1,
 *       "title": "Pending",
 *       "roles_has_access": "Admin, Manager",
 *       "color": "<span class=\"badge bg-warning\">Pending</span>",
 *       "created_at": "2024-08-20 10:12 AM",
 *       "updated_at": "2024-08-25 03:45 PM"
 *     },
 *     {
 *       "id": 2,
 *       "title": "Approved",
 *       "roles_has_access": "Admin",
 *       "color": "<span class=\"badge bg-success\">Approved</span>",
 *       "created_at": "2024-08-21 11:30 AM",
 *       "updated_at": "2024-08-26 02:15 PM"
 *     }
 *   ],
 *   "total": 2
 * }
 *
 * @response 200 scenario="Fetch single status by ID" {
 *   "id": 3,
 *   "title": "Rejected",
 *   "roles_has_access": "User",
 *   "color": "<span class=\"badge bg-danger\">Rejected</span>",
 *   "created_at": "2024-08-22 09:00 AM",
 *   "updated_at": "2024-08-28 01:00 PM"
 * }
 *
 * @response 404 scenario="Status not found for given ID" {
 *   "message": "Status not found."
 * }
 *
 * @response 401 scenario="Unauthenticated request" {
 *   "message": "Unauthenticated."
 * }
 *
 * @response 500 scenario="Unexpected server error" {
 *   "message": "Internal Server Error"
 * }
 */

    public function listapi(Request $request, $id = null)
{
    $query = Status::query(); // ← removed admin_id filter

    // If ID is provided, return a single status
    if ($id) {
        $status = $query->where('id', $id)->first();

        if (!$status) {
            return response()->json([
                'message' => 'Status not found.',
            ], 404);
        }

        $roles = optional($status->roles)->pluck('name')->map(function ($roleName) {
            return ucfirst($roleName);
        })->implode(', ');

        return formatApiResponse(
            false,
            'Status retrieved successfully',
            [
                'total' => 1,
                'data' => [[
                    'id' => $status->id ?? 0,
                    'title' => $status->title ?? '',
                    'color' => $status->color ?? '',
                    'created_at' => format_date($status->created_at, to_format: 'Y-m-d') ?? '',
                    'updated_at' => format_date($status->updated_at, to_format: 'Y-m-d') ?? '',
                ]]
            ]
        );
    }

    // Get pagination params
    $per_page = (int) $request->get('per_page', 10); // default 10
    $page    = (int) $request->get('page', 1);

    // Fetch paginated results
    $paginator = $query->paginate($per_page, ['*'], 'page', $page);

    // Transform results
    $statuses = $paginator->getCollection()->map(function ($status) {
        $roles = optional($status->roles)->pluck('name')->map(function ($roleName) {
            return ucfirst($roleName);
        })->implode(', ');

        return [
            'id' => $status->id,
            'title' => $status->title,
            'roles_has_access' => $roles ?: ' - ',
            'color' => '<span class="badge bg-' . $status->color . '">' . $status->title . '</span>',
            'created_at' => format_date($status->created_at, true),
            'updated_at' => format_date($status->updated_at, true),
        ];
    });

    return formatApiResponse(
        false,
        'Statuses retrieved successfully',
        [
            'total'        => $paginator->total(),
            'data'         => $statuses
        ]
    );
}

}