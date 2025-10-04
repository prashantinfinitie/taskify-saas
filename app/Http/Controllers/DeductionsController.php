<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Deduction;
use Illuminate\Support\Facades\Session;
use App\Services\DeletionService;

class DeductionsController extends Controller
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

    /**
     * Create Deduction
     *
     * @group Deductions
     * @header workspace_id: 2
     *
     * Create a new deduction. Supports both web and API usage.
     *
     * @bodyParam isApi boolean required Set to true for API response. Example: true
     * @bodyParam title string required The deduction title. Example: Provident Fund
     * @bodyParam type string required The deduction type (amount or percentage). Example: amount
     * @bodyParam amount number required if type=amount The deduction amount. Example: 500
     * @bodyParam percentage number required if type=percentage The deduction percentage. Example: 10
     *
     * @response 200 scenario="Success" {
     *   "error": false,
     *   "message": "Deduction created successfully.",
     *   "id": 1,
     *   "data": {"id":1, ...}
     * }
     * @response 400 scenario="Validation error" {"error": true, "message": "..."}
     * @response 500 scenario="Error" {"error": true, "message": "An error occurred: ..."}
     */
    public function index(Request $request)
    {
        $deductions = $this->workspace->deductions();
        $deductions = $deductions->count();
        return view('deductions.list', ['deductions' => $deductions]);
    }

    /**
     * Create Deduction
     *
     * @group Deductions
     * @header workspace_id: 2
     *
     * Create a new deduction. Supports both web and API usage.
     *
     * @bodyParam isApi boolean required Set to true for API response. Example: true
     * @bodyParam title string required The deduction title. Example: Provident Fund
     * @bodyParam type string required The deduction type (amount or percentage). Example: amount
     * @bodyParam amount number required if type=amount The deduction amount. Example: 500
     * @bodyParam percentage number required if type=percentage The deduction percentage. Example: 10
     *
     * @response 200 scenario="Success" {
     *   "error": false,
     *   "message": "Deduction created successfully.",
     *   "id": 1,
     *   "data": {"id":1, ...}
     * }
     * @response 400 scenario="Validation error" {"error": true, "message": "..."}
     * @response 500 scenario="Error" {"error": true, "message": "An error occurred: ..."}
     */
    public function store(Request $request)
    {
        try {
            $isApi = $request->get('isApi', false);
            $adminId = getAdminIdByUserRole();
            // Validate the request data
            $formFields = $request->validate([
                'title' => 'required|unique:deductions,title',
                'type' => [
                    'required',
                    Rule::in(['amount', 'percentage']),
                ],
            ]);

            $formFields['workspace_id'] = $this->workspace->id;
            $formFields['admin_id'] = $adminId;

            if ($request->type === 'amount') {
                $validatedAmount = $request->validate([
                    'amount' => 'required|numeric',
                ]);
                $formFields['amount'] = $validatedAmount['amount'];
                $formFields['percentage'] = null;
            } elseif ($request->type === 'percentage') {
                $validatedPercentage = $request->validate([
                    'percentage' => 'required|numeric',
                ]);
                $formFields['percentage'] = $validatedPercentage['percentage'];
                $formFields['amount'] = null;
            }

            if ($deduction = Deduction::create($formFields)) {
                if ($isApi) {
                    return formatApiResponse(false, 'Deduction created successfully.', [
                        'id' => $deduction->id,
                        'data' => formatDeduction($deduction),
                    ]);
                } else {
                    return response()->json([
                        'error' => false,
                        'message' => 'Deduction created successfully.',
                        'id' => $deduction->id,
                        'deduction' => $deduction
                    ]);
                }
            } else {
                if ($isApi) {
                    return formatApiResponse(true, 'Deduction couldn\'t be created.', [], 400);
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => 'Deduction couldn\'t created.'
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

    /**
     * List Deductions (API)
     *
     * @group Deductions
     * @header workspace_id: 2
     *
     * List all deductions or fetch a single deduction by ID. Supports filters and pagination.
     *
     * @queryParam id int Optional. If provided, returns the deduction with this ID. Example: 5
     * @queryParam search string Optional. Search term for deductions. Example: Provident
     * @queryParam sort string Optional. Sort field. Default: id. Example: title
     * @queryParam order string Optional. Sort order. Default: DESC. Example: ASC
     * @queryParam limit int Optional. Pagination limit. Default: 10. Example: 10
     *
     * @response 200 scenario="List" {
     *   "error": false,
     *   "message": "Deductions fetched successfully.",
     *   "total": 2,
     *   "rows": [
     *     {"id":1,"title":"Provident Fund", ...},
     *     {"id":2,"title":"TDS", ...}
     *   ]
     * }
     * @response 200 scenario="Single" {
     *   "error": false,
     *   "message": "Deduction fetched successfully.",
     *   "data": {"id":1,"title":"Provident Fund", ...}
     * }
     * @response 404 scenario="Not found" {"error": true, "message": "Deduction not found."}
     * @response 500 scenario="Error" {"error": true, "message": "An error occurred: ..."}
     */
    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $deductions = $this->workspace->deductions();
        if ($search) {
            $deductions = $deductions->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('amount', 'like', '%' . $search . '%')
                    ->orWhere('percentage', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $deductions->count();
        $deductions = $deductions->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(
                fn ($deduction) => [
                    'id' => $deduction->id,
                    'title' => $deduction->title,
                    'type' => $deduction->type,
                    'percentage' => $deduction->percentage,
                    'amount' => format_currency($deduction->amount),
                    'created_at' => format_date($deduction->created_at,  'H:i:s'),
                    'updated_at' => format_date($deduction->updated_at, 'H:i:s'),
                ]
            );

        return response()->json([
            "rows" => $deductions->items(),
            "total" => $total,
        ]);
    }



    public function get($id)
    {
        $deduction = Deduction::findOrFail($id);
        return response()->json(['deduction' => $deduction]);
    }

    /**
     * Update Deduction
     *
     * @group Deductions
     * @header workspace_id: 2
     *
     * Update a deduction by ID. Supports both web and API usage.
     *
     * @bodyParam isApi boolean required Set to true for API response. Example: true
     * @bodyParam id int required The ID of the deduction. Example: 1
     * @bodyParam title string required The deduction title. Example: Provident Fund
     * @bodyParam type string required The deduction type (amount or percentage). Example: amount
     * @bodyParam amount number required if type=amount The deduction amount. Example: 500
     * @bodyParam percentage number required if type=percentage The deduction percentage. Example: 10
     *
     * @response 200 scenario="Success" {
     *   "error": false,
     *   "message": "Deduction updated successfully.",
     *   "id": 1,
     *   "data": {"id":1, ...}
     * }
     * @response 400 scenario="Validation error" {"error": true, "message": "..."}
     * @response 404 scenario="Not found" {"error": true, "message": "Deduction not found."}
     * @response 500 scenario="Error" {"error": true, "message": "An error occurred: ..."}
     */
    public function update(Request $request)
    {
        try {
            $isApi = $request->get('isApi', false);
            $formFields = $request->validate([
                'id' => 'required',
                'title' => 'required|unique:deductions,title,' . $request->id,
                'type' => [
                    'required',
                    Rule::in(['amount', 'percentage']),
                ],
            ]);

            $formFields['workspace_id'] = $this->workspace->id;

            if ($request->type === 'amount') {
                $validatedAmount = $request->validate([
                    'amount' => 'required|numeric',
                ]);
                $formFields['amount'] = $validatedAmount['amount'];
                $formFields['percentage'] = null;
            } elseif ($request->type === 'percentage') {
                $validatedPercentage = $request->validate([
                    'percentage' => 'required|numeric',
                ]);
                $formFields['percentage'] = $validatedPercentage['percentage'];
                $formFields['amount'] = null;
            }

            $deduction = Deduction::findOrFail($request->id);

            if ($deduction->update($formFields)) {
                if ($isApi) {
                    return formatApiResponse(false, 'Deduction updated successfully.', [
                        'id' => $deduction->id,
                        'data' => formatDeduction($deduction->fresh()),
                    ]);
                } else {
                    return response()->json([
                        'error' => false,
                        'message' => 'Deduction updated successfully.',
                        'id' => $deduction->id
                    ]);
                }
            } else {
                if ($isApi) {
                    return formatApiResponse(true, 'Deduction couldn\'t be updated.', [], 400);
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => 'Deduction couldn\'t updated.'
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

    /**
     * Delete Deduction(s)
     *
     * @group Deductions
     * @header workspace_id: 2
     *
     * Delete one or more deductions by ID. Supports both web and API usage.
     *
     * @bodyParam isApi boolean required Set to true for API response. Example: true
     * @bodyParam ids array required The IDs of the deductions to delete. Example: [1,2]
     *
     * @response 200 scenario="Success" {
     *   "error": false,
     *   "message": "Deduction(s) deleted successfully.",
     *   "id": [1,2],
     *   "titles": ["Provident Fund", "TDS"]
     * }
     * @response 400 scenario="Validation error" {"error": true, "message": "..."}
     * @response 500 scenario="Error" {"error": true, "message": "An error occurred: ..."}
     */
    public function destroy($id)
    {
        $deduction = Deduction::findOrFail($id);
        $deduction->payslips()->detach();
        $response = DeletionService::delete(Deduction::class, $id, 'Deduction');
        return $response;
    }
    /**
     * Delete Deduction(s) - Multiple
     *
     * @group Deductions
     * @header workspace_id: 2
     *
     * Delete multiple deductions by their IDs. Supports both web and API usage.
     *
     * @bodyParam isApi boolean required Set to true for API response. Example: true
     * @bodyParam ids array required The IDs of the deductions to delete. Example: [1,2]
     *
     * @response 200 scenario="Success" {
     *   "error": false,
     *   "message": "Deduction(s) deleted successfully.",
     *   "id": [1,2],
     *   "titles": ["Provident Fund", "TDS"]
     * }
     * @response 400 scenario="Validation error" {"error": true, "message": "..."}
     * @response 500 scenario="Error" {"error": true, "message": "An error occurred: ..."}
     */
    public function destroy_multiple(Request $request)
    {
        try {
            $isApi = $request->get('isApi', false);
            $validatedData = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:deductions,id'
            ]);

            $ids = $validatedData['ids'];
            $deletedIds = [];
            $deletedTitles = [];
            foreach ($ids as $id) {
                $deduction = Deduction::findOrFail($id);
                $deletedIds[] = $id;
                $deletedTitles[] = $deduction->title;
                $deduction->payslips()->detach();
                DeletionService::delete(Deduction::class, $id, 'Deduction');
            }

            if ($isApi) {
                return formatApiResponse(false, 'Deduction(s) deleted successfully.', [
                    'id' => $deletedIds,
                    'titles' => $deletedTitles
                ]);
            } else {
                return response()->json([
                    'error' => false,
                    'message' => 'Deduction(s) deleted successfully.',
                    'id' => $deletedIds,
                    'titles' => $deletedTitles
                ]);
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
    /**
     * List Deductions (API)
     *
     * @group Deductions
     * @header workspace_id: 2
     *
     * List all deductions or fetch a single deduction by ID. Supports filters and pagination.
     *
     * @queryParam id int Optional. If provided, returns the deduction with this ID. Example: 5
     * @queryParam search string Optional. Search term for deductions. Example: Provident
     * @queryParam sort string Optional. Sort field. Default: id. Example: title
     * @queryParam order string Optional. Sort order. Default: DESC. Example: ASC
     * @queryParam limit int Optional. Pagination limit. Default: 10. Example: 10
     *
     * @response 200 scenario="List" {
     *   "error": false,
     *   "message": "Deductions fetched successfully.",
     *   "total": 2,
     *   "rows": [
     *     {"id":1,"title":"Provident Fund", ...},
     *     {"id":2,"title":"TDS", ...}
     *   ]
     * }
     * @response 200 scenario="Single" {
     *   "error": false,
     *   "message": "Deduction fetched successfully.",
     *   "data": {"id":1,"title":"Provident Fund", ...}
     * }
     * @response 404 scenario="Not found" {"error": true, "message": "Deduction not found."}
     * @response 500 scenario="Error" {"error": true, "message": "An error occurred: ..."}
     */
    public function listApi(Request $request, $id = null)
    {
        try {
            if ($id) {
                $deduction = $this->workspace->deductions()->where('id', $id)->first();
                if (!$deduction) {
                    return formatApiResponse(true, 'Deduction not found.', [], 404);
                }
                return formatApiResponse(false, 'Deduction fetched successfully.', [
                    'data' => formatDeduction($deduction),
                ]);
            } else {
                $search = $request->get('search');
                $sort = $request->get('sort', 'id');
                $order = $request->get('order', 'DESC');
                $limit = $request->get('limit', 10);
                $deductions = $this->workspace->deductions();
                if ($search) {
                    $deductions = $deductions->where(function ($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%')
                            ->orWhere('amount', 'like', '%' . $search . '%')
                            ->orWhere('percentage', 'like', '%' . $search . '%')
                            ->orWhere('type', 'like', '%' . $search . '%')
                            ->orWhere('id', 'like', '%' . $search . '%');
                    });
                }
                $total = $deductions->count();
                $deductions = $deductions->orderBy($sort, $order)
                    ->paginate($limit);

                return formatApiResponse(false, 'Deductions fetched successfully.', [
                    'rows' => formatDeduction($deductions->items()),
                    'total' => $total,
                ]);
            }
        } catch (\Throwable $e) {
            return formatApiResponse(true, 'An error occurred: ' . $e->getMessage(), [], 500);
        }
    }
}
