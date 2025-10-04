<?php

namespace App\Http\Controllers;

use App\Models\LeadStage;
use App\Models\Workspace;
use App\Services\DeletionService;
use Illuminate\Http\Request;

class LeadStageController extends Controller
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
        // dd($this->workspace);
        $lead_stages = $this->workspace->lead_stages(); 
        // dd($lead_stages);
        return view('lead_stages.index', compact('lead_stages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'color' => 'required|in:primary,secondary,success,danger,info,dark,warning',
            ]);
            $lead_stage = new LeadStage();
            $lead_stage->name = $request->name;
            $lead_stage->workspace_id = $this->workspace->id;
            $lead_stage->admin_id = getAdminIdByUserRole();
            $lead_stage->slug = generateUniqueSlug($request->name, LeadStage::class);
            $lead_stage->order = LeadStage::getNextOrderForWorkspace( getWorkspaceId())     ;
            $lead_stage->color = $request->color;
            $lead_stage->save();
            

            return response()->json([
                'error' => false,
                'message' => 'Lead Stage Created Successfully.',
                'id' => $lead_stage->id,
                'type' => 'lead_stage'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => true,
                'message' => 'Lead Stage Couldn\'t Created.'
            ]);
        }
    }

    public function get(string $id)
    {
        $lead_stage = LeadStage::findOrFail($id);
        return response()->json(['error' => false, 'message' => 'Lead Stage Retrived Successfully', 'lead_stage' => $lead_stage]);
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'exists:lead_stages,id',
                'name' => 'required|string',
                'color' => 'required|in:primary,secondary,success,danger,info,dark,warning',
            ]);

            $lead_stage = LeadStage::findOrFail($request->id);
            $lead_stage->name = $request->name;
            $lead_stage->slug = generateUniqueSlug($request->name, LeadStage::class, $lead_stage->id);
            $lead_stage->color = $request->color;
            $lead_stage->save();


            return response()->json(['error' => false, 'message' => 'Lead Stage Updated Successfully.', 'id' => $lead_stage->id, 'type' => 'lead_stage']);
        } catch (\Exception $e) {
            dd($e);

            return response()->json([
                'message' => 'Lead Stage Couldn\'t Updated.',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $response = DeletionService::delete(LeadStage::class, $id, 'LeadStage');
        LeadStage::where('workspace_id', $this->workspace->id)
            ->orderBy('order')
            ->get()
            ->values()
            ->each(function ($stage, $index) {
                $stage->order = $index + 1;
                $stage->save();
            });
        return $response;
    }

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:lead_stages,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $lead_stage = LeadStage::findOrFail($id);
            $deletedIds[] = $id;
            $deletedTitles[] = $lead_stage->name;
            DeletionService::delete(LeadStage::class, $id, 'LeadStage');
        }

        LeadStage::where('workspace_id', $this->workspace->id)
            ->orderBy('order')
            ->get()
            ->values()
            ->each(function ($stage, $index) {
                $stage->order = $index + 1;
                $stage->save();
            });
        return response()->json(['error' => false, 'message' => 'LeadSource(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles]);
    }

    public function list()
    {
        $search = request('search');
        $sort = request('sort', "id");
        $order = request('order', "DESC");
        $limit = request('limit', 10);


        $lead_stages_query = $this->workspace->lead_stages()->orderBy($sort, $order);

        if ($search) {
            $lead_stages_query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        $total = $lead_stages_query->count();

        $canEdit = checkPermission('manage_leads');
        $canDelete = checkPermission('manage_leads');

        $lead_stages = $lead_stages_query
            ->paginate($limit)
            ->through(function ($lead_stage) use ($canEdit, $canDelete) {
                $actions = '';
                if ($lead_stage->is_default == 1) {
                    $actions = '-';
                } else {
                    if ($canEdit) {
                        $actions .= '<a href="javascript:void(0);" class="edit-lead-stage" data-bs-toggle="modal" data-bs-target="#edit_lead_stage_modal" data-id="' . $lead_stage->id . '" title="' . get_label('update', 'Update') . '">' .
                            '<i class="bx bx-edit mx-1"></i>' .
                            '</a>';
                    }

                    if ($canDelete) {
                        $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $lead_stage->id . '" data-type="lead-stages">' .
                            '<i class="bx bx-trash text-danger mx-1"></i>' .
                            '</button>';
                    }
                }
                return [
                    'id' => $lead_stage->id,
                    'name' => ucwords($lead_stage->name),
                    'preview' => '<span class="badge bg-' . ($lead_stage->color ?? 'secondary') . '">' . $lead_stage->name . '</span>',
                    'order' => $lead_stage->order,
                    'actions' => $actions,
                ];
            });

        return response()->json([
            "rows" => $lead_stages->items(),
            "total" => $total,
        ]);
    }

    public function reorder(Request $request)
    {
        try {
            $request->validate([
                'order' => 'required|array',
                'order.*.id' => 'required|integer|exists:lead_stages,id',
                'order.*.position' => 'required|integer'
            ]);

            foreach ($request->order as $item) {
                LeadStage::where('id', $item['id'])->update([
                    'order' => $item['position']
                ]);
            }


            return response()->json([
                'error' => false,
                'message' => 'Lead Stages Reordered Successfully'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => true,
                'message' => 'Lead Stages Reordering Failed.',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
        }
    }

    public function searchLeadStages(Request $request)
    {

        $query = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;

        $leadStages = LeadStage::query();
        // Filter by name if search query is present
        $leadStages = $leadStages->when($query, function ($queryBuilder) use ($query) {
            $queryBuilder->where('name', 'like', '%' . $query . '%');
        })
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'name']);

        // Format for Select2
        $results = $leadStages->map(function ($stage) {
            return ['id' => $stage->id, 'text' => $stage->name];
        });

        // Detect if more pages are available
        $pagination = ['more' => $leadStages->count() === $perPage];

        return response()->json([
            'items' => $results,
            'pagination' => $pagination
        ]);
    }
}
