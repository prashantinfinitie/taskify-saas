<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CandidateStatus;
use App\Models\Workspace;
use App\Services\DeletionService;

class CandidateStatusController extends Controller
{
    protected $workspace;

    protected $user;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // fetch session and use it in entire class with constructor
            $this->workspace = Workspace::find(session()->get('workspace_id'));
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
    }

    public function index()
    {
        $statuses = CandidateStatus::orderBy('order')->get();
        return view('candidate_status.index', compact('statuses'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required'
        ]);

        $order = CandidateStatus::max('order') + 1;
        $admin_id = getAdminIdByUserRole();
        $workspace = Workspace::find(session()->get('workspace_id'));
        $this->workspace = $workspace;
        $workspace_id = $workspace?->id;

        $candidate_status = CandidateStatus::create([
            'name' => $request->name,
            'order' => $order,
            'color' => $request->color,
            'admin_id' => $admin_id,
            'workspace_id' => $workspace_id
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Status Created Successfully!',
            'candidate_statuses' => $candidate_status
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $candidate_status = CandidateStatus::findOrFail($id);

        $candidate_status->update([
            'name' => $request->name,
            'color' => $request->color
        ]);

        return response()->json([
            'error' => false,
            'message' => 'Status updated Successfully!',
            'candidate_status' => $candidate_status
        ]);
    }

    public function destroy($id)
    {

        $candidate_status = CandidateStatus::findOrFail($id);

        $candidateCount = $candidate_status->candidates->count();

        if ($candidateCount > 0) {
            return response()->json([
                'error' => 'false',
                'message' => ' Cannot delete . This status is assigned to one or more candidates . '
            ]);
        }

        $response = DeletionService::delete(CandidateStatus::class, $candidate_status->id, 'Candidate Status');

        return $response;
    }

    public function destroy_multiple(Request $request)
    {

        $validatedData = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:candidate_statuses,id'
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $notDeleted = [];

        foreach ($ids as $id) {
            $candidate_status = CandidateStatus::findOrFail($id);

            // If status is linked to candidates, skip deletion
            if ($candidate_status->candidates()->count() > 0) {
                $notDeleted[] = $id;
                continue;
            }


            DeletionService::delete(CandidateStatus::class, $candidate_status->id, 'Candidate Status');
            $deletedIds[] = $id;
        }

        return response()->json([
            'error' => count($notDeleted) > 0,
            'message' => count($notDeleted) ? 'Some statuses could not be deleted because they are assigned to candidates.' : 'Candidate Status(es) Deleted Successfully!',
            'id' => $deletedIds,
        ]);
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $item) {
            CandidateStatus::where('id', $item['id'])->update([
                'order' => $item['position']
            ]);
        }
        return response()->json([
            'error' => false,
            'message' => 'Order updated successfully!'
        ]);
    }


    public function list()
    {

        $search = request('search');
        $limit = request('limit', 10);
        $offset = request('offset', 0);
        $order = request('order', 'DESC');
        $sort = request('sort', 'id');

        // $query = CandidateStatus::orderBy('order');
        $query = CandidateStatus::where('workspace_id', $this->workspace->id)
            ->where('admin_id', getAdminIdByUserRole())
            ->orderBy('order');



        if ($search) {
            $query->where('name', 'like', "%$search%");
        }

        $total = $query->count();

        $canEdit = checkPermission('edit_candidate_status');
        $canDelete = checkPermission('delete_candidate_status');

        $statuses = $query->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($status) use ($canDelete, $canEdit) {

                $actions = '';

                if ($canEdit) {
                    $actions .= '<a href="javascript:void(0);" class="edit-candidate-status-btn"
                                            data-candidate-status=\'' . htmlspecialchars(json_encode($status), ENT_QUOTES, 'UTF-8') . '\'
                                            title="' . get_label('update', 'Update') . '">
                                            <i class="bx bx-edit mx-1"></i>
                                            </a>';
                }


                if ($canDelete) {
                    $actions .= '<button type="button"
                                            class="btn delete"
                                            data-id="' . $status->id . '"
                                            data-type="candidate-status"
                                            title="' . get_label('delete', 'Delete') . '">
                                            <i class="bx bx-trash text-danger mx-1"></i>
                                            </button>';
                }

                return [
                    'id' => $status->id,
                    'order' => $status->order,
                    'name' => ucwords($status->name),
                    'created_at' => format_date($status->created_at),
                    'color' => '<span class="badge bg-' . $status->color . '">' . ucfirst($status->color) . '</span>',
                    'updated_at' => format_date($status->updated_at),
                    'actions' => $actions ?: '-'
                ];
            });

        return response()->json([
            'rows' => $statuses,
            'total' => $total,
        ]);
    }

    public function searchStatuses(Request $request)
    {
        // dd($request);
        $query = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;

        $statusesQuery = CandidateStatus::where('workspace_id', session()->get('workspace_id'));

        if ($query) {
            $statusesQuery->where('name', 'like', '%' . $query . '%');
        }

        $statuses = $statusesQuery
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'name']);

        // Format for Select2
        $results = $statuses->map(function ($status) {
            return [
                'id' => $status->id,
                'text' => $status->name
            ];
        });

        $pagination = ['more' => $statuses->count() === $perPage];

        return response()->json([
            'items' => $results,
            'pagination' => $pagination
        ]);
    }
}
