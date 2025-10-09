<?php

namespace Plugins\AssetManagement\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Workspace;
use App\Imports\AssetImport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\DeletionService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;
use Plugins\AssetManagement\Models\Asset;
use Illuminate\Validation\ValidationException;
use Plugins\AssetManagement\Models\AssetHistory;
use Plugins\AssetManagement\Exports\AssetsExport;
use Plugins\AssetManagement\Imports\AssetsImport;
use Plugins\AssetManagement\Models\AssetCategory;


class AssetsController extends Controller
{
    /**
     * Get current workspace assets with proper scoping
     */

    private function getWorkspaceAssetsQuery()
    {

        $workspace = Workspace::find(session()->get('workspace_id'));

        if (!$workspace) {
            abort(403, 'workspace not found');
        }

        $adminId = getAdminIdByUserRole();

        return Asset::where('admin_id', $adminId);
    }

    private function getWorkspaceUsers()
    {

        $workspace = Workspace::find(session()->get('workspace_id'));

        if (!$workspace) {
            return collect([]);
        }

        return $workspace->users();
    }

    public function index()
    {

        $workspace = Workspace::find(session()->get('workspace_id'));


        if (!$workspace) {
            abort(403, 'UnAuthorized Action.');
        }

        $users = $this->getWorkspaceUsers()->get();

        $adminId = getAdminIdByUserRole();

        $categories = AssetCategory::where('admin_id', $adminId)->get();

        if (isAdminOrHasAllDataAccess()) {
            $assets = $this->getWorkspaceAssetsQuery()->get();
        } else {
            // Normal users see only see assets assigned to them
            $assets = Asset::where('assigned_to', auth()->id())->get();

            $assets = $this->getWorkspaceAssetsQuery()
                ->where('assigned_to', auth()->id())
                ->orWhere('assigned_to', auth()->id())
                ->get();
        }

        return view('assets::assets.index', compact('assets', 'users', 'categories'));
    }

    public function show($id)
    {
        try {

            $asset = $this->getWorkspaceAssetsQuery()
                ->with(['histories' => function ($query) {
                    $query->with(['user', 'lentToUser', 'returnedByUser'])
                        ->orderBy('created_at', 'desc');
                }])->findOrfail($id);

            if (!isAdminOrHasAllDataAccess() && $asset->assigned_to !== auth()->id()) {
                abort(403, 'UnAuthorized Access to Asset');
            }

            $users = $this->getWorkspaceUsers()->get(); // For lending modal

            return view('assets::assets.show', compact('asset', 'users'));
        } catch (\Exception $e) {
            // dd($e);

            return response()->json([
                'error' => true,
                'message' => config('app.debug') ? $e->getMessage() : 'An error occured'
            ]);
        }
    }

    public function history($id)
    {
        $asset = $this->getWorkspaceAssetsQuery()->with(['histories' => function ($query) {
            $query->with(['user', 'lentToUser', 'returnedByUser'])
                ->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        if (!isAdminOrHasAllDataAccess() && $asset->assigned_to !== auth()->id()) {
            abort(403, 'UnAuthorized Access to Asset History');
        }

        return response()->json($asset->histories);
    }

    public function lend(Request $request, $id)
    {
        $isApi = true;

        $request->validate([
            'lent_to' => 'required|exists:users,id',
            'estimated_return_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string'
        ]);



        $lentToUser = $this->getWorkspaceUsers()->find($request->lent_to);

        if (!$lentToUser) {

            return response()->json([
                'error' => true,
                'message' => 'Selected user is not in your workspace'
            ]);
        }



        $asset = $this->getWorkspaceAssetsQuery()
            ->where('id', $id)
            ->firstOrFail();


        // Check if asset is available for lending
        if ($asset->status !== 'available') {
            return response()->json([
                'error' => true,
                'message' => 'Asset is not available for lending.'
            ], 400);
        }

        try {
            DB::transaction(function () use ($asset, $request) {
                // Create lending history
                AssetHistory::create([
                    'asset_id' => $asset->id,
                    'user_id' => auth()->id(),
                    'action' => 'Lent',
                    'lent_to' => $request->lent_to,
                    'date_given' => now(),
                    'estimated_return_date' => $request->estimated_return_date,
                    'notes' => $request->notes
                ]);

                // Update asset status and assignment
                $asset->update([
                    'assigned_to' => $request->lent_to,
                    'status' => 'lent'
                ]);
            });

            return response()->json([
                'error' => false,
                'message' => 'Asset lent successfully!'
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            // dd($e);
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while lending the asset.'
            ], 500);
        }
    }

    public function returnAsset(Request $request, $id)
    {
        $asset = $this->getWorkspaceAssetsQuery()
            ->where('id', $id)
            ->firstOrFail();

        // check if user is authorized to return asset
        if (!isAdminOrHasAllDataAccess() && $asset->assigned_to !== auth()->id()) {

            return response()->json([
                'error' => true,
                'message' => 'Unauthorized: You can only return assets assigned to you.'
            ], 403);
        }



        // Find the current lending record
        $currentLending = AssetHistory::where('asset_id', $id)
            ->where('action', 'Lent')
            ->whereNull('actual_return_date')
            ->first();

        if (!$currentLending) {
            return response()->json([
                'error' => true,
                'message' => 'No active lending record found for this asset.'
            ], 400);
        }

        try {
            DB::transaction(function () use ($currentLending, $asset, $request) {
                // Update the lending record with return information
                $currentLending->update([
                    'actual_return_date' => now(),
                    'returned_by' => auth()->id()
                ]);

                // Create a return history record
                AssetHistory::create([
                    'asset_id' => $asset->id,
                    'user_id' => auth()->id(),
                    'action' => 'Returned',
                    'notes' => $request->input('notes', 'Asset returned from lending.'),
                ]);

                // Update asset status

                // dd($asset->admin_id);
                $asset->update([
                    'status' => 'available',
                    'assigned_to' => null
                ]);
            });

            return response()->json([
                'error' => false,
                'message' => 'Asset returned successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while returning the asset.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $isApi = request()->get('isApi', false);
        $admin = Admin::where('user_id', auth()->id())->first();


        // Defining rules for validation
        $rules = [
            'name' => 'required|string|max:255',
            'asset_tag' => 'required|string|unique:assets,asset_tag,NULL,id,admin_id,' . $admin->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:asset_categories,id',
            'status' => 'required|string|in:available,non-functional,lent,lost,damaged,under-maintenance',
            'purchase_date' => 'nullable|date|before:today',
            'purchase_cost' => 'nullable|numeric',
            'picture' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp'
        ];


        try {

            // Validating data from request
            $data = $request->validate($rules);
            $data['admin_id'] =  $admin->id;

            if (!$admin) {
                abort(403, 'Unauthorized Action');
            }


            // Validate category belongs to same admin
            $category = AssetCategory::where('id', $data['category_id'])
                ->where('admin_id', $admin->id)
                ->first();

            if (!$category) {
                return response()->json([
                    'error' => true,
                    'message' => 'Invalid Category Selected'
                ]);
            }

            // Creating Asset
            $asset = Asset::Create($data);



            AssetHistory::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'action' => 'Created',
                'notes' => 'Asset created.',
                'admin_id' => $admin
            ]);


            // Handle picture
            if ($request->hasFile('picture')) {
                $asset->addMedia($request->file('picture'))->sanitizingFileName(function ($fileName) {

                    $baseName = pathinfo($fileName, PATHINFO_FILENAME);

                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);

                    $uniqueId = time() . '_' . mt_rand(1000, 9999);

                    return strtolower(str_replace(['#', '/', '\\', ' '], '-', $baseName)) . "-{$uniqueId}.{$extension}";
                })->toMediaCollection('asset-media');
            }


            return response()->json([
                'error' => false,
                'message' => 'Asset created successfully'
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            return formatApiResponse(
                true,
                config('app.debug') ? $e->getMessage() : 'An error occurred',
                [],
                500
            );
        }
    }

    public function update(Request $request, $id)
    {;
        $isApi = request()->get('isApi', false);
        $admin = Admin::where('user_id', auth()->id())->first();
        // Defining rules for validation
        $rules = [
            'name' => 'required|string|max:255',
            'asset_tag' => 'required|string|unique:assets,asset_tag,' . $id . ',id,admin_id,' . $admin->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:asset_categories,id',
            'status' => 'required|string|in:available,lent,non-functional,lost,damaged,under-maintenance',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric',
            'picture' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp'
        ];


        try {

            // Validating data from request
            $data = $request->validate($rules);

            $admin = Admin::where('user_id', auth()->id())->first();

            $data['admin_id'] =  $admin->id;
            if (!$admin->id) {
                abort(403, 'Unauthorized Action');
            }

            $asset = $this->getWorkspaceAssetsQuery()
                ->where('id', $id)
                ->firstOrFail();


            // Validate category belongs to same admin
            $category = AssetCategory::where('id', $data['category_id'])
                ->where('admin_id', $admin->id)
                ->first();

            if (!$category) {
                return response()->json([
                    'error' => true,
                    'message' => 'Invalid Category Selected'
                ]);
            }

            $asset->update($data);

            AssetHistory::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'action' => 'Updated',
                'notes' => 'Asset details updated.',
            ]);


            // Remove picture
            if ($request->boolean('remove_picture')) {
                $asset->clearMediaCollection('asset-media');
            }

            // Handle picture
            if ($request->hasFile('picture')) {
                // Optionally clear existing media if you only want one picture
                $asset->clearMediaCollection('asset-media');
                $asset->addMedia($request->file('picture'))->sanitizingFileName(function ($fileName) {

                    $baseName = pathinfo($fileName, PATHINFO_FILENAME);

                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);

                    $uniqueId = time() . '_' . mt_rand(1000, 9999);

                    return strtolower(str_replace(['#', '/', '\\', ' '], '-', $baseName)) . "-{$uniqueId}.{$extension}";
                })->toMediaCollection('asset-media');
            }


            return response()->json([
                'error' => false,
                'message' => 'Asset Updated successfully'
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            return formatApiResponse(
                true,
                config('app.debug') ? $e->getMessage() : 'An error occurred',
                [],
                500
            );
        }
    }

    public function destroy($id)
    {

        $asset = $this->getWorkspaceAssetsQuery()->findOrFail($id);

        $response = DeletionService::delete(Asset::class, $asset->id, 'Asset');

        return $response;
    }

    public function destroy_multiple(Request $request)
    {

        $validatedData = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:assets,id'
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];

        foreach ($ids as $id) {

            $asset = $this->getWorkspaceAssetsQuery()->findOrFail($id);


            $deletedIds[] = $id;
            DeletionService::delete(Asset::class, $asset->id, 'Asset');
        }

        return response()->json([
            'error' => false,
            'message' => 'Asset(s) deleted successfully.',
            'id' => $deletedIds
        ]);
    }

    // bulk assignment of asset to users
    public function bulkassign(Request $request)
    {
        $request->validate([
            'asset_ids' => 'required|array',
            'asset_ids.*' => 'exists:assets,id',
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable',
        ]);


        $workspace = Workspace::find(session()->get('workspace_id'));
        $assigned_to = $workspace->users()->find($request->assigned_to);


        if (!$assigned_to) {
            return response()->json([
                'error' => true,
                'message' => 'Selected user does not belong in this workspace'
            ]);
        }
        try {
            $asset_ids = $request->asset_ids;
            $assigned_to = $request->assigned_to;
            $notes = $request->notes;


            // Ensure all the assets belong to current workspace and are available
            $assets = $this->getWorkspaceAssetsQuery()
                ->whereIn('id', $asset_ids)
                ->get();


            // Pre-validate all assets before starting transaction
            foreach ($assets as $asset) {

                if ($asset->status !== 'available') {
                    return response()->json([
                        'error' => true,
                        'message' => "Asset {$asset->name} is not available for assignment.",
                    ], 400);
                }
            }

            DB::transaction(function () use ($assets, $assigned_to, $notes) {

                foreach ($assets as $asset) {


                    $asset->update([
                        'assigned_to' => $assigned_to,
                        'status' => 'lent'
                    ]);

                    AssetHistory::create([
                        'asset_id' => $asset->id,
                        'lent_to' => $assigned_to,
                        'user_id' => auth()->id(),
                        'action' => 'Lent',
                        'notes' => $notes,
                        'date_given' => now()
                    ]);
                }
            });
            return response()->json([
                'error' => false,
                'message' => 'Assets assigned successfully!'
            ]);
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $message = 'Validation failed: ' . implode(', ', $errors);
            return formatApiResponse(true, $message, [], 422);
        } catch (\Exception $e) {

            return response()->json([
                'error' => true,
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }



    public function list()
    {
        $search = request('search');
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $limit = request('limit', 10);
        $offset = request('offset', 0);
        $categories = request('categories');
        $assigned_to = request('assigned_to');

        $asset_status = request()->query('asset_status');

        $adminId = getAdminIdByUserRole();

        $query = Asset::query()->where('admin_id', $adminId);

        // Restrict normal users to their own assets
        if (!isAdminOrHasAllDataAccess()) {
            $query->where('assigned_to', auth()->id());
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->OrWhere('asset_tag', 'like', "%$search%")
                    ->Orwhere('description', 'like', "%$search%");
            });
        }

        if ($categories) {
            $query->whereIn('category_id', $categories);
        }

        if ($assigned_to) {
            $query->whereIn('assigned_to', $assigned_to);
        }


        if ($asset_status) {
            $query->whereIn('status', $asset_status);
        }


        $total = $query->count();

        $canEdit = checkPermission('edit_assets');
        $canDelete = checkPermission('delete_assets');
        $canCreate = checkPermission('create_assets');

        $assets = $query->orderBy($sort, $order)
            ->take($limit)
            ->skip($offset)
            ->get()
            ->map(function ($asset) use ($canEdit, $canDelete, $canCreate) {
                $general_settings = get_settings('general_settings');
                $currency_symbol = $general_settings['currency_symbol'] ?? '₹';
                $actions = '';

                // Edit button (icon)
                if ($canEdit) {
                    $assetData = $asset->toArray();
                    $assetData['picture_url'] = $asset->getFirstMediaUrl('asset-media');
                    $assetData['purchase_date'] = format_date($asset->purchase_date, false, 'Y-m-d\TH:i:s.u\Z', 'Y-m-d');

                    $actions .= '<a href="javascript:void(0);"
                        class="updateAssetOffcanvasBtn"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#updateAssetOffcanvas"
                        data-asset=\'' . htmlspecialchars(json_encode($assetData), ENT_QUOTES, 'UTF-8') . '\'
                        title="' . get_label('update', 'Update') . '">
                        <i class="bx bx-edit text-primary mx-1"></i>
                    </a>';
                }

                // Delete button (icon)
                if ($canDelete) {
                    $actions .= '<button type="button"
                    class="btn delete"
                    data-id="' . $asset->id . '"
                    data-type="assets"
                    title="' . get_label('delete', 'Delete') . '">
                    <i class="bx bx-trash text-danger mx-1"></i>
                </button>';
                }

                // Duplicate Assets

                if ($canCreate) {
                    $actions .= '<a href="javascript:void(0);" class="duplicateAsset" data-asset=\'' . htmlspecialchars(json_encode($assetData), ENT_QUOTES, 'UTF-8') . '\' data-id="' . $asset->id . '" data-title="' . $asset->title . '" data-type="asset" data-table="asset_table" data-reload="' . ('true') . '" title="' . get_label('duplicate', 'Duplicate') . '">' .
                        '<i class="bx bx-copy text-warning mx-2"></i>' .
                        '</a>';
                }

                // Helper function to get asset tag badge color (dynamic based on tag hash)
                $getAssetTagBadgeClass = function ($assetTag) {
                    if (empty($assetTag)) return 'bg-secondary';

                    $colors = [
                        'bg-primary',
                        'bg-info',
                        'bg-success',
                        'bg-warning',
                        'bg-danger',
                        'bg-dark',
                        'bg-secondary',
                        'bg-light text-dark'
                    ];

                    // Use string hash to ensure consistent color for same asset tag
                    $hash = crc32($assetTag);
                    $colorIndex = abs($hash) % count($colors);
                    return $colors[$colorIndex];
                };

                if ($asset->category) {
                    $category = '<span class=" badge bg-' . $asset->category->color . '">' . $asset->category->name . '</span>';
                } else {
                    $category = '-';
                }

                return [
                    'id' => $asset->id,
                    'name' => "<a href='" . route('assets.show', ['id' => $asset->id]) . "'>{$asset->name}</a>",
                    'asset_tag' => $asset->asset_tag ?
                        '<span class="badge ' . $getAssetTagBadgeClass($asset->asset_tag) . '">' . $asset->asset_tag . '</span>'
                        : '<span class="badge bg-secondary">N/A</span>',
                    'lent_to' => formatUserHtml($asset->assignedUser) ?? 'N/A',
                    'category' => $category,
                    'status' => '<span class="badge ' . $asset->getStatusBadgeClass() . '">' . ucfirst(str_replace('-', ' ', $asset->status)) . '</span>',
                    'purchase_date' => format_date($asset->purchase_date, false, 'Y-m-d'),
                    'purchase_cost' => $asset->purchase_cost ? ($currency_symbol . " " . $asset->purchase_cost) : "-",
                    'description' => $asset->description,
                    'created_at' => format_date($asset->created_at, false, 'Y-m-d'),
                    'updated_at' => format_date($asset->updated_at, false, 'Y-m-d'),
                    'actions' => $actions,
                ];
            });

        return response()->json([
            'rows' => $assets,
            'total' => $total
        ]);
    }

    public function globalAnalytics()
    {

        $workspace = $this->getWorkspaceUsers();


        $users =  $workspace
            ->with(['assets', 'assets.category'])
            ->whereHas('assets')
            ->get();


        $adminId = getAdminIdByUserRole();

        $assets = Asset::where('admin_id', $adminId)->get();

        // Get all assets and count status in PHP
        $statusCounts = $assets
            ->groupBy('status')
            ->map(fn($items) => $items->count());

        $allStatuses = ['available', 'lent', 'non-functional', 'lost', 'damaged', 'under-maintenance'];

        $statusData = [];
        foreach ($allStatuses as $status) {
            $statusData[$status] = $statusCounts[$status] ?? 0;
        }

        return view('assets::assets.global_analytics', compact('users', 'statusData'));
    }


    public function search(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type');
        $results = [];

        $adminId = getAdminIdByUserRole();

        if ($type) {
            // handle single type search
            switch ($type) {
                case 'asset_category':
                    $asset_categories = AssetCategory::where('name', 'like', '%' . $query . '%')->where('admin_id', $adminId)->get();
                    foreach ($asset_categories as $asset_category) {
                        $results[] = [
                            'id' => $asset_category->id,
                            'text' => $asset_category->name
                        ];
                    }
                    break;
                case 'assets':
                    $assets = Asset::where('name', 'like', '%' . $query . '%')->where('admin_id', $adminId)->Available()->get();
                    foreach ($assets as $asset) {
                        $results[] = [
                            'id' => $asset->id,
                            'text' => $asset->name
                        ];
                    }
                    break;

                case 'users':
                    $workspace = Workspace::find(session()->get('workspace_id'));
                    $users = $workspace->users()
                        ->where('first_name', 'like', '%' . $query . '%')
                        ->whereDoesntHave('roles', function ($q) {
                            $q->whereIn('name', ['admin', 'superadmin']);
                        })
                        ->get();
                    foreach ($users as $user) {
                        $results[] = [
                            'id' => $user->id,
                            'text' => $user->first_name . " " . $user->last_name
                        ];
                    }
                    break;

                default:
                    break;
            }
        }

        return response()->json(['results' => $results]);
    }

    public function duplicate($id)
    {
        $isApi = request()->get('isApi', false);
        try {
            $validated = request()->validate([
                'asset_tag' => 'required|string|unique:assets,asset_tag',
            ]);

            $originalAsset = $this->getWorkspaceAssetsQuery()
                ->with(['category'])
                ->findOrFail($id);

            DB::beginTransaction();

            $duplicateAsset = $originalAsset->replicate();
            $duplicateAsset->asset_tag = $validated['asset_tag'];
            $duplicateAsset->assigned_to = null;
            $duplicateAsset->status = 'available';
            $duplicateAsset->save();

            // Copy media
            if ($originalAsset->hasMedia('asset-media')) {
                $mediaItem = $originalAsset->getFirstMedia('asset-media');
                if ($mediaItem) {
                    $duplicateAsset->addMedia($mediaItem->getPath())
                        ->sanitizingFileName(function ($fileName) {
                            $baseName = pathinfo($fileName, PATHINFO_FILENAME);
                            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                            $uniqueId = time() . '-' . mt_rand(1000, 9999);
                            return strtolower(str_replace(['#', '/', '\\', ' '], '-', $baseName)) . "-{$uniqueId}.{$extension}";
                        })
                        ->toMediaCollection('asset-media');
                }
            }


            AssetHistory::create([
                'asset_id' => $duplicateAsset->id,
                'user_id' => auth()->id(),
                'action' => 'created',
                'notes' => 'Asset created as a duplicate of ' . $originalAsset->name . ' (ID: ' . $originalAsset->id . ')',
            ]);

            DB::commit();

            return response()->json([
                'error' => false,
                'message' => 'Asset duplicated successfully.'
            ]);
        } catch (ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => config('app.debug') ? $e->getMessage() : 'Asset duplication failed.',
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $isApi = $request->get('isApi', false);

        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
            ]);

            $importer = new AssetsImport;
            Excel::import($importer, $request->file('file'));

            if (!empty($importer->errors)) {
                return response()->json([
                    'error' => true,
                    'message' => 'Some rows failed validation.',
                    'validation_errors' => $importer->errors,
                ], 422);
            }

            return response()->json([
                'error' => false,
                'message' => 'Assets imported successfully.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return formatApiValidationError($isApi, $e->errors());
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'Duplicate entry')) {
                return response()->json([
                    'error' => true,
                    'message' => 'One or more assets have duplicate asset tags. Please ensure each asset_tag is unique.'
                ], 422);
            }

            return response()->json([
                'error' => true,
                'message' => config('app.debug') ? $e->getMessage() : 'Database error during import.'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => config('app.debug') ? $e->getMessage() : 'Asset import failed.'
            ], 500);
        }
    }


    public function export()
    {
        try {

            return Excel::download(new AssetsExport, 'assets.xlsx');

            return response()->json([
                'error' => false,
                'message' => 'Assets exported successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => config('app.debug') ? $e->getMessage() : 'Asset import failed.'
            ], 500);
        }
    }
}
