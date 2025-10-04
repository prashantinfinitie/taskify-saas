<?php


namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Support\Facades\Log;

class IssueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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


     /**
 * Create a new issue under a project.
 * @group project issues
 * This endpoint allows you to create a new issue related to a specific project. You must provide issue details such as title, description, status, and optional assignees. The issue will be created under the given project and notifications will be dispatched to the assignees.
 *
 * @authenticated
 *
 * @header workspace_id 2
 *
 * @urlParam project integer required The ID of the project in which the issue is being created. Example: 5
 *
 * @bodyParam title string required The title of the issue. Example: Database connectivity issue
 * @bodyParam description string required A description of the issue. Example: There is an intermittent issue connecting to the database from the API server.
 * @bodyParam status string required The current status of the issue. Must be one of: `open`, `in_progress`, `resolved`, `closed`. Example: open
 * @bodyParam assignee_id array optional An array of user IDs to assign the issue to. Example: [1, 3]
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Issue created successfully.",
 *   "data": {
 *     "id": 17,
 *     "project_id": 5,
 *     "title": "Database connectivity issue",
 *     "description": "There is an intermittent issue connecting to the database from the API server.",
 *     "status": "open",
 *     "created_by": 2,
 *     "assignees": [
 *       {
 *         "id": 1,
 *         "name": "John Doe",
 *         "email": "john@example.com"
 *       },
 *       {
 *         "id": 3,
 *         "name": "Jane Smith",
 *         "email": "jane@example.com"
 *       }
 *     ]
 *   }
 * }
 *
 * @response 422 {
 *   "error": true,
 *   "message": "Validation failed.",
 *   "data": {
 *     "title": ["The title field is required."]
 *   }
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "Database error occurred while creating the issue.",
 *   "data": {
 *     "details": "SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'project_id' cannot be null..."
 *   }
 * }
 */

   public function store(Request $request, Project $project)
{
    $isApi = $request->get('isApi', $request->expectsJson());

    try {
        // Validate form data
        $formFields = $request->validate([
            'title' => 'required|max:256',
            'description' => 'required|max:512',
            'status' => 'required|in:open,in_progress,resolved,closed',
            'assignee_id' => 'nullable|array',
            'assignee_id.*' => 'exists:users,id',
        ]);

        $user = getAuthenticatedUser(); // Should support both web and API
        $formFields['created_by'] = $user->id;

        // Create issue
        $issue = $project->issues()->create($formFields);

        // Attach assignees
        $assignee_ids = $request->assignee_id;
        if (!empty($assignee_ids)) {
            $issue->users()->attach($assignee_ids);
        }

        // Send notifications
        $notification_data = [
            'type' => 'project_issue',
            'type_id' => $issue->id,
            'type_title' => $issue->title,
            'status' => ucwords(str_replace('_', ' ', $issue->status)),
            'creator_first_name' => ucwords($user->first_name),
            'creator_last_name' => ucwords($user->last_name),
            'access_url' => 'projects/information/' . $project->id,
            'action' => 'assigned'
        ];

        $recipients = array_map(fn($id) => 'u_' . $id, $assignee_ids ?? []);
        processNotifications($notification_data, $recipients);

        // Response
        if ($isApi) {
            return formatApiResponse(false, 'Issue created successfully.', [
                'issue' => formatIssue($issue)
            ]);
        }

        return redirect()->back()->with('success', 'Issue created successfully.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        return $isApi
            ? formatApiResponse(true, 'Validation failed.', ['errors' => $e->errors()], 422)
            : redirect()->back()->withErrors($e->errors())->withInput();
    } catch (\Illuminate\Database\QueryException $e) {
        return $isApi
            ? formatApiResponse(true, 'Database error occurred while creating the issue.', ['details' => $e->getMessage()], 500)
            : redirect()->back()->with('error', 'Database error occurred while creating the issue.');
    } catch (\Exception $e) {
        return $isApi
            ? formatApiResponse(true, 'An unexpected error occurred.', ['details' => $e->getMessage()], 500)
            : redirect()->back()->with('error', 'An unexpected error occurred.');
    }
}




    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project, Issue $issue)
    {
        try {
            // Ensure the issue belongs to the project
            if ($issue->project_id !== $project->id) {
                return response()->json(['error' => true, 'message' => 'Issue does not belong to the specified project.'], 403);
            }
            $assigneeIds = $issue->users->pluck('id')->toArray();
            return response()->json(['error' => false, 'issue' => $issue, 'assignee_ids' => $assigneeIds], 200);
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error editing issue: ' . $th->getMessage());

            return response()->json(['error' => true, 'message' => 'An unexpected error occurred.'], 500);
        }
    }






     /**
 * Update an existing issue within a project.
 * @group project issues
 * This endpoint allows you to update the title, description, status, and assignees of an existing issue
 * associated with a project. You must pass the issue `id` as part of the payload. Assignee user IDs
 * must be valid user IDs that exist in the system.
 *
 * The request works for both API and web usage. Use `Accept: application/json` and `isApi=true` for API responses.
 *
 * @bodyParam id integer required The ID of the issue to update. Example: 8
 * @bodyParam title string required The updated title of the issue. Max 256 characters. Example: Database connectivity issue
 * @bodyParam description string required The updated description of the issue. Max 512 characters. Example: There is an intermittent issue connecting to the database from the API server.
 * @bodyParam status string required The current status of the issue. Must be one of: open, in_progress, resolved, closed. Example: in_progress
 * @bodyParam assignee_id array[] The list of user IDs to assign this issue to. Optional. Example: [1, 3]
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Issue updated successfully.",
 *   "issue": {
 *     "id": 8,
 *     "title": "Database connectivity issue",
 *     "description": "There is an intermittent issue connecting to the database from the API server.",
 *     "status": "in_progress",
 *     "assignees": [...]
 *   }
 * }
 *
 * @response 404 {
 *   "error": true,
 *   "message": "Issue not found."
 * }
 *
 * @response 422 {
 *   "error": true,
 *   "message": "Validation failed.",
 *   "errors": {
 *     "title": ["The title field is required."]
 *   }
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "An unexpected error occurred.",
 *   "details": "Exception message here"
 * }
 *
 * @header Accept application/json
 * @header workspace_id 2
 */

   public function update(Project $project, Request $request)
{
    $isApi = $request->get('isApi', $request->expectsJson());

    try {
        // Validate request data
        $formFields = $request->validate([
            'id' => 'required|exists:issues,id',
            'title' => 'required|max:256',
            'description' => 'required|max:512',
            'status' => 'required|in:open,in_progress,resolved,closed',
            'assignee_id' => 'nullable|array',
            'assignee_id.*' => 'exists:users,id'
        ]);

        // Retrieve the issue
        $issue = Issue::findOrFail($formFields['id']);

        // Update issue fields
        $issue->title = $formFields['title'];
        $issue->description = $formFields['description'];
        $issue->status = $formFields['status'];
        $issue->save();

        // Sync assignees if provided
        if (!empty($formFields['assignee_id'])) {
            $issue->users()->sync($formFields['assignee_id']);
        }

        if ($isApi) {
            return formatApiResponse(false, 'Issue updated successfully.', [
                'issue' => formatIssue($issue)
            ]);
        }

        return redirect()->back()->with('success', 'Issue updated successfully.');

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return $isApi
            ? formatApiResponse(true, 'Issue not found.', [], 404)
            : redirect()->back()->with('error', 'Issue not found.');
    } catch (\Illuminate\Validation\ValidationException $e) {
        return $isApi
            ? formatApiResponse(true, 'Validation failed.', ['errors' => $e->errors()], 422)
            : redirect()->back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        return $isApi
            ? formatApiResponse(true, 'An unexpected error occurred.', ['details' => $e->getMessage()], 500)
            : redirect()->back()->with('error', 'An unexpected error occurred.');
    }
}




     /**
 * Delete an issue from a project.
 * @group project issues
 * This endpoint deletes a specific issue associated with a project. It uses the `DeletionService` to handle
 * soft or hard deletion logic based on the application's internal rules.
 *
 * Requires authentication and a valid issue ID. Works for both API and web responses if handled inside `DeletionService`.
 *
 * @urlParam project int required The ID of the project to which the issue belongs. Example: 5
 * @urlParam id int required The ID of the issue to delete. Example: 8
 *
 * @response 200 {
 *   "error": false,
 *   "message": "Issue deleted successfully."
 * }
 *
 * @response 404 {
 *   "error": true,
 *   "message": "Issue not found."
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "An unexpected error occurred.",
 *   "details": "Exception message here"
 * }
 *
 * @header Accept application/json
 * @header workspace_id 2
 */

    public function destroy(Project $project, Issue $id)
    {
        $response = DeletionService::delete(Issue::class, $id->id, 'Issue' , );
        return $response;
    }
    public function destroy_multiple(Request $request, Project $id)
    {
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:issues,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $issue = Issue::findOrFail($id);
            $deletedIds[] = $id;
            $deletedTitles[] = $issue->title;
            DeletionService::delete(Issue::class, $id, 'Issue');
        }
        return response()->json(['error' => false, 'message' => 'Issue(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles, 'type' => 'issue']);
    }
    public function list(Request $request, $id = '', $type = '')
    {
        $search = $request->input('search', '');
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'DESC');
        $status = $request->input('status', '');
        $assigned_to = $request->input('assigned_to', '');
        $created_by = $request->input('created_by', '');
        $start_date = $request->input('start_date', '');
        $end_date = $request->input('end_date', '');
        $limit = $request->input('limit', 10);

        // Initialize issues query
        $issuesQuery = Issue::query();

        // Filter by project ID if provided
        if ($id) {
            $issuesQuery->where('project_id', $id);
        }

        // Apply additional filters
        if ($status) {
            $issuesQuery->where('status', $status);
        }
        if ($assigned_to) {
            $issuesQuery->where('assigned_to', $assigned_to);
        }
        if ($created_by) {
            $issuesQuery->where('created_by', $created_by);
        }
        if ($start_date && $end_date) {
            $issuesQuery->whereBetween('created_at', [$start_date, $end_date]);
        }

        // Apply search filter
        if ($search) {
            $issuesQuery->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->orWhere('status', 'LIKE', "%{$search}%")
                ->orWhereHas('users', function ($query) use ($search) {
                    $query->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%");
                })
                    ->orWhereHas('creator', function ($query) use ($search) {
                        $query->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Get total issues count before applying pagination
        $totalIssues = $issuesQuery->count();
        // dd($totalIssues);

        // Apply sorting and pagination
        $issues = $issuesQuery
            ->orderBy($sort, $order)
            ->paginate($limit)
            ->through(function ($issue) {
                $userHtml = '';
                $userHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
            if ($issue->users->count() > 0) {
                foreach ($issue->users as $user) {
                    $userHtml .= "<li class='avatar avatar-sm pull-up'><a href='" . route('users.show', ['id' => $user->id]) . "' target='_blank' title='{$user->first_name} {$user->last_name}'><img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                }
            } else {
                $userHtml .= "<li class=''><a href='#' title='No assignees'><span class='fw-semibold'>No Assignees</span></a></li>";
            }
                $userHtml .= '</ul>';
                $createdByHtml = '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                $createdByHtml .= "<li class='avatar avatar-sm pull-up'><a href='" . route('users.show', ['id' => $issue->creator->id]) . "' target='_blank' title='{$issue->creator->first_name} {$issue->creator->last_name}'><img src='" . ($issue->creator->photo ? asset('storage/' . $issue->creator->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                $createdByHtml .= '</ul>';

                return [
                    'id' => $issue->id,
                    'title' => $issue->title,
                    'description' => Str::limit($issue->description, 512),
                'status' => $this->generateIssueStatus($issue->status),
                'users' => $userHtml,
                    'created_by' => $createdByHtml,
                    'created_at' => format_date($issue->created_at),
                    'updated_at' => format_date($issue->updated_at),
                    'actions' => $this->generateIssueActions($issue),
                ];
            });

        return response()->json([
            'rows' => $issues->items(),
            'total' => $totalIssues,
        ]);
    }

    /**
     * @group project issues
     *
     * List or fetch issues (API)
     *
     * This endpoint returns a paginated list of issues for a given project, or a single issue by its ID if `type=issue` is provided.
     *
     * @urlParam id int optional The project ID to filter issues by, or the issue ID if `type=issue` is set. Example: 117
     * @queryParam type string optional If set to 'issue', fetches a single issue by its ID. Example: issue
     * @queryParam search string optional Search term for title, description, status, assignee, or creator. Example: bug
     * @queryParam sort string optional Field to sort by. Default: created_at. Example: updated_at
     * @queryParam order string optional Sort direction (ASC or DESC). Default: DESC. Example: ASC
     * @queryParam status string optional Filter by issue status. Example: open
     * @queryParam assigned_to int optional Filter by assigned user ID. Example: 5
     * @queryParam created_by int optional Filter by creator user ID. Example: 2
     * @queryParam start_date date optional Filter issues created after this date. Example: 2025-06-01
     * @queryParam end_date date optional Filter issues created before this date. Example: 2025-06-30
     * @queryParam limit int optional Number of results per page. Default: 10. Example: 20
     *
     * @response 200 {
     *   "error": false,
     *   "message": "Issue list fetched successfully.",
     *   "rows": [
     *     {
     *       "id": 1,
     *       "project_id": 117,
     *       "title": "data not retrive",
     *       "description": "when Api was call data nor retrived",
     *       "status": "in_progress",
     *       "created_by": {
     *         "id": 2,
     *         "first_name": "herry",
     *         "last_name": "porter",
     *         "email": "admin@gmail.com"
     *       },
     *       "assignees": [
     *         {
     *           "id": 2,
     *           "first_name": "herry",
     *           "last_name": "porter",
     *           "email": "admin@gmail.com",
     *           "photo": null
     *         }
     *       ],
     *       "created_at": "2025-06-12 03:59:42",
     *       "updated_at": "2025-06-12 03:59:42"
     *     }
     *   ],
     *   "total": 1,
     *   "current_page": 1,
     *   "last_page": 1,
     *   "per_page": 10
     * }
     */
    public function apiList(Request $request, $id = null, $type = null)
{
    $isApi = $request->get('isApi', true);

    try {
        // Fetch a single issue if $id is set and $type is 'issue'
        if ($id && $type === 'issue') {
            $issue = \App\Models\Issue::with(['creator', 'users'])->find($id);

            if ($issue) {
                return formatApiResponse(false, 'Issue fetched successfully.', [
                    'rows' => [formatIssue($issue)],

                    'total' => 1,
                ]);
            } else {
                return formatApiResponse(true, 'Issue not found.', [
                    'rows' => [],
                    'total' => 0,
                ], 404);
            }
        }

        // List of issues (all or project-specific)
        $search = $request->input('search', '');
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'DESC');
        $status = $request->input('status', '');
        $assigned_to = $request->input('assigned_to', '');
        $created_by = $request->input('created_by', '');
        $start_date = $request->input('start_date', '');
        $end_date = $request->input('end_date', '');
        $limit = $request->input('limit', 10);

        $issuesQuery = \App\Models\Issue::with(['creator', 'users']);
        // dd($issuesQuery);

        // Filter by project ID if $id is numeric and $type is not 'issue'
        if ($id && $type !== 'issue') {
            $issuesQuery->where('project_id', $id);
        }

        // Apply filters
        if ($status) {
            $issuesQuery->where('status', $status);
        }
        if ($assigned_to) {
            $issuesQuery->whereHas('users', function ($q) use ($assigned_to) {
                $q->where('users.id', $assigned_to);
            });
        }
        if ($created_by) {
            $issuesQuery->where('created_by', $created_by);
        }
        if ($start_date && $end_date) {
            $issuesQuery->whereBetween('created_at', [$start_date, $end_date]);
        }
        if ($search) {
            $issuesQuery->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%")
                    ->orWhereHas('users', function ($q) use ($search) {
                        $q->where('first_name', 'LIKE', "%{$search}%")
                          ->orWhere('last_name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($q) use ($search) {
                        $q->where('first_name', 'LIKE', "%{$search}%")
                          ->orWhere('last_name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $totalIssues = $issuesQuery->count();
        // dd($totalIssues);

        $issues = $issuesQuery
            ->orderBy($sort, $order)
            ->paginate($limit)
            ->through(function ($issue) {
                return formatIssue($issue);
            });

        return formatApiResponse(false, 'Issue list fetched successfully.', [
            'rows' => $issues->items(),
            'total' => $totalIssues,
            'current_page' => $issues->currentPage(),
            'last_page' => $issues->lastPage(),
            'per_page' => $issues->perPage(),
        ]);
    } catch (\Exception $e) {
        return formatApiResponse(true, 'Failed to fetch issues: ' . $e->getMessage(), [], 500);
    }
}

    /**
     * Generate action buttons for an issue.
     */
    private function generateIssueActions($issue)
    {
        $actions = '';
        $actions .= '<a href="javascript:void(0);" class="edit-project-issue" data-project-id ="' . $issue->project->id . '"
        data-id="' . $issue->id . '" title="' . get_label('update', 'Update') . '">' .
            '<i class="bx bx-edit mx-1"></i>' .
            '</a>';
        $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $issue->id . '" data-type="projects/' . $issue->project->id . '/issues" data-table="project_issue_table">' .
            '<i class="bx bx-trash text-danger mx-1"></i>' .
            '</button>';

        return $actions;
    }

    /**
     * Generates a HTML badge for an issue status.
     *
     * @param string $status Issue status.
     *
     * @return string HTML badge.
     */
    public function generateIssueStatus($status)
    {
        switch ($status) {
            case 'open':
                $status = '<span class="badge bg-label-primary">' . get_label('open', 'Open') . '</span>';
                break;
            case 'in_progress':
                $status = '<span class="badge bg-label-info">' . get_label('in_progress', 'In Progress') . '</span>';
                break;
            case 'resolved':
                $status = '<span class="badge bg-label-success">' . get_label('resolved', 'Resolved') . '</span>';
                break;
            case 'closed':
                $status = '<span class="badge bg-label-danger">' . get_label('closed', 'Closed') . '</span>';
                break;
            default:
                $status = '<span class="badge bg-label-danger">' . get_label('unknown', 'Unknown') . '</span>';
                break;
        }
        return $status;
    }
}
