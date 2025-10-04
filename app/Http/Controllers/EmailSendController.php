<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Models\ScheduledEmail;
use App\Services\DeletionService;
use App\Models\Workspace;
use App\Notifications\DynamicTemplateMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailSendController extends Controller
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

    public function index()
    {
        $templates = EmailTemplate::all();
        $emails = isAdminOrHasAllDataAccess() ? $this->workspace->scheduledEmails() : $this->user->scheduledEmails();
        return view('emails.index', compact('templates', 'emails'));
    }

    public function create(Request $request)
    {
        try {
            $templates = EmailTemplate::all();
            return view('emails.send', compact('templates'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load email send page.');
        }
    }

    public function getTemplateData($id)
    {
        try {
            $template = EmailTemplate::findOrFail($id);
            $defaultPlaceholders = ['CURRENT_YEAR', 'COMPANY_TITLE', 'COMPANY_LOGO', 'SUBJECT'];

            preg_match_all('/{(\w+)}/', $template->body, $matches);
            $placeholders = array_diff(array_unique($matches[1]), $defaultPlaceholders);

            return response()->json([
                'subject' => $template->subject,
                'body' => $template->body,
                'placeholders' => array_values($placeholders)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Template not found'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $general_settings = get_settings('general_settings');
            $maxFileSizeBytes = config('media-library.max_file_size');
            $maxFileSizeKb = (int) ($maxFileSizeBytes / 1024);

            // Determine if this is a template email or custom email
            $isTemplateEmail = $request->filled('email_template_id');

            // Common validation rules
            $rules = [
                'emails' => 'required|array|min:1',
                'emails.*' => 'email',
                'attachments' => 'nullable|array',
                'attachments.*' => "file|max:$maxFileSizeKb",
                'scheduled_at' => 'nullable|date|after:now',
            ];

            // Add template-specific or custom-specific validation
            if ($isTemplateEmail) {
                $rules = array_merge($rules, [
                    'email_template_id' => 'required|exists:email_templates,id',
                    'placeholders' => 'required|array',
                ]);
            } else {
                $rules = array_merge($rules, [
                    'subject' => 'required|string|max:255',
                    'body' => 'required|string|min:1', // Added min:1 to ensure it's not empty
                ]);
            }

            // Custom validation messages
            $messages = [
                'body.required' => 'The message field is required.',
                'body.min' => 'The message field cannot be empty.',
                'subject.required' => 'The subject field is required.',
                'emails.required' => 'At least one recipient email is required.',
                'emails.*.email' => 'Please enter valid email addresses.',
            ];

            $data = $request->validate($rules, $messages);

            // Validate file extensions (BLOCK zip, exe, bat, etc.)
            $blockedExtensions = ['zip', 'exe', 'bat', 'cmd', 'scr', 'com', 'pif', 'jar', 'js', 'php', 'html', 'htm', 'vbs', 'wsf', 'wsh', 'cmd', 'cpl', 'reg', 'dll'];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if (in_array($file->getClientOriginalExtension(), $blockedExtensions)) {
                        return response()->json([
                            'error' => true,
                            'message' => 'Attachments with .zip, .exe and similar file types are not allowed for security reasons.',
                        ]);
                    }
                }
            }

            // Store uploaded files temporarily to avoid file consumption issue
            $storedAttachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $baseName = pathinfo($originalName, PATHINFO_FILENAME);
                    $uniqueId = time() . '_' . mt_rand(1000, 9999);
                    $sanitizedName = strtolower(str_replace(['#', '/', '\\', ' '], '-', $baseName)) . "-{$uniqueId}.{$extension}";

                    // Store file temporarily
                    $tempPath = $file->storeAs('temp/email-attachments', $sanitizedName, 'local');
                    $storedAttachments[] = [
                        'temp_path' => $tempPath,
                        'original_name' => $originalName,
                        'sanitized_name' => $sanitizedName,
                    ];
                }
            }

            // Prepare email data for template emails
            if ($isTemplateEmail) {
                $template = EmailTemplate::findOrFail($data['email_template_id']);
                $subject = $template->subject;
                $body = $template->body;

                // Add default placeholders
                $data['placeholders'] = array_merge($data['placeholders'], [
                    'CURRENT_YEAR' => now()->year,
                    'COMPANY_TITLE' => $general_settings['company_title'] ?? 'Company Title',
                    'COMPANY_LOGO' => '<img src="' . asset("/storage/" . (get_settings('general_settings')['full_logo'] ?? 'logos/default_full_logo.png')) . '" width="200px" alt="Company Logo">',
                    'SUBJECT' => $subject,
                ]);

                // Replace placeholders in body
                foreach ($data['placeholders'] as $key => $value) {
                    $body = str_replace(['{' . $key . '}', '{{' . $key . '}}'], $value, $body);
                }
            } else {
                // For custom emails, use provided subject and body
                $subject = $data['subject'];
                $body = $data['body'];
            }

            // Determine if scheduled
            $isScheduled = !empty($data['scheduled_at']);
            $status = $isScheduled ? 'pending' : 'sent';
            $scheduledAtUtc = $isScheduled
                ? Carbon::parse($data['scheduled_at'], config('app.timezone', 'UTC'))->setTimezone('UTC')
                : null;

            // Keep track of created emails for cleanup if needed
            $createdEmails = [];

            // Loop through each recipient and send/schedule the email
            foreach ($data['emails'] as $recipient) {
                // Store email record
                $email = ScheduledEmail::create([
                    'user_id' => auth()->id(),
                    'email_template_id' => $isTemplateEmail ? $data['email_template_id'] : null,
                    'workspace_id' => getWorkspaceId(),
                    'to_email' => $recipient,
                    'subject' => $subject,
                    'body' => $body,
                    'placeholders' => $isTemplateEmail ? $data['placeholders'] : null,
                    'scheduled_at' => $scheduledAtUtc,
                    'status' => $status,
                ]);

                $createdEmails[] = $email;

                // Handle attachments - copy from temp storage for each recipient
                if (!empty($storedAttachments)) {
                    foreach ($storedAttachments as $attachment) {
                        try {
                            $tempFilePath = storage_path('app/' . $attachment['temp_path']);
                            if (file_exists($tempFilePath)) {
                                $email->addMedia($tempFilePath)
                                    ->usingName($attachment['original_name'])
                                    ->usingFileName($attachment['sanitized_name'])
                                    ->toMediaCollection('email-media');
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to attach file for email ID ' . $email->id . ': ' . $e->getMessage());
                        }
                    }
                }

                // Send email immediately if not scheduled
                if (!$isScheduled) {
                    try {
                        Mail::to($email->to_email)->send(new DynamicTemplateMail($email));
                        $email->update(['status' => 'sent']);
                    } catch (\Throwable $th) {
                        $email->update(['status' => 'failed']);
                        Log::error('Email sending failed for ' . $recipient . ': ' . $th->getMessage());
                    }
                }
            }

            // Clean up temporary files
            foreach ($storedAttachments as $attachment) {
                $tempFilePath = storage_path('app/' . $attachment['temp_path']);
                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }
            }

            if ($isScheduled) {
                return response()->json(['error' => false, 'message' => 'Emails scheduled successfully.']);
            } else {
                return response()->json(['error' => false, 'message' => 'Emails sent successfully.']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors specifically
            return response()->json([
                'error' => true,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to send or schedule emails: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred while sending/scheduling the emails.',
                'details' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function preview(Request $request)
    {
        if ($request->has('is_encoded') && $request->is_encoded == '1') {
            $decodedContent = base64_decode($request->content);
            $request->merge(['body' => $decodedContent]);
        }

        try {
            $subject = $request->subject ?? 'No Subject';
            $body = $request->body;
            $placeholders = $request->placeholders ?? [];

            // Replace placeholders
            foreach ($placeholders as $key => $value) {
                $body = str_replace("{{$key}}", $value, $body);
                $subject = str_replace("{{$key}}", $value, $subject);
            }

            // Process attachments
            $attachmentPreview = '';
            if ($request->hasFile('attachments')) {
                $files = $request->file('attachments');
                $attachmentPreview .= "<hr><div><strong>Attachments:</strong><ul>";
                foreach ($files as $file) {
                    $attachmentPreview .= "<li>{$file->getClientOriginalName()}</li>";
                }
                $attachmentPreview .= "</ul></div>";
            }

            $body = preg_replace('/background-color:\s*[^;]+;?/i', '', $body);
            $html = "<div>{$body}</div>{$attachmentPreview}";

            return response()->json(['preview' => $html]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate preview'], 500);
        }
    }

    public function historyList(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');
        $limit = (int) $request->input('limit', 10);
        $offset = (int) $request->input('offset', 0);

        $query = isAdminOrHasAllDataAccess()
            ? $this->workspace->scheduledEmails()
            : $this->user->scheduledEmails();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('to_email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $total = $query->toBase()->getCountForPagination(); // get total before limit/offset

        $emails = $query->orderBy('scheduled_emails.' . $sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        $rows = $emails->map(function ($email) {
            $canDelete = isAdminOrHasAllDataAccess() || ($email->user_id == auth()->id());
            $status = match ($email->status) {
                'pending' => '<span class="badge bg-warning">Pending</span>',
                'sent' => '<span class="badge bg-success">Sent</span>',
                default => '<span class="badge bg-danger">Failed</span>',
            };
            $actions = $canDelete ? '<button type="button"
            class="btn delete"
            data-id="' . $email->id . '"
            data-type="emails"
            title="' . get_label('delete', 'Delete') . '">
            <i class="bx bx-trash text-danger mx-1"></i>
        </button>' : '-';

            return [
                'id' => $email->id,
                'to_email' => $email->to_email,
                'subject' => ucwords($email->subject),
                'status' => $status,
                'scheduled_at' => format_date($email->scheduled_at, true),
                'created_at' => format_date($email->created_at, true),
                'updated_at' => format_date($email->updated_at, true),
                'user_name' => formatUserHtml($email->user) ?? 'N/A',
                'body' => $email->body,
                'actions' => $actions,
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $rows,
        ]);
    }


    public function destroy($id)
    {
        $email = ScheduledEmail::findOrFail($id);
        $response = DeletionService::delete(ScheduledEmail::class, $email->id, 'Scheduled Email');
        return $response;
    }

    public function destroy_multiple(Request $request)
    {
        $validatedData = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:scheduled_emails,id',
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];

        foreach ($ids as $id) {
            $email = ScheduledEmail::findOrFail($id);
            $deletedIds[] = $id;
            DeletionService::delete(ScheduledEmail::class, $email->id, 'Scheduled Email');
        }

        return response()->json([
            'error' => false,
            'message' => 'Scheduled Email(s) deleted successfully.',
            'id' => $deletedIds,
        ]);
    }
}
