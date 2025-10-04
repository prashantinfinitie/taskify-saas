<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\UserClientPreference;
use App\Models\Workspace;
use App\Services\DeletionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    protected $workspace;
    protected $user;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // fetch session and use it in entire class with constructor
            $this->workspace = Workspace::find(getWorkspaceId());
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $leads = $this->workspace->leads();
        // dd($leads->count());
        return view('leads.index', compact('leads'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $lead_sources = LeadSource::where('workspace_id', $this->workspace->id)->get();
        $lead_stages = LeadStage::where('workspace_id', $this->workspace->id)->get();
        $users = $this->workspace->users;
        return view('leads.create', compact('users', 'lead_sources', 'lead_stages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $formFields = $request->validate([
                'first_name'        => 'required|string|max:255',
                'last_name'         => 'required|string|max:255',
                'email'             => 'required|email|unique:leads,email',
                'phone'             => 'required|string|max:20',
                'country_code'      => 'required|string|max:5',
                'country_iso_code'  => 'required|string|size:2',
                'source_id'         => 'required|exists:lead_sources,id',
                'stage_id'          => 'required|exists:lead_stages,id',
                'assigned_to'       => 'required|exists:users,id',
                'job_title'         => 'nullable|string|max:255',
                'industry'          => 'nullable|string|max:255',
                'company'           => 'required|string|max:255',
                'website'           => 'nullable|url|max:255',
                'linkedin'          => 'nullable|url|max:255',
                'instagram'         => 'nullable|url|max:255',
                'facebook'          => 'nullable|url|max:255',
                'pinterest'         => 'nullable|url|max:255',
                'city'              => 'nullable|string|max:255',
                'state'             => 'nullable|string|max:255',
                'zip'               => 'nullable|string|max:20',
                'country'           => 'nullable|string|max:255',
            ]);

            $formFields['created_by'] = $this->user->id;
            $formFields['workspace_id'] = $this->workspace->id;
            $formFields['admin_id'] = getAdminIdByUserRole();

            $lead = Lead::create($formFields);

            return response()->json([
                'error' => false,
                'message' => 'Lead Created Successfully.',
                'id' => $lead->id,
                'type' => 'lead'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while updating the announcement.',
                'exception' => $e->getMessage(), // Optional: Remove in production
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $lead = Lead::where('workspace_id', $this->workspace->id)
                ->where('id', $id)
                ->firstOrFail();

            $formFields = $request->validate([
                'first_name'        => 'required|string|max:255',
                'last_name'         => 'required|string|max:255',
                'email'             => 'required|email|unique:leads,email,' . $lead->id,
                'phone'             => 'required|string|max:20',
                'country_code'      => 'required|string|max:5',
                'country_iso_code'  => 'required|string|size:2',
                'source_id'         => 'required|exists:lead_sources,id',
                'stage_id'          => 'required|exists:lead_stages,id',
                'assigned_to'       => 'required|exists:users,id',
                'job_title'         => 'nullable|string|max:255',
                'industry'          => 'nullable|string|max:255',
                'company'           => 'required|string|max:255',
                'website'           => 'nullable|url|max:255',
                'linkedin'          => 'nullable|url|max:255',
                'instagram'         => 'nullable|url|max:255',
                'facebook'          => 'nullable|url|max:255',
                'pinterest'         => 'nullable|url|max:255',
                'city'              => 'nullable|string|max:255',
                'state'             => 'nullable|string|max:255',
                'zip'               => 'nullable|string|max:20',
                'country'           => 'nullable|string|max:255',
            ]);

            $lead->update($formFields);

            return response()->json([
                'error' => false,
                'message' => 'Lead Updated Successfully.',
                'id' => $lead->id,
                'type' => 'lead'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Lead not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while updating the lead.',
                'exception' => $e->getMessage(), // Optional: Remove in production
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $lead = Lead::findOrFail($id);
        return view('leads.show', compact('lead'));
    }

    public function edit(string $id)
    {

        $lead = Lead::findOrFail($id);
        // dd($lead);
        return view('leads.edit', compact('lead'));
    }


    private function getActions($lead)
    {
        $actions = '';
        $canEdit = checkPermission('edit_leads');  // Replace with your actual condition
        $canDelete = checkPermission('delete_leads'); // Replace with your actual condition
        $isConverted = $lead->is_converted == 1 ? true : false;


        $actions = '<div class="d-flex align-items-center">';

        $actions .= '<a href="' . route('leads.show', ['id' => $lead->id]) . '"
                class="text-info btn btn-sm p-1 me-1"
                data-id="' . $lead->id . '"
                title="' . get_label('view', 'View') . '">
                <i class="bx bx-show"></i>
            </a>';

        if ($canEdit) {
            $actions .= '<a href="' . route('leads.edit', ['id' => $lead->id]) . '"
                    class="text-primary btn btn-sm  p-1 me-1"
                    data-id="' . $lead->id . '"
                    title="' . get_label('update', 'Update') . '">
                    <i class="bx bx-edit"></i>
                </a>';
        }

        if ($canDelete) {
            $actions .= '<button title="' . get_label('delete', 'Delete') . '"
                    type="button"
                    class="btn btn-sm p-1 delete text-danger"
                    data-id="' . $lead->id . '"
                    data-type="leads"
                    data-table="table">
                    <i class="bx bx-trash"></i>
                </button>';
        }
        if (!$isConverted) {
            $actions .= '<button class="btn btn-sm text-primary convert-to-client" title="' . get_label('convert_to_client', 'Convert To Client') . '"
                             data-id="' . $lead->id . '"><i
                            class="bx bxs-analyse me-1 p-1"></i>
                        </button>';
        }

        $actions .= '</div>';
        return $actions;
    }

    public function list()
    {
        $search = request('search');
        $sortOptions = [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'recently-updated' => ['updated_at', 'desc'],
            'earliest-updated' => ['updated_at', 'asc'],
        ];
        [$sort, $order] = $sortOptions[request()->input('sort')] ?? ['id', 'desc'];
        $source_ids  = request('source_ids', []);
        $stage_ids   = request('stage_ids', []);
        $start_date = request('start_date');
        $end_date = request('end_date');

        $limit = request('limit', 10);

        $leads = isAdminOrHasAllDataAccess()
            ? $this->workspace->leads()
            : $this->user->leads();

        $leads = $leads->with(['source', 'stage', 'assigned_user']); // eager load if needed
        $leads = $leads->orderBy($sort, $order);

        if ($search) {
            $leads->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('company', 'like', "%$search%")
                    ->orWhere('job_title', 'like', "%$search%")
                    ->orWhere('id', 'like', "%$search%");
            });
        }

        if (!empty($source_ids)) {
            $leads->whereIn('source_id', $source_ids);
        }
        if (!empty($stage_ids)) {
            $leads->whereIn('stage_id', $stage_ids);
        }
        if ($start_date && $end_date) {
            $leads->whereBetween('created_at', [$start_date, $end_date]);
        }

        $total = $leads->count();

        $leads = $leads->paginate($limit)->through(function ($lead) {

            $stage = '<span class="badge bg-' . $lead->stage->color . '">' . $lead->stage->name . '</span>';

            return [
                'id' => $lead->id,
                'name' => formatLeadUserHtml($lead),
                'email' => $lead->email,
                'phone' => $lead->phone,
                'company' => $lead->company,
                'website' => $lead->website,
                'job_title' => $lead->job_title,
                'stage' => $stage,
                'source' => optional($lead->source)->name,
                'assigned_to' => formatUserHtml($lead->assigned_user),
                'created_at' => format_date($lead->created_at, true),
                'updated_at' => format_date($lead->updated_at, true),
                'actions' => $this->getActions($lead),
            ];
        });

        return response()->json([
            'rows' => $leads->items(),
            'total' => $total,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = DeletionService::delete(Lead::class, $id, 'leads');
        return $response;
    }

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:leads,id' // Ensure each ID in 'ids' is an integer and exists in the 'projects' table
        ]);
        $ids = $validatedData['ids'];
        $deletedLeads = [];
        $deletedLeadsTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $lead = Lead::find($id);
            if ($lead) {
                $deletedLeadTitles[] = ucwords($lead->first_name . ' ' . $lead->last_name);

                DeletionService::delete(Lead::class, $id, 'Lead');
                $deletedLeads[] = $id;
            }
        }
        return response()->json(['error' => false, 'message' => 'Lead(s) deleted successfully.', 'id' => $deletedLeads, 'titles' => $deletedLeadsTitles]);
    }

    public function stageChange(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:leads,id',
                'stage_id' => 'required|exists:lead_stages,id',
            ]);
            $lead = Lead::findOrFail($request->id);
            $lead->stage_id = $request->stage_id;

            $lead->save();

            return response()->json([
                'error' => false,
                'message' => 'Lead Stage Updated Successfully.',
                'id' => $lead->id,
                'type' => 'lead',
                'activity_message' => 'Lead Stage Changed to ' . $lead->stage->name,
            ]);
        } catch (\Exception $e) {
        }
    }

    public function saveViewPreference(Request $request)
    {
        $view = $request->input('view');
        $prefix = isClient() ? 'c_' : 'u_';
        if (
            UserClientPreference::updateOrCreate(
                ['user_id' => $prefix . $this->user->id, 'table_name' => 'leads'],
                ['default_view' => $view]
            )
        ) {
            return response()->json(['error' => false, 'message' => 'Default View Set Successfully.']);
        } else {
            return response()->json(['error' => true, 'message' => 'Something Went Wrong.']);
        }
    }

    public function kanban(Request $request)
    {
        $sources = (array) $request->input('sources', []);
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $sortOptions = [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'recently-updated' => ['updated_at', 'desc'],
            'earliest-updated' => ['updated_at', 'asc'],
        ];
        [$sort, $order] = $sortOptions[$request->input('sort')] ?? ['id', 'desc'];

        $leadsQuery = isAdminOrHasAllDataAccess()
            ? $this->workspace->leads()
            : $this->user->leads();
        $leadsQuery = $leadsQuery
            ->with(['source', 'stage', 'assigned_user'])
            ->orderBy($sort, $order);

        if (!empty($sources)) {
            $leadsQuery->whereIn('source_id', $sources);
        }
        if ($start_date && $end_date) {
            $leadsQuery->whereBetween('updated_at', [$start_date, $end_date]);
        }

        $leads = $leadsQuery->get();

        $lead_stages = $this->workspace->lead_stages()->get();
        // dd($lead_stages->get());
        return view('leads.kanban', compact('leads', 'lead_stages'));
    }


    public function convertToClient(Request $request, Lead $lead)
    {
        if ($lead->is_converted == 1) {
            return response()->json([
                'success' => true,
                'message' => 'Lead is already converted to the client.',
                'data' => [
                    'id' => $lead->id
                ]
            ]);
        }

        // Prepare new request data
        $clientData = [
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'company' => $lead->company,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'country_code' => $lead->country_code,
            'address' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'country' => $lead->country,
            'zip' => $lead->zip,
            'internal_purpose' => 'on', // so password is optional
        ];

        // Use ClientController directly to reuse store logic
        $clientRequest = new Request($clientData);
        $clientController = new \App\Http\Controllers\ClientController();
        $response = $clientController->store($clientRequest);

        // Decode the response body (assuming JSON response)
        $responseBody = json_decode($response->getContent(), true);

        if (isset($responseBody['error']) && $responseBody['error'] === true) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $responseBody['errors'] ?? []
            ], 422);
        }

        if ($response->getStatusCode() != 200) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while converting the lead.'
            ], $response->getStatusCode());
        }

        // Update the lead as converted
        $lead->update(['is_converted' => 1, 'converted_at' => now()]);

        // Return the original success response from clientController
        return $response;
    }
}
