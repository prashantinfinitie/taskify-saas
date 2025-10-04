<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use Ramsey\Uuid\Type\Time;
use Illuminate\Http\Request;
use App\Models\TaskTimeEntry as TaskTimeEntryModel;
use App\Services\DeletionService;
use Illuminate\Support\Facades\Log;

class TaskTimeEntry extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function list($id, Request $request)
    {
        // Fetch request parameters
        $search = $request->input('search', '');
        $order = $request->input('order', 'DESC');
        $sort = $request->input('sort', 'entry_date');
        $limit = $request->input('limit', 10);

        // Find the task and its time entries
        $task = Task::findOrFail($id); // Fail if task not found
        $entries = $task->timeEntries()->orderBy($sort, $order);

        // Apply search filter
        if ($search) {
            $entries->where(function ($query) use ($search) {
                $query->where('entry_date', 'like', '%' . $search . '%')
                    ->orWhere('entry_type', 'like', '%' . $search . '%')
                    ->orWhere('standard_hours', 'like', '%' . $search . '%')
                    ->orWhere('start_time', 'like', '%' . $search . '%')
                    ->orWhere('end_time', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Calculate total duration for all filtered entries
        $totalMinutes = 0;
        $allEntries = clone $entries;
        foreach ($allEntries->get() as $entry) {
            if ($entry->entry_type == 'flexible') {
                $start = Carbon::createFromFormat('H:i:s', $entry->start_time);
                $end = Carbon::createFromFormat('H:i:s', $entry->end_time);
                if ($end < $start) {
                    $end->addDay();
                }
                $totalMinutes += $start->diffInMinutes($end);
            } else {
                $totalMinutes += floatval($entry->standard_hours) * 60;
            }
        }

        // Convert total minutes to hours and minutes
        $totalHours = floor($totalMinutes / 60);
        $remainingMinutes = $totalMinutes % 60;
        // Paginate results
        $entries = $entries->paginate($limit);

        // Transform entries for JSON response
        $entries->transform(function ($entry) {
            $userIdAndType = $entry->user_type();
            if ($userIdAndType['type'] == 'user') {
                $user = User::find($userIdAndType['id']);
                if (!$user) {
                    Log::warning("User not found with ID: " . $userIdAndType['id']);
                }
            } elseif ($userIdAndType['type'] == 'client') {
                $user = Client::find($userIdAndType['id']);
                if (!$user) {
                    Log::warning("Client not found with ID: " . $userIdAndType['id']);
                }
            } else {
                $user = null;
                Log::warning("Invalid user type: " . $userIdAndType['type']);
            }
            $userHtml = "<ul class='list-unstyled users-list m-0 avatar-group d-flex align-items-center'><li class='avatar avatar-sm pull-up'><a href='" . route('users.show', ['id' => $user->id]) . "' target='_blank' title='{$user->first_name} {$user->last_name}'><img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle ' /></a></li></ul>";
            $totalDuration = '';
            if ($entry->entry_type == 'flexible') {
                $start = Carbon::createFromFormat('H:i:s', $entry->start_time);
                $end = Carbon::createFromFormat('H:i:s', $entry->end_time);

                // Handle cases that cross midnight
                if (
                    $end < $start
                ) {
                    $end->addDay();
                }

                // Calculate duration in minutes
                $totalDuration = $start->diff($end)->format('%h hours %i minutes');
            } else {
                // Handle standard hours in HH:MM:SS format
                $timeParts = explode(':', $entry->standard_hours);

                // Extract hours and minutes
                $hours = intval($timeParts[0]);
                $minutes = isset($timeParts[1]) ? intval($timeParts[1]) : 0;

                // Format the result
                $totalDuration = "{$hours} hours {$minutes} minutes";
            }
            $action = '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $entry->id . '" data-type="tasks/time-entries" data-table="task-time-entries">' .
            '<i class="bx bx-trash text-danger mx-1"></i>' .
            '</button>';
            return [
                'id' => $entry->id,
                'entry_date' => format_date($entry->entry_date),
                'entry_type' => ucfirst($entry->entry_type), // Capitalize
                'standard_hours' => $entry->standard_hours,
                'start_time' => $entry->start_time,
                'end_time' => $entry->end_time,
                'user' => $userHtml,
                'total_duration' => $totalDuration,
                'description' => $entry->description,
                'is_billable' => $entry->is_billable ? 'Yes' : 'No', // Convert boolean to human-readable
                'created_at' => format_date($entry->created_at),
                'updated_at' => format_date($entry->updated_at),
                'actions' => $action
            ];
        });



        // Return JSON response
        return response()->json([

            'rows' => $entries->items(), // Data rows
            'total' => $entries->total(), // Total entries
            'totalDuration' => [
                'hours' => $totalHours,
                'minutes' => $remainingMinutes
            ]
        ]);
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
        $formFields = $request->validate([
            'task_id' => 'required',
            'entry_date' => 'required',
            'entry_type' => 'required|in:standard,flexible',
            'standard_hours' => 'required_if:entry_type,standard',
            'start_time' => 'required_if:entry_type,flexible',
            'end_time' => 'required_if:entry_type,flexible',
            'description' => 'nullable',
            'is_billable' => 'required',


        ]);
        $formFields['workspace_id'] = session()->get('workspace_id');
        $formFields['user_id'] = getAuthenticatedUser(true, true);
        $formFields['entry_date'] = format_date($formFields['entry_date'], false, app('php_date_format'), 'Y-m-d');
        $task = Task::find($formFields['task_id']);
        $task->timeEntries()->create($formFields);
        return response()->json(['error' => false, 'message' => 'Task time entry created successfully.']);
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        DeletionService::delete(TaskTimeEntryModel::class, $id, 'Task Time Entry');
        return response()->json(['error' => false, 'message' => 'Task time entry deleted successfully.']);
    }
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:task_time_entries,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $deletedIds = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $deletedIds[] = $id;
            DeletionService::delete(TaskTimeEntryModel::class, $id, 'Task Time Entry');
        }
        return response()->json(['error' => false, 'message' => 'Task time entry(s) deleted successfully.', 'id' => $deletedIds, 'type' => 'task_time_entry']);
    }
}
