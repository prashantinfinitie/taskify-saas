<?php

namespace Plugins\AssetManagement\Controllers;

use App\Models\Admin;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Plugins\AssetManagement\Models\AssetCategory;

class AssetsCategoryController extends Controller
{

    private function getWorkspaceAssetsCategoriesQuery(){

        $workspace = Workspace::find(session()->get('workspace_id'));

        if(!$workspace){
            abort(403, 'No workspace found.');
        }

        $adminId = getAdminIdByUserRole();

        return AssetCategory::where('admin_id', $adminId);

    }

    public function index()
    {

        if (!isAdminOrHasAllDataAccess()) {
            abort(403, 'UnAuthorized Access');
        }

        $workspace = Workspace::find(session()->get('worksapce_id'));

        if($workspace){
            abort(403, 'UnAuthorized Action.');
        }

        $categories = $this->getWorkspaceAssetsCategoriesQuery()->get();
        // dd($categories);

        return view('assets::assets.category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $isApi = $request->get('isApi', false);

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required',
        ];

        try {
            $data = $request->validate($rules);

          $admin = Admin::where('user_id', auth()->id())->first();

          if(!$admin){
            abort(403, 'UnAuthorized Action');
          }


            $data['admin_id'] = $admin->id;
            AssetCategory::create($data);

            return response()->json([
                'error' => false,
                'message' => 'Asset Category created successfully!',
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
    {
        $isApi = $request->get('isApi', false);

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required',
        ];

        try {
            $data = $request->validate($rules);

            $admin = Admin::where('user_id', auth()->id())->first();

            if(!$admin){
                abort(403, 'Not Authorized');
            }

            $data['admin_id'] = $admin->id;
            $assetCategory = $this->getWorkspaceAssetsCategoriesQuery()->findOrFail($id);
            $assetCategory->update($data);

            return response()->json([
                'error' => false,
                'message' => 'Asset Category updated successfully!',
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

        $admin = Admin::where('user_id', auth()->id())->first();

        if(!$admin){
            abort(403, 'UnAuthorised Action');
        }

        $assetCategory =$this->getWorkspaceAssetsCategoriesQuery()->findOrFail($id);
        return DeletionService::delete(AssetCategory::class, $assetCategory->id, 'Asset Category');
    }

    public function destroy_multiple(Request $request)
    {
        $validatedData = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:asset_categories,id',
        ]);

        $deletedIds = [];

        foreach ($validatedData['ids'] as $id) {
            $assetCategory = $this->getWorkspaceAssetsCategoriesQuery()->findOrFail($id);
            DeletionService::delete(AssetCategory::class, $assetCategory->id, 'Asset Categories');
            $deletedIds[] = $id;
        }

        return response()->json([
            'error' => false,
            'message' => 'Asset Category(ies) deleted successfully.',
            'id' => $deletedIds,
        ]);
    }

    public function list()
    {
        $search = request('search');
        $order = request('order', 'DESC');
        $limit = request('limit', 10);
        $offset = request('offset', 0);
        $sort = request('sort', 'id');

        $admin = Admin::where('user_id', auth()->id())->first();
        // dd($admin);
        $query = AssetCategory::query()->where('admin_id', $admin->id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $canEdit = checkPermission('edit_asset_categories');
        $canDelete = checkPermission('delete_asset_categories');

        $assetCategories = $query->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($assetCategory) use ($canEdit, $canDelete) {
                $actions = '';

                if ($canEdit) {
                    $actions .= '<a href="javascript:void(0);" class="updateCategoryModal"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#updateCategoryOffcanvas"
                        data-asset-category=\'' . htmlspecialchars(json_encode($assetCategory), ENT_QUOTES, 'UTF-8') . '\'
                        title="' . get_label('update', 'Update') . '">
                        <i class="bx bx-edit mx-1"></i>
                    </a>';
                }

                if ($canDelete) {
                    $actions .= '<button type="button"
                        class="btn delete"
                        data-id="' . $assetCategory->id . '"
                        data-type="assets/category"
                        title="' . get_label('delete', 'Delete') . '">
                        <i class="bx bx-trash text-danger mx-1"></i>
                    </button>';
                }

                return [
                    'id' => $assetCategory->id,
                    'name' => $assetCategory->name,
                    'color' => '<span class="badge bg-' . $assetCategory->color . '">' . ucfirst($assetCategory->color) . '</span>',
                    'description' => $assetCategory->description,
                    'created_at' => format_date($assetCategory->created_at, false, 'Y-m-d'),
                    'updated_at' => format_date($assetCategory->updated_at, false, 'Y-m-d'),
                    'actions' => $actions,
                ];
            });

        return response()->json([
            'rows' => $assetCategories,
            'total' => $total,
        ]);
    }
}
