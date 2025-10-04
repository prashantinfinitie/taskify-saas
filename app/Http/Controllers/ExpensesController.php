<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Workspace;
use App\Models\ExpenseType;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
class ExpensesController extends Controller
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
        $expenses = $this->workspace->expenses();
        if (!isAdminOrHasAllDataAccess()) {
            $expenses = $expenses->where(function ($query) {
                $query->where('expenses.created_by', isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id)
                    ->orWhere('expenses.user_id', $this->user->id);
            });
        }
        $expenses = $expenses->count();
        $expense_types = $this->workspace->expense_types;
        $users = $this->workspace->users;
        return view('expenses.list', ['expenses' => $expenses, 'expense_types' => $expense_types, 'users' => $users]);
    }
    public function expense_types(Request $request)
    {
        $expense_types = $this->workspace->expense_types();
        $expense_types = $expense_types->count();
        return view('expenses.expense_types', ['expense_types' => $expense_types]);
    }
    /**
     * Store a new expense.
     *
     * Create a new expense record in the workspace.
     *
     * @group Expenses & Expense Types
     * @header workspace_id: 2
     * @bodyParam title string required The title of the expense. Example: "Travel Reimbursement"
     * @bodyParam expense_type_id integer required The ID of the expense type. Example: 1
     * @bodyParam user_id integer required The ID of the user. Example: 3
     * @bodyParam amount number required The amount of the expense. Example: 150.75
     * @bodyParam expense_date date required The date of the expense. Example: "2024-06-30"
     * @bodyParam note string Optional note for the expense. Example: "Taxi fare for client meeting."
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Expense created successfully.",
     *   "id": 12,
     *   "expenses": {"id": 12, "title": "Travel Reimbursement", ...}
     * }
     * @response 400 {
     *   "error": true,
     *   "message": "Expense couldn't be created."
     * }
     * @response 422 {
     *   "error": true,
     *   "message": "The given data was invalid.",
     *   "errors": {"title": ["The title field is required."]}
     * }
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred: ..."
     * }
     */
    public function store(Request $request)
{
    $isApi = $request->get('isApi', false);

    try {
        // Validate the request data
        $formFields = $request->validate([
            'title' => 'required|unique:expenses,title',
            'expense_type_id' => 'required',
            'user_id' => 'required',
            'amount' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'expense_date' => 'required',
            'note' => 'nullable'
        ], [
            'user_id.required' => 'The user field is required.',
            'expense_type_id.required' => 'The expense type field is required.'
        ]);

        // Format and add additional fields
        $expense_date = $request->input('expense_date');
        $formFields['expense_date'] = format_date($expense_date, false, app('php_date_format'), 'Y-m-d');
        $formFields['workspace_id'] = $this->workspace->id;
        $formFields['created_by'] = isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id;

        // Create the expense
        if ($exp = Expense::create($formFields)) {
            if ($isApi) {
                return formatApiResponse(false, 'Expense created successfully.', [
                    'id' => $exp->id,
                    'data' => formatExpense($exp),
                ]);
            } else {
                return response()->json([
                    'error' => false,
                    'message' => 'Expense created successfully.',
                    'id' => $exp->id,
                    'expenses' => formatExpense($exp),
                ]);
            }
        } else {
            if ($isApi) {
                return formatApiResponse(true, 'Expense couldn\'t be created.', [], 400);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Expense couldn\'t be created.'
                ]);
            }
        }
    } catch (\Throwable $e) {
        if ($isApi) {
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
     * Delete an expense.
     *
     * Delete an expense by ID.
     *
     * @group Expenses & Expense Types
     * @header workspace_id: 2
     * @urlParam id integer required The ID of the expense. Example: 12
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Expense deleted successfully.",
     *   "id": 12,
     *   "title": "Travel Reimbursement"
     * }
     * @response 404 {
     *   "error": true,
     *   "message": "Expense not found."
     * }
     */
    public function destroy($id)
    {
        $exp = Expense::findOrFail($id);
        DeletionService::delete(Expense::class, $id, 'Expense');
        return response()->json(['error' => false, 'message' => 'Expense deleted successfully.', 'id' => $id, 'title' => $exp->title]);
    }
    /**
     * Store a new expense type.
     *
     * Create a new expense type in the workspace.
     *
     * @group Expenses & Expense Types
     * @header workspace_id: 2
     * @bodyParam title string required The title of the expense type. Example: "Travel"
     * @bodyParam description string Optional description. Example: "Expenses related to travel."
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Expense type created successfully.",
     *   "id": 1,
     *   "expenses": {"id": 1, "title": "Travel", ...}
     * }
     * @response 400 {
     *   "error": true,
     *   "message": "Expense type couldn't be created."
     * }
     * @response 422 {
     *   "error": true,
     *   "message": "The given data was invalid.",
     *   "errors": {"title": ["The title field is required."]}
     * }
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred: ..."
     * }
     */
    public function store_expense_type(Request $request)
    {
        $isApi = $request->get('isApi', false);

    try {
        // Validate the request data
        $formFields = $request->validate([
            'title' => 'required|unique:expense_types,title', // Validate the type
            'description' => 'nullable'
        ]);
        $formFields['workspace_id'] = $this->workspace->id;
        if ($et = ExpenseType::create($formFields)) {
            Session::flash('message', 'Expense type created successfully.');
            if ($isApi) {
                return formatApiResponse(false, 'Expensaes type created successfully.', [
                    'id' => $et->id,
                    'data' => formatExpenseType($et),
                ]);
            } else {
                return response()->json([
                    'error' => false,
                    'message' => 'Expense type created successfully.',
                    'id' => $et->id,
                    'expenses' => formatExpenseType($et),
                ]);
            }
        } else {
            if ($isApi) {
                return formatApiResponse(true, 'Expense type couldn\'t be created.', [], 400);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Expense type couldn\'t be created.'
                ]);
            }
        }
    } catch (\Throwable $e) {
        if ($isApi) {
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
        $type_id = (request('type_id')) ? request('type_id') : "";
        $user_id = (request('user_id')) ? request('user_id') : "";
        $exp_date_from = (request('date_from')) ? request('date_from') : "";
        $exp_date_to = (request('date_to')) ? request('date_to') : "";
        $where = ['expenses.workspace_id' => $this->workspace->id];
        $expenses = Expense::select(
            'expenses.*',
            DB::raw('CONCAT(users.first_name, " ", users.last_name) AS user_name'),
            'expense_types.title as expense_type'
        )
            ->leftJoin('users', 'expenses.user_id', '=', 'users.id')
        ->leftJoin('expense_types', 'expenses.expense_type_id', '=', 'expense_types.id');
        if (!isAdminOrHasAllDataAccess()) {
            $expenses = $expenses->where(function ($query) {
                $query->where('expenses.created_by', isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id)
                    ->orWhere('expenses.user_id', $this->user->id);
            });
        }
        if ($type_id) {
            $where['expense_type_id'] = $type_id;
        }
        if ($user_id) {
            $where['user_id'] = $user_id;
        }
        if ($exp_date_from && $exp_date_to) {
            $expenses = $expenses->whereBetween('expenses.expense_date', [$exp_date_from, $exp_date_to]);
        }
        if ($search) {
            $expenses = $expenses->where(function ($query) use ($search) {
                $query->where('expenses.title', 'like', '%' . $search . '%')
                    ->orWhere('amount', 'like', '%' . $search . '%')
                    ->orWhere('expenses.note', 'like', '%' . $search . '%')
                    ->orWhere('expenses.id', 'like', '%' . $search . '%');
            });
        }
        $expenses->where($where);
        $total = $expenses->count();
        $canCreate = checkPermission('create_expenses');
        $canEdit = checkPermission('edit_expenses');
        $canDelete = checkPermission('delete_expenses');
        $expenses = $expenses->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($expense) use ($canEdit, $canDelete, $canCreate) {
                if (strpos($expense->created_by, 'u_') === 0) {
                    // The ID corresponds to a user
                    $creator = User::find(substr($expense->created_by, 2)); // Remove the 'u_' prefix
                } elseif (strpos($expense->created_by, 'c_') === 0) {
                // The ID corresponds to a client
                $creator = Client::find(substr($expense->created_by, 2)); // Remove the 'c_' prefix
                }
                if ($creator !== null) {
                    $creator = $creator->first_name . ' ' . $creator->last_name;
                } else {
                    $creator = '-';
            }
            $actions = '';
            if ($canEdit) {
                $actions .= '<a href="javascript:void(0);" class="edit-expense" data-bs-toggle="modal" data-id="' . $expense->id . '" title="' . get_label('update', 'Update') . '" class="card-link"><i class="bx bx-edit mx-1"></i></a>';
            }
            if ($canDelete) {
                $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $expense->id . '" data-type="expenses">' .
                '<i class="bx bx-trash text-danger mx-1"></i>' .
                '</button>';
            }
            if ($canCreate) {
                $actions .= '<a href="javascript:void(0);" class="duplicate" data-id="' . $expense->id . '" data-title="' . $expense->title . '" data-type="expenses" title="' . get_label('duplicate', 'Duplicate') . '">' .
                '<i class="bx bx-copy text-warning mx-2"></i>' .
                '</a>';
            }
            $actions = $actions ?: '-';
                return [
                    'id' => $expense->id,
                    'user_id' => $expense->user_id,
                    'user' => $expense->user_name,
                    'title' => $expense->title,
                    'expense_type_id' => $expense->expense_type_id,
                    'expense_type' => $expense->expense_type,
                    'amount' => format_currency($expense->amount),
                    'expense_date' => format_date($expense->expense_date),
                    'note' => $expense->note,
                    'created_by' => $creator,
                'created_at' => format_date($expense->created_at, true),
                'updated_at' => format_date($expense->updated_at, true),
                'actions' => $actions
                ];
            });
        return response()->json([
            "rows" => $expenses->items(),
            "total" => $total,
        ]);
    }
    public function expense_types_list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $expense_types = $this->workspace->expense_types();
        if ($search) {
            $expense_types = $expense_types->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $expense_types->count();
        $expense_types = $expense_types->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(
                fn ($expense_types) => [
                    'id' => $expense_types->id,
                    'title' => $expense_types->title,
                    'description' => $expense_types->description,
                'created_at' => format_date($expense_types->created_at),
                'updated_at' => format_date($expense_types->updated_at),
                ]
            );
        return response()->json([
            "rows" => $expense_types->items(),
            "total" => $total,
        ]);
    }
    public function get($id)
    {
        $exp = Expense::findOrFail($id);
        return response()->json(['exp' => $exp]);
    }
    public function get_expense_type($id)
    {
        $et = ExpenseType::findOrFail($id);
        return response()->json(['et' => $et]);
    }
    /**
     * Update an expense.
     *
     * Update an existing expense record by ID.
     *
     * @group Expenses & Expense Types
     * @header workspace_id: 2
     * @bodyParam id integer required The ID of the expense. Example: 12
     * @bodyParam title string required The title of the expense. Example: "Updated Title"
     * @bodyParam expense_type_id integer required The ID of the expense type. Example: 1
     * @bodyParam user_id integer required The ID of the user. Example: 3
     * @bodyParam amount number required The amount of the expense. Example: 200.00
     * @bodyParam expense_date date required The date of the expense. Example: "2024-07-01"
     * @bodyParam note string Optional note for the expense. Example: "Updated note."
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Expense updated successfully.",
     *   "id": 12
     * }
     * @response 400 {
     *   "error": true,
     *   "message": "Expense couldn't be updated."
     * }
     * @response 404 {
     *   "error": true,
     *   "message": "Expense not found."
     * }
     * @response 422 {
     *   "error": true,
     *   "message": "The given data was invalid.",
     *   "errors": {"title": ["The title field is required."]}
     * }
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred: ..."
     * }
     */
    public function update(Request $request)
{
    $isApi = $request->get('isApi', false);

    try {
        $formFields = $request->validate([
            'id' => 'required',
            'title' => 'required|unique:expenses,title,' . $request->id,
            'expense_type_id' => 'required',
            'user_id' => 'required',
            'amount' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'expense_date' => 'required',
            'note' => 'nullable'
        ], [
            'user_id.required' => 'The user field is required.',
            'expense_type_id.required' => 'The expense type field is required.'
        ]);

        $expense_date = $request->input('expense_date');
        $formFields['expense_date'] = format_date($expense_date, false, app('php_date_format'), 'Y-m-d');

        $exp = Expense::findOrFail($request->id);

        if ($exp->update($formFields)) {
            if ($isApi) {
                return formatApiResponse(false, 'Expense updated successfully.', [
                    'id' => $exp->id,
                    'data' => formatExpense($exp->fresh()),
                ]);
            } else {
                return response()->json([
                    'error' => false,
                    'message' => 'Expense updated successfully.',
                    'id' => $exp->id
                ]);
            }
        } else {
            if ($isApi) {
                return formatApiResponse(true, 'Expense couldn\'t be updated.', [], 400);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Expense couldn\'t be updated.'
                ]);
            }
        }
    } catch (\Throwable $e) {
        if ($isApi) {
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
     * Update an expense type.
     *
     * Update an existing expense type by ID.
     *
     * @group Expenses & Expense Types
     * @header workspace_id: 2
     * @bodyParam id integer required The ID of the expense type. Example: 1
     * @bodyParam title string required The title of the expense type. Example: "Updated Type"
     * @bodyParam description string Optional description. Example: "Updated description."
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Expense Type updated successfully.",
     *   "id": 1
     * }
     * @response 400 {
     *   "error": true,
     *   "message": "Expense Type couldn't be updated."
     * }
     * @response 404 {
     *   "error": true,
     *   "message": "Expense type not found."
     * }
     * @response 422 {
     *   "error": true,
     *   "message": "The given data was invalid.",
     *   "errors": {"title": ["The title field is required."]}
     * }
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred: ..."
     * }
     */
    public function update_expense_type(Request $request)
    {
         $isApi = $request->get('isApi', false);

    try {
        $formFields = $request->validate([
            'id' => ['required'],
            'title' => 'required|unique:expense_types,title,' . $request->id,
            'description' => 'nullable',
        ]);
        $et = ExpenseType::findOrFail($request->id);
        if ($et->update($formFields)) {
             if ($isApi) {
                return formatApiResponse(false, 'Expense Type updated successfully.', [
                    'id' => $et->id,
                    'data' => formatExpenseType($et->fresh()),
                ]);
            } else {
                return response()->json([
                    'error' => false,
                    'message' => 'Expense Type updated successfully.',
                    'id' => $et->id
                ]);
            }
        } else {
            if ($isApi) {
                return formatApiResponse(true, 'Expense Type couldn\'t be updated.', [], 400);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'ExpenseType couldn\'t be updated.'
                ]);
            }
        }
    } catch (\Throwable $e) {
        if ($isApi) {
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
     * List expenses (API).
     *
     * Get a paginated list of expenses or a single expense by ID.
     *
     * @group Expenses & Expense Types
     * @header workspace_id: 2
     * @queryParam id integer The ID of the expense to fetch. Example: 12
     * @queryParam search string Search term for filtering expenses. Example: "Travel"
     * @queryParam sort string Field to sort by. Example: "id"
     * @queryParam order string Sort order (ASC or DESC). Example: "DESC"
     * @queryParam type_id integer Filter by expense type ID. Example: 1
     * @queryParam user_id integer Filter by user ID. Example: 3
     * @queryParam date_from date Filter expenses from this date. Example: "2024-06-01"
     * @queryParam date_to date Filter expenses up to this date. Example: "2024-06-30"
     * @queryParam limit integer Number of records per page. Example: 10
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Expenses retrieved.",
     *   "data": {
     *     "rows": [{"id": 12, "title": "Travel Reimbursement", ...}],
     *     "total": 1
     *   }
     * }
     * @response 404 {
     *   "error": true,
     *   "message": "Expense not found."
     * }
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred: ..."
     * }
     */
    public function apiList(Request $request)
{
    $isApi = $request->get('isApi', false);

    try {
        $id = $request->get('id');
        $search = $request->get('search');
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'DESC');
        $type_id = $request->get('type_id');
        $user_id = $request->get('user_id');
        $exp_date_from = $request->get('date_from');
        $exp_date_to = $request->get('date_to');
        $per_page = $request->get('per_page', 10);


        if ($id) {
            $expense = Expense::with(['user', 'expenseType'])
                ->where('workspace_id', $this->workspace->id)
                ->find($id);

            if (!$expense) {
                return $isApi
                    ? formatApiResponse(true, 'Expense not found.', [], 404)
                    : response()->json(['error' => true, 'message' => 'Expense not found.'], 404);
            }

            return $isApi
                ? formatApiResponse(false, 'Expense retrieved successfully.', ['data' => formatExpense($expense)])
                : response()->json(['error' => false, 'message' => 'Expense retrieved.', 'data' => formatExpense($expense)]);
        }


        $expenses = Expense::with(['user', 'expenseType'])
            ->where('workspace_id', $this->workspace->id);

        if (!isAdminOrHasAllDataAccess()) {
            $expenses = $expenses->where(function ($query) {
                $query->where('created_by', isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id)
                      ->orWhere('user_id', $this->user->id);
            });
        }

        if ($type_id) {
            $expenses->where('expense_type_id', $type_id);
        }

        if ($user_id) {
            $expenses->where('user_id', $user_id);
        }

        if ($exp_date_from && $exp_date_to) {
            $expenses->whereBetween('expense_date', [$exp_date_from, $exp_date_to]);
        }

        if ($search) {
            $expenses->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('amount', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $total = $expenses->count();

        $results = $expenses
            ->orderBy($sort, $order)
            ->paginate($per_page)
            ->through(function ($expense) {
                return formatExpense($expense);
            });

        $response = [
            'data' => $results->items(),
            'total' => $total,
        ];

        return $isApi
            ? formatApiResponse(false, 'Expenses retrieved successfully.', $response)
            : response()->json(['error' => false, 'message' => 'Expenses retrieved.', 'data' => $response]);

    } catch (\Throwable $e) {
        return $isApi
            ? formatApiResponse(true, 'An error occurred: ' . $e->getMessage(), [], 500)
            : response()->json(['error' => true, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}

 /**
     * List or fetch expense types (API).
     *
     * Get a paginated list of expense types or a single expense type by ID.
     *
     * @group Expenses & Expense Types
     * @header workspace_id: 2
     * @urlParam id integer The ID of the expense type to fetch. Example: 1
     * @queryParam search string Search term for filtering expense types. Example: "Travel"
     * @queryParam sort string Field to sort by. Example: "id"
     * @queryParam order string Sort order (ASC or DESC). Example: "DESC"
     * @queryParam limit integer Number of records per page. Example: 10
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Expense types fetched successfully.",
     *   "data": {
     *     "rows": [{"id": 1, "title": "Travel", ...}],
     *     "total": 1
     *   }
     * }
     * @response 404 {
     *   "error": true,
     *   "message": "Expense type not found."
     * }
     * @response 500 {
     *   "error": true,
     *   "message": "An error occurred: ..."
     * }
     */
    public function Api_expense_types_list(Request $request, $id = null)
{
    $isApi = $request->get('isApi', false);

    try {
        // If $id is provided, fetch by id
        if ($id) {
            $expenseType = $this->workspace->expense_types()->where('id', $id)->first();
            if (!$expenseType) {
                return $isApi
                    ? formatApiResponse(true, 'Expense type not found.', [], 404)
                    : response()->json([
                        'error' => true,
                        'message' => 'Expense type not found.'
                    ], 404);
            }
            return $isApi
                ? formatApiResponse(false, 'Expense type fetched successfully.', [
                    'id' => $expenseType->id,
                    'data' => formatExpenseType($expenseType)
                ])
                : response()->json([
                    'error' => false,
                    'message' => 'Expense type fetched successfully.',
                    'id' => $expenseType->id,
                    'data' => formatExpenseType($expenseType)
                ]);
        }

        // List all expense types with filters
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $limit = $request->input('limit', 10); // Default pagination limit

        $query = $this->workspace->expense_types();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$search%");
            });
        }

        $total = $query->count();

        $expenseTypes = $query->orderBy($sort, $order)
            ->paginate($limit);

        $rows = collect($expenseTypes->items())->map(function($item) {
            return formatExpenseType($item);
        });

        $responseData = [
            'data' => $rows,
            'total' => $total
        ];

        return $isApi
            ? formatApiResponse(false, 'Expense types fetched successfully.', $responseData)
            : response()->json([
                'error' => false,
                'message' => 'Expense types fetched successfully.',
                'data' => $responseData
            ]);
    } catch (\Throwable $e) {
        return $isApi
            ? formatApiResponse(true, 'An error occurred: ' . $e->getMessage(), [], 500)
            : response()->json([
                'error' => true,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
    }
}

/**
     * Delete an expense type.
     *
     * Delete an expense type by ID. Will fail if the type is assigned to any expenses.
     *
     * @group Expenses & Expense Types
     * @header workspace_id: 2
     * @urlParam id integer required The ID of the expense type. Example: 1
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Expense type deleted successfully.",
     *   "id": 1,
     *   "title": "Travel",
     *   "type": "expense_type"
     * }
     * @response 400 {
     *   "error": true,
     *   "message": "Cannot delete this expense type as it is associated with one or more expenses."
     * }
     * @response 404 {
     *   "error": true,
     *   "message": "Expense type not found."
     * }
     */
    public function delete_expense_type($id)
    {
        $et = ExpenseType::findOrFail($id);
        if ($et->expenses()->exists()) {
            return response()->json([
                'error' => true,
                'message' => 'Cannot delete this expense type as it is associated with one or more expenses.'
            ]);
        }
        DeletionService::delete(ExpenseType::class, $id, 'Expense type');
        return response()->json(['error' => false, 'message' => 'Expense type deleted successfully.', 'id' => $id, 'title' => $et->title, 'type' => 'expense_type']);
    }
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:expenses,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $exp = Expense::findOrFail($id);
            $deletedIds[] = $id;
            $deletedTitles[] = $exp->title;
            DeletionService::delete(Expense::class, $id, 'Expense');
        }
        return response()->json(['error' => false, 'message' => 'Expense(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles, 'type' => 'expense']);
    }
    public function delete_multiple_expense_type(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:expense_types,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $assignedExpenseTypes = [];
        $deletableExpenseTypes = [];
        foreach ($ids as $id) {
            $et = ExpenseType::findOrFail($id);
            // Check if the expense type is associated with any expenses
            if ($et->expenses()->exists()) {
                $assignedExpenseTypes[] = $et->title;
            } else {
                // Add to deletable list if not assigned to any expense
                $deletableExpenseTypes[] = $et;
            }
        }
        // If there are assigned expense types, return an error message
        if (count($assignedExpenseTypes) > 0) {
            return response()->json([
                'error' => true,
                'message' => 'The following expense type(s) are assigned to expenses and cannot be deleted: ' . implode(', ', $assignedExpenseTypes),
            ]);
        }
        // Proceed with deletion of deletable expense types
        foreach ($deletableExpenseTypes as $expenseType) {
            try {
                DeletionService::delete(ExpenseType::class, $expenseType->id, 'Expense type');
            } catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => 'An error occurred while deleting expense types: ' . $e->getMessage(),
                ], 500);
            }
        }
        // If no errors, return success message
        return response()->json([
            'error' => false,
            'message' => 'Expense type(s) deleted successfully.',
        ]);
    }
    public function duplicate($id)
    {
        // Use the general duplicateRecord function
        $title = (request()->has('title') && !empty(trim(request()->title))) ? request()->title : '';
        $duplicated = duplicateRecord(Expense::class, $id, [], $title);
        if (!$duplicated) {
            return response()->json(['error' => true, 'message' => 'Expense duplication failed.']);
        }
        if (request()->has('reload') && request()->input('reload') === 'true') {
            Session::flash('message', 'Expense duplicated successfully.');
        }
        return response()->json(['error' => false, 'message' => 'Expense duplicated successfully.', 'id' => $id]);
    }

}
