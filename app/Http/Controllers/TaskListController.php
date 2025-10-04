<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\TaskList;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Support\Facades\Log;

class TaskListController extends Controller
{
    public function index()
    {
        $taskLists = TaskList::all();
        return view('task_lists.index', compact('taskLists'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|integer|exists:projects,id',

        ]);
        try {
            $taskList = TaskList::create([
                'name' => $validated['name'],
                'project_id' => $validated['project_id'],
                'admin_id' => getAdminIdByUserRole(), //
            ]);
            return response()->json(['error' => false, 'message' => 'Task list created successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => 'Task list couldn\'t created.']);
        }
    }

    public function get($id)
    {
        $task_list = TaskList::with('project')->find($id);
        if (!$task_list) {
            return response()->json(['error' => true, 'message' => 'Task list not found.']);
        }
        return response()->json(['error' => false, 'task_list' => $task_list]);
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|exists:task_lists,id',
            'name' => 'required|string|max:255',
        ]);
        try {

            $taskList = TaskList::findOrFail($validatedData['id']);
            $taskList->update([
                'name' => $validatedData['name'],
            ]);
            return response()->json(['error' => false, 'message' => 'Task list updated successfully']);
        } catch (Exception $e) {
            Log::error('Error updating task list: Id ' . $validatedData['id'] . ': ' . $e->getMessage());
            return response()->json(['error' => true, 'message' => 'Task lists Couldn\'t be Updated']);
        }

    }

    public function destroy($id)
    {
        $taskList = TaskList::findOrFail($id);
        $taskList->tasks()->update(['task_list_id' => null]);
        $response = DeletionService::delete(TaskList::class, $id, 'TaskList');

        return $response;
    }
    public function list()
    {
        $search = request('search');
        $sort = request('sort', "id");
        $order = request('order', "DESC");
        $limit = request('limit', 10);

        $task_lists = TaskList::orderBy($sort, $order);
        $adminId = getAdminIdByUserRole();
        $task_lists->where('admin_id', $adminId);

        if ($search) {
            $task_lists->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhereHas('project', function ($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%');
                    });
            });
        }

        $total = $task_lists->count();

        $task_lists = $task_lists
            ->paginate($limit)
            ->through(
                fn($task_list) => [
                    'id' => $task_list->id,
                    'name' => ucwords($task_list->name),
                    'project' => ucwords($task_list->project->title),
                    'created_at' => format_date($task_list->created_at),
                    'updated_at' => format_date($task_list->updated_at),
                    'actions' => $this->getActions($task_list),
                ]
            );

        return response()->json([
            "rows" => $task_lists->items(),
            "total" => $total,
        ]);
    }

    private function getActions($task_list)
    {
        $actions = '';
        $canEdit = true;  // Replace with your actual condition
        $canDelete = true; // Replace with your actual condition


        if ($canEdit) {
            $actions .= '<a href="javascript:void(0);" class="edit-task-list" data-id="' . $task_list->id . '" title="' . get_label('update', 'Update') . '">' .
                '<i class="bx bx-edit mx-1"></i>' .
                '</a>';
        }

        if ($canDelete) {
            $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $task_list->id . '" data-type="task-lists" data-table="table">' .
                '<i class="bx bx-trash text-danger mx-1"></i>' .
                '</button>';
        }



        return $actions ?: '-';
    }
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:task_lists,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $taskList = TaskList::findOrFail($id);
            $taskList->tasks()->update(['task_list_id' => null]);
            $deletedIds[] = $id;
            $deletedTitles[] = $taskList->title;
            DeletionService::delete(TaskList::class, $id, 'TaskList');

        }
        return response()->json(['error' => false, 'message' => 'Task List(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles]);
    }

public function search(Request $request)
{
    $query = TaskList::query();
    
    // If filtering by project
    if ($request->has('project_id') && $request->project_id) {
        $query->where('project_id', $request->project_id);
    }
    
    // Search functionality
    if ($request->has('q') && $request->q) {
        $query->where('name', 'like', '%' . $request->q . '%');
    }
    
    $page = $request->get('page', 1);
    $perPage = 20;
    
    $taskLists = $query->paginate($perPage, ['*'], 'page', $page);
    
    $items = $taskLists->map(function ($taskList) {
        return [
            'id' => $taskList->id,
            'text' => $taskList->name
        ];
    });
    
    return response()->json([
        'items' => $items,
        'pagination' => [
            'more' => $taskLists->hasMorePages()
        ]
    ]);
}
}