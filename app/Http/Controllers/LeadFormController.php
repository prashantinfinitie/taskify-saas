<?php

namespace App\Http\Controllers;

use App\Models\LeadForm;
use App\Models\LeadFormField;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\User;
use App\Models\Workspace;
use App\Services\DeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeadFormController extends Controller
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
        $forms = LeadForm::with(['creator', 'leadSource', 'leadStage', 'assignedUser'])
            ->latest()
            ->get();

        return view('lead_forms.index', compact('forms'));
    }

    public function create()
    {
        $sources = LeadSource::where('workspace_id', auth()->user()->workspace_id)->get();
        $stages = LeadStage::where('workspace_id', auth()->user()->workspace_id)->get();
        $users = User::all();
        // $users = User::where('workspace_id', auth()->user()->workspace_id)->get();

        return view('lead_forms.create', compact('sources', 'stages', 'users'));
    }

    public function store(Request $request)
    {
        // Clean up empty fields before validation
        $cleanedFields = [];
        if ($request->has('fields')) {
            foreach ($request->fields as $index => $field) {
                // Only include fields that have at least a label or type
                if (!empty($field['label']) || !empty($field['type'])) {
                    $cleanedFields[$index] = $field;
                }
            }
        }

        // Replace the fields in the request
        $request->merge(['fields' => $cleanedFields]);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'source_id' => 'required|exists:lead_sources,id',
            'stage_id' => 'required|exists:lead_stages,id',
            'assigned_to' => 'required|exists:users,id',
            'success_message' => 'nullable|string',
            'redirect_url' => 'nullable|url',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|in:' . implode(',', array_keys(LeadFormField::FIELD_TYPES)),
            'fields.*.is_required' => 'boolean',
            'fields.*.is_mapped' => 'boolean',
            'fields.*.name' => 'required_if:fields.*.is_mapped,true|nullable|in:' . implode(',', array_keys(LeadFormField::MAPPABLE_FIELDS)),
            'fields.*.options' => 'nullable|array',
            'fields.*.placeholder' => 'nullable|string',
        ], [
            'fields.required' => 'At least one field is required.',
            'fields.min' => 'At least one field is required.',
            'fields.*.label.required' => 'Field label is required.',
            'fields.*.label.max' => 'Field label cannot exceed 255 characters.',
            'fields.*.type.required' => 'Field type is required.',
            'fields.*.type.in' => 'Invalid field type selected.',
            'fields.*.name.required_if' => 'Field mapping is required when field is marked as mapped.',
            'fields.*.name.in' => 'Invalid field mapping selected.',
            'fields.*.options.array' => 'Field options must be an array.',
            'fields.*.options.*.string' => 'Each option must be text.',
            'fields.*.options.*.max' => 'Each option cannot exceed 255 characters.',
            'fields.*.placeholder.max' => 'Placeholder cannot exceed 255 characters.',
            'title.required' => 'Form title is required.',
            'title.max' => 'Form title cannot exceed 255 characters.',
            'description.max' => 'Form description cannot exceed 1000 characters.',
            'source_id.required' => 'Lead source is required.',
            'source_id.exists' => 'Selected lead source does not exist.',
            'stage_id.required' => 'Lead stage is required.',
            'stage_id.exists' => 'Selected lead stage does not exist.',
            'assigned_to.required' => 'Assigned user is required.',
            'assigned_to.exists' => 'Selected user does not exist.',
            'success_message.max' => 'Success message cannot exceed 500 characters.',
            'redirect_url.url' => 'Redirect URL must be a valid URL.',
            'redirect_url.max' => 'Redirect URL cannot exceed 2048 characters.',
        ]);


        // Custom validation for select/radio/checkbox fields
        $validator->after(function ($validator) use ($request) {
            if ($request->has('fields')) {
                foreach ($request->fields as $index => $field) {
                    if (in_array($field['type'] ?? '', ['select', 'radio', 'checkbox'])) {
                        if (empty($field['options']) || !is_array($field['options']) || count(array_filter($field['options'])) === 0) {
                            $validator->errors()->add(
                                "fields.{$index}.options",
                                "At least one option is required for {$field['type']} fields."
                            );
                        }
                    }
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $form = LeadForm::create([
                'title' => $request->title,
                'description' => $request->description,
                'created_by' => auth()->id(),
                'workspace_id' => $this->workspace->id,
                'source_id' => $request->source_id,
                'stage_id' => $request->stage_id,
                'assigned_to' => $request->assigned_to,
                'admin_id' => getAdminIdByUserRole()

            ]);

            // Validate required fields if you have this method
            if (method_exists($this, 'validateRequiredFields')) {
                $this->validateRequiredFields($request->fields);
            }

            // Create form fields with proper ordering
            $order = 1;
            foreach ($request->fields as $index => $fieldData) {
                // Clean options array - remove empty values
                $options = null;
                if (!empty($fieldData['options']) && is_array($fieldData['options'])) {
                    $cleanOptions = array_filter($fieldData['options'], function ($option) {
                        return !empty(trim($option));
                    });
                    if (!empty($cleanOptions)) {
                        $options = json_encode(array_values($cleanOptions));
                    }
                }

                LeadFormField::create([
                    'form_id' => $form->id,
                    'label' => $fieldData['label'],
                    'name' => $fieldData['is_mapped'] ? ($fieldData['name'] ?? null) : null,
                    'type' => $fieldData['type'],
                    'is_required' => $fieldData['is_required'] ?? false,
                    'is_mapped' => $fieldData['is_mapped'] ?? false,
                    'options' => $options,
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'order' => $order++,
                    'validation_rules' => method_exists($this, 'generateValidationRules')
                        ? $this->generateValidationRules($fieldData)
                        : null,
                ]);
            }

            DB::commit();

            return response()->json([
                'error' => false,
                'message' => 'Lead form created successfully!',
                'form' => $form->load('leadFormFields'),
                'public_url' => $form->public_url ?? null,
                'embed_code' => $form->embed_code ?? null
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            // Log::error('Lead form creation failed: ' . $e->getMessage(), [
            //     'request_data' => $request->all(),
            //     'exception' => $e
            // ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create form: ' . $e->getMessage()
            ], 500);
        }
    }

    public function list()
    {
        $search = request()->input('search');
        $limit = request()->input('limit', 10);
        $sortOptions = [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'recently-updated' => ['updated_at', 'desc'],
            'earliest-updated' => ['updated_at', 'asc'],
        ];
        [$sort, $order] = $sortOptions[request()->input('sort')] ?? ['id', 'desc'];


        $leadForms = isAdminOrHasAllDataAccess()
            ? $this->workspace->leadForms()
            : $this->user->leadForms();

        $leadForms = $leadForms->with(['leadSource', 'leadStage', 'assignedUser', 'creator']);

        if ($search) {
            $leadForms->where(function ($query) use ($search) {
                $query->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        $total = $leadForms->count();

        $canEdit = isAdminOrHasAllDataAccess() || checkPermission('manage_leads');
        $canDelete = isAdminOrHasAllDataAccess();

        $leadForms = $leadForms->orderBy($sort, $order)
            ->paginate($limit)
            ->through(function ($leadForm) use ($canDelete, $canEdit) {
                $stage = $leadForm->leadStage
                    ? '<span class="badge bg-' . $leadForm->leadStage->color . '">' . $leadForm->leadStage->name . '</span>'
                    : '-';

                $actions = '';

                if ($canEdit) {
                    $actions .= '
                    <a href="' . route('lead-forms.edit', $leadForm->id) . '"
                       class="mx-1"
                       title="' . get_label('update', 'Update') . '">
                        <i class="bx bx-edit text-primary"></i>
                    </a>
                    <a href="' . route('lead-forms.embed', $leadForm->id) . '"
                       class="mx-1"
                       title="' . get_label('embed_code', 'Embed Code') . '">
                        <i class="bx bx-code-alt text-info"></i>
                    </a>';
                }

                if ($canDelete) {
                    $actions .= '
                    <a href="javascript:void(0);"
                       class="delete"
                       data-id="' . $leadForm->id . '"
                       data-type="lead-forms"
                       title="' . get_label('delete', 'Delete') . '">
                        <i class="bx bx-trash mx-1 text-danger"></i>
                    </a>';
                }

                $responses = '<div class="text-center">
                <a href="' . route('lead-forms.responses', $leadForm->id) . '"
                   class="get-embed-code-btn"
                   title="' . $leadForm->leads_count . ' ' . get_label('responses', 'Responses') . '">
                    <i class="bx bx-message-dots fs-5 text-success"></i>
                </a>
            </div>';

                return [
                    'id' => $leadForm->id,
                    'title' => $leadForm->title,
                    'description' => $leadForm->description ? (strlen($leadForm->description) > 50 ? substr($leadForm->description, 0, 50) . '...' : $leadForm->description) : '-',
                    'source' => optional($leadForm->leadSource)->name ?? '-',
                    'stage' => $stage,
                    'assigned_to' => formatUserHtml($leadForm->assignedUser) ?? 'N/A',
                    'public_url' => '<a href="' . $leadForm->public_url . '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bx bx-link-external"></i> View</a>',
                    'responses' => $responses,
                    'created_at' => format_date($leadForm->created_at, true),
                    'updated_at' => format_date($leadForm->updated_at, true),
                    'actions' => $actions
                ];
            });

        return response()->json([
            'rows' => $leadForms->items(),
            'total' => $total
        ]);
    }

    public function update(Request $request, $id)
    {
                // dd($request->all());

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'source_id' => 'required|exists:lead_sources,id',
            'stage_id' => 'required|exists:lead_stages,id',
            'assigned_to' => 'required|exists:users,id',
            'fields' => 'required|array|min:5',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|in:' . implode(',', array_keys(LeadFormField::FIELD_TYPES)),
            'fields.*.is_required' => 'boolean',
            'fields.*.is_mapped' => 'boolean',
            'fields.*.name' => 'required_if:fields.*.is_mapped,true|nullable|in:' . implode(',', array_keys(LeadFormField::MAPPABLE_FIELDS)),
            'fields.*.options' => 'nullable|array',
            'fields.*.placeholder' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {

            $leadForm = LeadForm::findOrFail($id);
            $leadForm->update([
                'title' => $request->title,
                'description' => $request->description,
                'source_id' => $request->source_id,
                'stage_id' => $request->stage_id,
                'assigned_to' => $request->assigned_to,
            ]);

            $this->validateRequiredFields($request->fields);

            $leadForm->leadFormFields()->delete();

            foreach ($request->fields as $index => $fieldData) {

                LeadFormField::create([
                    'form_id' => $leadForm->id,
                    'label' => $fieldData['label'],
                    'name' => $fieldData['is_mapped'] ? ($fieldData['name'] ?? null) : null,
                    'type' => $fieldData['type'],
                    'is_required' => $fieldData['is_required'] ?? false,
                    'is_mapped' => $fieldData['is_mapped'] ?? false,
                    'options' => !empty($fieldData['options']) ? json_encode($fieldData['options']) : null,
                    'placeholder' => $fieldData['placeholder'] ?? null,
                    'order' => $index + 1,
                    'validation_rules' => $this->generateValidationRules($fieldData),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lead form updated successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update form: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {

        $leadForm = LeadForm::with(['leadFormFields' => function ($query) {
            $query->orderBy('order');
        }])->findOrFail($id);


        $sources = LeadSource::where('workspace_id', auth()->user()->workspace_id)->get();
        $stages = LeadStage::where('workspace_id', auth()->user()->workspace_id)->get();
        $users = User::all();


        return view('lead_forms.edit', compact('leadForm', 'sources', 'stages', 'users'));
    }

    public function embed(LeadForm $leadForm)
    {
        return view('lead_forms.embed', compact('leadForm'));
    }

    public function responses(Request $request, $id)
    {
        $leadForm = LeadForm::findOrFail($id);

        return view('lead_forms.responses', compact('leadForm'));
    }


    public function responseList(Request $request, $id)
    {
        $leadForm = LeadForm::findOrFail($id);

        $search = $request->input('search');
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'DESC');

        $query = $leadForm->leads(); 

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('company', 'like', "%$search%");
            });
        }

        $total = $query->count();

        $leads = $query->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($lead) {
                return [
                    'id' => $lead->id,
                    'name' => $lead->first_name . ' ' . $lead->last_name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'company' => $lead->company ?? '-',
                    'submitted_at' => format_date($lead->created_at, to_format: "Y-m-d"),
                    'actions' => '<a href="' . route('leads.show', $lead->id) . '" class="btn btn-sm btn-outline-primary">View</a>',
                ];
            });

        return response()->json([
            'total' => $total,
            'rows' => $leads,
        ]);
    }
    public function destroy($id)
    {
        $leadForm = LeadForm::findOrFail($id);

        $response = DeletionService::delete(LeadForm::class, $leadForm->id, 'Lead Form');

        return $response;
    }

    public function destroy_multiple(Request $request)
    {

        $validatedData = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:lead_forms,id'
        ]);



        $ids = $validatedData['ids'];
        $deletedIds = [];

        foreach ($ids as $id) {
            $candidate = LeadForm::findOrFail($id);
            $deletedIds[] = $id;

            DeletionService::delete(LeadForm::class, $candidate->id, 'Candidate');
        }

        return response()->json([
            'error' => false,
            'message' => 'Lead Form(s) Deleted Successfully!',
            'id' => $deletedIds,
        ]);
    }

    private function validateRequiredFields($fields)
    {
        $requiredFieldNames = LeadFormField::REQUIRED_FIELDS;
        $providedMappedFields = collect($fields)
            ->where('is_mapped', true)
            ->pluck('name')
            ->toArray();

        $missingFields = array_diff($requiredFieldNames, $providedMappedFields);

        if (!empty($missingFields)) {
            throw new \Exception('Missing required fields: ' . implode(', ', $missingFields));
        }
    }

    private function generateValidationRules($fieldData)
    {
        $rules = [];
        if ($fieldData['is_required'] ?? false) {
            $rules[] = 'required';
        }

        switch ($fieldData['type']) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'tel':
                $rules[] = 'regex:/^[\+]?[1-9][\d]{0,15}$/';
                break;
            case 'url':
                $rules[] = 'url';
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'date':
                $rules[] = 'date';
                break;
        }

        return implode('|', $rules);
    }
}
