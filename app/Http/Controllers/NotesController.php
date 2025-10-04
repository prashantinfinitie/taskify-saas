<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Services\DeletionService;
use Illuminate\Validation\ValidationException;

class NotesController extends Controller
{
  protected $workspace;
protected $user;

public function __construct()
{
    $this->middleware(function ($request, $next) {
        // Prefer workspace_id from header, fallback to session
        $workspaceId = $request->header('workspace-id') ?? session()->get('workspace_id');
        $this->workspace = Workspace::find($workspaceId);
        $this->user = getAuthenticatedUser();
        return $next($request);
    });
}


    public function index()
    {
        $notes = $this->user->notes();
        return view('notes.list', ['notes' => $notes]);
    }


    /**
 * Create a new note.
 *
 * This endpoint allows you to create a new note of type `text` or `drawing`. If the note type is `drawing`,
 * you must provide valid `drawing_data` in base64-encoded SVG format. The note is associated with the current
 * workspace and the authenticated user (either client or user).
 *
 * @group note Managemant
 *
 * @bodyParam note_type string required The type of note. Must be either `text` or `drawing`. Example: text
 * @bodyParam title string required The title of the note. Example: Project Kickoff Notes
 * @bodyParam color string required Color code or name for the note. Example: #ffcc00
 * @bodyParam description string The description or body content of the note (required for text notes). Example: Discussed project milestones and timelines.
 * @bodyParam drawing_data string The base64-encoded SVG content (required if note_type is `drawing`). Example: PHN2ZyB...
 *  
 * @header workspace_id 2
 * @response 200 {
 *   "error": false,
 *   "message": "Note created successfully.",
 *   "data": {
 *     "id": 12,
 *     "data": {
 *       "id": 12,
 *       "title": "Project Kickoff Notes",
 *       "note_type": "text",
 *       "color": "#ffcc00",
 *       "description": "Discussed project milestones and timelines.",
 *       "creator_id": "u_1",
 *       "workspace_id": 3,
 *       ...
 *     }
 *   }
 * }
 *
 * @response 422 {
 *   "error": true,
 *   "message": "The given data was invalid.",
 *   "errors": {
 *     "note_type": ["The note type field is required."],
 *     "title": ["The title field is required."]
 *   }
 * }
 *
 * @response 500 {
 *   "error": true,
 *   "message": "An error occurred while creating the note.",
 *   "data": {
 *     "error": "Exception message here"
 *   }
 * }
 */
    public function store(Request $request)
{   

    $isApi = $request->get('isApi', false);
    $adminId = getAdminIdByUserRole();

    try {
        $formFields = $request->validate([
            'note_type' => ['required', 'in:text,drawing'],
            'title' => ['required'],
            'color' => ['required'],
            'description' => ['nullable'],
            'drawing_data' => ['nullable', 'string', 'required_if:note_type,drawing']
        ]);

        $drawingData = $request->input('drawing_data');
        $decodedSvg = $drawingData ? base64_decode($drawingData) : null;
        
        $formFields['drawing_data'] = $decodedSvg;
        $formFields['workspace_id'] = $this->workspace->id;
        $formFields['admin_id'] = $adminId;
        $formFields['creator_id'] = isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id;

        $note = Note::create($formFields);

        if (!$note) {
            return formatApiResponse(true, 'Note could not be created.', [], 500);
        }

        Session::flash('message', 'Note created successfully.');

        return formatApiResponse(false, 'Note created successfully.', [
            'id' => $note->id,
            'data' => formatNote($note),
        ]);
    } catch (ValidationException $e) {
        return formatApiValidationError($isApi, $e->errors());
    } catch (\Exception $e) {
        return formatApiResponse(true, 'An error occurred while creating the note.', ['error' => $e->getMessage()], 500);
    }
}

/**
 * Update an existing note.
 *
 * Updates the note identified by `id` with the provided data.
 * Supports notes of type `text` or `drawing`. For `drawing` type,
 * the `drawing_data` must be a base64-encoded string, which will be decoded.
 *
 * @group note Managemant
 *
 * @bodyParam id integer required The ID of the note to update. Example: 12
 * @bodyParam note_type string required The type of note, either `text` or `drawing`. Example: text
 * @bodyParam title string required The title of the note. Example: Meeting notes
 * @bodyParam color string required The color associated with the note. Example: #FF5733
 * @bodyParam description string nullable Optional description for the note. Example: Detailed notes from the meeting
 * @bodyParam drawing_data string required_if:note_type,drawing Base64-encoded SVG data for drawing notes.
 *
 * @response 200 {
 *    "error": false,
 *    "message": "Note updated successfully.",
 *    "data": {
 *       "id": 12,
 *       "data": {
 *          "id": 12,
 *          "note_type": "text",
 *          "title": "Meeting notes",
 *          "color": "#FF5733",
 *          "description": "Detailed notes from the meeting",
 *          "drawing_data": null,
 *          // ... other formatted note fields
 *       }
 *    }
 * }
 *
 * @response 422 {
 *    "error": true,
 *    "message": "The given data was invalid.",
 *    "errors": {
 *       "title": ["The title field is required."],
 *       "note_type": ["The selected note type is invalid."]
 *    }
 * }
 *
 * @response 500 {
 *    "error": true,
 *    "message": "An error occurred while updating the note.",
 *    "error": "Detailed error message here"
 * }
 */

  public function api_update(Request $request)
{
    $isApi = $request->get('isApi', false);

    try {
        $formFields = $request->validate([
            'note_type' => ['required', 'in:text,drawing'],
            'id' => ['required', 'exists:notes,id'],
            'title' => ['required'],
            'color' => ['required'],
            'description' => ['nullable'],
            'drawing_data' => ['nullable', 'string', 'required_if:note_type,drawing'],
        ]);

        // Store base64 string directly (do not decode!)
        $formFields['drawing_data'] = $request->input('drawing_data');

        $note = Note::findOrFail($formFields['id']);

        if ($note->update($formFields)) {
            Session::flash('message', 'Note updated successfully.');

            return formatApiResponse(
                false,
                'Note updated successfully.',
                [
                    'id' => $note->id,
                    'data' => formatNote($note)
                ]
            );
        } else {
            return formatApiResponse(true, "Note couldn't be updated.", [], 500);
        }
    } catch (ValidationException $e) {
        return formatApiValidationError($isApi, $e->errors());
    } catch (\Exception $e) {
        return formatApiResponse(true, 'An error occurred while updating the note.', ['error' => $e->getMessage()], 500);
    }
}
public function update(Request $request)
    {
        $formFields = $request->validate([
            'note_type' => ['required', 'in:text,drawing'],
            'id' => ['required'],
            'title' => ['required'],
            'color' => ['required'],
            'description' => ['nullable'],
            'drawing_data' => ['nullable', 'string', 'required_if:note_type,drawing']
        ]);
        $drawingData = $request->input('drawing_data');

        if ($drawingData) {
            // Simply decode the base64 data without additional URL decoding
            $decodedSvg = base64_decode($drawingData);
        } else {
            $decodedSvg = null;
        }

        $formFields['drawing_data'] = $decodedSvg;

        $note = Note::findOrFail($request->id);

        if ($note->update($formFields)) {
            Session::flash('message', 'Note updated successfully.');
            return response()->json(['error' => false, 'id' => $note->id]);
        } else {
            return response()->json(['error' => true, 'message' => 'Note couldn\'t updated.']);
        }
    }

    public function get($id)
    {
        $note = Note::findOrFail($id);
        return response()->json(['note' => $note]);
    }




/**
 * Delete a Note
 *@group note Managemant
 * This endpoint allows you to delete a note by its ID.
 * It performs a lookup for the note, deletes it using the DeletionService,
 * and returns a formatted API response.
 *
 * @urlParam id integer required The ID of the note to delete. Example: 7
 *
 * @response 200 {
 *   "success": true,
 *   "message": "Note deleted successfully.",
 *   "data": {
 *     "id": 7,
 *     "title": "Project Kickoff Notes",
 *     "content": "Initial project meeting details...",
 *     "user_id": 3,
 *     "workspace_id": 2,
 *     "created_at": "2024-05-24T08:12:54.000000Z",
 *     "updated_at": "2024-06-01T10:20:41.000000Z"
 *   }
 * }
 *
 * @response 404 {
 *   "success": false,
 *   "message": "No query results for model [App\\Models\\Note] 99",
 *   "data": []
 * }
 *
 * @response 500 {
 *   "success": false,
 *   "message": "Something went wrong while deleting the note.",
 *   "data": []
 * }
 */
  public function api_destroy($id)
{
    $isApi = request()->get('isApi', true); // default to API mode

    try {
        $note = Note::findOrFail($id);

        $response = DeletionService::delete(Note::class, $id, 'Note');

        if ($isApi) {
            return formatApiResponse(
                true,
                'Note deleted successfully.',
                ['data' => []]
            );
        }

        return $response;
    } catch (\Exception $e) {
        if ($isApi) {
            return formatApiResponse(false, $e->getMessage(), [], 500);
        }

        return back()->with('error', $e->getMessage());
    }
}


 public function destroy($id)
    {
        $response = DeletionService::delete(Note::class, $id, 'Note');
        return $response;
    }


    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:notes,id' // Ensure each ID in 'ids' is an integer and exists in the notes table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];

        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $note = Note::findOrFail($id);
            // Add any additional logic you need here, such as updating related data
            $deletedIds[] = $id;
            $deletedTitles[] = $note->title; // Assuming 'title' is a field in the notes table
            DeletionService::delete(Note::class, $id, 'Note');
        }
        Session::flash('message', 'Note(s) deleted successfully.');
        return response()->json([
            'error' => false,
            'message' => 'Note(s) deleted successfully.',
            'id' => $deletedIds,
            'titles' => $deletedTitles
        ]);
    }
    /**
 * @group note Managemant
 *
 * Get All Notes or a Specific Note
 *
 * This endpoint retrieves either:
 * - A list of all notes (if no ID is provided), or
 * - A single note by its ID (if provided).
 *
 * Notes are filtered by the current workspace and admin context.
 *
 * @urlParam id integer optional The ID of the note to retrieve. Example: 3
 *
 * @response 200 {
 *   "success": true,
 *   "message": "Notes retrieved successfully.",
 *   "data": {
 *     "total": 2,
 *     "data": [
 *       {
 *         "id": 1,
 *         "title": "Sprint Planning",
 *         "note_type": "text",
 *         "color": "#ffffff",
 *         "workspace_id": 1,
 *         "admin_id": 1,
 *         "creator_id": "u_3",
 *         "created_at": "2025-06-01T12:00:00.000000Z",
 *         "updated_at": "2025-06-01T12:30:00.000000Z"
 *       },
 *       {
 *         "id": 2,
 *         "title": "UI Wireframe",
 *         "note_type": "drawing",
 *         "color": "#000000",
 *         "workspace_id": 1,
 *         "admin_id": 1,
 *         "creator_id": "u_3",
 *         "created_at": "2025-06-02T08:45:00.000000Z",
 *         "updated_at": "2025-06-02T09:15:00.000000Z"
 *       }
 *     ]
 *   }
 * }
 *
 * @response 200 {
 *   "success": true,
 *   "message": "Note retrieved successfully.",
 *   "data": {
 *     "total": 1,
 *     "data": [
 *       {
 *         "id": 3,
 *         "title": "Design Plan",
 *         "note_type": "drawing",
 *         "color": "#ffdd00",
 *         "workspace_id": 1,
 *         "admin_id": 1,
 *         "creator_id": "u_5",
 *         "created_at": "2025-06-03T10:00:00.000000Z",
 *         "updated_at": "2025-06-03T10:30:00.000000Z"
 *       }
 *     ]
 *   }
 * }
 *
 * @response 404 {
 *   "success": false,
 *   "message": "Note not found.",
 *   "data": []
 * }
 *
 * @response 500 {
 *   "success": false,
 *   "message": "Failed to retrieve notes.",
 *   "data": {
 *     "error": "SQLSTATE[42S02]: Base table or view not found: 1146 Table 'notes' doesn't exist"
 *   }
 * }
 */

    public function apilist($id = null)
{
    $isApi = request()->get('isApi', true); // default to API
    $adminId = getAdminIdByUserRole();

    try {
        $query = Note::query()
            ->where('workspace_id', $this->workspace->id)
            ->where('admin_id', $adminId);
        
        $per_page = request('per_page', 10);

        // Fetch specific note
        if ($id) {
            $note = $query->where('id', $id)->first();

            if (!$note) {
                return formatApiResponse(false, 'Note not found.', [], 404);
            }

            return formatApiResponse(true, 'Note retrieved successfully.', [
                'total' => 1,
                'data' => [formatNote($note)]
            ]);
        }

        // Fetch all notes
        $notes = $query->latest()->paginate($per_page);

        return formatApiResponse(true, 'Notes retrieved successfully.', [
            'total' => $notes->total(), 
            'data' => $notes->map(fn($note) => formatNote($note))->toArray()
        ]);
    } catch (\Exception $e) {
        return formatApiResponse(false, 'Failed to retrieve notes.', [
            'error' => $e->getMessage()
        ], 500);
    }
}
}
