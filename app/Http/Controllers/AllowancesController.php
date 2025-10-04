<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\Workspace;

use Illuminate\Http\Request;
use App\Services\DeletionService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class AllowancesController extends Controller
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

        return $next($request);
    });
}
    public function index(Request $request)
    {
        $allowances = $this->workspace->allowances();
        $allowances = $allowances->count();
        return view('allowances.list', ['allowances' => $allowances]);
    }
    /**
     * @group Allowances
     * @header workspace_id: 2
     *
     * Create a new allowance.
     *
     * This endpoint creates a new allowance for the given workspace.
     *
     * @bodyParam title string required The allowance title.
     * @bodyParam amount number required The allowance amount.
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Allowance created successfully.",
     *   "id": 1,
     *   "data": {
     *     "id": 1,
     *     "title": "Travel Allowance",
     *     "amount": 1500,
     *     ...
     *   }
     * }
     *
     * @response 400 {
     *   "error": true,
     *   "message": "Allowance couldn't be created."
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
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred: ..."
     * }
     */
    public function store(Request $request)
    {
        try {
            $isApi = $request->get('isApi', false);
            $adminId = getAdminIdByUserRole();
            $formFields = $request->validate([
                'title' => 'required|unique:allowances,title',
                'amount' => 'required|numeric',
            ]);
            $formFields['workspace_id'] = $this->workspace->id;
            $formFields['admin_id'] = $adminId;

            if ($allowance = Allowance::create($formFields)) {
                if ($isApi) {
                    return formatApiResponse(false, 'Allowance created successfully.', [
                        'id' => $allowance->id,
                        'data' => formatAllowance($allowance),
                    ]);
                } else {
                    return response()->json([
                        'error' => false,
                        'message' => 'Allowance created successfully.',
                        'id' => $allowance->id,
                        'allowance' => $allowance
                    ]);
                }
            } else {
                if ($isApi) {
                    return formatApiResponse(true, 'Allowance couldn\'t be created.', [], 400);
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => 'Allowance couldn\'t created.'
                    ]);
                }
            }
        } catch (\Throwable $e) {
            if ($request->get('isApi', false)) {
                return formatApiResponse(true, 'An error occurred: ' . $e->getMessage(), [], 500);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
        }
}

    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $allowances = $this->workspace->allowances();
        if ($search) {
            $allowances = $allowances->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('amount', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $allowances->count();
        $allowances = $allowances->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(
                fn ($allowance) => [
                    'id' => $allowance->id,
                    'title' => $allowance->title,
                    'amount' => format_currency($allowance->amount),
                'created_at' => format_date($allowance->created_at),
                'updated_at' => format_date($allowance->updated_at)
                ]
            );

        return response()->json([
            "rows" => $allowances->items(),
            "total" => $total,
        ]);
    }

    /**
     * @group Allowances
     * @header workspace_id: 2
     *
     * List allowances (API).
     *
     * This endpoint returns a list of allowances for the given workspace, or a single allowance if an ID is provided.
     *
     * @queryParam id integer Optional. The ID of the allowance to fetch.
     * @queryParam search string Optional. Search by title, amount, or ID.
     * @queryParam sort string Optional. Field to sort by. Default is "id".
     * @queryParam order string Optional. Sort order (ASC or DESC). Default is "DESC".
     * @queryParam limit integer Optional. Number of results per page.
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Allowances fetched successfully.",
     *   "rows": [
     *     { "id": 1, "title": "Travel Allowance", "amount": 1500, ... }
     *   ],
     *   "total": 1
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "Allowance not found."
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred: ..."
     * }
     */
    public function apiList(Request $request, $id = null)
    {
        if ($id) {
            $allowance = $this->workspace->allowances()->where('id', $id)->first();
            if (!$allowance) {
                return formatApiResponse(true, 'Allowance not found.', null, 404);
            }
            return formatApiResponse(false, 'Allowance fetched successfully.', [
                'data' => formatAllowance($allowance),
            ]);
        } else {
            $search = $request->get('search');
            $sort = $request->get('sort', 'id');
            $order = $request->get('order', 'DESC');
            $limit = $request->get('limit', 10);
            $allowances = $this->workspace->allowances();
            if ($search) {
                $allowances = $allowances->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('amount', 'like', '%' . $search . '%')
                        ->orWhere('id', 'like', '%' . $search . '%');
                });
            }
            $total = $allowances->count();
            $allowances = $allowances->orderBy($sort, $order)
                ->paginate($limit);

            return formatApiResponse(false, 'Allowances fetched successfully.', [
                'rows' => formatAllowance($allowances->items()),
                'total' => $total,
            ]);
        }
    }

    public function get($id)
    {
        $allowance = Allowance::findOrFail($id);
        return response()->json(['allowance' => $allowance]);
    }
    /**
     * @group Allowances
     * @header workspace_id: 2
     *
     * Update an existing allowance.
     *
     * This endpoint updates an allowance for the given workspace.
     *
     * @bodyParam id integer required The ID of the allowance to update.
     * @bodyParam title string required The allowance title.
     * @bodyParam amount number required The allowance amount.
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Allowance updated successfully.",
     *   "id": 1,
     *   "data": {
     *     "id": 1,
     *     "title": "Updated Allowance",
     *     "amount": 2000,
     *     ...
     *   }
     * }
     *
     * @response 400 {
     *   "error": true,
     *   "message": "Allowance couldn't be updated."
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "Allowance not found."
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
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred: ..."
     * }
     */
    public function update(Request $request)
    {
        $isApi = $request->get('isApi', false);
        $formFields = $request->validate([
            'id' => 'required',
            'title' => 'required|unique:allowances,title,' . $request->id,
            'amount' => 'required|numeric',
        ]);
        $allowance = Allowance::findOrFail($request->id);

        if ($allowance->update($formFields)) {
            if ($isApi) {
                return formatApiResponse(false, 'Allowance updated successfully.', [
                    'id' => $allowance->id,
                    'data' => formatAllowance($allowance->fresh()),
                ]);
            } else {
                return response()->json([
                    'error' => false,
                    'message' => 'Allowance updated successfully.',
                    'id' => $allowance->id
                ]);
            }
        } else {
            if ($isApi) {
                return formatApiResponse(true, 'Allowance couldn\'t be updated.', [], 400);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Allowance couldn\'t updated.'
                ]);
            }
        }
    }
    /**
     * @group Allowances
     * @header workspace_id: 2
     *
     * Delete an allowance.
     *
     * This endpoint deletes an allowance by its ID for the given workspace.
     *
     * @urlParam id integer required The ID of the allowance to delete.
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Allowance(s) deleted successfully.",
     *   "id": [1],
     *   "titles": ["Travel Allowance"]
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "Allowance not found."
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred: ..."
     * }
     */
    public function destroy($id)
    {
        $allowance = Allowance::findOrFail($id);
        $allowance->payslips()->detach();
        $response = DeletionService::delete(Allowance::class, $id, 'Allowance');
        return $response;
    }

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:allowances,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $allowance = Allowance::findOrFail($id);
            $deletedIds[] = $id;
            $deletedTitles[] = $allowance->title;
            $allowance->payslips()->detach();
            DeletionService::delete(Allowance::class, $id, 'Allowance');
        }

        return response()->json(['error' => false, 'message' => 'Allowance(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles]);
    }
}
