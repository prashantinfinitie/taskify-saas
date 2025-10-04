<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payslip;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\DeletionService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class PayslipsController extends Controller
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
        $payslips = isAdminOrHasAllDataAccess() ? $this->workspace->payslips() : $this->user->payslips();
        $payslips = $payslips->count();
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        return view('payslips.list', ['payslips' => $payslips, 'users' => $users, 'clients' => $clients]);
    }

    public function create(Request $request)
    {
        $users = $this->workspace->users;
        $payment_methods = $this->workspace->payment_methods;
        $allowances = $this->workspace->allowances;
        $deductions = $this->workspace->deductions;
        return view('payslips.create', ['users' => $users, 'payment_methods' => $payment_methods, 'allowances' => $allowances, 'deductions' => $deductions]);
    }

    /**
     * Create Payslip
     *
     * @group Payslips
     * @header workspace_id: 2
     *
     * Create a new payslip. Supports both web and API usage.
     *
     * @bodyParam isApi boolean required Set to true for API response. Example: true
     * @bodyParam user_id int required The user ID. Example: 2
     * @bodyParam month string required The month (YYYY-MM). Example: 2024-06
     * @bodyParam basic_salary number required. Example: 50000
     * @bodyParam working_days int required. Example: 22
     * @bodyParam lop_days int required. Example: 2
     * @bodyParam paid_days int required. Example: 20
     * @bodyParam bonus number required. Example: 1000
     * @bodyParam incentives number required. Example: 500
     * @bodyParam leave_deduction number required. Example: 200
     * @bodyParam ot_hours int required. Example: 5
     * @bodyParam ot_rate number required. Example: 100
     * @bodyParam ot_payment number required. Example: 500
     * @bodyParam total_allowance number required. Example: 1500
     * @bodyParam total_deductions number required. Example: 300
     * @bodyParam total_earnings number required. Example: 52000
     * @bodyParam net_pay number required. Example: 51700
     * @bodyParam payment_method_id int optional. Example: 1
     * @bodyParam payment_date string optional. Example: 2024-06-30
     * @bodyParam status int required. Example: 1
     * @bodyParam note string optional. Example: June payslip
     * @bodyParam allowances array optional. Example: [1,2]
     * @bodyParam deductions array optional. Example: [1,2]
     *
     * @response 200 scenario="Success" {
     *   "error": false,
     *   "message": "Payslip created successfully.",
     *   "id": 1,
     *   "data": {"id":1, ...}
     * }
     * @response 400 scenario="Validation error" {"error": true, "message": "..."}
     * @response 500 scenario="Error" {"error": true, "message": "An error occurred: ..."}
     */
    public function store(Request $request)
{
    $isApi = $request->get('isApi', false);
    $adminId = getAdminIdByUserRole();

    try {
        $formFields = $request->validate([
            'user_id' => ['required'],
            'month' => ['required'],
            'basic_salary' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'working_days' => ['required'],
            'lop_days' => ['required'],
            'paid_days' => ['required'],
            'bonus' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'incentives' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'leave_deduction' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'ot_hours' => ['required'],
            'ot_rate' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'ot_payment' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'total_allowance' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'total_deductions' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'total_earnings' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'net_pay' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'payment_method_id' => ['nullable', 'required_if:status,1'],
            'payment_date' => ['nullable', 'required_if:status,1'],
            'status' => ['required'],
            'note' => ['nullable']
        ], [
            'user_id.required' => 'The user field is required.',
            'payment_date.required_if' => 'The payment date is required when status is paid.',
            'payment_method_id.required_if' => 'The payment method is required when status is paid.',
        ]);

        $status = $request->input('status');
        $payment_date = $request->input('payment_date');

        if ($status == '0') {
            $formFields['payment_date'] = null;
            $formFields['payment_method_id'] = null;
        } elseif (!empty($payment_date)) {
            $formFields['payment_date'] = format_date($payment_date, false, app('php_date_format'), 'Y-m-d');
        }

        $formFields['workspace_id'] = $this->workspace->id;
        $formFields['admin_id'] = $adminId;
        $formFields['created_by'] = isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id;

        $allowance_ids = $request->input('allowances', []);
        $deduction_ids = $request->input('deductions', []);

        $payslip = Payslip::create($formFields);

        if ($payslip) {
            $payslip->allowances()->attach($allowance_ids);
            $payslip->deductions()->attach($deduction_ids);

            Session::flash('message', 'Payslip created successfully.');

            if ($isApi) {
                return formatApiResponse(false, 'Payslip created successfully.', [
                    'id' => $payslip->id,
                    'data' => formatPayslip($payslip),
                ]);
            } else {
                return response()->json([
                    'error' => false,
                    'message' => 'Payslip created successfully.',
                    'id' => $payslip->id,
                    'data' => $payslip
                ]);
            }
        } else {
            if ($isApi) {
                return formatApiResponse(true, 'Payslip couldn\'t be created.', [], 400);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Payslip couldn\'t be created.'
                ], 400);
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
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $status = request('status');

        $user_id = request('user_id');
        $created_by = request('created_by');
        $month = request('month');

        $payslips = Payslip::select(
            'payslips.*',
            DB::raw('CONCAT(users.first_name, " ", users.last_name) AS user_name'),
            'payment_methods.title as payment_method'
        )
            ->leftJoin('users', 'payslips.user_id', '=', 'users.id')
        ->leftJoin('payment_methods', 'payslips.payment_method_id', '=', 'payment_methods.id')
        ->where('payslips.workspace_id', $this->workspace->id);

        // Apply filters
        if ($status !== null) {
            // dd($status);
            $payslips->where('payslips.status', $status);
        }
        if ($user_id) {
            $payslips->where('payslips.user_id', $user_id);
        }
        if ($created_by) {
            $payslips->where('payslips.created_by', 'u_' . $created_by);
        }
        if ($month) {
            $payslips->where('payslips.month', $month);
        }

        // Apply access control
        if (!isAdminOrHasAllDataAccess()) {
            $payslips->where(function ($query) {
                $query->where('payslips.created_by', isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id)
                    ->orWhere('payslips.user_id', $this->user->id);
            });
        }

        // Apply search
        if ($search) {
            $payslips->where(function ($query) use ($search) {
                $query->where('payslips.id', 'like', '%' . $search . '%')
                    ->orWhere('payslips.note', 'like', '%' . $search . '%')
                    ->orWhere('payment_methods.title', 'like', '%' . $search . '%');
            });
        }

        $total = $payslips->count();

        $canCreate = checkPermission('create_payslips');
        $canEdit = checkPermission('edit_payslips');
        $canDelete = checkPermission('delete_payslips');

        $payslips = $payslips->orderBy($sort, $order)
            ->paginate(request('limit', 15))
            ->through(function ($payslip) use ($canEdit, $canDelete, $canCreate) {
            $creator = User::find(substr($payslip->created_by, 2));
            $creatorName = $creator ? "{$creator->first_name} {$creator->last_name}" : '-';
                $month = Carbon::parse($payslip->month);
            $payment_date = $payslip->payment_date ? Carbon::parse($payslip->payment_date) : null;

            $actions = '';
            if ($canEdit) {
                $actions .= '<a href="' . route('payslips.edit', ['id' => $payslip->id]) . '" title="' . get_label('update', 'Update') . '"><i class="bx bx-edit mx-1"></i></a>';
            }
            if ($canDelete) {
                $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $payslip->id . '" data-type="payslips" data-table="payslips_table"><i class="bx bx-trash text-danger mx-1"></i></button>';
            }
            if ($canCreate) {
                $actions .= '<a href="javascript:void(0);" class="duplicate" data-id="' . $payslip->id . '" data-type="payslips" data-table="payslips_table" title="' . get_label('duplicate', 'Duplicate') . '"><i class="bx bx-copy text-warning mx-2"></i></a>';
            }
            $actions = $actions ?: '-';

                return [
                    'id' => $payslip->id,
                    'user' => $payslip->user_name,
                    'payment_method' => $payslip->payment_method,
                    'month' => $month->format('F, Y'),
                    'working_days' => $payslip->working_days,
                    'lop_days' => $payslip->lop_days,
                    'paid_days' => $payslip->paid_days,
                    'basic_salary' => format_currency($payslip->basic_salary),
                    'leave_deduction' => format_currency($payslip->leave_deduction),
                    'ot_hours' => $payslip->ot_hours,
                    'ot_rate' => format_currency($payslip->ot_rate),
                    'ot_payment' => format_currency($payslip->ot_payment),
                    'total_allowance' => format_currency($payslip->total_allowance),
                    'incentives' => format_currency($payslip->incentives),
                    'bonus' => format_currency($payslip->bonus),
                    'total_earnings' => format_currency($payslip->total_earnings),
                    'total_deductions' => format_currency($payslip->total_deductions),
                    'net_pay' => format_currency($payslip->net_pay),
                'payment_date' => $payment_date ? format_date($payment_date) : '-',
                    'status' => $payslip->status == 1 ? '<span class="badge bg-success">' . get_label('paid', 'Paid') . '</span>' : '<span class="badge bg-danger">' . get_label('unpaid', 'Unpaid') . '</span>',
                    'note' => $payslip->note,
                'created_by' => $creatorName,
                'created_at' => format_date($payslip->created_at, true),
                'updated_at' => format_date($payslip->updated_at, true),
                'actions' => $actions
                ];
            });

        return response()->json([
            "rows" => $payslips->items(),
            "total" => $total,
        ]);
    }


    public function edit(Request $request, $id)
    {

        $payslip = Payslip::select(
            'payslips.*',
            DB::raw('CONCAT(users.first_name, " ", users.last_name) AS user_name'),
            'payment_methods.title as payment_method'
        )->where('payslips.id', '=', $id)
            ->leftJoin('users', 'payslips.user_id', '=', 'users.id')
            ->leftJoin('payment_methods', 'payslips.payment_method_id', '=', 'payment_methods.id')->first();

        $creator = User::find(substr($payslip->created_by, 2));
        if ($creator !== null) {
            $payslip->creator = $creator->first_name . ' ' . $creator->last_name;
        } else {
            $payslip->creator = ' -';
        }
        $users = $this->workspace->users;
        $payment_methods = $this->workspace->payment_methods;
        $allowances = $this->workspace->allowances;
        $deductions = $this->workspace->deductions;
        return view('payslips.update', ['payslip' => $payslip, 'users' => $users, 'payment_methods' => $payment_methods, 'allowances' => $allowances, 'deductions' => $deductions]);
    }

    /**
     * Update Payslip
     *
     * @group Payslips
     * @header workspace_id: 2
     *
     * Update a payslip by ID. Supports both web and API usage.
     *
     * @bodyParam isApi boolean required Set to true for API response. Example: true
     * @bodyParam id int required The ID of the payslip. Example: 1
     * @bodyParam user_id int required The user ID. Example: 2
     * @bodyParam month string required The month (YYYY-MM). Example: 2024-06
     * @bodyParam basic_salary number required. Example: 50000
     * @bodyParam working_days int required. Example: 22
     * @bodyParam lop_days int required. Example: 2
     * @bodyParam paid_days int required. Example: 20
     * @bodyParam bonus number required. Example: 1000
     * @bodyParam incentives number required. Example: 500
     * @bodyParam leave_deduction number required. Example: 200
     * @bodyParam ot_hours int required. Example: 5
     * @bodyParam ot_rate number required. Example: 100
     * @bodyParam ot_payment number required. Example: 500
     * @bodyParam total_allowance number required. Example: 1500
     * @bodyParam total_deductions number required. Example: 300
     * @bodyParam total_earnings number required. Example: 52000
     * @bodyParam net_pay number required. Example: 51700
     * @bodyParam payment_method_id int optional. Example: 1
     * @bodyParam payment_date string optional. Example: 2024-06-30
     * @bodyParam status int required. Example: 1
     * @bodyParam note string optional. Example: June payslip
     * @bodyParam allowances array optional. Example: [1,2]
     * @bodyParam deductions array optional. Example: [1,2]
     *
     * @response 200 scenario="Success" {
     *   "error": false,
     *   "message": "Payslip updated successfully.",
     *   "id": 1,
     *   "data": {"id":1, ...}
     * }
     * @response 400 scenario="Validation error" {"error": true, "message": "..."}
     * @response 404 scenario="Not found" {"error": true, "message": "Payslip not found."}
     * @response 500 scenario="Error" {"error": true, "message": "An error occurred: ..."}
     */
    public function update(Request $request)
    {
        $isApi = $request->get('isApi', false);
        try {
            $formFields = $request->validate([
                'id' => ['required'],
                'user_id' => ['required'],
                'month' => ['required'],
                'basic_salary' => ['required', 'regex:/^\\d+(\\.\\d+)?$/'],
                'working_days' => ['required'],
                'lop_days' => ['required'],
                'paid_days' => ['required'],
                'bonus' => ['required', 'regex:/^\\d+(\\.\\d+)?$/'],
                'incentives' => ['required', 'regex:/^\\d+(\\.\\d+)?$/'],
                'leave_deduction' => ['required', 'regex:/^\\d+(\\.\\d+)?$/'],
                'ot_hours' => ['required'],
                'ot_rate' => ['required', 'regex:/^\\d+(\\.\\d+)?$/'],
                'ot_payment' => ['required', 'regex:/^\\d+(\\.\\d+)?$/'],
                'total_allowance' => ['required', 'regex:/^\\d+(\\.\\d+)?$/'],
                'total_deductions' => ['required', 'regex:/^\\d+(\\.\\d+)?$/'],
                'total_earnings' => ['required', 'regex:/^\\d+(\\.\\d+)?$/'],
                'net_pay' => ['required', 'regex:/^\\d+(\\.\\d+)?$/'],
                'payment_method_id' => ['nullable', 'required_if:status,1'],
                'payment_date' => ['nullable', 'required_if:status,1'],
                'status' => ['required'],
                'note' => ['nullable']
            ], [
                'user_id.required' => 'The user field is required.',
                'payment_date.required_if' => 'The payment date is required when status is paid.',
                'payment_method_id.required_if' => 'The payment method is required when status is paid.',
            ]);

            $payment_date = $request->input('payment_date');
            $status = $request->input('status');

            if ($status == '0') {
                $formFields['payment_date'] = null;
                $formFields['payment_method_id'] = null;
            } elseif (!empty($payment_date)) {
                $formFields['payment_date'] = format_date($payment_date, false, app('php_date_format'), 'Y-m-d');
            }

            $formFields['workspace_id'] = $this->workspace->id;
            $formFields['created_by'] = isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id;
            $allowance_ids = $request->input('allowances') ?? [];
            $deduction_ids = $request->input('deductions') ?? [];

            $payslip = Payslip::find($request->input('id'));
            if (!$payslip) {
                if ($isApi) {
                    return formatApiResponse(true, 'Payslip not found.', [], 404);
                } else {
                    return response()->json(['error' => true, 'message' => 'Payslip not found.'], 404);
                }
            }

            $payslip->update($formFields);
            if (!empty($allowance_ids)) {
                $payslip->allowances()->sync($allowance_ids);
            }
            if (!empty($deduction_ids)) {
                $payslip->deductions()->sync($deduction_ids);
            }

            Session::flash('message', 'Payslip updated successfully.');
            if ($isApi) {
                return formatApiResponse(false, 'Payslip updated successfully.', [
                    'id' => $payslip->id,
                    'data' => formatPayslip($payslip),
                ]);
            } else {
                return response()->json(['error' => false, 'id' => $payslip->id]);
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

    public function view(Request $request, $id)
    {
        $payslip = Payslip::select(
            'payslips.*',
            DB::raw('CONCAT(users.first_name, " ", users.last_name) AS user_name'),
            'users.email as user_email',
            'payment_methods.title as payment_method'
        )->where('payslips.id', '=', $id)
            ->leftJoin('users', 'payslips.user_id', '=', 'users.id')
            ->leftJoin('payment_methods', 'payslips.payment_method_id', '=', 'payment_methods.id')->first();


        // The ID corresponds to a user
        $creator = User::find(substr($payslip->created_by, 2)); // Remove the 'u_' prefix
        if ($creator !== null) {
            $payslip->creator = $creator->first_name . ' ' . $creator->last_name;
        } else {
            $payslip->creator = ' -';
        }
        $payslip->month = Carbon::parse($payslip->month);
        $payment_date = $payslip->payment_date !== null ? Carbon::parse($payslip->payment_date) : '';
        $payment_date = $payment_date != '' ? format_date($payment_date) : '-';
        $payslip->payment_date = $payment_date;

        $payslip->status = $payslip->status == 1 ? '<span class="badge bg-success">' . get_label('paid', 'Paid') . '</span>' : '<span class="badge bg-danger">' . get_label('unpaid', 'Unpaid') . '</span>';
        return view('payslips.view', compact('payslip'));
    }


    public function destroy($id)
    {
        $payslip = Payslip::findOrFail($id);
        $payslip->allowances()->detach();
        $payslip->deductions()->detach();
        $response = DeletionService::delete(Payslip::class, $id, 'Payslip');
        return $response;
    }

    /**
     * Delete Payslip(s)
     *
     * @group Payslips
     * @header workspace_id: 2
     *
     * Delete one or more payslips by ID. Supports both web and API usage.
     *
     * @bodyParam isApi boolean required Set to true for API response. Example: true
     * @bodyParam ids array required The IDs of the payslips to delete. Example: [1,2]
     *
     * @response 200 scenario="Success" {
     *   "error": false,
     *   "message": "Payslip(s) deleted successfully.",
     *   "id": [1,2],
     *   "titles": ["PSL - 1", "PSL - 2"]
     * }
     * @response 400 scenario="Validation error" {"error": true, "message": "..."}
     * @response 500 scenario="Error" {"error": true, "message": "An error occurred: ..."}
     */
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:payslips,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedPayslips = [];
        $deletedPayslipTitles = [];

        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $payslip = Payslip::findOrFail($id);
            if ($payslip) {
                $deletedPayslips[] = $id;
                $deletedPayslipTitles[] = get_label('payslip_id_prefix', 'PSL - ') . $id;
                $payslip->allowances()->detach();
                $payslip->deductions()->detach();
                DeletionService::delete(Payslip::class, $id, 'Payslip');
            }
        }

        return response()->json(['error' => false, 'message' => 'Payslip(s) deleted successfully.', 'id' => $deletedPayslips, 'titles' => $deletedPayslipTitles]);
    }

    public function duplicate($id)
    {
        $relatedTables = ['deductions', 'allowances']; // Include related tables as needed

        // Use the general duplicateRecord function
        $duplicate = duplicateRecord(Payslip::class, $id, $relatedTables);

        if (!$duplicate) {
            return response()->json(['error' => true, 'message' => 'Payslip duplication failed.']);
        }
        if (request()->has('reload') && request()->input('reload') === 'true') {
            Session::flash('message', 'Payslip duplicated successfully.');
        }
        return response()->json(['error' => false, 'message' => 'Payslip duplicated successfully.', 'id' => $id]);
    }

    /**
     * List Payslips (API)
     *
     * @group Payslips
     * @header workspace_id: 2
     *
     * List all payslips or fetch a single payslip by ID. Supports filters and pagination.
     *
     * @queryParam id int Optional. If provided, returns the payslip with this ID. Example: 5
     * @queryParam search string Optional. Search term for payslips. Example: PSL-2024
     * @queryParam sort string Optional. Sort field. Default: id. Example: month
     * @queryParam order string Optional. Sort order. Default: DESC. Example: ASC
     * @queryParam status int Optional. Filter by status (0=Unpaid, 1=Paid). Example: 1
     * @queryParam user_id int Optional. Filter by user. Example: 3
     * @queryParam created_by int Optional. Filter by creator. Example: 2
     * @queryParam month string Optional. Filter by month (YYYY-MM). Example: 2024-06
     * @queryParam limit int Optional. Pagination limit. Default: 15. Example: 10
     *
     * @response 200 scenario="List" {
     *   "error": false,
     *   "message": "Payslips fetched successfully.",
     *   "total": 2,
     *   "rows": [
     *     {"id":1,"user":"John Doe", ...},
     *     {"id":2,"user":"Jane Smith", ...}
     *   ]
     * }
     * @response 200 scenario="Single" {
     *   "error": false,
     *   "message": "Payslip fetched successfully.",
     *   "data": {"id":1,"user":"John Doe", ...}
     * }
     * @response 404 scenario="Not found" {"error": true, "message": "Payslip not found."}
     * @response 500 scenario="Error" {"error": true, "message": "An error occurred: ..."}
     */
    public function listApi(Request $request, $id = null)
    {
        try {
            if ($id) {
                $payslip = $this->workspace->payslips()
                    ->select(
                        'payslips.*',
                        DB::raw('CONCAT(users.first_name, " ", users.last_name) AS user_name'),
                        'payment_methods.title as payment_method'
                    )
                    ->leftJoin('users', 'payslips.user_id', '=', 'users.id')
                    ->leftJoin('payment_methods', 'payslips.payment_method_id', '=', 'payment_methods.id')
                    ->where('payslips.id', $id)
                    ->first();

                if (!$payslip) {
                    return formatApiResponse(true, 'Payslip not found.', [], 404);
                }

                return formatApiResponse(false, 'Payslip fetched successfully.', [
                    'data' => formatPayslip($payslip),
                ]);
            } else {
                $search = $request->get('search');
                $sort = $request->get('sort', 'id');
                $order = $request->get('order', 'DESC');
                $limit = $request->get('limit', 15);
                $status = $request->get('status');
                $user_id = $request->get('user_id');
                $created_by = $request->get('created_by');
                $month = $request->get('month');

                $payslips = $this->workspace->payslips()
                    ->select(
                        'payslips.*',
                        DB::raw('CONCAT(users.first_name, " ", users.last_name) AS user_name'),
                        'payment_methods.title as payment_method'
                    )
                    ->leftJoin('users', 'payslips.user_id', '=', 'users.id')
                    ->leftJoin('payment_methods', 'payslips.payment_method_id', '=', 'payment_methods.id');

                // 🔍 Filters
                if (!is_null($status)) {
                    $payslips->where('payslips.status', $status);
                }

                if ($user_id) {
                    $payslips->where('payslips.user_id', $user_id);
                }

                if ($created_by) {
                    $payslips->where('payslips.created_by', 'u_' . $created_by);
                }

                if ($month) {
                    $payslips->where('payslips.month', $month);
                }

                if (!isAdminOrHasAllDataAccess()) {
                    $payslips->where(function ($query) {
                        $query->where('payslips.created_by', isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id)
                              ->orWhere('payslips.user_id', $this->user->id);
                    });
                }

                if ($search) {
                    $payslips->where(function ($query) use ($search) {
                        $query->where('payslips.id', 'like', '%' . $search . '%')
                              ->orWhere('payslips.note', 'like', '%' . $search . '%')
                              ->orWhere('payment_methods.title', 'like', '%' . $search . '%');
                    });
                }

                $total = $payslips->count();

                $paginated = $payslips->orderBy($sort, $order)
                    ->paginate($limit);

                $rows = collect($paginated->items())->map(function($p) { return formatPayslip($p); });

                return formatApiResponse(false, 'Payslips fetched successfully.', [
                    'rows' => $rows,
                    'total' => $total,
                ]);
            }
        } catch (\Throwable $e) {
            return formatApiResponse(true, 'An error occurred: ' . $e->getMessage(), [], 500);
        }
    }




}
