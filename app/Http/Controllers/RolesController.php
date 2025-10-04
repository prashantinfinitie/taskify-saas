<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Artisan;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::all();
        return view('settings.permission_settings', ['roles' => $roles]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $projects = Permission::where('name', 'like', '%projects%')->get()->sortBy('name');
        $tasks = Permission::where('name', 'like', '%tasks%')->get()->sortBy('name');
        $users = Permission::where('name', 'like', '%users%')->get()->sortBy('name');
        $clients = Permission::where('name', 'like', '%clients%')->get()->sortBy('name');
        return view('roles.create_role', ['projects' => $projects, 'tasks' => $tasks, 'users' => $users, 'clients' => $clients]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $formFields = $request->validate([
            'name' => ['required']
        ]);

        $formFields['guard_name'] = 'web';

        $role = Role::create($formFields);
        $filteredPermissions = array_filter($request->input('permissions'), function ($permission) {
            return $permission != 0;
        });
        $role->permissions()->sync($filteredPermissions);
        Artisan::call('cache:clear');

        Session::flash('message', 'Role created successfully.');
        return response()->json(['error' => false]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $role = Role::findOrFail($id);
        $role_permissions = $role->permissions;
        $guard = $role->guard_name == 'client' ? 'client' : 'web';
        return view('roles.edit_role', ['role' => $role, 'role_permissions' => $role_permissions, 'guard' => $guard, 'user' => getAuthenticatedUser()]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $formFields = $request->validate([
            'name' => ['required']
        ]);
        $role = Role::findOrFail($id);
        $role->name = $formFields['name'];
        $role->save();
        $filteredPermissions = array_filter($request->input('permissions'), function ($permission) {
            return $permission != 0;
        });
        $role->permissions()->sync($filteredPermissions);

        Artisan::call('cache:clear');

        Session::flash('message', 'Role updated successfully.');
        return response()->json(['error' => false]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        $response = DeletionService::delete(Role::class, $id, 'Role');
        return $response;
    }

    public function create_permission()
    {
        // $createProjectsPermission = Permission::findOrCreate('create_tasks', 'client');
        Permission::create(['name' => 'edit_projects', 'guard_name' => 'client']);
    }

    /**
 * Get all roles.
 *
 * Returns a list of all roles available in the system.
 *
 * @response 200 {
 *   "error": false,
 *   "roles": [
 *     {
 *       "id": 1,
 *       "name": "Admin",
 *       "guard_name": "web",
 *       "created_at": "2024-01-01T12:00:00.000000Z",
 *       "updated_at": "2024-01-01T12:00:00.000000Z"
 *     },
 *     {
 *       "id": 2,
 *       "name": "Client",
 *       "guard_name": "client",
 *       "created_at": "2024-01-01T12:00:00.000000Z",
 *       "updated_at": "2024-01-01T12:00:00.000000Z"
 *     }
 *   ]
 * }
 */
public function apiRolesIndex($id = null)
{
    if ($id) {
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return formatApiResponse(true,'Roles not found',[ ], 404);
        }
        

        return formatApiResponse(false,'Role retrived successfully',[
            'role' => $role
        ]);

    }

    $roles = Role::with('permissions')->get();

    return formatApiResponse(false,'Roles retrieved successfully',[
        'roles' => $roles
    ]);
    }
    public function apiPermissionList($id = null)
{
    if ($id) {
        // Get the role with its permissions
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return formatApiResponse(true,'Role not found',[],404);
        }

        // Get all permissions grouped by categories from config
        $permissionCategories = config('taskify.permissions');
        
        // Get role's assigned permission names for quick lookup
        $assignedPermissions = $role->permissions->pluck('name')->toArray();
        
        // Structure the response according to your requirements
        $categorizedPermissions = [];
        
        foreach ($permissionCategories as $category => $permissions) {
            $categoryData = [
                'category' => $category,
                'permissions_assigned' => []
            ];
            
            foreach ($permissions as $permissionName) {
                // Get the permission from database
                $permission = Permission::where('name', $permissionName)->first();
                
                if ($permission) {
                    // Extract action from permission name (e.g., 'edit_tasks' -> 'edit')
                    $action = $this->extractActionFromPermission($permissionName);
                    
                    $categoryData['permissions_assigned'][] = [
                        'action' => $action,
                        'id' => $permission->id,
                        'isAssigned' => in_array($permissionName, $assignedPermissions) ? 1 : 0
                    ];
                }
            }
            
            // Only add category if it has permissions
            if (!empty($categoryData['permissions_assigned'])) {
                $categorizedPermissions[] = $categoryData;
            }
        }

        return formatApiResponse(
            false,
            'Role retrieved successfully.',
            [
                'data' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'data' => $categorizedPermissions
                ]
            ]
        );
    }

    // If no ID provided, return all permissions (your existing logic)
    $permissions = Permission::with('roles')->get();

    return formatApiResponse(false , 'Permissions retrived successfully',[
        'data' => $permissions
    ]);
}

/**
 * Extract action from permission name
 * e.g., 'edit_tasks' -> 'edit', 'create_projects' -> 'create'
 */
private function extractActionFromPermission($permissionName)
{
    $parts = explode('_', $permissionName);
    return $parts[0] ?? $permissionName;
}
}



