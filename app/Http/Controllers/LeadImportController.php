<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LeadImportController extends Controller
{
    /**
     * Display the bulk upload form
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('leads.bulk-upload');
    }

    /**
     * Parse the uploaded Excel/CSV file and return headers and preview data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function parse(Request $request)
    {
        // Validate the uploaded file
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max size
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'File validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Store the file temporarily
            $tempPath = $request->file('file')->store('temp_leads');

            // Determine file type and load the Excel/CSV file
            $extension = $request->file('file')->getClientOriginalExtension();

            if (in_array($extension, ['xlsx', 'xls'])) {
                $data = Excel::toArray([], storage_path('app/' . $tempPath));
            } elseif ($extension == 'csv') {
                $data = Excel::toArray([], storage_path('app/' . $tempPath), null, \Maatwebsite\Excel\Excel::CSV);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported file type'
                ], 400);
            }

            // Extract first sheet's data
            $sheet = $data[0];

            // Validate that the sheet has data
            if (count($sheet) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'File contains insufficient data'
                ], 400);
            }

            // Extract headers (first row)
            $headers = $sheet[0];

            // Get preview data (excluding headers)
            $rows = array_slice($sheet, 1, 5); // Just show first 5 rows for preview

            return response()->json([
                'success' => true,
                'message' => 'File parsed successfully',
                'data' => [
                    'headers' => $headers,
                    'rows' => $rows,
                    'temp_path' => $tempPath,
                    'showModal' => true,
                    'total_rows' => count($sheet) - 1
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('File parsing error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to parse file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview the mapped leads data before import
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewMappedLeads(Request $request)
    {
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'mapping' => 'required|array',
            'temp_path' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid mapping data',
                'errors' => $validator->errors()
            ], 422);
        }

        $mapping = $request->input('mapping');
        $tempPath = $request->input('temp_path');
        $filePath = storage_path('app/' . $tempPath);

        // Check if file exists
        if (!Storage::exists($tempPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Uploaded file not found'
            ], 404);
        }

        try {
            // Check the file extension
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            // Load the file based on its extension
            if (in_array($extension, ['xlsx', 'xls'])) {
                $data = Excel::toArray([], $filePath);
            } elseif ($extension == 'csv') {
                $data = Excel::toArray([], $filePath, null, \Maatwebsite\Excel\Excel::CSV);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported file type'
                ], 400);
            }

            // Extract the first sheet's data
            $sheet = $data[0];

            // Get the headers (first row)
            $headers = $sheet[0];

            // Clean up the headers (trim spaces and normalize case)
            $headers = array_map(function ($header) {
                return strtolower(trim($header));
            }, $headers);

            // Get the rows (excluding the headers)
            $rows = array_slice($sheet, 1);

            // Map the rows to the selected database fields
            $mappedData = [];
            foreach ($rows as $index => $row) {
                if ($index >= 5) break; // Only preview first 5 rows

                $mappedRow = [];
                foreach ($mapping as $dbField => $excelField) {
                    // Clean up the Excel field
                    $excelField = strtolower(trim($excelField));

                    // Search for the index of the selected field in the headers
                    $columnIndex = array_search($excelField, $headers);

                    if ($columnIndex !== false && isset($row[$columnIndex])) {
                        $mappedRow[$dbField] = trim($row[$columnIndex]);
                    } else {
                        $mappedRow[$dbField] = null;
                    }
                }
                $mappedData[] = $mappedRow;
            }

            // Return the mapped data for preview
            return response()->json([
                'success' => true,
                'message' => 'Data mapped successfully',
                'data' => [
                    'mapped_data' => $mappedData,
                    'total_rows' => count($rows)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Data mapping error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to map data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import the leads from the uploaded and mapped file
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'mapping' => 'required|array',
            'temp_path' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid import data',
                'errors' => $validator->errors()
            ], 422);
        }

        $mapping = $request->input('mapping');
        $path = $request->input('temp_path');

        // Check if file exists
        if (!Storage::exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Uploaded file not found'
            ], 404);
        }

        try {
            $rows = Excel::toArray([], storage_path("app/{$path}"))[0];

            // Ensure we have data to import
            if (count($rows) <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data to import'
                ], 400);
            }

            $headers = array_map('trim', $rows[0]);
            unset($rows[0]);

            $errors = [];
            $importedLeads = [];
            $failedRows = [];
            $ids = [];

            $rowNumber = 1;

            foreach ($rows as $row) {
                $rowNumber++;
                $row = array_map('trim', $row);

                $leadData = [];
                foreach ($mapping as $dbField => $excelHeader) {
                    $index = array_search(trim($excelHeader), $headers);
                    $leadData[$dbField] = $index !== false ? ($row[$index] ?? null) : null;
                }
                // dd($leadData);
                // Handle source and stage before validation
                try {
                    // Process source
                    if (!empty($leadData['source'])) {
                        $source = LeadSource::where('name', $leadData['source'])
                            ->where(function ($query) {
                                $query->where('workspace_id', getWorkspaceId())
                                    ->orWhere(function ($q) {
                                        $q->whereNull('workspace_id')->where('is_default', true);
                                    });
                            })
                            ->first();


                        if (!$source) {
                            // If no matching source is found, create a new one
                            $source = LeadSource::create([
                                'workspace_id' => getWorkspaceId(),
                                'admin_id' => getAdminIDByUserRole(),
                                'name' => $leadData['source'],
                                'is_default' => false, // Ensure it's not marked as default if newly created
                            ]);
                        }
                        $leadData['source_id'] = $source->id;
                    }
                    unset($leadData['source']); // Remove source name from leadData

                    // Process stage
                    if (!empty($leadData['stage'])) {
                        // Get the maximum order value
                        $maxOrder = LeadStage::getNextOrderForWorkspace( getWorkspaceId());

                        $stage = LeadStage::where('name', $leadData['stage'])
                            ->where(function ($query) {
                                $query->where('workspace_id', getWorkspaceId())
                                    ->orWhere(function ($q) {
                                        $q->whereNull('workspace_id')->where('is_default', true);
                                    });
                            })
                            ->first();

                        // dd($stage);
                        // dd(getWorkspaceId(), $leadData['stage']);

                        if (!$stage) {
                            // If no matching stage is found, create a new one
                            // dd($leadData['stage'] , $stage , generateUniqueSlug($leadData['stage'], LeadStage::class));
                            $stage = LeadStage::create([
                                'workspace_id' => getWorkspaceId(),
                                'admin_id' => getAdminIDByUserRole(),
                                'name' => $leadData['stage'],
                                'slug' => generateUniqueSlug($leadData['stage'], LeadStage::class),
                                'order' => $maxOrder + 1,
                                'color' => 'primary', // Default color
                                'is_default' => false, // Ensure it's not marked as default if newly created
                            ]);
                        }
                        $leadData['stage_id'] = $stage->id;
                    }
                    unset($leadData['stage']); // Remove stage name from leadData

                } catch (\Exception $e) {
                    Log::error('Error processing source/stage at row ' . $rowNumber . ': ' . $e->getMessage());
                    $errors["Row {$rowNumber}"] = ['source_stage_error' => $e->getMessage()];
                    $failedRows[] = [
                        'row' => $rowNumber,
                        'data' => $leadData,
                        'errors' => ['source_stage_error' => $e->getMessage()]
                    ];
                    continue;
                }


                // Add required system fields
                $leadData['workspace_id'] = getWorkspaceId();
                $leadData['created_by'] = auth()->id();
                $leadData['assigned_to'] = auth()->id();
                $leadData['admin_id'] = getAdminIDByUserRole();
                // Validation rules
                $validationRules = [
                    'first_name'        => 'required|string|max:255',
                    'last_name'         => 'required|string|max:255',
                    'email'             => 'required|email|unique:leads,email',
                    'phone'             => 'required|string|max:20',
                    'country_code'      => 'required|string|max:5',
                    'country_iso_code'  => 'required|string|size:2',
                    'source_id'         => 'required|exists:lead_sources,id',
                    'stage_id'          => 'required|exists:lead_stages,id',
                    'job_title'         => 'nullable|string|max:255',
                    'industry'          => 'nullable|string|max:255',
                    'company'           => 'required|string|max:255',
                    'website'           => 'nullable|url|max:255',
                    'linkedin'          => 'nullable|url|max:255',
                    'instagram'         => 'nullable|url|max:255',
                    'facebook'          => 'nullable|url|max:255',
                    'pinterest'         => 'nullable|url|max:255',
                    'city'              => 'nullable|string|max:255',
                    'state'             => 'nullable|string|max:255',
                    'zip'               => 'nullable|string|max:20',
                    'country'           => 'nullable|string|max:255',
                ];

                $validator = Validator::make($leadData, $validationRules);

                if ($validator->fails()) {
                    $errors["Row {$rowNumber}"] = $validator->errors()->messages();
                    $failedRows[] = [
                        'row' => $rowNumber,
                        'data' => $leadData,
                        'errors' => $validator->errors()->messages()
                    ];
                    continue;
                }

                try {
                    $lead = Lead::create($leadData);
                    $ids[] = $lead->id;
                    $importedLeads[] = $lead->toArray();
                } catch (\Exception $e) {
                    Log::error('Lead import error at row ' . $rowNumber . ': ' . $e->getMessage());
                    $errors["Row {$rowNumber}"] = ['exception' => $e->getMessage()];
                    $failedRows[] = [
                        'row' => $rowNumber,
                        'data' => $leadData,
                        'errors' => ['exception' => $e->getMessage()]
                    ];
                }
            }

            // Prepare response
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => count($importedLeads) . ' leads imported successfully with ' . count($errors) . ' errors.',
                    'data' => [
                        'successful' => count($importedLeads),
                        'failed' => count($errors),
                        'total' => count($rows),
                        'imported_leads' => $importedLeads,
                        'failed_rows' => $failedRows,
                        'showInModal' => true
                    ]
                ], 422);
            }

            // Clean up the temporary file
            Storage::delete($path);

            return response()->json([
                'success' => true,
                'message' => count($importedLeads) . ' leads imported successfully.',
                'data' => [
                    'total' => count($importedLeads),
                    'imported_leads' => $importedLeads
                ],
                'ids' => $ids,
                'type' => 'leads',
            ]);
        } catch (\Exception $e) {
            Log::error('Lead import process error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to import leads',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
