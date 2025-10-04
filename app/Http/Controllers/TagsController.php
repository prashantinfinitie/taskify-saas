<?php

namespace App\Http\Controllers;
use App\Models\Tag;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class TagsController extends Controller
{
    public function index()
    {
        return view('tags.list');
    }
      /**
     * Store a newly created tag in the database.
     * 
     * This API endpoint creates a new tag with the given title and color.
     * A unique slug will be generated automatically and the tag will be associated
     * with the currently authenticated admin.
     *  
     * @group Tags
     * 
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing tag data.
     *
     * Required Parameters:
     * - title (string): The name of the tag.
     * - color (string): The color code of the tag (e.g., "#FF0000").
     *
     * Optional Parameters:
     * - isApi (bool): Whether the request is for API response formatting. Default is false.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * Response Example (success, API mode):
     * {
     *   "error": false,
     *   "message": "Tag created successfully",
     *   "data": {
     *     "id": 1,
     *     "title": "Important",
     *     "color": "#FF0000",
     *     "created_at": "2025-09-04",
     *     "updated_at": "2025-09-04"
     *   }
     * }
     *
     * Response Example (error):
     * {
     *   "error": true,
     *   "message": "An error occurred while creating the tag",
     *   "data": []
     * }
     */
    public function store(Request $request)
    {   
         $isApi = $request->get('isApi', false);
        try {
            $formFields = $request->validate([
                'title' => ['required'],
                'color' => ['required']
            ]);
            $slug = generateUniqueSlug($request->title, Tag::class);
            $formFields['slug'] = $slug;
            $formFields['admin_id'] = getAdminIdByUserRole();
            $tag = Tag::create($formFields);
            if ($isApi) {
                return formatApiResponse(
                    false,
                    'Tag created successfully',

                    [
                        'id' => $tag->id,
                        'data' => [
                            'id' => $tag->id,
                            'title' => $tag->title,
                            'color' => $tag->color,
                            'created_at' => format_date($tag->created_at, to_format: 'Y-m-d'),
                            'updated_at' => format_date($tag->updated_at, to_format: 'Y-m-d'),
                        ]

                    ]
                );
            }
            return response()->json(['error' => false, 'message' => 'Tag created successfully.', 'id' => $tag->id, 'tag' => $tag]);
        } catch (\Exception $e) {
            if ($isApi) {
                return formatApiResponse(
                    true,
                    'An error occurred while creating the tag',
                    [],
                    500
                );
            }
            return response()->json(['error' => true, 'message' => 'Tag couldn\'t be created.']);
        }
    }
    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $tags = Tag::orderBy($sort, $order); // or 'desc'
        $adminId = getAdminIdByUserRole();
        $tags->where('admin_id', $adminId);
        if ($search) {
            $tags = $tags->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $tags->count();
        $tags = $tags
            ->paginate(request("limit"))
            ->through(
                fn ($tag) => [
                    'id' => $tag->id,
                    'title' => $tag->title,
                    'color' => '<span class="badge bg-' . $tag->color . '">' . $tag->title . '</span>',
                ]
            );
        return response()->json([
            "rows" => $tags->items(),
            "total" => $total,
        ]);
    }
    public function get($id)
    {
        $tag = Tag::findOrFail($id);
        return response()->json(['tag' => $tag]);
    }
    /**
     * Update an existing tag in the database.
     *
     * This API endpoint updates an existing tag's title and color.
     * A unique slug will be regenerated if the title changes.
     *  
     * @group Tags
     * 
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing updated tag data.
     *
     * Required Parameters:
     * - id (int): The ID of the tag to update.
     * - title (string): The new name of the tag.
     * - color (string): The new color code of the tag (e.g., "#00FF00").
     *
     * Optional Parameters:
     * - isApi (bool): Whether the request is for API response formatting. Default is true.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * Response Example (success):
     * {
     *   "error": false,
     *   "message": "Tag updated successfully.",
     *   "data": {
     *     "id": 1,
     *     "title": "Updated Title",
     *     "color": "#00FF00",
     *     "created_at": "2025-09-01",
     *     "updated_at": "2025-09-04"
     *   }
     * }
     *
     * Response Example (error):
     * {
     *   "error": true,
     *   "message": "An error occurred while updating the tag"
     * }
     */
public function update(Request $request)
{
    $isApi = $request->get('isApi', true);

    try {
        $formFields = $request->validate([
            'id'    => ['required'],
            'title' => ['required'],
            'color' => ['required'],
        ]);

        $slug = generateUniqueSlug($request->title, Tag::class, $request->id);
        $formFields['slug'] = $slug;

        $tag = Tag::findOrFail($request->id);

        if ($tag->update($formFields)) {
            return response()->json([
                'error'   => false,
                'message' => 'Tag updated successfully.',
                'data' => [
                            'id' => $tag->id,
                            'title' => $tag->title,
                            'color' => $tag->color,
                            'created_at' => format_date($tag->created_at, to_format: 'Y-m-d'),
                            'updated_at' => format_date($tag->updated_at, to_format: 'Y-m-d'),
                        ]
            ]);
        }

        return response()->json([
            'error'   => true,
            'message' => 'Tag couldn\'t be updated.',
        ]);
    } catch (ValidationException $e) {
        return formatApiValidationError($isApi, $e->errors());
    } catch (\Exception $e) {
        if ($isApi) {
            return formatApiResponse(
                true,
                'An error occurred while updating the tag: ' . $e->getMessage(),
                [],
                500
            );
        }

        // Fallback for non-API requests
        return back()->withErrors('An error occurred while updating the tag: ' . $e->getMessage());
    }
}
              
    public function get_suggestions()
    {
        $tags = Tag::pluck('title');
        return response()->json($tags);
    }
    public function get_ids(Request $request)
    {
        $tagNames = $request->input('tag_names');
        $tagIds = Tag::whereIn('title', $tagNames)->pluck('id')->toArray();
        return response()->json(['tag_ids' => $tagIds]);
    }
      /**
     * Delete a tag from the database.
     *
     * This API endpoint deletes a tag by its ID.
     * If the tag is associated with one or more projects, it cannot be deleted
     * and an error response will be returned.
     *  
     * @group Tags
     * 
     * @param int $id The ID of the tag to delete.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * Response Example (success):
     * {
     *   "error": false,
     *   "message": "Tag deleted successfully."
     * }
     *
     * Response Example (failure - associated with project):
     * {
     *   "error": true,
     *   "message": "Tag can't be deleted. It is associated with a project"
     * }
     *
     * Response Example (failure - not found):
     * {
     *   "error": true,
     *   "message": "No query results for model [Tag] 999"
     * }
     */
    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);
        if ($tag->projects()->count() > 0) {
            return response()->json(['error' => true, 'message' => 'Tag can\'t be deleted.It is associated with a project']);
        } else {
            $response = DeletionService::delete(Tag::class, $id, 'Tag');
        return $response;
        }
    }
    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:tags,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $tag = Tag::findOrFail($id);
            if ($tag->projects()->count() > 0) {
                return response()->json(['error' => true, 'message' => 'Tag can\'t be deleted.It is associated with a project']);
            } else {
                $deletedIds[] = $id;
                $deletedTitles[] = $tag->title;
                DeletionService::delete(Tag::class, $id, 'Tag');
            }
        }
        return response()->json(['error' => false, 'message' => 'Tag(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles]);
    }

      /**
     * Display a list of tags or a single tag by ID.
     *
     * This API endpoint supports pagination, sorting, and ordering when retrieving tags.
     * If an ID is provided, it will return the details of that specific tag.
     * Otherwise, it will return a paginated list of all tags.
     * 
     * @group Tags
     * 
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing filters and pagination options.
     * @param string $id (optional) The ID of the tag to retrieve. If empty, returns a list of tags.
     *
     * Query Parameters:
     * - sort (string, optional): The column to sort by. Default is `id`.
     * - order (string, optional): Sorting order, either `ASC` or `DESC`. Default is `DESC`.
     * - per_page (int, optional): Number of records per page. Default is `10`.
     *
     * @return \Illuminate\Http\JsonResponse
     * 
     * Response Example (single tag):
     * {
     *   "error": false,
     *   "message": "Tag retrieved successfully",
     *   "data": {
     *     "total": 1,
     *     "data": [
     *       {
     *         "id": 1,
     *         "title": "Important",
     *         "color": "#FF0000",
     *         "created_at": "2025-09-04",
     *         "updated_at": "2025-09-04"
     *       }
     *     ]
     *   }
     * }
     *
     * Response Example (list of tags):
     * {
     *   "error": false,
     *   "message": "Tags retrieved successfully",
     *   "data": {
     *     "total": 25,
     *     "data": [
     *       {
     *         "id": 1,
     *         "title": "Urgent",
     *         "color": "#FF0000",
     *         "created_at": "2025-09-01",
     *         "updated_at": "2025-09-03"
     *       },
     *       {
     *         "id": 2,
     *         "title": "Review",
     *         "color": "#00FF00",
     *         "created_at": "2025-09-02",
     *         "updated_at": "2025-09-03"
     *       }
     *     ]
     *   }
     * }
     */
    public function apilist(Request $request, $id = ''){

        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $per_page = $request->input('per_page',10);
        $tagsQuery = Tag::query();

         if ($id) {
            $tag = $tagsQuery->find($id);
            if (!$tag) {
                return formatApiResponse(
                    true,
                    'Tag not found',
                    [
                        'total' => 0,
                        'data' => []
                    ]
                );
            }
            return formatApiResponse(
                false,
                'Tag retrieved successfully',
                [
                    'total' => 1,
                    'data' => [
                        [
                            'id' => $tag->id,
                            'title' => $tag->title,
                            'color' => $tag->color,
                            'created_at' => format_date($tag->created_at, to_format: 'Y-m-d'),
                            'updated_at' => format_date($tag->updated_at, to_format: 'Y-m-d'),
                        ]
                    ]
                ]
            );
        } else {
            $total = $tagsQuery->count(); // Get total count before applying offset and limit
            $tags = $tagsQuery->orderBy($sort, $order)->paginate($per_page)->getCollection();
            if ($tags->isEmpty()) {
                return formatApiResponse(
                    false,
                    'Tags not found',
                    [
                        'total' => 0,
                        'data' => []
                    ]
                );
            }
            $data = $tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'title' => $tag->title,
                    'color' => $tag->color,
                    'created_at' => format_date($tag->created_at, to_format: 'Y-m-d'),
                    'updated_at' => format_date($tag->updated_at, to_format: 'Y-m-d'),
                ];
            });
            return formatApiResponse(
                false,
                'Tags retrieved successfully',
                [
                    'total' => $total,
                    'data' => $data
                ]
            );
        }
    }
}
