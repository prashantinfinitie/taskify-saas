<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadForm;
use App\Models\LeadFormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PublicFormController extends Controller
{
    public function show($slug)
    {
        $form = LeadForm::with(['leadFormFields' => function ($query) {
            $query->orderBy('order');
        }])->where('slug', $slug)->firstOrFail();

        return view('lead_forms.public_form', compact('form'));
    }

    public function submit(Request $request, $slug)
    {

        // dd($request->input('country_code'));
        $form = LeadForm::with(['leadFormFields' => function ($query) {
            $query->orderBy('order');
        }])->where('slug', $slug)->firstOrFail();

        $rules = [];
        $messages = [];

        foreach ($form->leadFormFields as $field) {
            $fieldName = $field->name ?: 'field_' . $field->id;
            $fieldRules = $field->validation_rules ? explode('|', $field->validation_rules) : [];

            if ($field->is_required && !in_array('required', $fieldRules)) {
                $fieldRules[] = 'required';
            }

            if ($field->name === 'phone') {
                $fieldRules = array_filter($fieldRules, function ($rule) {
                    return !str_starts_with($rule, 'regex:');
                });
                $fieldRules[] = 'regex:/^[\+]?[0-9\-\(\)\s]{7,20}$/';
            }
            // dd($field->name)
            // Additional hardcoded validations for known mapped fields
            switch ($field->name) {
                case 'email':
                    $fieldRules[] = 'email';
                    $messages[$fieldName . '.email'] = $field->label . ' must be a valid email address.';
                    break;
                case 'website':
                case 'linkedin':
                case 'instagram':
                case 'facebook':
                case 'pinterest':
                    $fieldRules[] = 'url';
                    $messages[$fieldName . '.url'] = $field->label . ' must be a valid URL.';
                    break;
                case 'country_iso_code':
                    $fieldRules[] = 'size:2';
                    $messages[$fieldName . '.size'] = $field->label . ' must be exactly 2 characters.';
                    break;
                case 'country_code':
                    $fieldRules[] = 'regex:/^[\+\-A-Za-z0-9]{1,5}$/';
                    $messages[$fieldName . '.regex'] = $field->label . ' must be alphanumeric and may include "+" or "-".';
                    break;
            }
            if (!empty($fieldRules)) {
                $rules[$fieldName] = $fieldRules;
                $messages[$fieldName . '.required'] = $field->label . ' is required.';

                // Fix: only add fallback if no custom message set
                if (!isset($messages[$fieldName . '.max'])) {
                    $messages[$fieldName . '.max'] = $field->label . ' may not be greater than the allowed length.';
                }

                $messages[$fieldName . '.regex'] = $field->label . ' is invalid.';
                $messages[$fieldName . '.numeric'] = $field->label . ' must be a number.';
                $messages[$fieldName . '.date'] = $field->label . ' must be a valid date.';
            }
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Please fix the errors below.',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $leadData = [
                'workspace_id' => $form->workspace_id,
                'source_id' => $form->source_id,
                'stage_id' => $form->stage_id,
                'assigned_to' => $form->assigned_to,
                'lead_form_id' => $form->id,
                'custom_fields' => [],
                'admin_id' => getAdminIdByUserRole()
            ];

            foreach ($form->leadFormFields as $field) {
                $fieldName = $field->name ?: 'field_' . $field->id;
                $value = $request->input($fieldName);

                if ($field->name === 'phone' && $value) {
                    $value = preg_replace('/[^0-9\+]/', '', $value);
                }

                if ($field->is_mapped && $field->name) {
                    $leadData[$field->name] = $value;
                } elseif (!$field->is_mapped && $value !== null) {
                    $leadData['custom_fields'][$field->label] = [
                        'value' => $value,
                        'type' => $field->type,
                        'required' => $field->is_required,
                        'options' => $field->type === 'select' ? ($field->options ?? []) : null,
                    ];
                }
            }

            foreach (LeadFormField::REQUIRED_FIELDS as $requiredField) {
                if (empty($leadData[$requiredField])) {
                    throw new \Exception(ucfirst(str_replace('_', ' ', $requiredField)) . ' is required');
                }
            }

            $leadData['custom_fields'] = json_encode($leadData['custom_fields']);
            $lead = Lead::create($leadData);

            DB::commit();


            return response()->json(['error' => false, 'message' => 'Form Submitted Successfully' , 'redirect_url' => route('lead_forms.submitted')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit form: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function json($slug)
    {
        $form = LeadForm::with(['leadFormFields' => function ($query) {
            $query->orderBy('order');
        }])->where('slug', $slug)->where('is_active', true)->firstOrFail();

        return response()->json([
            'id' => $form->id,
            'title' => $form->title,
            'description' => $form->description,
            'fields' => $form->leadFormFields->map(function ($field) {
                return [
                    'id' => $field->id,
                    'label' => $field->label,
                    'name' => $field->name ?: 'field_' . $field->id,
                    'type' => $field->type,
                    'is_required' => $field->is_required,
                    'placeholder' => $field->placeholder,
                    'options' => $field->options
                ];
            })
        ]);
    }
}
