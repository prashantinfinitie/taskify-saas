<?php

namespace App\Http\Controllers;
use Exception;
use App\Models\Priority;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class PriorityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $priorities = Priority::all();
        return view('priority.list', compact('priorities'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     /**
 * Create a new priority
 *
 * This endpoint allows you to create a new priority with a title and color. It automatically generates a unique slug and assigns the current admin as the creator.
 *
 * @group Priority
 *
 * @bodyParam title string required The title of the priority. Example: High Priority
 * @bodyParam color string required The color associated with the priority (hex code or color name). Example: #FF0000
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Priority created successfully.",
 *   "data": {
 *     "id": 7,
 *     "title": "High Priority",
 *     "slug": "high-priority",
 *     "color": "#FF0000",
 *     "admin_id": 1,
 *     "created_at": "2025-06-04T14:25:30.000000Z",
 *     "updated_at": "2025-06-04T14:25:30.000000Z"
 *   },
 *   "id": 7
 * }
 *
 * @response 422 {
 *   "error": true,
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "title": ["The title field is required."],
 *     "color": ["The color field is required."]
 *   }
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Priority could not be created.",
 *   "error": "Exception message here",
 *   "line": 45,
 *   "file": "PriorityController.php"
 * }
 */

public function store(Request $request)
{
    $isApi = true;

    try {
        $formFields = $request->validate([
            'title' => ['required'],
            'color' => ['required']
        ]);

        $formFields['slug'] = generateUniqueSlug($request->title, Priority::class);
        $formFields['admin_id'] = getAdminIdByUserRole();

        $priority = Priority::create($formFields);
        // dd($priority);
        return formatApiResponse(false, 'Priority created successfully.', [
            'data' => formatPriority($priority),
            'id' => $priority->id,
        ]);

    } catch (ValidationException $e) {
        return formatApiValidationError($isApi, $e->errors());
    } catch (Exception $e) {
        return formatApiResponse(true, 'Priority could not be created.', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);
    }
}


    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $priority = Priority::orderBy($sort, $order);
        $priority->where('admin_id', getAdminIdByUserRole());
        if ($search) {
            $priority = $priority->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $priority->count();

        $priority = $priority
            ->paginate(request("limit"))
            ->through(
                fn ($priority) => [
                    'id' => $priority->id,
                    'title' => $priority->title,
                    'color' => '<span class="badge bg-' . $priority->color . '">' . $priority->title . '</span>',
                    'created_at' => format_date($priority->created_at),
                    'updated_at' => format_date($priority->updated_at),
                ]
            );


        return response()->json([
            "rows" => $priority->items(),
            "total" => $total,
        ]);
    }
    /**
 * Get priorities list or a specific priority.
 *
 * This endpoint allows fetching either a paginated list of priorities or a single priority by providing its ID.
 * It supports optional searching, sorting, and pagination when listing all priorities.
 *
 * @group Priority
 *
 * @urlParam id int optional The ID of the priority to retrieve. Example: 3
 *
 * @queryParam search string optional Search term for filtering by title or ID. Example: Urgent
 * @queryParam sort string optional Field to sort by. Defaults to id. Example: title
 * @queryParam order string optional Sort order. Either ASC or DESC. Defaults to DESC. Example: ASC
 * @queryParam limit int optional Number of records per page. Defaults to 15. Example: 10
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Priorities fetched successfully.",
 *   "data": {
 *     "rows": [
 *       {
 *         "id": 1,
 *         "title": "High",
 *         "color": "red",
 *         "created_at": "2024-06-01",
 *         "updated_at": "2024-06-01"
 *       }
 *     ],
 *     "total": 1,
 *     "current_page": 1,
 *     "last_page": 1
 *   }
 * }
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Priority fetched successfully.",
 *   "data": {
 *     "id": 1,
 *     "title": "High",
 *     "color": "red",
 *     "created_at": "2024-06-01",
 *     "updated_at": "2024-06-01"
 *   }
 * }
 *
 * @response 404 {
 *   "error": true,
 *   "message": "Priority not found.",
 *   "error": "No query results for model [App\\Models\\Priority] 3"
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Failed to fetch priorities.",
 *   "error": "Exception message here",
 *   "line": 101,
 *   "file": "PriorityController.php"
 * }
 */

    public function apilist($id = null)
{
    try {
        $adminId = getAdminIdByUserRole();

        if ($id) {
            // Fetch single priority by id and admin_id
            $priority = Priority::where('id', $id)
                ->where('admin_id', $adminId)
                ->firstOrFail();

            return formatApiResponse(false, 'Priority fetched successfully.', [
                'data' => formatPriority($priority)
            ]);
        }

        // Fetch multiple priorities with search, sorting, pagination
        $search = request('search');
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $per_page = request('per_page', 10);

        $query = Priority::where('admin_id', $adminId)
            ->orderBy($sort, $order);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $total = $query->count();

        $priorities = $query->paginate($per_page)
            ->through(fn($priority) => formatPriority($priority));

        return formatApiResponse(false, 'Priorities fetched successfully.', [
            'data' => $priorities->items(),
            'total' => $total,
            'current_page' => $priorities->currentPage(),
            'last_page' => $priorities->lastPage(),
        ]);
    } catch (Exception $e) {
        return formatApiResponse(true, 'Failed to fetch priorities.', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);
    }
}


    public function get($id)
    {
        $priority = Priority::findOrFail($id);
        return response()->json(['priority' => $priority]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     /**
 * Update an existing priority.
 *
 * This endpoint updates the title, color, and slug of an existing priority record.
 * The priority is identified by its ID.
 *
 * @group Priority
 *
 * @bodyParam id int required The ID of the priority to update. Example: 10
 * @bodyParam title string required The new title of the priority. Example: High
 * @bodyParam color string required The new color code for the priority. Example: #FF0000
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Priority updated successfully.",
 *   "data": {
 *     "id": 10,
 *     "title": "High",
 *     "color": "#FF0000",
 *     "slug": "high"
 *   },
 *   "id": 10
 * }
 *
 * @response 422 {
 *   "error": true,
 *   "message": "Validation error.",
 *   "errors": {
 *     "title": ["The title field is required."],
 *     "color": ["The color field is required."]
 *   }
 * }
 *
 * @response 404 {
 *   "error": true,
 *   "message": "Priority not found."
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Priority could not be updated due to server error."
 * }
 */

    public function update(Request $request)
{
    $isApi = true;

    try {
        $formFields = $request->validate([
            'id' => ['required', 'exists:priorities,id'],
            'title' => ['required'],
            'color' => ['required']
        ]);

        $slug = generateUniqueSlug($request->title, Priority::class, $request->id);
        $formFields['slug'] = $slug;

        $priority = Priority::findOrFail($request->id);

        $updated = $priority->update($formFields);

        if ($updated) {
            return formatApiResponse(false, 'Priority updated successfully.', [
                'data' => formatPriority($priority),
                'id' => $priority->id,
            ]);
        } else {
            return formatApiResponse(true, "Priority couldn't be updated.");
        }

    } catch (ValidationException $e) {
        return formatApiValidationError($isApi, $e->errors());
    } catch (Exception $e) {
        return formatApiResponse(true, 'Priority update failed.', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);
    }
}


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
 * Delete a priority.
 *
 * This endpoint deletes a specific priority by its ID.
 * Before deletion, it detaches the priority from all related projects and tasks.
 *
 * @group Priority
 *
 * @urlParam id int required The ID of the priority to delete. Example: 5
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Priority deleted successfully.",
 *   "id": 5,
 *   "title": "High Priority"
 * }
 *
 * @response 404 {
 *   "error": true,
 *   "message": "Priority not found.",
 *   "error": "No query results for model [App\\Models\\Priority] 999"
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Priority could not be deleted.",
 *   "error": "Exception message here",
 *   "line": 42,
 *   "file": "PriorityController.php"
 * }
 */

 public function destroy($id)
{
    try {
        $priority = Priority::findOrFail($id);

        // Detach priority from related projects and tasks
        $priority->projects(false)->update(['priority_id' => null]);
        $priority->tasks(false)->update(['priority_id' => null]);

        $response = DeletionService::delete(Priority::class, $id, 'Priority');

        return formatApiResponse(false, 'Priority deleted successfully.', [
            'id' => $id,
            'title' => $priority->title,
            'data' =>[]
        ]);
    } catch (Exception $e) {
        return formatApiResponse(true, 'Priority could not be deleted.', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);
    }
}
/**
 * Delete multiple priorities.
 *
 * This endpoint deletes one or more priorities based on the provided array of IDs.
 * It also detaches the priorities from any associated projects and tasks before deletion.
 * Each priority must exist in the database.
 *
 * @group Priority
 *
 * @bodyParam ids array required The list of priority IDs to delete. Example: [1, 2, 3]
 * @bodyParam ids.* integer required Each ID must correspond to an existing priority. Example: 1
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Priority/Priorities deleted successfully.",
 *   "ids": [1, 2, 3],
 *   "titles": ["High", "Medium", "Low"]
 * }
 *
 * @response 422 {
 *   "error": true,
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "ids.0": [
 *       "The selected ids.0 is invalid."
 *     ]
 *   }
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Priorities could not be deleted.",
 *   "error": "Exception message",
 *   "line": 123,
 *   "file": "PriorityController.php"
 * }
 */


  public function destroy_multiple(Request $request)
{
    try {
        $validatedData = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:priorities,id',
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];

        foreach ($ids as $id) {
            $priority = Priority::findOrFail($id);

            // Detach priority from related projects and tasks
            $priority->projects(false)->update(['priority_id' => null]);
            $priority->tasks(false)->update(['priority_id' => null]);

            // Record deleted info
            $deletedIds[] = $id;
            $deletedTitles[] = $priority->title;

            // Use deletion service
            DeletionService::delete(Priority::class, $id, 'Priority');
        }

        return formatApiResponse(false, 'Priority/Priorities deleted successfully.', [
            'ids' => $deletedIds,
            'titles' => $deletedTitles,
        ]);
    } catch (ValidationException $e) {
        return formatApiValidationError(true, $e->errors());
    } catch (Exception $e) {
        return formatApiResponse(true, 'Priorities could not be deleted.', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);
    }
}


    public function search(Request $request)
    {
        // dd($request->all());

        $query = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;

        // If a specific  ID is passed, return only that priorities
        if ($request->has('priority')) {
            $priority = Priority::where('id', $request->priority)
                ->where('admin_id', getAdminIDByUserRole())
                ->first(['id', 'title', 'color']);

            if ($priority) {
                return response()->json([
                    'items' => [['id' => $priority->id, 'text' => $priority->title]],
                    'pagination' => ['more' => false],
                ]);
            }
        }

        // Otherwise, search based on the query
        $priorities = Priority::where('admin_id', getAdminIDByUserRole())
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where('title', 'like', '%' . $query . '%');
            })
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
        ->get(['id', 'title', 'color']);

        // Prepare response for Select2
        $results = $priorities->map(function ($priority) {
            return ['id' => $priority->id, 'text' => $priority->title, 'color' => $priority->color];
        });

        // Flag for more results
        $pagination = ['more' => $priorities->count() === $perPage];

        return response()->json([
            'items' => $results,
            'pagination' => $pagination
        ]);
    }

}
