<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateStatus;
use App\Models\Interview;
use App\Models\User;
use App\Models\UserClientPreference;
use App\Models\Workspace;
use App\Services\DeletionService;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CandidateController extends Controller
{
  //
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


  public function index()
  {
    // $candidate_statuses = CandidateStatus::orderBy('order')->get();
    $candidate_statuses = CandidateStatus::where('workspace_id', $this->workspace->id)
      ->where('admin_id', getAdminIdByUserRole())
      ->orderBy('order')
      ->get();
    $candidates = Candidate::with('status')->get();


    // dd($candidate_statuses);
    return view('candidate.index', compact('candidate_statuses', 'candidates'));
  }

  public function show($id)
  {

    $users = User::all();
    $candidates = Candidate::with('status')->findOrFail($id);
    $candidate_statuses = CandidateStatus::where('workspace_id', $this->workspace->id)
      ->where('admin_id', getAdminIdByUserRole())
      ->orderBy('order')
      ->get();
    // $statuses = CandidateStatus::orderBy('order')->get();
    $interviews = Interview::all();

    return view('candidate.show', compact( 'users', 'candidates', 'interviews','candidate_statuses'));
  }
  public function store(Request $request)
  {
    //    dd($request->input('source'));

    $maxFileSizeBytes = config('media-library.max_file_size');
    $maxFileSizeKb = (int) ($maxFileSizeBytes / 1024);

    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email',
      'phone' => 'nullable|max:15',
      'position' => 'required|string|max:255',
      'source' => 'required|string|max:255',
      'status_id' => 'required|exists:candidate_statuses,id',
      'attachments.*' => "nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:$maxFileSizeKb",
    ]);


    $validatedData['workspace_id'] = $this->workspace->id;
    $validatedData['admin_id'] = getAdminIdByUserRole();

    // dd($validatedData);
    // check if email alrady exists
    if (Candidate::where('email', $validatedData['email'])->exists()) {
      return response()->json([
        'error' => true,
        'message' => 'A candidate with this email alrady exists.'
      ]);
    }

    $candidate = Candidate::create($validatedData);
    // dd($candidate);

    // Handle file attachments
    if ($request->hasFile('attachments')) {
      foreach ($request->file('attachments') as $file) {
        // dd($file);
        $mediaItem = $candidate->addMedia($file)
          ->sanitizingFileName(function ($fileName) {
            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
            $uniqueId = time() . '_' . mt_rand(1000, 9999);
            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);
            return "{$baseName}-{$uniqueId}.{$extension}";
          })
          ->toMediaCollection('candidate-media');
      }
    }

    return response()->json([
      'error' => false,
      'message' => 'Candidate Created Successfully!',
      'candidate' => $candidate
    ]);
  }

  public function getCandidate($id)
  {
    $candidate = Candidate::with(['status', 'interviews.interviewer', 'media'])->findOrFail($id);

    return response()->json([
      'candidate' => [
        'id' => $candidate->id,
        'name' => $candidate->name,
        'email' => $candidate->email,
        'phone' => $candidate->phone,
        'position' => $candidate->position,
        'source' => $candidate->source,
        'status' => $candidate->status ? $candidate->status->name : '-',
        'created_at' => format_date($candidate->created_at),
        'avatar' => $candidate->getFirstMediaUrl('candidate-media') ?: asset('/storage/photos/no-image.jpg'),
      ],
      'attachments' => $candidate->getMedia('candidate-media')->map(function ($media) {
        $isPublicDisk = $media->disk == 'public';
        $fileUrl = $isPublicDisk
          ? asset('storage/candidate-media/' . $media->file_name)
          : $media->getFullUrl();

        $fileExtension = pathinfo($fileUrl, PATHINFO_EXTENSION);
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $isImage = in_array(strtolower($fileExtension), $imageExtensions);

        return [
          'id' => $media->id,
          'name' => $media->file_name,
          'type' => $media->mime_type,
          'size' => round($media->size / 1024, 2) . ' KB',
          'created_at' => format_date($media->created_at),
          'url' => $fileUrl,
          'is_image' => $isImage,
        ];
      }),
      'interviews' => $candidate->interviews->map(function ($interview) {
        return [
          'id' => $interview->id,
          'candidate_name' => $interview->candidate->name,
          'interviewer' => $interview->interviewer->first_name . ' ' . $interview->interviewer->last_name,
          'round' => $interview->round,
          'scheduled_at' => $interview->scheduled_at,
          'status' => $interview->status,
          'location' => $interview->location,
          'mode' => $interview->mode,
          'created_at' => format_date($interview->created_at),
          'updated_at' => format_date($interview->updated_at),
        ];
      }),
    ]);
  }

  public function edit($id)
  {
    $candidate = Candidate::with('status')->findOrFail($id);
    return response()->json([
      'error' => false,
      'candidate' => $candidate,
    ]);
  }
  public function list()
  {

    $search = request('search');
    $order = request('order', 'DESC');
    $limit = request('limit', 10);
    $offset = request('offset', 0);
    $sort = request()->input('sort', 'id');
    $start_date = request('start_date');
    $end_date = request('end_date');

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


    $candidateStatus = request('candidate_status');
    // dd($candidateStatus);

    // $query = Candidate::query();
    $query = Candidate::query()
        ->where('workspace_id', $this->workspace->id)
        ->where('admin_id', getAdminIdByUserRole());

    if ($search) {
      $query->where(function ($query) use ($search) {
        $query->whereHas('status', function ($q) use ($search) {
          $q->where('name', 'like', "%$search%");
        })
          ->orWhere('name', 'like', "%$search%")
          ->orWhere('position', 'like', "%$search%")
          ->orWhere('source', 'like', "%$search%");
      });
    }
    if ($start_date && $end_date) {
      $query->whereBetween('created_at', [$start_date, $end_date]);
    }

    if ($candidateStatus) {
      $query->whereIn('status_id', $candidateStatus);
    }

    $total = $query->count();

    $canEdit = checkPermission('edit_candidate');
    $canDelete = checkPermission('delete_candidate');


    $candidates = $query->orderBy($sort, $order)
      ->skip($offset)
      ->take($limit)
      ->get()
      ->map(function ($candidate) use ($canDelete, $canEdit) {

        $actions = '';

        if ($canEdit) {
          $actions .= '<a href="javascript:void(0);" class="edit-candidate-btn"
                                        data-candidate=\'' . htmlspecialchars(json_encode($candidate), ENT_QUOTES, 'UTF-8') . '\'
                                        title="' . get_label('update', 'Update') . '">
                                        <i class="bx bx-edit mx-1"></i>
                                    </a>';
        }

        if ($canDelete) {
          $actions .= '<button type="button"
                                        class="btn delete"
                                        data-id="' . $candidate->id . '"
                                        data-type="candidate"
                                        title="' . get_label('delete', 'Delete') . '">
                                        <i class="bx bx-trash text-danger mx-1"></i>
                                    </button>';
        }


        // Generate interview preview button
        $interviewsCount = $candidate->interviews->count();
        // dd($interviewsCount);
        $interviewsPreview = '';

        if ($interviewsCount > 0) {
          $interviewsPreview = '
            <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1 view-interviews-btn" data-bs-toggle="modal" data-bs-target="#interviewDetailsModal" data-id = "' . $candidate->id . '">
                <i class="bx bx-calendar-check me-1"></i>' . get_label('view', 'View') . '
            </button>
        ';
        } else {
          $interviewsPreview = '
            <span class="text-muted small">
                <i class="bx me-1"></i>' . get_label('no_interviews', 'No interviews') . '
            </span>
        ';
        }


        return [
          'id' => $candidate->id,
          'name' => "<a href='" . route('candidate.show', ['id' => $candidate->id]) . "' >" . $candidate->name . "</a>",
          'email' => $candidate->email,
          'phone' => $candidate->phone,
          'position' => ucwords($candidate->position),

          'status' => $candidate->status
            ? '<span class="badge bg-' . $candidate->status->color . '">' . $candidate->status->name . '</span>'
            : '<span class="badge bg-secondary">Unknown</span>',

          'source' => ucwords($candidate->source),
          'interviews' => $interviewsPreview,
          'created_at' => format_date($candidate->created_at),
          'updated_at' => format_date($candidate->updated_at),
          'actions' => $actions ?: '-'
        ];
      });

    // dd($candidates);
    return response()->json([
      'rows' => $candidates,
      'total' => $total
    ]);
  }

  public function kanbanView(Request $request)
  {
    $statuses = (array) $request->input('statuses', []);
    // dd($statuses);
    $start_date = $request->input('start_date');
    $end_date = $request->input('end_date');


    $sortOptions = [
      'newest' => ['created_at', 'desc'],
      'oldest' => ['created_at', 'asc'],
      'recently-updated' => ['updated_at', 'desc'],
      'earliest-updated' => ['updated_at', 'asc'],
    ];
    [$sort, $order] = $sortOptions[$request->input('sort')] ?? ['id', 'desc'];

    // $candidatesQuery = Candidate::with(['status'])
    //   ->orderBy($sort, $order);
    $candidatesQuery = Candidate::with(['status'])
      ->where('workspace_id', $this->workspace->id)
      ->where('admin_id', getAdminIdByUserRole())
      ->orderBy($sort, $order);

    if (!empty($statuses)) {
      $candidatesQuery->whereIn('status_id', $statuses);
    }

    if ($start_date && $end_date) {
      $candidatesQuery->whereBetween('updated_at', [$start_date, $end_date]);
    }

    $candidates = $candidatesQuery->get();

    // $candidate_statuses = CandidateStatus::orderBy('order')->get();
    $candidate_statuses = CandidateStatus::where('workspace_id', $this->workspace->id)
                                    ->where('admin_id', getAdminIdByUserRole())
                                    ->orderBy('order')
                                    ->get();
    
    return view('candidate.kanban', compact('candidates', 'candidate_statuses'));
  }

  public function update(Request $request, $id)
  {

    $maxFileSizeBytes = config('media-library.max_file_size');
    $maxFileSizeKb = (int) ($maxFileSizeBytes / 1024);

    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email',
      'phone' => 'nullable|max:15',
      'position' => 'required|string|max:255',
      'source' => 'required|string|max:255',
      'status_id' => 'required|exists:candidate_statuses,id',
      'attachments.*' => "nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:$maxFileSizeKb",
    ]);


    $candidate = Candidate::findOrFail($id);

    $candidate->update($validatedData);

    if ($request->hasFile('attachments')) {
      foreach ($request->file('attachments') as $file) {
        // dd($file);
        $mediaItem = $candidate->addMedia($file)
          ->sanitizingFileName(function ($fileName) {
            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
            $uniqueId = time() . '_' . mt_rand(1000, 9999);
            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);
            return "{$baseName}-{$uniqueId}.{$extension}";
          })
          ->toMediaCollection('candidate-media');
      }
    }


    return response()->json([
      'error' => false,
      'message' => 'User Updated Successfully!',
      'candidate' => $candidate
    ]);
  }

  public function getInterviewDetails($id)
  {
    $candidate = Candidate::with(['interviews.interviewer'])->findOrFail($id);



    return response()->json([
      'error' => false,
      'candidate' => $candidate,
      'html' => view('partials.interview-details', compact('candidate'))->render()
    ]);
  }

  public function destroy($id)
  {

    $candidate = Candidate::findOrFail($id);

    $response = DeletionService::delete(Candidate::class, $candidate->id, 'Candidate');

    return $response;
  }

  public function destroy_multiple(Request $request)
  {

    $validatedData = $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:candidates,id'
    ]);



    $ids = $validatedData['ids'];
    $deletedIds = [];

    foreach ($ids as $id) {
      $candidate = Candidate::findOrFail($id);
      $deletedIds[] = $id;

      DeletionService::delete(Candidate::class, $candidate->id, 'Candidate');
    }

    return response()->json([
      'error' => false,
      'message' => 'Candidate(s) Deleted Successfully!',
      'id' => $deletedIds,
    ]);
  }

  public function attachmentsList($candidateId)
  {
    $search = request('search');
    $sort = request('sort', 'id');
    $order = request('order', 'desc');
    $limit = request('limit', 10);
    $offset = request('offset', 0);

    $candidate = Candidate::findOrFail($candidateId);
    $mediaCollection = $candidate->getMedia('candidate-media');

    if ($search) {
      $mediaCollection = $mediaCollection->filter(function ($media) use ($search) {
        return str_contains(strtolower($media->name), strtolower($search)) ||
          str_contains(strtolower($media->mime_type), strtolower($search));
      });
    }

    $total = $mediaCollection->count();
    $mediaCollection = ($order === 'desc')
      ? $mediaCollection->sortByDesc($sort)
      : $mediaCollection->sortBy($sort);
    $mediaItems = $mediaCollection->slice($offset, $limit);
    $canDelete = isAdminOrHasAllDataAccess();

    $rows = $mediaItems->map(function ($media) use ($canDelete, $candidate) {
      $actions = '';

      $isPublicDisk = $media->disk == 'public' ? 1 : 0;
      $fileUrl = $isPublicDisk
        ? asset('/storage/candidate-media/' . $media->file_name)
        : $media->getFullUrl();
      $fileExtension = pathinfo($fileUrl, PATHINFO_EXTENSION);
      $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
      $isImage = in_array(strtolower($fileExtension), $imageExtensions);
      $isWordDoc = in_array(strtolower($fileExtension), ['doc', 'docx']);
      $isPdf = strtolower($fileExtension) === 'pdf';

      if (in_array($media->mime_type, [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
      ])) {
        $viewUrl = $isImage ? $fileUrl : route('candidate.attachment.view', ['mediaId' => $media->id, 'candidateId' => $candidate->id]);

        if ($isImage) {
          // For images, use lightbox
          $actions .= '<a href="' . $viewUrl . '" data-lightbox="candidate-media" data-title="' . $media->name . '" class="btn view-lightbox"
                      title="' . get_label('view', 'View') . '">
                      <i class="bx bx-show text-info mx-1"></i>
                  </a>';
        } else if ($isWordDoc) {
          // For Word documents, use Google Docs Viewer
          $encodedUrl = urlencode($fileUrl);
          $googleDocsViewerUrl = "https://docs.google.com/viewer?url={$encodedUrl}&embedded=true";

          // We'll use a modal approach for Word documents
          $actions .= '<a href="javascript:void(0);" class="btn view-word-doc"
                      onclick="openWordDocViewer(\'' . $googleDocsViewerUrl . '\', \'' . htmlspecialchars($media->name, ENT_QUOTES, 'UTF-8') . '\')"
                      title="' . get_label('view', 'View') . '">
                      <i class="text-info mx-1"></i>
                  </a>';
        } else {
          // For PDFs, use browser's built-in viewer
          $actions .= '<a href="javascript:void(0);" class="btn view-in-lightbox"
                      onclick="window.open(\'' . $viewUrl . '\', \'_blank\', \'noopener,noreferrer\')"
                      title="' . get_label('view', 'View') . '">
                      <i class="bx bx-show text-info mx-1"></i>
                  </a>';
        }
      }

      $actions .= '<button class="btn download"
              onclick="window.location.href=\'' . route('candidate.attachment.download', ['mediaId' => $media->id, 'candidateId' => $candidate->id]) . '\'"
              title="' . get_label('download', 'Download') . '">
              <i class="bx bx-download text-primary mx-1"></i>
          </button>';

      if ($canDelete) {
        $actions .= '<button class="btn delete"
                  data-id="' . $media->id . '"
                  data-type="candidate/candidate-media"
                  title="' . get_label('delete', 'Delete') . '">
                  <i class="bx bx-trash text-danger mx-1"></i>
              </button>';
      }

      return [
        'id' => $media->id,
        'name' => $media->name,
        'type' => $media->mime_type,
        'size' => round($media->size / 1024, 2) . ' KB',
        'created_at' => format_date($media->created_at),
        'actions' => $actions ?: '-',
      ];
    })->values();

    return response()->json([
      'total' => $total,
      'rows' => $rows,
    ]);
  }

  public function upload(Request $request, $id)
  {
    $maxFileSizeBytes = config('media-library.max_file_size');
    $maxFileSizeKb = (int) ($maxFileSizeBytes / 1024);

    $request->validate([
      'attachments.*' => "required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:$maxFileSizeKb",
    ]);

    $candidate = Candidate::findOrFail($id);
    $uploadedFiles = [];

    if ($request->hasFile('attachments')) {
      foreach ($request->file('attachments') as $file) {
        $mediaItem = $candidate->addMedia($file)
          ->sanitizingFileName(function ($fileName) {
            $sanitizedFileName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
            $uniqueId = time() . '_' . mt_rand(1000, 9999);
            $extension = pathinfo($sanitizedFileName, PATHINFO_EXTENSION);
            $baseName = pathinfo($sanitizedFileName, PATHINFO_FILENAME);
            return "{$baseName}-{$uniqueId}.{$extension}";
          })
          ->toMediaCollection('candidate-media');

        $uploadedFiles[] = $mediaItem;
      }
    }

    return response()->json([
      'error' => false,
      'message' => 'Files uploaded successfully!',
      'files' => $uploadedFiles
    ]);
  }
  public function view($candidateId, $mediaId)
  {

    $candidate = Candidate::findOrFail($candidateId);
    $media = $candidate->getMedia('candidate-media')->find($mediaId);

    if (!$media) {
      abort(404, 'Media not found');
    }

    if (in_array($media->mime_type, [
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'image/jpeg',
      'image/png',
    ])) {
      return response()->file($media->getPath());
    } else {
      return response()->json([
        'error' => true,
        'message' => 'File type not supported for viewing'
      ]);
    }
  }


  public function download($candidateId, $mediaId)
  {

    $candidate = Candidate::findOrFail($candidateId);
    $media = $candidate->getMedia('candidate-media')->find($mediaId);

    if (!$media) {
      abort(404, 'Media not found');
    }

    return response()->download($media->getPath());
  }


  public function delete($id)
  {
    $media = Media::findOrFail($id);

    $response = DeletionService::delete(Media::class, $media->id, 'Attachment');

    return $response;
  }

  public function search_candidates(Request $request)
  {
    $query = $request->input('q'); // search term
    $page = $request->input('page', 1);
    $perPage = 10;
    $candidatesQuery = Candidate::query();

    // Filter by name, email or phone if search query is present
    if (!empty($query)) {
      $candidatesQuery->where(function ($subQuery) use ($query) {
        $subQuery->where('name', 'like', '%' . $query . '%')
          ->orWhere('email', 'like', '%' . $query . '%')
          ->orWhere('phone', 'like', '%' . $query . '%');
      });
    }

    // Get total count for pagination
    $totalCount = $candidatesQuery->count();

    // Apply pagination
    $candidates = $candidatesQuery->skip(($page - 1) * $perPage)
      ->take($perPage)
      ->get(['id', 'name', 'email', 'phone']);

    // Format for Select2
    $results = $candidates->map(function ($candidate) {
      return [
        'id' => $candidate->id,
        'text' => $candidate->name,
      ];
    });

    // Return format that matches your initSelect2Ajax function
    return response()->json([
      'items' => $results,  // ← Changed from 'results' to 'items'
      'pagination' => [
        'more' => ($page * $perPage) < $totalCount
      ]
    ]);
  }


  public function saveViewPreference(Request $request)
  {
    $view = $request->input('view');
    $prefix = isClient() ? 'c_' : 'u_';
    if (
      UserClientPreference::updateOrCreate(
        ['user_id' => $prefix . $this->user->id, 'table_name' => 'candidates'],
        ['default_view' => $view]
      )
    ) {
      return response()->json(['error' => false, 'message' => 'Default View Set Successfully.']);
    } else {
      return response()->json(['error' => true, 'message' => 'Something Went Wrong.']);
    }
  }
}
