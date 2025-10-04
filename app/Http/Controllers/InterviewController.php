<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Interview;
use App\Models\User;
use App\Models\Workspace;
use App\Services\DeletionService;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    //
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
        $candidates = Candidate::where('workspace_id', $this->workspace->id)
            ->where('admin_id', getAdminIdByUserRole())
            ->get();

        $interviews = Interview::all();
        $users = $this->workspace->users;
        // dd($candidates);
        return view('interviews.index', compact('candidates', 'interviews', 'users'));
    }

    public function store(Request $request)
    {

        $form_fields =  $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'interviewer_id' => 'required|exists:users,id',
            'round' => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'mode' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|max:255|in:scheduled,completed,cancelled'
        ]);

        $form_fields['workspace_id'] = $this->workspace->id;
        $form_fields['admin_id'] = getAdminIdByUserRole();

        $interview = Interview::create($form_fields);
        // trigger notification

        $candidate = Candidate::find($request->candidate_id);
        $interviewer = User::find($request->interviewer_id);


        $data = [
            'type' => 'interview_assignment',
            'type_id' => $interview->id,
            'candidate_name' => $candidate->name,
            'round' => $interview->round,
            'scheduled_at' => $interview->scheduled_at,
            'mode' => $interview->mode,
            'location' => $interview->location,
            'interviewer_first_name' => $interviewer->first_name,
            'interviewer_last_name' => $interviewer->last_name,
            'access_url' => 'interviews.index',
            'action' => 'update'
        ];

        $recipients = ['u_' . $interviewer->id, 'ca' . $candidate->id];
        processNotifications($data, $recipients);


        return response()->json([
            'error' => false,
            'message' => 'Interview Created Successfully!',
            'interview' => $interview
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'interviewer_id' => 'required|exists:users,id',
            'round' => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'mode' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|max:255|in:scheduled,completed,cancelled'
        ]);

        $interview = Interview::findOrFail($id);
        $oldStatus = $interview->status;
        $interview->update($request->all());

        // trigger notification if status has changed

        if ($oldStatus !== $request->status) {
            $candidate = Candidate::find($request->candidate_id);
            $interviewer = User::find($request->interviewer_id);

            $data = [
                'type' => 'interview_status_update',
                'type_id' => $interview->id,
                'candidate_name' => $candidate->name,
                'round' => $interview->round,
                'scheduled_at' => $interview->scheduled_at,
                'mode' => $interview->mode,
                'location' => $interview->location,
                'interviewer_first_name' => $interviewer->first_name,
                'interviewer_last_name' => $interviewer->last_name,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'updater_first_name' => auth()->user()->first_name,
                'updater_last_name' => auth()->user()->last_name,
                'access_url' => 'interviews',
                'action' => 'update'
            ];

            $recipients = ['u_' . $interviewer->id, 'ca' . $candidate->id];
            processNotifications($data, $recipients);
        }

        return response()->json([
            'error' => false,
            'message' => 'Interview Updated Successfully!',
            'interview' => $interview
        ]);
    }

    public function destroy_multiple(Request $request)
    {

        $validatedData = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:interviews,id',
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];

        foreach ($ids as $id) {
            $interview = Interview::findOrFail($id);
            $deletedIds[] = $id;

            DeletionService::delete(Interview::class, $interview->id, 'Interview');
        }

        return response()->json([
            'error' => false,
            'message' => 'Interviews Deleted Successfully!',
            'deleted_ids' => $deletedIds
        ]);
    }

    public function destroy($id)
    {
        $interview = Interview::findOrFail($id);

        $response = DeletionService::delete(Interview::class, $interview->id, 'Interview');

        return $response;
    }

    public function list()
    {
        $search = request('search');
        $order = request('order', 'DESC');
        $limit = request('limit', 10);
        $offset = request('offset');
        $sort = request('sort', 'id');
        $start_date = request('start_date'); // Added start_date parameter
        $end_date = request('end_date');     // Added end_date parameter

        $order = 'desc';
        switch ($sort) {
            case 'newest':
                $sort = 'created_at';
                $order = 'desc';
                break;
            case 'oldest':
                $sort = 'created_at';
                $order = 'asc';
                break;
            case 'recently-updated':
                $sort = 'updated_at';
                $order = 'desc';
                break;
            case 'earliest-updated':
                $sort = 'updated_at';
                $order = 'asc';
                break;
            default:
                $sort = 'id';
                $order = 'desc';
                break;
        }

        $interviewStatus = request('status');

        // $query = Interview::query();
        $query = Interview::query()
            ->where('workspace_id', $this->workspace->id)
            ->where('admin_id', getAdminIdByUserRole());

        // Apply search filters
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('candidate', function ($q) use ($search) {
                    $q->where('candidates.id', 'like', "%$search%")
                        ->orWhere('candidates.name', 'like', "%$search%");
                })
                    ->orWhereHas('interviewer', function ($q) use ($search) {
                        $q->where('users.id', 'like', "%$search%")
                            ->orWhere('users.first_name', 'like', "%$search%")
                            ->orWhere('users.last_name', 'like', "%$search%");
                    })
                    ->orWhere('round', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%")
                    ->orWhere('location', 'like', "%$search%")
                    ->orWhere('mode', 'like', "%$search%");
            });
        }

        if ($interviewStatus) {
            $query->where('status', $interviewStatus);
        }

        // Apply date filtering - similar to lead controller
        if ($start_date && $end_date) {
            $query->whereBetween('scheduled_at', [$start_date, $end_date]);
        }

        // Get total count for pagination
        $total = $query->count();

        $canEdit = checkPermission('edit_interview');
        $canDelete = checkPermission('delete_interview');

        // Apply sorting, pagination, and limit
        $interviews = $query->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($interview) use ($canDelete, $canEdit) {

                $actions = '';

                if ($canEdit) {
                    $actions .= '<a href="javascript:void(0);" class="edit-interview-btn"
                                    data-interview=\'' . htmlspecialchars(json_encode($interview), ENT_QUOTES, 'UTF-8') . '\'
                                    title="' . get_label('update', 'Update') . '">
                                    <i class="bx bx-edit mx-1"></i>
                                </a>';
                }

                if ($canDelete) {
                    $actions .= '<button type="button"
                                    class="btn delete"
                                    data-id="' . $interview->id . '"
                                    data-type="interviews"
                                    title="' . get_label('delete', 'Delete') . '">
                                    <i class="bx bx-trash text-danger mx-1"></i>
                                </button>';
                }

                return [
                    'id' => $interview->id,
                    'candidate' => $interview->candidate->name,
                    'interviewer' => $interview->interviewer->first_name . ' ' . $interview->interviewer->last_name,
                    'round' => ucwords($interview->round),
                    'scheduled_at' => ucwords($interview->scheduled_at),
                    'mode' => ucwords($interview->mode),
                    'location' => ucwords($interview->location),
                    'status' => ucwords($interview->status),
                    'created_at' => format_date($interview->created_at),
                    'updated_at' => format_date($interview->updated_at),
                    'actions' => $actions
                ];
            });

        // Return the result as JSON
        return response()->json([
            'rows' => $interviews,
            'total' => $total,
        ]);
    }
}
