<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Services\DeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomFieldsController extends Controller
{
    public function index()
    {
        $customFields = CustomField::all();
        return view('settings.custom_fields', compact('customFields'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        // $request->all();
        $adminId = getAdminIdByUserRole();
        $rules = [
            'module' => 'required|string|in:project,task',
            'field_label' => 'required|string',
            'field_type' => 'required|string|in:text,number,password,textarea,radio,date,checkbox,select',
            'options' => 'nullable|string|required_if:field_type,radio,checkbox,select',
            'required' => 'nullable|string',
            'visibility' => 'nullable|string',
        ];
        $validator = Validator::make($request->all(), $rules, [
            'regex' => 'This field must not contain special characters.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customField = new CustomField();
        $customField->admin_id = $adminId;
        $customField->module = $request->module;
        $customField->field_type = $request->field_type;
        $customField->field_label = $request->field_label;
        $customField->name = '';
        $customField->options = in_array($request->field_type, ['radio', 'checkbox', 'select'])
            ? json_encode(preg_split('/\r\n|\r|\n/', trim($request->options)))
            : null;

        $customField->required = $request->required;
        $customField->visibility = $request->visibility;
        $customField->save();

        return response()->json(['success' => 'Custom field created successfully'], 200);
    }

    public function update(Request $request, string $id)
    {
        $field = CustomField::find($id);
        $adminId = getAdminIdByUserRole();

        if (!$field || !$adminId) {
            return response()->json(['success' => false, 'message' => !$field ? 'Field not found' : 'Unauthorized'], !$field ? 404 : 403);
        }

        $rules = [
            'module' => 'required|string|in:project,task',
            'field_label' => 'required|string',
            'field_type' => 'required|string|in:text,number,password,textarea,radio,date,checkbox,select',
            'options' => 'nullable|string|required_if:field_type,radio,checkbox,select',
            'required' => 'nullable|string',
            'visibility' => 'nullable|string',
        ];
        $validator = Validator::make($request->all(), $rules, [
            'regex' => 'This field must not contain special characters.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $field->admin_id = $adminId;
        $field->module = $request->module;
        $field->field_label = $request->field_label;
        $field->field_type = $request->field_type;
        $field->options = in_array($request->field_type, ['radio', 'checkbox', 'select'])
            ? json_encode(preg_split('/\r\n|\r|\n/', trim($request->options)))
            : null;
        $field->required = $request->required;
        $field->visibility = $request->visibility;
        $field->save();

        return response()->json(['success' => 'Custom field updated successfully'], 200);
    }

    public function edit(string $id)
    {
        $field = CustomField::find($id);
        $adminId = getAdminIdByUserRole();

        if (!$field || !$adminId) {
            return response()->json(['success' => false, 'message' => !$field ? 'Field not found' : 'Unauthorized'], !$field ? 404 : 403);
        }

        // Decode JSON options for radio, checkbox, select
        if (in_array($field->field_type, ['radio', 'checkbox', 'select']) && $field->options) {
            $field->options = json_decode($field->options, true);
        }

        return response()->json(['success' => true, 'data' => $field]);
    }



   public function destroy(string $id)
{
    $field = CustomField::find($id);

    if (!$field) {
        return response()->json([
            'success' => false,
            'message' => 'Field not found',
        ], 404);
    }

    $adminId = getAdminIdByUserRole();

    if (!$adminId) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ], 403);
    }

    $response = DeletionService::delete(CustomField::class, $field->id, 'Custom Field');

    return $response;
}


    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:custom_fields,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $customField = CustomField::findOrFail($id);
            $deletedIds[] = $id;
            $deletedTitles[] = $customField->field_label;
            DeletionService::delete(CustomField::class, $id, 'custom_fields');
        }

        return response()->json(['error' => false, 'message' => 'Custom Field(s) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles]);
    }
    public function list()
    {
        $search = request('search');
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $limit = request('limit', 10);
        $offset = request('offset', 0);


        $customFields = CustomField::orderBy($sort, $order);
        $customFields->where('admin_id', getAdminIdByUserRole());

        if ($search) {
            $customFields = $customFields->where(function ($query) use ($search) {
                $query->where('module', 'like', '%' . $search . '%')
                    ->orWhere('field_label', 'like', '%' . $search . '%')
                    ->orWhere('field_type', 'like', '%' . $search . '%');
            });
        }

        $total = $customFields->count();

        $customFields = $customFields
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(
                fn($field) => [
                    'id' => $field->id,
                    'module' => $field->module,
                    'field_label' => $field->field_label,
                    'field_type' => $field->field_type,
                    'required' => ($field->required == '1') ? 'Yes' : 'No',
                    'visibility' => ($field->visibility == '1') ? 'Yes' : 'No',
                    'actions' => ''
                ]
            );

        return response()->json([
            "rows" => $customFields,
            "total" => $total,
        ]);
    }
}
