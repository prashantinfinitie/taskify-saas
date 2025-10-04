<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Client;
use App\Models\Status;
use App\Models\Project;
use App\Models\Priority;
use App\Models\Workspace;
use Illuminate\Support\Str;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\EstimatesInvoice;
use App\Models\Expense;
use App\Models\TimeTracker;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
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
    public function showProjectReport()
    {
        $projects = $this->workspace->projects()->pluck('title', 'id');
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        $statuses = Status::where('admin_id', getAdminIdByUserRole())
            ->orWhere(function ($query) {
                $query->whereNull('admin_id')
                    ->where('is_default', 1);
            })->get();
        return view('reports.projects-report', [
            'workspace' => $this->workspace,
            'projects' => $projects,
            'users' => $users,
            'clients' => $clients,
            'statuses' => $statuses,
        ]);
    }
    public function getProjectReportData(Request $request)
    {
        // Debugging: Check the request data
        // dd($request->all());
        // Determine the base query based on user's access level
        $query = isAdminOrHasAllDataAccess() ? $this->workspace->projects() : $this->user->projects();
        // Apply filters only if they have values
        if ($request->filled('project_id')) {
            $query->whereIn('id', explode(',', $request->project_id)); // Handle comma-separated string
        }
        if ($request->filled('user_id')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->whereIn('users.id', explode(',', $request->user_id)); // Handle comma-separated string
            });
        }
        if ($request->filled('client_id')) {
            $query->whereHas('clients', function ($q) use ($request) {
                $q->whereIn('clients.id', explode(',', $request->client_id)); // Handle comma-separated string
            });
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('status_id')) {
            $query->whereIn('status_id', explode(',', $request->status_id)); // Handle comma-separated string
        }
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhereHas('status', function ($q) use ($searchTerm) {
                        $q->where('title', 'like', $searchTerm);
                    })
                    ->orWhereHas('priority', function ($q) use ($searchTerm) {
                        $q->where('title', 'like', $searchTerm);
                    })
                    ->orWhereHas('users', function ($q) use ($searchTerm) {
                        $q->where(function ($q) use ($searchTerm) {
                            $q->where('first_name', 'like', $searchTerm)
                                ->orWhere('last_name', 'like', $searchTerm);
                        });
                    })
                    ->orWhereHas('clients', function ($q) use ($searchTerm) {
                        $q->where(function ($q) use ($searchTerm) {
                            $q->where('first_name', 'like', $searchTerm)
                                ->orWhere('last_name', 'like', $searchTerm);
                        });
                    });
            });
        }
        // Apply sorting
        $sort = $request->input('sort', 'id'); // Default sort column
        $order = $request->input('order', 'desc'); // Default sort order
        // Sorting logic
        switch ($sort) {
            case 'status':
                $query->join('statuses', 'projects.status_id', '=', 'statuses.id')
                    ->select('projects.*', 'statuses.title as status_title')
                    ->orderBy('status_title', $order);
                break;
            case 'priority':
                $query->join('priorities', 'projects.priority_id', '=', 'priorities.id')
                    ->select('projects.*', 'priorities.title as priority_title')
                    ->orderBy('priority_title', $order);
                break;
            case 'title':
            case 'start_date':
            case 'end_date':
                $query->orderBy($sort, $order);
                break;
            default:
                $query->orderBy('id', $order); // Default sort column
        }
        // Pagination setup
        $perPage = $request->input('limit', 10);
        $page = $request->input('offset', 0) / $perPage + 1;
        // Get the total count before pagination
        $total = $query->count();
        // Fetch paginated results with related models
        $projects = $query->with(['tasks', 'users', 'clients', 'status', 'priority', 'tags'])
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();
        // Transform project data into the desired report format
        $report = $projects->map(function ($project) {
            $now = now();
            $startDate = Carbon::parse($project->start_date);
            $endDate = Carbon::parse($project->end_date);
            $totalProjectDays = $startDate->diffInDays($endDate) + 1;
            $daysElapsed = (int) floor($now->diffInDays($startDate, true));
            $daysRemaining = $endDate->isPast() ? 0 : (int) floor($now->diffInDays($endDate));
            $tasks = $project->tasks;
            $totalTasks = $tasks->count();
            $dueTasks = $tasks->where('due_date', '<=', $now)->count();
            $overdueTasks = $tasks->where('due_date', '<', $now)->count();
            $overdueDays = (int) $tasks->where('due_date', '<', $now)->map(function ($task) use ($now) {
                return Carbon::parse($task->due_date)->diffInDays($now);
            })->sum();
            $totalBudget = $project->budget ?? 0;
            // Format clients' HTML
            $clientHtml = $project->clients->map(function ($client) {
                return "<a href='" . route('clients.profile', ['id' => $client->id]) . "' target='_blank'>
                    <li class='avatar avatar-sm pull-up' title='" . e($client->first_name . " " . $client->last_name) . "'>
                        <img src='" . ($client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' />
                    </li>
                </a>";
            })->implode('');
            // Format users' HTML
            $userHtml = $project->users->map(function ($user) {
                return "<a href='" . route('users.show', ['id' => $user->id]) . "' target='_blank'>
                    <li class='avatar avatar-sm pull-up' title='" . e($user->first_name . " " . $user->last_name) . "'>
                        <img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' class='rounded-circle' />
                    </li>
                </a>";
            })->implode('');
            return [
                'id' => $project->id,
                'title' => Str::limit(ucfirst($project->title), 25, '...'),
                'description' => $project->description,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'status' => "<span class='badge bg-label-" . e($project->status->color) . "'>" . e($project->status->title) . "</span>",
                'priority' => $project->priority ? "<span class='badge bg-label-" . e($project->priority->color) . "'>" . e($project->priority->title) . "</span>" : '-',
                'budget' => [
                    'total' => $totalBudget,
                ],
                'time' => [
                    'total_days' => $totalProjectDays,
                    'days_elapsed' => $daysElapsed,
                    'days_remaining' => $daysRemaining,
                ],
                'tasks' => [
                    'total' => $totalTasks,
                    'due' => $dueTasks,
                    'overdue' => $overdueTasks,
                    'overdue_days' => $overdueDays,
                ],
                'team' => [
                    'users' => $project->users->map(function ($user) use ($project) {
                        return [
                            'id' => $user->id,
                            'name' => $user->first_name . ' ' . $user->last_name,
                            'tasks_assigned' => $user->tasks()->where('project_id', $project->id)->count(),
                        ];
                    }),
                    'total_members' => $project->users->count()
                ],
                'users' => $userHtml,
                'clients' => $clientHtml,
                'total_clients' => $project->clients->count(),
                'tags' => $project->tags->pluck('title'),
                'is_favorite' => $project->is_favorite,
                'task_accessibility' => $project->task_accessibility,
                'created_at' => format_date($project->created_at),
                'updated_at' => format_date($project->updated_at),
            ];
        });
        $teamMembers = $projects->flatMap(function ($project) {
            return $project->users;
        })->unique('id')->count();
        // Generate summary data
        $summary = [
            'total_projects' => $report->count(),
            'on_time_projects' => $report->where('tasks.overdue', 0)->count(),
            'projects_with_due_tasks' => $report->where('tasks.due', '>', 0)->count(),
            'projects_with_overdue_tasks' => $report->where('tasks.overdue', '>', 0)->count(),
            'average_days_remaining' => round($report->avg('time.days_remaining'), 2),
            'average_task_progress' => round($report->avg(function ($project) {
                if ($project['tasks']['total'] > 0) {
                    return ($project['tasks']['total'] - $project['tasks']['overdue']) / $project['tasks']['total'] * 100;
                }
                return 0; // Return 0 if there are no tasks in the project
            }), 2),
            'average_overdue_days_per_project' => round($report->where('tasks.overdue_days', '>', 0)->avg('tasks.overdue_days'), 2),
            'total_team_members' => $teamMembers,
            'overdue_projects_percentage' => round(($report->where('tasks.overdue', '>', 0)->count() / $report->count()) * 100, 2),
            'total_overdue_days' => $report->sum('tasks.overdue_days'),
            'average_task_duration' => round($report->avg(function ($project) {
                // Ensure tasks are an array or collection
                $tasks = collect($project['tasks']);
                return $tasks->count() > 0 ? $tasks->avg(function ($task) {
                    // Ensure that start_date and due_date are accessible
                    return isset($task['start_date'], $task['due_date'])
                        ? Carbon::parse($task['start_date'])->diffInDays(Carbon::parse($task['due_date']))
                        : 0;
                }) : 0;
            }), 2),
            'total_tasks' => $projects->flatMap(function ($project) {
                return $project->tasks;
            })->count(),
        ];
        return response()->json([
            'projects' => $report,
            'total' => $total,
            'summary' => $summary,
        ]);
    }
    public function exportProjectReport(Request $request)
    {
        $projectsData = $this->getProjectReportData($request)->getData();
        // dd($projectsData);
        $pdf = Pdf::loadView('reports.projects-report-pdf', ['projects' => $projectsData->projects, 'summary' => $projectsData->summary])
            ->setPaper([0, 0, 2000, 900], 'mm');
        return $pdf->download('projects_report.pdf');
    }
    public function showTaskReport()
    {
        $projects = $this->workspace->projects()->pluck('title', 'id');
        $users = $this->workspace->users;
        $clients = $this->workspace->clients;
        $statuses = Status::where('admin_id', getAdminIdByUserRole())
            ->orWhere(function ($query) {
                $query->whereNull('admin_id')
                    ->where('is_default', 1);
            })->get()->pluck('title', 'id');
        $priorities = Priority::where('admin_id', getAdminIdByUserRole())->get()->pluck('title', 'id');
        return view('reports.tasks-report', [
            'workspace' => $this->workspace,
            'projects' => $projects,
            'users' => $users,
            'clients' => $clients,
            'statuses' => $statuses,
            'priorities' => $priorities,
        ]);
    }
    public function getTaskReportData(Request $request)
    {
        // Determine the base query based on user's access level
        $query = isAdminOrHasAllDataAccess() ? $this->workspace->tasks() : $this->user->tasks();
        // Apply filters
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('user_id')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->whereIn('users.id', explode(',', $request->user_id));
            });
        }
        if ($request->filled('client_id')) {
            $query->whereHas('project.clients', function ($q) use ($request) {
                $q->whereIn('clients.id', explode(',', $request->client_id));
            });
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('due_date', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('status_id')) {
            $query->whereIn('status_id', explode(',', $request->status_id));
        }
        if ($request->filled('priority_id')) {
            $query->whereIn('priority_id', explode(',', $request->priority_id));
        }
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhereHas('status', function ($q) use ($searchTerm) {
                        $q->where('title', 'like', $searchTerm);
                    })
                    ->orWhereHas('priority', function ($q) use ($searchTerm) {
                        $q->where('title', 'like', $searchTerm);
                    });
            });
        }
        // Apply sorting
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'desc');
        $query->orderBy($sort, $order);
        // Pagination setup
        $perPage = $request->input('limit', 10);
        $page = $request->input('offset', 0) / $perPage + 1;
        $total = $query->count();
        $tasks = $query->with(['project', 'status', 'priority',  'project.clients'])
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();
        // Transform task data into the desired report format
        $report = $tasks->map(function ($task) {
            $now = now();
            $startDate = Carbon::parse($task->start_date);
            $dueDate = Carbon::parse($task->due_date);
            $daysElapsed = $now->diffInDays($startDate);
            $daysRemaining = $dueDate->isPast() ? 0 : $now->diffInDays($dueDate);
            $overdueDays = $dueDate->isPast() ? (int) $dueDate->diffInDays($now) : 0;

            // Format clients' HTML
            $clientHtml = $task->project->clients->map(function ($client) {
                return "<a href='" . route('clients.profile', ['id' => $client->id]) . "' target='_blank'>
                    <li class='avatar avatar-sm pull-up' title='" . e($client->first_name . " " . $client->last_name) . "'>
                        <img src='" . ($client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' />
                    </li>
                </a>";
            })->implode('');
            // Format users' HTML
            $userHtml = $task->users->map(function ($user) {
                return "<a href='" . route('users.show', ['id' => $user->id]) . "' target='_blank'>
                    <li class='avatar avatar-sm pull-up' title='" . e($user->first_name . " " . $user->last_name) . "'>
                        <img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' class='rounded-circle' />
                    </li>
                </a>";
            })->implode('');
            return [
                'id' => $task->id,
                'title' => Str::limit(ucfirst($task->title), 25, '...'),
                'description' => $task->description,
                'start_date' => $task->start_date,
                'due_date' => $task->due_date,
                'status' => "<span class='badge bg-label-" . e($task->status->color) . "'>" . e($task->status->title) . "</span>",
                'priority' => $task->priority ? "<span class='badge bg-label-" . e($task->priority->color) . "'>" . e($task->priority->title) . "</span>" : '-',
                'project' => $task->project ? $task->project->title : '-',
                'assigned_to' => $task->assignedTo ? $task->assignedTo->first_name . ' ' . $task->assignedTo->last_name : '-',
                'time' => [
                    'days_elapsed' => $daysElapsed,
                    'days_remaining' => $daysRemaining,
                    'overdue_days' => $overdueDays,
                ],
                'users' => $userHtml,
                'clients' => $clientHtml,
                'is_urgent' => $task->priority && $task->priority->title === 'High' && $dueDate->isPast(),
                'created_at' => format_date($task->created_at),
                'updated_at' => format_date($task->updated_at),
            ];
        });
        // Generate summary data
        $summary = [
            'total_tasks' => $total,
            'overdue_tasks' => $report->where('time.overdue_days', '>', 0)->count(),
            'urgent_tasks' => $report->where('is_urgent', true)->count(),
            'average_task_duration' => round($report->avg(function ($task) {
                return Carbon::parse($task['start_date'])->diffInDays(Carbon::parse($task['due_date']));
            }), 2),
        ];
        return response()->json([
            'tasks' => $report,
            'total' => $total,
            'summary' => $summary,
        ]);
    }

    public function exportTaskReport(Request $request)
    {
        $tasksData = $this->getTaskReportData($request)->getData();
        $pdf = Pdf::loadView('reports.tasks-report-pdf', ['tasks' => $tasksData->tasks, 'summary' => $tasksData->summary])
            ->setPaper([0, 0, 2000, 900], 'mm');
        return $pdf->download('tasks_report.pdf');
    }

    public function showInvoicesReport()
    {
        $clients = $this->workspace->clients;
        $invoice_statuses = [
            'sent' => get_label('sent', 'Sent'),
            'accepted' => get_label('accepted', 'Accepted'),
            'partially_paid' => get_label('partially_paid', 'Partially Paid'),
            'fully_paid' => get_label('fully_paid', 'Fully Paid'),
            'draft' => get_label('draft', 'Draft'),
            'declined' => get_label('declined', 'Declined'),
            'expired' => get_label('expired', 'Expired'),
            'not_specified' => get_label('not_specified', 'Not Specified'),
            'due' => get_label('due', 'Due')
        ];
        return view('reports.invoices-report', compact('clients', 'invoice_statuses',));
    }
    public function getInvoicesReportData(Request $request)
    {
        // dd($request);
        // Determine the base query based on user's access level
        $query = EstimatesInvoice::query()
            ->select(
                'estimates_invoices.*',
                DB::raw('CONCAT(clients.first_name, " ", clients.last_name) AS client_name')
            )
            ->leftJoin('clients', 'estimates_invoices.client_id', '=', 'clients.id')
            ->where('estimates_invoices.workspace_id', $this->workspace->id);
        if (!isAdminOrHasAllDataAccess()) {
            $query->where(function ($q) {
                $q->where('estimates_invoices.created_by', isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id)
                    ->orWhere('estimates_invoices.client_id', $this->user->id);
            });
        }
        $query->where('estimates_invoices.type', 'invoice');
        // Apply filters
        if ($request->filled('status_id')) {
            $query->where('estimates_invoices.status', $request->status_id);
        }
        if ($request->filled('client_id')) {
            $query->whereIn('estimates_invoices.client_id', explode(',', $request->client_id));
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('estimates_invoices.from_date', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('estimates_invoices.to_date', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('estimates_invoices.id', 'like', $searchTerm)
                    ->orWhere('estimates_invoices.name', 'like', $searchTerm);
            });
        }
        // Apply sorting
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $query->orderBy($sort, $order);
        // Pagination setup
        $perPage = $request->input('limit', 10);
        $page = $request->input('offset', 0) / $perPage + 1;
        $total = $query->count();
        // Calculate totals
        $totalAmount = $query->sum('total');
        $totalTax = $query->sum('tax_amount');
        $totalFinal = $query->sum('final_total');
        $invoices = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();
        // Transform invoice data into the desired report format
        $report = $invoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'type' => ucfirst($invoice->type),
                'client' => $invoice->client_name,
                'total' => format_currency($invoice->total),
                'tax_amount' => format_currency($invoice->tax_amount),
                'final_total' => format_currency($invoice->final_total),
                'from_date' => format_date($invoice->from_date),
                'to_date' => format_date($invoice->to_date),
                'status' => $this->getStatusBadge($invoice->status),
                'created_by' => $this->getCreatorName($invoice->created_by),
                'created_at' => format_date($invoice->created_at),
                'updated_at' => format_date($invoice->updated_at),
            ];
        });
        // Generate summary data
        $summary = [
            'total_invoices' => $total,
            'total_amount' => format_currency($totalAmount),
            'total_tax' => format_currency($totalTax),
            'total_final' => format_currency($totalFinal),
            'average_invoice_value' => $total > 0 ? format_currency($totalFinal / $total) : format_currency(0),
        ];
        return response()->json([
            'invoices' => $report,
            'total' => $total,
            'summary' => $summary,
        ]);
    }
    private function getStatusBadge($status)
    {
        // Generate status badge HTML based on status
        $badges = [
            'sent' => 'bg-primary',
            'accepted' => 'bg-success',
            'partially_paid' => 'bg-warning',
            'fully_paid' => 'bg-success',
            'draft' => 'bg-secondary',
            'declined' => 'bg-danger',
            'expired' => 'bg-warning',
            'not_specified' => 'bg-secondary',
            'due' => 'bg-danger'
        ];
        return isset($badges[$status]) ? '<span class="badge ' . $badges[$status] . '">' . get_label($status, ucfirst(str_replace('_', ' ', $status))) . '</span>' : '';
    }
    private function getCreatorName($createdBy)
    {
        // Extract creator's name from ID
        $userId = substr($createdBy, 2);
        $user = User::find($userId);
        return $user ? $user->first_name . ' ' . $user->last_name : '-';
    }
    public function exportInvoicesReport(Request $request)
    {
        // dd($this->getInvoicesReportData($request)->getData());
        $invoicesData = $this->getInvoicesReportData($request)->getData();
        $pdf = Pdf::loadView('reports.invoices-report-pdf', ['invoices' => $invoicesData->invoices, 'summary' => $invoicesData->summary])
            ->setPaper([0, 0, 2000, 900], 'mm');
        // dd($pdf);
        return $pdf->download('invoices_report.pdf');
    }
    public function showLeavesReport()
    {
        $users = isAdminOrHasAllDataAccess() ? $this->workspace->users : $this->user;
        return view('reports.leaves-report', ['users' => $users]);
    }
    public function getLeavesReportData(Request $request)
    {
        $search = $request->input('search', '');

        // Determine the users to fetch based on the user's role
        $users = is_admin_or_leave_editor() ? $this->workspace->users() : $this->user;

        // If the user is not an admin or leave editor, merge their ID into the user_ids in the request
        if (!is_admin_or_leave_editor()) {
            $request->merge(['user_ids' => [$this->user->id]]);
        }

        // Filter the users based on the user_ids in the request
        if ($request->filled('user_ids')) {
            $users = $users->whereIn('users.id', $request->user_ids);
        }

        $dateFilterFrom = $request->input('date_between_from');
        $dateFilterTo = $request->input('date_between_to');

        $users = $users->with([
            'leave_requests' => function ($query) use ($dateFilterFrom, $dateFilterTo) {
                if ($dateFilterFrom && $dateFilterTo) {
                    $query->where('from_date', '>=', $dateFilterFrom)
                        ->where('to_date', '<=', $dateFilterTo);
                }
            }
        ])->get();

        // Apply search filter if provided
        if ($search) {
            $users = $users->filter(function ($user) use ($search) {
                return Str::contains(strtolower($user->first_name . ' ' . $user->last_name), strtolower($search))
                    || Str::contains(strtolower($user->email), strtolower($search));
            });
        }

        $report = $users->map(function ($user) use ($request) {
            $leaveRequests = $user->leave_requests;

            // Apply status filter if provided
            if ($request->filled('statuses')) {
                $leaveRequests = $leaveRequests->whereIn('status', $request->statuses);
            }

            $fullLeaves = 0;
            $partialLeaves = 0;
            $approvedHours = 0;
            $approvedDays = 0;
            $pendingHours = 0;
            $pendingDays = 0;
            $rejectedHours = 0;
            $rejectedDays = 0;
            $partialHours = 0;

            foreach ($leaveRequests as $leave_request) {
                $fromDate = Carbon::parse($leave_request->from_date);
                $toDate = Carbon::parse($leave_request->to_date);

                if ($leave_request->from_time && $leave_request->to_time) {
                    // Handle partial leave requests
                    $partialLeaves++;

                    $fromDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $leave_request->from_time);
                    $toDateTime = Carbon::parse($fromDate->toDateString() . ' ' . $leave_request->to_time);

                    $hours = $fromDateTime->diffInMinutes($toDateTime) / 60;

                    if ($leave_request->status === 'approved') {
                        $approvedHours += $hours;
                    } elseif ($leave_request->status === 'pending') {
                        $pendingHours += $hours;
                    } elseif ($leave_request->status === 'rejected') {
                        $rejectedHours += $hours;
                    }
                    $partialHours += $hours;
                } else {
                    // Handle full day leave requests
                    $days = $fromDate->diffInDays($toDate) + 1;

                    if ($leave_request->status === 'approved') {
                        $approvedDays += $days;
                    } elseif ($leave_request->status === 'pending') {
                        $pendingDays += $days;
                    } elseif ($leave_request->status === 'rejected') {
                        $rejectedDays += $days;
                    }

                    $fullLeaves++;
                }
            }

            return [
                'id' => $user->id,
                'user_name' => $this->formatUserHtml($user),
                'total_leaves' => $leaveRequests->count(),
                'approved_leaves' => $leaveRequests->where('status', 'approved')->count(),
                'pending_leaves' => $leaveRequests->where('status', 'pending')->count(),
                'rejected_leaves' => $leaveRequests->where('status', 'rejected')->count(),
                'full_leaves' => $fullLeaves,
                'partial_leaves' => $partialLeaves,
                'approved_hours' => round($approvedHours, 2),
                'approved_days' => $approvedDays,
                'pending_hours' => round($pendingHours, 2),
                'pending_days' => $pendingDays,
                'rejected_hours' => round($rejectedHours, 2),
                'rejected_days' => $rejectedDays,
                'total_hours' => round($approvedHours + $pendingHours + $rejectedHours, 2),
                'total_days' => $approvedDays + $pendingDays + $rejectedDays,
                'total_partial_hours' => round($partialHours, 2),

                // User-wise formatted durations
                'formatted_total_leaves' => $this->formatLeaveDuration($leaveRequests->count(), $approvedDays + $pendingDays + $rejectedDays, round($approvedHours + $pendingHours + $rejectedHours, 2)),
                'formatted_partial_leaves' => $this->formatLeaveDuration($partialLeaves, '', round($partialHours, 2)),
                'formatted_approved_leaves' => $this->formatLeaveDuration($leaveRequests->where('status', 'approved')->count(), $approvedDays, $approvedHours),
                'formatted_pending_leaves' => $this->formatLeaveDuration($leaveRequests->where('status', 'pending')->count(), $pendingDays, $pendingHours),
                'formatted_rejected_leaves' => $this->formatLeaveDuration($leaveRequests->where('status', 'rejected')->count(), $rejectedDays, $rejectedHours)
            ];
        });

        $sort = $request->input('sort', 'user_name');
        $order = $request->input('order', 'asc');

        if ($sort === 'user_name') {
            $report = $report->sortBy(function ($item) {
                return strtolower($item['user_name']);
            }, SORT_NATURAL | SORT_FLAG_CASE);
        } else {
            $report = $report->sortBy($sort);
        }

        if ($order === 'desc') {
            $report = $report->reverse();
        }

        $perPage = $request->input('limit', 10);
        $page = ($request->input('offset', 0) / $perPage) + 1;
        $total = $report->count();

        $paginatedReport = $report->forPage($page, $perPage);

        $summary = [
            'total_leaves' => $report->sum('total_leaves'),
            'total_approved_leaves' => $report->sum('approved_leaves'),
            'total_pending_leaves' => $report->sum('pending_leaves'),
            'total_rejected_leaves' => $report->sum('rejected_leaves'),
            'total_full_leaves' => $report->sum('full_leaves'),
            'total_partial_leaves' => $report->sum('partial_leaves'),
            'total_approved_hours' => $report->sum('approved_hours'),
            'total_approved_days' => $report->sum('approved_days'),
            'total_pending_hours' => $report->sum('pending_hours'),
            'total_pending_days' => $report->sum('pending_days'),
            'total_rejected_hours' => $report->sum('rejected_hours'),
            'total_rejected_days' => $report->sum('rejected_days'),
            'total_hours' => round($report->sum('approved_hours') + $report->sum('pending_hours') + $report->sum('rejected_hours'), 2),
            'total_days' => $report->sum('approved_days') + $report->sum('pending_days') + $report->sum('rejected_days'),
        ];

        // Formatting the duration data before sending it
        $summary['formatted_total_leaves'] = $this->formatLeaveDuration($summary['total_leaves'], $summary['total_days'], $summary['total_hours']);
        $summary['formatted_partial_leaves'] = $this->formatLeaveDuration($summary['total_partial_leaves'], '', $summary['total_hours']);
        $summary['formatted_approved_leaves'] = $this->formatLeaveDuration($summary['total_approved_leaves'], $summary['total_approved_days'], $summary['total_approved_hours']);
        $summary['formatted_pending_leaves'] = $this->formatLeaveDuration($summary['total_pending_leaves'], $summary['total_pending_days'], $summary['total_pending_hours']);
        $summary['formatted_rejected_leaves'] = $this->formatLeaveDuration($summary['total_rejected_leaves'], $summary['total_rejected_days'], $summary['total_rejected_hours']);


        return response()->json([
            'users' => $paginatedReport->values(),
            'total' => $total,
            'summary' => $summary,
        ]);
    }

    public function formatLeaveDuration($totalLeaves, $days, $hours)
    {
        $dayLabel = get_label('day', 'Day');
        $daysLabel = get_label('days', 'Days');
        $hourLabel = get_label('hour', 'Hour');
        $hoursLabel = get_label('hours', 'Hours');

        // If there are no days or hours, return just the total leaves
        if ($days == 0 && $hours == 0) {
            return "{$totalLeaves}";
        }

        // Initialize the formatted string
        $formatted = "{$totalLeaves}";

        // Check if there are any days or hours to include
        $leaveDuration = [];

        // If there are days, format and add them
        if ($days > 0) {
            $leaveDuration[] = "{$days} " . ($days > 1 ? $daysLabel : $dayLabel);
        }

        // If there are hours, format and add them
        if ($hours > 0) {
            $leaveDuration[] = "{$hours} " . ($hours > 1 ? $hoursLabel : $hourLabel);
        }

        // If we have any leave duration to display, append it inside parentheses
        if (!empty($leaveDuration)) {
            $formatted .= " (" . implode(' and ', $leaveDuration) . ")";
        }

        return $formatted;
    }
    private function formatUserHtml($user)
    {
        $profileLink = route('users.show', ['id' => $user->id]);
        $photoUrl = $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg');
        return "<div class='d-flex justify-content-start align-items-center user-name'>
        <div class='avatar-wrapper me-3'>
            <div class='avatar avatar-sm pull-up'>
                <img src='{$photoUrl}' alt='Avatar' class='rounded-circle'>
            </div>
        </div>
        <div class='d-flex flex-column'>
            <a href='{$profileLink}' target='_blank'>
                <span class='fw-semibold'>{$user->first_name} {$user->last_name}</span>
            </a>
            <small class='text-muted'>{$user->email}</small>
        </div>
    </div>";
    }
    public function exportLeavesReport(Request $request)
    {
        $leavesData = $this->getLeavesReportData($request)->getData();
        $pdf = Pdf::loadView('reports.leaves-report-pdf', ['users' => $leavesData->users, 'summary' => $leavesData->summary])
            ->setPaper([0, 0, 2000, 900], 'mm');
        return $pdf->download('leaves_report.pdf');
    }
    public function showIncomeVsExpenseReport(Request $request)
    {
        $reportData = $this->getIncomeVsExpenseReportData($request)->getData();
        // Pass data to view
        return view('reports.income-vs-expense-report', [
            'report' => $reportData,
        ]);
    }
    public function getIncomeVsExpenseReportData(Request $request)
    {
        // Initialize the query for total income from invoices
        $invoicesQuery = EstimatesInvoice::query()
            ->select('id', 'final_total', 'from_date', 'to_date')
            ->where('status', 'fully_paid')
            ->where('type', 'invoice')
            ->where('workspace_id', $this->workspace->id);
        // Apply date filters if provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $invoicesQuery->whereBetween('to_date', [$request->start_date, $request->end_date]);
            $invoicesQuery->orWhereBetween('from_date', [$request->start_date, $request->end_date]);
        }
        // Get detailed income data
        $invoices = $invoicesQuery->get();
        $totalIncome = $invoices->sum('final_total');
        // Initialize the query for total expenses
        $expensesQuery = Expense::query()
            ->select('id', 'title', 'amount', 'expense_date')
            ->where('workspace_id', $this->workspace->id);
        // Apply date filters if provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $expensesQuery->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        }
        // Get detailed expense data
        $expenses = $expensesQuery->get();
        $totalExpenses = $expenses->sum('amount');
        // Calculate profit or loss
        $profitOrLoss = $totalIncome - $totalExpenses;
        // Prepare detailed report data
        $report = [
            'total_income' => format_currency($totalIncome),
            'total_expenses' => format_currency($totalExpenses),
            'profit_or_loss' => format_currency($profitOrLoss),
            'invoices' => $invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    "view_route" => route('estimates-invoices.view', ['id' => $invoice->id]),
                    'amount' => format_currency($invoice->final_total),
                    'to_date' => $invoice->to_date,
                    'from_date' => $invoice->from_date,
                ];
            }),
            'expenses' => $expenses->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'title' => $expense->title,
                    'amount' => format_currency($expense->amount),
                    'expense_date' => $expense->expense_date,
                ];
            }),
        ];
        return response()->json($report);
    }
    public function exportIncomeVsExpenseReport(Request $request)
    {
        $reportData = $this->getIncomeVsExpenseReportData($request)->getData();
        $pdf = Pdf::loadView('reports.income-vs-expense-report-pdf', ['report' => $reportData])
            ->setPaper([0, 0, 2000, 900], 'mm');
        return $pdf->download('income_vs_expense_report.pdf');
    }
    public function showTimeSheetReport()
    {
        $timesheets = TimeTracker::select(
            'time_trackers.*',
            'users.photo as user_photo',
            DB::raw('CONCAT(users.first_name, " ", users.last_name) AS user_name')
        )
            ->leftJoin('users', 'time_trackers.user_id', '=', 'users.id')
            ->where('workspace_id', $this->workspace->id)
            ->orderBy('id', 'DESC')
            ->paginate(10);
        // dd($timesheets);

        return view('reports.time-sheet-report', compact('timesheets'));
    }

    public function showWorkHoursReport()
    {
        $projects = isAdminOrHasAllDataAccess() ? $this->workspace->projects() : $this->user->projects();
        $projects = $projects->where('enable_tasks_time_entries', true)
            ->pluck('title', 'id');
        return view('reports.work-hours-report', [
            'projects' => $projects,
        ]);
    }
    public function getWorkHoursReportData(Request $request)
    {
        $user_ids =  $request->user_id ?? [];
        $project_ids = $request->project_id ?? [];
        // Determine the base query for projects
        $query = isAdminOrHasAllDataAccess() ? $this->workspace->projects() : $this->user->projects();
        $query->where('enable_tasks_time_entries', true);
        if (!isAdminOrHasAllDataAccess()) {
            $query->where('admin_id', getAdminIdByUserRole());
        }
        // Apply filters based on request inputs
        if (count($project_ids)) {
            $query->whereIn('id', $project_ids);  // Filter by selected projects
        }
        // Eager load tasks with time entries and necessary relationships
        $projects = $query->with(['tasks' => function ($taskQuery) use ($request, $user_ids) {
            $taskQuery->with(['timeEntries' => function ($entryQuery) use ($request, $user_ids) {
                $user_ids = array_map(function ($user_id) {
                    return 'u_' . $user_id;  // Add 'u_' prefix to each user ID
                }, $user_ids);  // Process each user ID in the $user_ids array
                // Now you can use $user_ids with the prefix for filtering
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $entryQuery->whereBetween('entry_date', [$request->start_date, $request->end_date]);
                }
                if (count($user_ids)) {
                    $entryQuery->whereIn('user_id', $user_ids);  // Filter by selected users
                }
            }, 'status', 'users']);
        }])->get();
        // Collect the report data
        $report = $projects->flatMap(function ($project) {
            return $project->tasks->flatMap(function ($task) use ($project) {
                return $task->timeEntries->map(function ($timeEntry) use ($task, $project) {
                    return [
                        'id' => $timeEntry->id,
                        'date' => $timeEntry->entry_date,
                        'project' => [
                            'id' => $project->id,
                            'title' => $project->title,
                            'billable' => $project->is_billable,
                        ],
                        'task' => [
                            'id' => $task->id,
                            'title' => $task->title,
                            'status' => [
                                'title' => $task->status->title,
                                'color' => $task->status->color,
                            ],
                            'progress' => $task->progress,
                        ],
                        'user' => [
                            'id' => $timeEntry->user_id,
                            'name' => $this->formatUserHtml($task->users->firstWhere('id', substr($timeEntry->user_id, 2))),
                        ],
                        'time_entry' => [
                            'type' => ucfirst($timeEntry->entry_type),
                            'hours' => $this->formatDuration($timeEntry),
                            'start_time' => $timeEntry->start_time,
                            'end_time' => $timeEntry->end_time,
                        ],
                        'hours' => [
                            'total' => $this->calculateTotalHours($timeEntry),
                            'is_billable' => $timeEntry->is_billable ? get_label('yes', 'Yes') : get_label('no', 'No'),
                            'is_billable_boolean' => $timeEntry->is_billable,
                        ],
                        'description' => $timeEntry->description,
                    ];
                });
            });
        });
        // Apply optional search filter
        if ($request->filled('search')) {
            $searchTerm = strtolower($request->search);
            $report = $report->filter(function ($entry) use ($searchTerm) {
                return str_contains(strtolower($entry['project']['title']), $searchTerm) ||
                    str_contains(strtolower($entry['task']['title']), $searchTerm) ||
                    str_contains(strtolower($entry['user']['name'] ?? ''), $searchTerm);
            });
        }
        // Sort report
        $sort = $request->input('sort', 'date');
        $order = $request->input('order', 'desc');
        $report = $report->sortBy($sort, SORT_REGULAR, $order === 'desc');
        // Paginate report
        $perPage = $request->input('limit', 10);
        $page = $request->input('offset', 0) / $perPage + 1;
        $paginatedReport = $report->forPage($page, $perPage);
        return response()->json([
            'report' => $paginatedReport->values(),
            'summary' => $this->generateReportSummary($report),
            'total' => $report->count(),
        ]);
    }

    public function getTimeSheetReportData(Request $request)
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $user_id = (request('user_id')) ? request('user_id') : "";
        $project_id = (request('project_id')) ? request('project_id') : "";

        $start_date_from = (request('start_date_from')) ? request('start_date_from') : "";
        $start_date_to = (request('start_date_to')) ? request('start_date_to') : "";
        $end_date_from = (request('end_date_from')) ? request('end_date_from') : "";
        $end_date_to = (request('end_date_to')) ? request('end_date_to') : "";

        $timesheetQuery = TimeTracker::with(['project', 'task', 'user'])
            ->select('time_trackers.*', 'users.photo as user_photo', DB::raw('CONCAT(users.first_name, " ", users.last_name) AS user_name'))
            ->leftJoin('users', 'time_trackers.user_id', '=', 'users.id')
            ->where('time_trackers.workspace_id', $this->workspace->id);

        if (!isAdminOrHasAllDataAccess()) {
            $timesheetQuery->where('user_id', $this->user->id);
        }

        if ($start_date_from) {
            $start_date_from = Carbon::parse($start_date_from)->startOfDay()->toDateTimeString();
            $timesheetQuery->where('start_date_time', '>=', $start_date_from);
        }

        if ($start_date_to) {
            $start_date_to = Carbon::parse($start_date_to)->endOfDay()->toDateTimeString();
            $timesheetQuery->where('start_date_time', '<=', $start_date_to);
        }

        if ($end_date_from && $end_date_to) {
            $end_date_from = $end_date_from . ' 00:00:00';
            $end_date_to = $end_date_to . ' 23:59:59';
            $timesheetQuery->whereBetween('end_date_time', [$end_date_from, $end_date_to]);
        }

        if ($user_id) {
            $timesheetQuery->where('user_id', $user_id);
        }
        if ($project_id) {
            $timesheetQuery->where('project_id', $project_id);
        }

        if ($search) {
            $timesheetQuery->where(function ($query) use ($search) {

                $query->where('message', 'like', '%' . $search . '%');
            });
        }

        $total = $timesheetQuery->count();

        $projectQuery = clone $timesheetQuery;

        // Calculate Summary Data
        $summary = [
            'total_seconds' => $timesheetQuery->sum(DB::raw("TIMESTAMPDIFF(SECOND, start_date_time, end_date_time)")),
            'total_tasks' => $timesheetQuery->distinct('task_id')->count('task_id'),
            'total_projects' => $timesheetQuery->distinct('project_id')->count('project_id'),
            'total_users' => $timesheetQuery->distinct('user_id')->count('user_id'),
            // New field for project-specific total seconds
            'project_total_seconds' => $projectQuery
                ->whereNotNull('project_id')
                ->sum(DB::raw("TIMESTAMPDIFF(SECOND, start_date_time, end_date_time)")),
        ];

        // Convert total seconds into hours and minutes (overall)
        $total_hours = floor($summary['total_seconds'] / 3600);
        $total_minutes = floor(($summary['total_seconds'] % 3600) / 60);
        $summary['total_hours'] = "{$total_hours} hours {$total_minutes} minutes";

        // Convert project-specific total seconds into hours and minutes
        $project_total_hours = floor($summary['project_total_seconds'] / 3600);
        $project_total_minutes = floor(($summary['project_total_seconds'] % 3600) / 60);
        $summary['project_total_hours'] = "{$project_total_hours} hours {$project_total_minutes} minutes";

        $timesheet = $timesheetQuery->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($timesheet) {
                $formattedDuration = '-';
                if ($timesheet->end_date_time) {
                    $startDateTime = Carbon::parse($timesheet->start_date_time);
                    $endDateTime = Carbon::parse($timesheet->end_date_time);

                    $duration = $endDateTime->diff($startDateTime);

                    if ($duration->days > 0) {
                        $formattedDuration = $duration->format('%D days %H:%I:%S');
                    } else {
                        $formattedDuration = $duration->format('%H:%I:%S');
                    }
                }

                return [
                    'id' => $timesheet->id,
                    'user_name' => $timesheet->user ? $timesheet->user->first_name . ' ' . $timesheet->user->last_name : '-',
                    'photo' => $timesheet->user ? "<div class='avatar avatar-md pull-up' title='" . $timesheet->user_name . "'>
                <a href='" . route('users.show', [$timesheet->user_id]) . "' target='_blank'>
                <img src='" . ($timesheet->user->photo ? asset('storage/' . $timesheet->user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle'>
                </a>
                </div>" : '-',
                    'date' => format_date($timesheet->start_date_time, 'Y-m-d'),
                    'project' => $timesheet->project
                        ? "<a href='" . route('projects.info', ['id' => $timesheet->project->id]) . "' target='_blank'>" . $timesheet->project->title . "</a>"
                        : '-',
                    'task' => $timesheet->task
                        ? "<a href='" . route('tasks.info', ['id' => $timesheet->task->id]) . "' target='_blank'>" . $timesheet->task->title . "</a>"
                        : '-',
                    'start_date_time' => format_date($timesheet->start_date_time, 'H:i:s'),
                    'end_date_time' =>  format_date($timesheet->end_date_time, 'H:i:s'),
                    'duration' => $formattedDuration,
                    'message' => $timesheet->message ?? '-',
                    'is_billable' => $timesheet->is_billable ? 'Yes' : 'No',
                ];
            });

        return response()->json([
            "rows" => $timesheet->items(),
            "total" => $total,
            "summary" => $summary,
        ]);
    }



    private function calculateTotalHours($timeEntry)
    {
        // Calculate total hours for time entry
        if ($timeEntry->standard_hours) {
            return $timeEntry->standard_hours;
        }
        if ($timeEntry->start_time && $timeEntry->end_time) {
            $start = Carbon::parse($timeEntry->start_time);
            $end = Carbon::parse($timeEntry->end_time);
            return $end->diffInMinutes($start) / 60;
        }
        return 0;
    }
    private function formatDuration($timeEntry)
    {
        // Format the time duration for flexible entries
        if ($timeEntry->entry_type == 'flexible') {
            $start = Carbon::createFromFormat('H:i:s', $timeEntry->start_time);
            $end = Carbon::createFromFormat('H:i:s', $timeEntry->end_time);
            if ($end < $start) {
                $end->addDay();
            }
            return $start->diff($end)->format('%h hours %i minutes');
        }
        // Handle standard hours in HH:MM:SS format
        if ($timeEntry->standard_hours) {
            $timeParts = explode(':', $timeEntry->standard_hours);
            $hours = intval($timeParts[0]);
            $minutes = isset($timeParts[1]) ? intval($timeParts[1]) : 0;
            return "{$hours} hours {$minutes} minutes";
        }
        return "0 hours 0 minutes";
    }
    public function generateReportSummary($report)
    {
        $totalHours = 0;
        $billableHours = 0;
        $nonBillableHours = 0;
        foreach ($report as $entry) {
            // dd($entry['hours']['total']);
            // Convert hours to decimal (to calculate the total time)
            $hoursDecimal = $this->convertHoursToDecimal($entry['hours']['total']);
            // Add to total hours
            $totalHours += $hoursDecimal;
            // Add to billable or non-billable hours
            if ($entry['hours']['is_billable_boolean']) {  // Billable
                $billableHours += $hoursDecimal;
            } else {  // Non-billable
                $nonBillableHours += $hoursDecimal;
            }
        }
        // Return summary
        return [
            'total_hours' => $this->formatDecimalToTime($totalHours),
            'billable_hours' => $this->formatDecimalToTime($billableHours),
            'non_billable_hours' => $this->formatDecimalToTime($nonBillableHours),
            'total_projects' => $report->pluck('project.id')->unique()->count(),
            'total_tasks' => $report->pluck('task.id')->unique()->count(),
            'total_users' => $report->pluck('user.id')->unique()->count(),
        ];
    }
    public function generateTimeSheetReportSummary($report)
    {
        return [
            'total_projects' => $report->pluck('project.id')->unique()->count(),
            'total_tasks' => $report->pluck('task.id')->unique()->count(),
            'total_users' => $report->pluck('user.id')->unique()->count(),
        ];
    }

    private function convertHoursToDecimal($time)
    {
        // Split the time string into parts (hours, minutes, seconds)
        $timeParts = explode(':', $time);
        // Check if the time is in valid format (HH:MM:SS or HH:MM)
        if (count($timeParts) == 1) {
            // If only one part (HH), assume minutes and seconds are zero
            $hours = (float)$timeParts[0];
            $minutes = 0;
            $seconds = 0;
        } elseif (count($timeParts) == 2) {
            // If two parts (HH:MM), assume seconds are zero
            $hours = (float)$timeParts[0];
            $minutes = (float)$timeParts[1];
            $seconds = 0;
        } elseif (count($timeParts) == 3) {
            // If three parts (HH:MM:SS), proceed normally
            $hours = (float)$timeParts[0];
            $minutes = (float)$timeParts[1];
            $seconds = (float)$timeParts[2];
        } else {
            // Handle invalid time format
            return 0;
        }
        // Convert the time to decimal hours
        return $hours + ($minutes / 60) + ($seconds / 3600);
    }
    private function formatDecimalToTime($decimal)
    {
        $hours = floor($decimal);
        $minutes = round(($decimal - $hours) * 60);
        return "{$hours} hours {$minutes} minutes";
    }
    public function exportWorkHoursReport(Request $request)
    {
        $workHoursData = $this->getWorkHoursReportData($request)->getData();
        $pdf = Pdf::loadView('reports.work-hours-report-pdf', [
            'report' => $workHoursData->report,
            'summary' => $workHoursData->summary
        ])->setPaper([0, 0, 2000, 900], 'mm');
        return $pdf->download('work_hours_report.pdf');
    }

    public function exportTimeSheetReport(Request $request)
    {
        $timeSheetData = $this->getTimeSheetReportData($request)->getData();

        $pdf = Pdf::loadView('reports.time-sheet-report-pdf', [
            'report' => $timeSheetData->rows,
            'summary' => $timeSheetData->summary
        ])->setPaper([0, 0, 2000, 900], 'mm');

        return $pdf->download('time_sheet_report.pdf');
    }
}
