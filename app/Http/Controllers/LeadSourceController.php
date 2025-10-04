<?php

namespace App\Http\Controllers;

use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\Workspace;
use App\Services\DeletionService;
use Illuminate\Http\Request;

class LeadSourceController extends Controller
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
        $lead_sources = $this->workspace->lead_sources();
        // dd($lead_sources);
        return view('lead_sources.index', compact('lead_sources'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string'
            ]);
            $lead_source = new LeadSource();
            $lead_source->workspace_id = getWorkspaceId();
            $lead_source->admin_id = getAdminIdByUserRole();
            $lead_source->name = $request->name;
            $lead_source->save();

            return response()->json(['error' => false, 'message' => 'Lead Source Created Successfully', 'id' => $lead_source->id, 'type' => 'lead_source']);
        } catch (\Exception $e) {

            return response()->json(['error' => true, 'message' => 'Lead Source Couldn\'t Created']);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


    public function list()
    {

        $search = request('search');
        $sort = request('sort', "id");
        $order = request('order', "DESC");
        $limit = request('limit', 10);

        $lead_sources = $this->workspace->lead_sources();
        $lead_sources  = $lead_sources->orderBy($sort, $order);


        if ($search) {
            $lead_sources->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $total = $lead_sources->count();
        $canEdit = checkPermission('manage_leads');
        $canDelete = checkPermission('manage_leads');

        $lead_sources = $lead_sources
            ->paginate($limit)
            ->through(function ($lead_source) use ($canEdit, $canDelete) {
                $actions = '';
                if ($lead_source->is_default == 1) {
                    $actions = '-';
                } else {
                    if ($canEdit) {
                        $actions .= '<a href="javascript:void(0);" class="edit-lead-source" data-bs-toggle="modal" data-bs-target="#edit_lead_source_modal" data-id="' . $lead_source->id . '" title="' . get_label('update', 'Update') . '">' .
                            '<i class="bx bx-edit mx-1"></i>' .
                            '</a>';
                    }

                    if ($canDelete) {
                        $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $lead_source->id . '" data-type="lead-sources">' .
                            '<i class="bx bx-trash text-danger mx-1"></i>' .
                            '</button>';
                    }
                }
                return [
                    'id' => $lead_source->id,
                    'name' => ucwords($lead_source->name),
                    // 'order' => $lead_source->order,
                    'actions' => $actions,
                ];
            });

        return response()->json([
            "rows" => $lead_sources->items(),
            "total" => $total,
        ]);
    }

    public function get(string $id)
    {
        $lead_source = LeadSource::findOrFail($id);
        return response()->json(['error' => false, 'message' => 'Lead Source Retrived Successfully', 'lead_source' => $lead_source]);
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:lead_sources,id',
                'name' => 'required',
            ]);
            $lead_source = LeadSource::findOrFail($request->id);
            // dd($lead_source);
            $lead_source->name = $request->name;
            $lead_source->save();

            return response()->json(['error' => false, 'message' => 'Lead Source Updated Successfully', 'id' => $lead_source->id, 'type' => 'lead_source']);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Lead Source Couldn\'t Updated.',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = DeletionService::delete(LeadSource::class, $id, 'lead_source');
        return $response;
    }
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:lead_sources,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $lead_source = LeadSource::findOrFail($id);
            $deletedIds[] = $id;
            $deletedTitles[] = $lead_source->name;
            DeletionService::delete(LeadSource::class, $id, 'lead_source');
        }

        return response()->json(['error' => false, 'message' => 'LeadSource(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles]);
    }

    public function search(Request $request)
    {
        // dd($request);
        $query = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;

        $leadSources = LeadSource::query();

        // Filter by name if search query is present
        $leadSources = $leadSources->when($query, function ($queryBuilder) use ($query) {
            $queryBuilder->where('name', 'like', '%' . $query . '%');
        })
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'name']);

        // Format for Select2
        $results = $leadSources->map(function ($stage) {
            return ['id' => $stage->id, 'text' => $stage->name];
        });

        // Detect if more pages are available
        $pagination = ['more' => $leadSources->count() === $perPage];

        return response()->json([
            'items' => $results,
            'pagination' => $pagination
        ]);
    }
}
