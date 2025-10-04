<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Workspace;
use App\Models\ContractType;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class ContractsController extends Controller
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
        $contracts = isAdminOrHasAllDataAccess() ? $this->workspace->contracts() : $this->user->contracts();
        $contracts = $contracts->count();
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects : $this->user->projects;
        $contract_types = $this->workspace->contract_types;
        return view('contracts.list', ['contracts' => $contracts, 'users' => $users, 'clients' => $clients, 'projects' => $projects, 'contract_types' => $contract_types]);
    }

/**
 * @group Contracts
 * @header workspace_id: 2
 *
 * Create a new contract.
 *
 * @bodyParam title string required The contract title.
 * @bodyParam value number required The contract value.
 * @bodyParam start_date date required The start date (Y-m-d).
 * @bodyParam end_date date required The end date (Y-m-d).
 * @bodyParam client_id integer required The client ID.
 * @bodyParam project_id integer required The project ID.
 * @bodyParam contract_type_id integer required The contract type ID.
 * @bodyParam description string The contract description.
 *
 * @response 200 {
 *   "message": "Contract created successfully.",
 *   "data": {
 *     "id": 1,
 *     "title": "Sample Contract",
 *     ...
 *   }
 * }
 */
public function store(Request $request)
{
    $adminId = getAdminIdByUserRole();
    if ($this->user->hasRole('client')) {

        $request->merge(['client_id' => $this->user->id]);
    }
    $formFields = $request->validate(
        [
            'title' => ['required'],
            'value' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required'],
            'client_id' => ['required'],
            'project_id' => ['required'],
            'contract_type_id' => ['required'],
            'description' => ['nullable']
        ],
        [
            'client_id.required' => 'The client field is required.',
            'value.regex' => 'The value must be a valid number.',
            'project_id.required' => 'The project field is required.',
            'contract_type_id.required' => 'The contract type field is required.'
        ]
    );

    $start_date = $request->input('start_date');
    $end_date = $request->input('end_date');
    $formFields['start_date'] = format_date($start_date, false, app('php_date_format'), 'Y-m-d');
    $formFields['end_date'] = format_date($end_date, false, app('php_date_format'), 'Y-m-d');
    $formFields['workspace_id'] = $this->workspace->id;
    $formFields['created_by'] = isClient() ? 'c_' : 'u_' . $this->user->id;
    $formFields['admin_id'] = $adminId;

    $isApi = $request->get('isApi', false);

    if ($contract = Contract::create($formFields)) {
        if ($isApi) {
            return response()->json([
                'message' => 'Contract created successfully.',
                'data' => formatContracts($contract)
            ]);
        } else {
            return response()->json(['error' => false, 'message' => 'Contract created successfully.', 'id' => $contract->id]);
        }
    } else {
        if ($isApi) {
            return response()->json([
                'message' => 'Contract could not be created.',
                'data' => null
            ], 400);
        } else {
            return response()->json(['error' => true, 'message' => 'Contract couldn\'t created.']);
        }
    }
}


/**
     * @group Contracts
     * @header workspace_id: 2
     *
     * Update an existing contract.
     *
     * This endpoint updates a contract for the given workspace.
     *
     * @bodyParam id integer required The ID of the contract to update.
     * @bodyParam title string required The contract title.
     * @bodyParam value number required The contract value.
     * @bodyParam start_date date required The start date (Y-m-d).
     * @bodyParam end_date date required The end date (Y-m-d).
     * @bodyParam client_id integer required The client ID.
     * @bodyParam project_id integer required The project ID.
     * @bodyParam contract_type_id integer required The contract type ID.
     * @bodyParam description string The contract description.
     *
     * @response 200 {
     *   "message": "Contract updated successfully.",
     *   "data": {
     *     "id": 1,
     *     "title": "Updated Contract",
     *     ...
     *   }
     * }
     */
    public function update(Request $request)
{
    $isApi = $request->get('isApi', false);

    $formFields = $request->validate([
        'id' => 'required|exists:contracts,id',
        'title' => ['required'],
        'value' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
        'start_date' => ['required', 'before_or_equal:end_date'],
        'end_date' => ['required'],
        'client_id' => ['required'],
        'project_id' => ['required'],
        'contract_type_id' => ['required'],
        'description' => ['nullable']
    ], [
        'client_id.required' => 'The client field is required.',
        'value.regex' => 'The value must be a valid number.',
        'project_id.required' => 'The project field is required.',
        'contract_type_id.required' => 'The contract type field is required.'
    ]);

    $contract = Contract::findOrFail($formFields['id']);

    $start_date = $request->input('start_date');
    $end_date = $request->input('end_date');
    $formFields['start_date'] = format_date($start_date, false, app('php_date_format'), 'Y-m-d');
    $formFields['end_date'] = format_date($end_date, false, app('php_date_format'), 'Y-m-d');

    if ($contract->update($formFields)) {
        if ($isApi) {
            return response()->json([
                'message' => 'Contract updated successfully.',
                'data' => formatContracts($contract->fresh())
            ]);
        } else {
            return response()->json(['error' => false, 'message' => 'Contract updated successfully.', 'id' => $formFields['id']]);
        }
    } else {
        if ($isApi) {
            return response()->json([
                'message' => 'Contract could not be updated.',
                'data' => null
            ], 400);
        } else {
            return response()->json(['error' => true, 'message' => 'Contract couldn\'t updated.']);
        }
    }
}

    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $status = (request('status')) ? request('status') : "";
        $type_id = (request('type_id')) ? request('type_id') : "";
        $project_id = (request('project_id')) ? request('project_id') : "";
        $start_date_from = (request('start_date_from')) ? request('start_date_from') : "";
        $start_date_to = (request('start_date_to')) ? request('start_date_to') : "";
        $end_date_from = (request('end_date_from')) ? request('end_date_from') : "";
        $end_date_to = (request('end_date_to')) ? request('end_date_to') : "";
        $where = ['contracts.workspace_id' => $this->workspace->id];

        $contracts = Contract::select(
            'contracts.*',
            DB::raw('CONCAT(clients.first_name, " ", clients.last_name) AS client_name'),
            'contract_types.type as contract_type',
            'projects.title as project_title'
        )
            ->leftJoin('users', 'contracts.created_by', '=', 'users.id')
            ->leftJoin('clients', 'contracts.client_id', '=', 'clients.id')
            ->leftJoin('contract_types', 'contracts.contract_type_id', '=', 'contract_types.id')
            ->leftJoin('projects', 'contracts.project_id', '=', 'projects.id');


        if (!isAdminOrHasAllDataAccess()) {
            $contracts = $contracts->where(function ($query) {
                $query->where('contracts.created_by', isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id)
                    ->orWhere('contracts.client_id', $this->user->id);
            });
        }

        if ($project_id) {
            $where['project_id'] = $project_id;
        }
        if ($type_id) {
            $where['contract_type_id'] = $type_id;
        }
        if ($status) {
            if ($status == 'partially_signed') {
                $contracts = $contracts->where(function ($query) {
                    $query->where(function ($subquery) {
                        $subquery->whereNotNull('promisor_sign')
                        ->whereNull('promisee_sign');
                    })
                        ->orWhere(function ($subquery) {
                            $subquery->whereNull('promisor_sign')
                        ->whereNotNull('promisee_sign');
                        });
                });
            }
            if ($status == 'signed') {
                $contracts = $contracts->whereNotNull('promisor_sign')->WhereNotNull('promisee_sign');
            }
            if ($status == 'not_signed') {
                $contracts = $contracts->whereNull('promisor_sign')->whereNull('promisee_sign');
            }
        }
        if ($start_date_from && $start_date_to) {
            $contracts = $contracts->whereBetween('contracts.start_date', [$start_date_from, $start_date_to]);
        }
        if ($end_date_from && $end_date_to) {
            $contracts  = $contracts->whereBetween('contracts.end_date', [$end_date_from, $end_date_to]);
        }
        if ($search) {
            $contracts = $contracts->where(function ($query) use ($search) {
                $query->where('contracts.title', 'like', '%' . $search . '%')
                    ->orWhere('value', 'like', '%' . $search . '%')
                    ->orWhere('contracts.description', 'like', '%' . $search . '%')
                    ->orWhere('contracts.id', 'like', '%' . $search . '%');
            });
        }

        $contracts->where($where);
        $total = $contracts->count();

        $canCreate = checkPermission('create_contracts');
        $canEdit = checkPermission('edit_contracts');
        $canDelete = checkPermission('delete_contracts');

        $contracts = $contracts->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($contract) use ($canEdit, $canDelete, $canCreate) {
            // Format "from_date" and "to_date" with labels
            $formattedDates = format_date($contract->start_date, false) . ' ' . get_label('to', 'To') . ' ' . format_date($contract->end_date, false);

                $promisorSign = $contract->promisor_sign;
                $promiseeSign = $contract->promisee_sign;

                $statusBadge = '';

                $promisor_sign_status = !is_null($promisorSign) ? '<span class="badge bg-success">' . get_label('signed', 'Signed') . '</span>' : '<span class="badge bg-danger">' . get_label('not_signed', 'Not signed') . '</span>';
                $promisee_sign_status = !is_null($promiseeSign) ? '<span class="badge bg-success">' . get_label('signed', 'Signed') . '</span>' : '<span class="badge bg-danger">' . get_label('not_signed', 'Not signed') . '</span>';

                if (!is_null($promisorSign) && !is_null($promiseeSign)) {
                    $statusBadge = '<span class="badge bg-success">' . get_label('signed', 'Signed') . '</span>';
                } elseif (!is_null($promisorSign) || !is_null($promiseeSign)) {
                    $statusBadge = '<span class="badge bg-warning">' . get_label('partially_signed', 'Partially signed') . '</span>';
                } else {
                    $statusBadge = '<span class="badge bg-danger">' . get_label('not_signed', 'Not signed') . '</span>';
                }
                if (strpos($contract->created_by, 'u_') === 0) {
                    // The ID corresponds to a user
                    $creator = User::find(substr($contract->created_by, 2)); // Remove the 'u_' prefix
                } elseif (strpos($contract->created_by, 'c_') === 0) {
                // The ID corresponds to a client
                $creator = Client::find(substr($contract->created_by, 2)); // Remove the 'c_' prefix
                }
                if ($creator !== null) {
                    $creator = $creator->first_name . ' ' . $creator->last_name;
                } else {
                    $creator = '-';
                }

            $actions = '';
            if ($canEdit) {
                $actions .= '<a href="javascript:void(0);" class="edit-contract" data-bs-toggle="modal" data-bs-target="#edit_contract_modal" data-id="' . $contract->id . '" title="' . get_label('update', 'Update') . '"><i class="bx bx-edit mx-1"></i></a>';
            }
            if ($canDelete) {
                $actions .= '<button title=' . get_label('delete', 'Delete') . ' type="button" class="btn delete" data-id="' . $contract->id . '" data-type="contracts" data-table="contracts_table"><i class="bx bx-trash text-danger mx-1"></i></button>';
            }
            if ($canCreate) {
                $actions .= '<a href="javascript:void(0);" class="duplicate" data-id="' . $contract->id . '" data-title="' . $contract->title . '" data-type="contracts" data-table="contracts_table" title=' . get_label('duplicate', 'Duplicate') . '><i class="bx bx-copy text-warning mx-2"></i></a>';
            }
            $actions = $actions ?: '-';
                return [
                    'id' => $contract->id,
                'title' => $contract->title,
                    'value' => format_currency($contract->value),
                    'start_date' => format_date($contract->start_date),
                    'end_date' => format_date($contract->end_date),
                    'duration' => $formattedDates,
                    'client' => $contract->client_name,
                    'project' => $contract->project_title,
                    'contract_type' => $contract->contract_type,
                    'description' => $contract->description,
                    'promisor_sign' => $promisor_sign_status,
                    'promisee_sign' => $promisee_sign_status,
                    'status' => $statusBadge,
                    'created_by' => $creator,
                'created_at' => format_date($contract->created_at, true),
                'updated_at' => format_date($contract->updated_at, true),
                'actions' => $actions
                ];
            });


        return response()->json([
            "rows" => $contracts->items(),
            "total" => $total,
        ]);
    }


    public function get($id)
    {
        $contract = Contract::findOrFail($id);
        return response()->json(['error' => false, 'contract' => $contract]);
    }

    public function duplicate($id)
    {
        // Use the general duplicateRecord function
        $title = (request()->has('title') && !empty(trim(request()->title))) ? request()->title : '';
        $duplicate = duplicateRecord(Contract::class, $id, [], $title);

        if (!$duplicate) {
            return response()->json(['error' => true, 'message' => 'Contract duplication failed.']);
        }
        return response()->json(['error' => false, 'id' => $id, 'message' => 'Contract duplicated successfully.']);
    }

    public function sign(Request $request, $id)
    {
        $contract = Contract::select(
            'contracts.*',
            'clients.id as client_id',
            'contracts.created_by as created_by_id',
            DB::raw('CONCAT(clients.first_name, " ", clients.last_name) AS client_name'),
            'contract_types.type as contract_type',
            'projects.title as project_title',
            'projects.id as project_id'
        )->where('contracts.id', '=', $id)
            ->leftJoin('users', 'contracts.created_by', '=', 'users.id')
            ->leftJoin('clients', 'contracts.client_id', '=', 'clients.id')
            ->leftJoin('contract_types', 'contracts.contract_type_id', '=', 'contract_types.id')
            ->leftJoin('projects', 'contracts.project_id', '=', 'projects.id')->first();

        if (strpos($contract->created_by, 'u_') === 0) {
            // The ID corresponds to a user
            $creator = User::find(substr($contract->created_by, 2)); // Remove the 'u_' prefix
        } elseif (strpos($contract->created_by, 'c_') === 0) {
            // The ID corresponds to a client
            $creator = Client::find(substr($contract->created_by, 2)); // Remove the 'c_' prefix
        }
        if ($creator !== null) {
            $contract->creator = $creator->first_name . ' ' . $creator->last_name;
        } else {
            $contract->creator = ' -';
        }
        return view('contracts.sign', compact('contract'));
    }

    public function create_sign(Request $request)
    {
        $formFields = $request->validate([
            'id' => 'required',
            'signatureImage' => 'required'
        ]);
        $contract = Contract::findOrFail($formFields['id']);
        $base64Data = $request->input('signatureImage');
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Data));
        // $imageData = base64_decode($base64Data);
        $filename = 'signature_' . uniqid() . '.png';
        Storage::put('public/signatures/' . $filename, $imageData);
        if (($this->user->id == $contract->created_by || isAdminOrHasAllDataAccess()) && !$this->user->hasRole('client')) {
            $contract->promisor_sign = $filename;
        } elseif (($this->user->id == $contract->client_id) && $this->user->hasRole('client')) {
            $contract->promisee_sign = $filename;
        }
        if ($contract->save()) {
            Session::flash('message', 'Signature created successfully.');
            return response()->json(['error' => false, 'id' => $formFields['id'], 'activity_message' => $this->user->first_name . ' ' . $this->user->last_name . ' signed contract ' . $contract->title]);
        } else {
            return response()->json(['error' => true, 'message' => 'Signature couldn\'t created.']);
        }
    }

    public function delete_sign($id)
    {
        $contract = Contract::findOrFail($id);
        if (($this->user->id == $contract->created_by || isAdminOrHasAllDataAccess()) && !$this->user->hasRole('client')) {
            Storage::delete('public/signatures/' . $contract->promisor_sign);
            Contract::where('id', $id)->update(['promisor_sign' => null]);
            Session::flash('message', 'Signature deleted successfully.');
            return response()->json(['error' => false, 'id' => $id, 'activity_message' => $this->user->first_name . ' ' . $this->user->last_name . ' unsigned contract ' . $contract->title]);
        } elseif ($this->user->id == $contract->client_id && $this->user->hasRole('client')) {
            Storage::delete('public/signatures/' . $contract->promisee_sign);
            Contract::where('id', $id)->update(['promisee_sign' => null]);
            Session::flash('message', 'Signature deleted successfully.');
            return response()->json(['error' => false, 'id' => $id, 'activity_message' => $this->user->first_name . ' ' . $this->user->last_name . ' unsigned contract ' . $contract->title]);
        } else {
            Session::flash('error', 'Un authorized access.');
            return response()->json(['error' => true]);
        }
    }

/**
     * @group Contracts
     * @header workspace_id: 2
     *
     * Delete a contract.
     *
     * This endpoint deletes a contract by its ID for the given workspace.
     *
     * @urlParam id integer required The ID of the contract to delete.
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Contract deleted successfully."
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "Contract not found."
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while deleting the contract."
     * }
     */
    public function destroy($id)
    {

        $contract = Contract::findOrFail($id);
        if ($response = DeletionService::delete(Contract::class, $id, 'Contract')) {
            Storage::delete('public/signatures/' . $contract->promisor_sign);
            Storage::delete('public/signatures/' . $contract->promisee_sign);
        }
        return $response;
    }

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:contracts,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedContracts = [];
        $deletedContractTitles = [];
        // Perform deletion using validated IDs

        foreach ($ids as $id) {
            $contract = Contract::findOrFail($id);
            if ($contract) {
                $deletedContracts[] = $id;
                $deletedContractTitles[] = $contract->title;
                if (DeletionService::delete(Contract::class, $id, 'Contract')) {
                    Storage::delete('public/signatures/' . $contract->promisor_sign);
                    Storage::delete('public/signatures/' . $contract->promisee_sign);
                }
            }
        }
        return response()->json(['error' => false, 'message' => 'Contract(s) deleted successfully.', 'id' => $deletedContracts, 'titles' => $deletedContractTitles]);
    }

    public function contract_types(Request $request)
    {
        $contract_types = $this->workspace->contract_types();
        $contract_types = $contract_types->count();
        return view('contracts.contract_types', ['contract_types' => $contract_types]);
    }

/**
     * @group Contracts
     * @header workspace_id: 2
     *
     * Create a new contract type.
     *
     * This endpoint creates a new contract type for the given workspace.
     *
     * @bodyParam type string required The contract type name.
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Contract type created successfully.",
     *   "id": 1,
     *   "title": "NDA",
     *   "type": "contract_type",
     *   "ct": { ... }
     * }
     *
     * @response 400 {
     *   "error": true,
     *   "message": "Contract type couldn't created."
     * }
     */
    public function store_contract_type(Request $request)
    {
        $isApi = $request->get('isApi', false);
        $adminId = getAdminIdByUserRole();
        $formFields = $request->validate([
            'type' => 'required|unique:contract_types,type',
        ]);
        $formFields['workspace_id'] = $this->workspace->id;
        $formFields['admin_id'] = $adminId;

        if ($ct = ContractType::create($formFields)) {
            if ($isApi) {
                return response()->json([
                    'error' => false,
                    'message' => 'Contract type created successfully.',
                    'id' => $ct->id,
                    'title' => $ct->type,
                    'type' => 'contract_type',
                    'ct' => $ct
                ]);
            } else {
                return response()->json([
                    'error' => false,
                    'message' => 'Contract type created successfully.',
                    'id' => $ct->id,
                    'title' => $ct->type,
                    'type' => 'contract_type',
                    'ct' => $ct
                ]);
            }
        } else {
            if ($isApi) {
                return response()->json([
                    'error' => true,
                    'message' => 'Contract type couldn\'t created.'
                ], 400);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Contract type couldn\'t created.'
                ]);
            }
        }
    }

    public function contract_types_list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $contract_types = $this->workspace->contract_types();
        if ($search) {
            $contract_types = $contract_types->where(function ($query) use ($search) {
                $query->where('type', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $contract_types->count();
        $contract_types = $contract_types->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(
                fn ($contract_type) => [
                    'id' => $contract_type->id,
                    'type' => $contract_type->type
                ]
            );

        return response()->json([
            "rows" => $contract_types->items(),
            "total" => $total,
        ]);
    }

/**
     * @group Contracts
     * @header workspace_id: 2
     *
     * List all contract types (API).
     *
     * This endpoint returns all contract types for the given workspace.
     *
     * @queryParam search string Optional. Search by contract type name or ID.
     * @queryParam sort string Optional. Field to sort by. Default is "id".
     * @queryParam order string Optional. Sort order (ASC or DESC). Default is "DESC".
     * @queryParam limit integer Optional. Number of results per page.
     *
     * @response 200 {
     *   "rows": [
     *     { "id": 1, "type": "NDA" },
     *     { "id": 2, "type": "MSA" }
     *   ],
     *   "total": 2
     * }
     */
    public function contract_types_list_api(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'DESC');
        $limit = $request->get('limit', 10);
        $contract_types = $this->workspace->contract_types();
        if ($search) {
            $contract_types = $contract_types->where(function ($query) use ($search) {
                $query->where('type', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $contract_types->count();
        $contract_types = $contract_types->orderBy($sort, $order)
            ->paginate($limit)
            ->through(
                fn ($contract_type) => [
                    'id' => $contract_type->id,
                    'type' => $contract_type->type
                ]
            );

        return response()->json([
            "rows" => $contract_types->items(),
            "total" => $total,
        ]);
    }

    public function get_contract_type($id)
    {
        $ct = ContractType::findOrFail($id);
        return response()->json(['ct' => $ct]);
    }

    /**
     * @group Contracts
     * @header workspace_id: 2
     *
     * Update a contract type.
     *
     * This endpoint updates a contract type for the given workspace.
     *
     * @bodyParam id integer required The ID of the contract type to update.
     * @bodyParam type string required The contract type name.
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Contract type updated successfully.",
     *   "id": 1,
     *   "title": "NDA",
     *   "type": "contract_type",
     *   "ct": { ... }
     * }
     *
     * @response 400 {
     *   "error": true,
     *   "message": "Contract type couldn't be updated."
     * }
     */
    public function update_contract_type(Request $request)
    {
        $isApi = $request->get('isApi', false);
        $formFields = $request->validate([
            'id' => ['required'],
            'type' => 'required|unique:contract_types,type,' . $request->id,
        ]);
        $ct = ContractType::findOrFail($request->id);
        if ($ct->update($formFields)) {
            if ($isApi) {
                return response()->json([
                    'error' => false,
                    'message' => 'Contract type updated successfully.',
                    'id' => $ct->id,
                    'title' => $formFields['type'],
                    'type' => 'contract_type',
                    'ct' => $ct
                ]);
            } else {
                return response()->json([
                    'error' => false,
                    'message' => 'Contract type updated successfully.',
                    'id' => $ct->id,
                    'title' => $formFields['type'],
                    'type' => 'contract_type',
                    'ct' => $ct
                ]);
            }
        } else {
            if ($isApi) {
                return response()->json([
                    'error' => true,
                    'message' => 'Contract type couldn\'t be updated.'
                ], 400);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Contract type couldn\'t be updated.'
                ]);
            }
        }
    }

/**
     * @group Contracts
     * @header workspace_id: 2
     *
     * Delete a contract type.
     *
     * This endpoint deletes a contract type by its ID for the given workspace.
     *
     * @urlParam id integer required The ID of the contract type to delete.
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Contract type deleted successfully."
     * }
     *
     * @response 400 {
     *   "error": true,
     *   "message": "Cannot delete this contract type as it is associated with one or more contracts."
     * }
     *
     * @response 404 {
     *   "error": true,
     *   "message": "Contract type not found."
     * }
     *
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred while deleting the contract type."
     * }
     */
    public function delete_contract_type($id)
    {
        $ct = ContractType::findOrFail($id);

        // Check if there are contracts associated with this contract type
        if ($ct->contracts()->exists()) {
            return response()->json([
                'error' => true,
                'message' => 'Cannot delete this contract type as it is associated with one or more contracts.'
            ]);
        }

        // Proceed to delete the contract type
        $response = DeletionService::delete(ContractType::class, $id, 'Contract type');

        return $response;
    }


    public function delete_multiple_contract_type(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:contract_types,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $assignedContractTypes = [];
        $deletableContractTypes = [];

        foreach ($ids as $id) {
            $ct = ContractType::findOrFail($id);

            // Check if the contract type is associated with any contracts
            if ($ct->contracts()->exists()) {
                $assignedContractTypes[] = $ct->type;
            } else {
                // Add to deletable list if not assigned to any contract
                $deletableContractTypes[] = $ct;
            }
        }

        // If there are assigned contract types, return an error message
        if (count($assignedContractTypes) > 0) {
            return response()->json([
                'error' => true,
                'message' => 'The following contract type(s) are assigned to contracts and cannot be deleted: ' . implode(', ', $assignedContractTypes),
            ]);
        }

        // Proceed with deletion of deletable contract types
        foreach ($deletableContractTypes as $contractType) {
            try {
                DeletionService::delete(ContractType::class, $contractType->id, 'Contract type');
            } catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => 'An error occurred while deleting contract types: ' . $e->getMessage(),
                ], 500);
            }
        }

        // If no errors, return success message
        return response()->json([
            'error' => false,
            'message' => 'Contract type(s) deleted successfully.',
        ]);
    }

/**
 * @group Contracts
 * @header workspace_id: 2
 *
 * List all contracts for the workspace.
 *
 * This endpoint returns all contracts for the given workspace.
 *
 * @queryParam id integer Optional. The ID of the contract to fetch.
 *
 * @response 200 {
 *   "message": "Contracts fetched successfully.",
 *   "data": [
 *     {
 *       "id": 1,
 *       "title": "Sample Contract",
 *       "value": "1000.00",
 *       "start_date": "2024-01-01",
 *       "end_date": "2024-12-31",
 *       ...
 *     }
 *   ]
 * }
 */
 public function listApi(Request $request, $id = null)
{
    try {
        if ($id) {
            $contract = Contract::where('workspace_id', $this->workspace->id)
                ->where('id', $id)
                ->first();

            if (!$contract) {
                return response()->json([
                    'message' => 'Contract not found.',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'message' => 'Contract fetched successfully.',
                'data' => formatContracts($contract)
            ]);
        } else {
            $contracts = Contract::where('workspace_id', $this->workspace->id)
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'message' => 'Contracts fetched successfully.',
                'data' => formatContracts($contracts)
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to fetch contract(s).',
            'error' => $e->getMessage()
        ], 500);
    }
}



}
