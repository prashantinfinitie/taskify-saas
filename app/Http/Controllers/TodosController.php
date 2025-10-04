<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Todo;
use App\Models\User;
use App\Models\Client;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;

class TodosController extends Controller
{
    protected $workspace;
    protected $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Use helper function to get workspace ID
            $workspaceId = getWorkspaceId();
            $this->workspace = Workspace::find($workspaceId);
            // dd($this->workspace);
            $this->user = getAuthenticatedUser();
            // dd($this->user);

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $todos = $this->user->todos()
            ->orderBy('is_completed', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
        // dd($todos);    
        return view('todos.list', ['todos' => $todos]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('todos.create_todo');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * Create a new Todo item.
     *
     * This endpoint creates a new todo item associated with the current workspace and admin.
     *
     * @group Todos Managemant
     * @authenticated
     * @header workspace_id 2
     * @bodyParam title string required The title of the todo item. Example: "Finish report"
     * @bodyParam priority string required The priority level of the todo. Example: "High"
     * @bodyParam description string nullable Optional detailed description of the todo. Example: "Complete the monthly sales report."
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Todo created successfully.",
     *   "data": {
     *     "id": 1,
     *     "title": "Finish report",
     *     "priority": "High",
     *     "description": "Complete the monthly sales report.",
     *     "workspace_id": 5,
     *     "admin_id": 3,
     *     "created_at": "2025-06-05 10:00:00",
     *     "updated_at": "2025-06-05 10:00:00"
     *   }
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "Validation error.",
     *   "errors": {
     *     "title": ["The title field is required."],
     *     "priority": ["The priority field is required."]
     *   }
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "Workspace not found in session.",
     *   "data": []
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "Lead Couldn't Created.",
     *   "error": "SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row",
     *   "line": 45,
     *   "file": "/var/www/html/app/Http/Controllers/TodoController.php"
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $isApi = $request->get('isApi', true);

        try {
            if (!$this->workspace) {
                return formatApiResponse(true, 'Workspace not found in session.', []);
            }

            $adminId = getAdminIdByUserRole();

            $formFields = $request->validate([
                'title' => ['required'],
                'priority' => ['required'],
                'description' => ['nullable'],
                
                // Reminder fields
                'enable_reminder' => ['nullable', 'in:on'],
                'frequency_type' => ['nullable', 'in:daily,weekly,monthly'],
                'day_of_week' => ['nullable', 'integer', 'between:1,7'],
                'day_of_month' => ['nullable', 'integer', 'between:1,31'],
                'time_of_day' => ['nullable', 'date_format:H:i'],
            ]);

            $formFields['workspace_id'] = $this->workspace->id;
            $formFields['admin_id'] = $adminId;

            $todo = new Todo($formFields);
            $todo->creator()->associate($this->user);
            $todo->save();

            // Save reminder if enabled
            if (!empty($formFields['enable_reminder']) && $formFields['enable_reminder'] === 'on') {
                $todo->reminders()->create([
                    'frequency_type' => $formFields['frequency_type'],
                    'day_of_week' => $formFields['day_of_week'],
                    'day_of_month' => $formFields['day_of_month'],
                    'time_of_day' => $formFields['time_of_day'],
                ]);
            }

            return formatApiResponse(false, 'Todo created successfully.', [
                'id' => $todo->id,
                'data' => formatTodo($todo)
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (Exception $e) {
            return formatApiResponse(
                true,
                'Todo couldn\'t be created.',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            );
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

        $todo = Todo::findOrFail($id);
        return view('todos.edit_todo', ['todo' => $todo]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Update an existing Todo
     *@group Todos Managemant
     * Updates a specific Todo item based on the provided ID and new data.
     * Requires the `id`, `title`, and `priority` fields in the request.
     *
     * @bodyParam id integer required The ID of the Todo to update. Example: 5
     * @bodyParam title string required The updated title for the Todo. Example: Complete Report
     * @bodyParam priority string required The updated priority level. Example: High
     * @bodyParam description string The updated description for the Todo (optional). Example: Submit final version to manager
     * @bodyParam isApi boolean Whether the request is from API. Example: true
     *@header workspace_id 2
     * @response 200 {
     *   "error": false,
     *   "message": "Todo updated successfully.",
     *   "data": {
     *     "id": 5,
     *     "data": {
     *       "id": 5,
     *       "title": "Complete Report",
     *       "priority": "High",
     *       "description": "Submit final version to manager",
     *       "workspace_id": 2,
     *       "admin_id": 1,
     *       "created_at": "2025-06-05 14:45:23",
     *       "updated_at": "2025-06-06 10:12:45"
     *     }
     *   }
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "title": ["The title field is required."],
     *     "priority": ["The priority field is required."]
     *   }
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "No query results for model [App\\Models\\Todo] 999."
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "Todo couldn't be updated.",
     *   "data": {
     *     "error": "Error message details...",
     *     "line": 45,
     *     "file": "/app/Http/Controllers/TodoController.php"
     *   }
     * }
     */

    public function update(Request $request)
    {
        $isApi = $request->get('isApi', true);

        try {
            $formFields = $request->validate([
                'id' => ['required', 'exists:todos,id'],
                'title' => ['required'],
                'priority' => ['required'],
                'description' => ['nullable'],
                // Reminder fields
                'enable_reminder' => ['nullable', 'in:on'],
                'frequency_type' => ['nullable', 'in:daily,weekly,monthly'],
                'day_of_week' => ['nullable', 'integer', 'between:1,7'],
                'day_of_month' => ['nullable', 'integer', 'between:1,31'],
                'time_of_day' => ['nullable', 'date_format:H:i'],
            ]);

            $todo = Todo::findOrFail($formFields['id']);
            $todo->update($formFields);

            // Reminder logic
            if (!empty($formFields['enable_reminder']) && $formFields['enable_reminder'] === 'on') {
                $reminderData = [
                    'frequency_type' => $formFields['frequency_type'],
                    'day_of_week' => $formFields['day_of_week'],
                    'day_of_month' => $formFields['day_of_month'],
                    'time_of_day' => $formFields['time_of_day'],
                ];

                // Update existing reminder or create new
                if ($todo->reminders()->exists()) {
                    $todo->reminders()->update($reminderData);
                } else {
                    $todo->reminders()->create($reminderData);
                }
            } else {
                // Optional: Remove reminders if "enable_reminder" is not "on"
                $todo->reminders()->delete();
            }

            return formatApiResponse(
                false,
                'Todo updated successfully.',
                [
                    'id' => $todo->id,
                    'data' => formatTodo($todo)
                ]
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            return formatApiResponse(
                true,
                'Todo couldn\'t be updated.',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            );
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * Delete a Todo
     *
     * Deletes a specific todo item by ID.
     *@group Todos Managemant
     * This endpoint will delete a Todo from the database. It returns a success response if the deletion was successful, or appropriate error messages otherwise.
     * @header workspace_id 2
     * @urlParam id integer required The ID of the todo to delete. Example: 42
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Todo deleted successfully.",
     *   "data": {
     *     "id": 42,
     *     "title": "Call client",
     *     "priority": "High",
     *     "description": "Discuss contract terms",
     *     "created_at": "2025-06-05T12:00:00Z",
     *     "updated_at": "2025-06-05T14:00:00Z"
     *   }
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "Todo not found.",
     *   "data": []
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while deleting Todo.",
     *   "data": {
     *     "error": "SQLSTATE[23000]: Integrity constraint violation: ...",
     *     "line": 88,
     *     "file": "/app/Http/Controllers/TodoController.php"
     *   }
     * }
     */

    public function destroy($id)
    {
        $isApi = request()->get('isApi', true);

        try {
            $todo = Todo::findOrFail($id);

            $response = DeletionService::delete(Todo::class, $id, 'Todo');

            if ($response->getData()->error === false) {
                return formatApiResponse(false, 'Todo deleted successfully.', formatTodo($todo));
            }

            return formatApiResponse(true, 'Failed to delete Todo.', []);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            return formatApiResponse(
                true,
                'Todo couldn\'t be updated.',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]
            );
        }
    }
    /**
     * Update the completion status of a Todo.
     *
     * This endpoint allows the user to update the `is_completed` status of a specific Todo item by providing its ID and the desired status.
     *
     * @bodyParam id integer required The ID of the Todo. Example: 12
     * @bodyParam status boolean required Status to set. 1 for completed, 0 for pending. Example: 1
     * @bodyParam isApi boolean optional Whether the request is API-based. Example: true
     * @header workspace_id 2
     * @response 200 {
     *   "error": false,
     *   "message": "Status updated successfully.",
     *   "id": 12,
     *   "activity_message": "John Doe marked todo Task Title as Completed"
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "id": ["The id field is required."],
     *     "status": ["The status field is required."]
     *   }
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "No query results for model [App\\Models\\Todo] 999."
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "Todo couldn't be updated.",
     *   "error": "Detailed error message",
     *   "line": 123,
     *   "file": "/path/to/file.php"
     * }
     */



    public function update_status(Request $request)
    {
        $isApi = $request->get('isApi', true);

        try {
            $formFields = $request->validate([
                'id' => ['required', 'exists:todos,id'],
                'status' => ['required', 'boolean'],
            ]);

            $id = $formFields['id'];
            $status = $formFields['status'];

            $todo = Todo::findOrFail($id);
            $todo->is_completed = $status;
            $statusText = $status == 1 ? 'Completed' : 'Pending';

            if ($todo->save()) {
                return formatApiResponse(
                    false,
                    'Status updated successfully.',
                    [
                        'id' => $id,
                        'activity_message' => $this->user->first_name . ' ' . $this->user->last_name . ' marked todo ' . $todo->title . ' as ' . $statusText,
                        'data' => formatTodo($todo)
                    ]
                );
            } else {
                return formatApiResponse(true, 'Status couldn\'t be updated.', []);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            return formatApiResponse(
                true,
                'An error occurred while updating the status.',
                [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]
            );
        }
    }

    public function get($id)
    {
        $todo = Todo::with('reminders')->findOrFail($id);
        return response()->json(['todo' => $todo]);
    }

    public function destroy_multiple(Request $request)
    {
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:todos,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];

        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $todo = Todo::findOrFail($id);
            $deletedIds[] = $id;
            $deletedTitles[] = $todo->title;
            DeletionService::delete(Todo::class, $id, 'Todo');
        }
        Session::flash('message', 'Todo(s) deleted successfully.');
        return response()->json([
            'error' => false,
            'message' => 'Todo(s) deleted successfully.',
            'id' => $deletedIds,
            'titles' => $deletedTitles
        ]);
    }
    /**
     * Display a listing of todos or a specific todo by ID.
     *@group Todos Managemant
     * This endpoint returns all todos for the current workspace with optional filtering,
     * sorting, and pagination. If an ID is provided, it returns the specific todo.
     *
     * @queryParam isApi bool Optional. Defaults to true. Set to true to receive API formatted response.
     * @queryParam id int Optional. If provided, fetch a specific todo by this ID.
     * @queryParam search string Optional. Search term to filter todos by title or description.
     * @queryParam sort string Optional. Column name to sort by. Default: id.
     * @queryParam order string Optional. Sort order: asc or desc. Default: desc.
     * @queryParam limit int Optional. Number of items per page for pagination. Default: 10.
     * @header workspace_id 2
     * @response 200 {
     *   "error": false,
     *   "message": "Todos retrieved successfully.",
     *   "total": 25,
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Sample Todo",
     *       "description": "Description here",
     *       "priority": "High",
     *       "is_completed": false,
     *       "creator": {
     *         "id": 2,
     *         "first_name": "John",
     *         "last_name": "Doe"
     *       },
     *       "created_at": "2025-06-05 10:00:00",
     *       "updated_at": "2025-06-05 10:30:00"
     *     }
     *   ]
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "Todo not found.",
     *   "data": []
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "Internal Server Error",
     *   "data": {
     *     "line": 123,
     *     "file": "/app/Http/Controllers/TodoController.php"
     *   }
     * }
     */

    public function listapi($id = null)
    {
        $isApi = request()->get('isApi', true);

        try {
            $query = Todo::with(['creator'])->where('workspace_id', $this->workspace->id);

            if ($id) {
                $todo = $query->findOrFail($id);
                return formatApiResponse(false, 'Todo retrieved successfully.', formatTodo($todo));
            }

            // Filters
            $search = request('search');
            $sort = request('sort', 'id');
            $order = request('order', 'DESC');
            $per_page = request('per_page', 10);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%");
                });
            }

            $total = $query->count();

            $todos = $query->orderBy($sort, $order)
                ->paginate($per_page)
                ->getCollection()
                ->map(function ($todo) {
                    return formatTodo($todo);
                });

            return formatApiResponse(false, 'Todos retrieved successfully.', [
                'total' => $total,
                'data' => $todos,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return formatApiResponse(true, 'Todo not found.', []);
        } catch (\Exception $e) {
            return formatApiResponse(true, $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }
    }
}
