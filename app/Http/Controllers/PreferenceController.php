<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class PreferenceController extends Controller
{
    public function index()
    {
        $menuOrder = json_decode(DB::table('menu_orders')
            ->where(getGuardName() == 'web' ? 'user_id' : 'client_id', getAuthenticatedUser()->id)
            ->value('menu_order'), true);
    
        $menus = getMenus();
        $sortedMenus = [];
        // dd($menuOrder);

        if ($menuOrder) {
            foreach ($menuOrder as $category) {
                if (!isset($category['menus']) || !is_array($category['menus'])) {
                    continue;
                }
            
                foreach ($category['menus'] as $order) {
                    if (!is_array($order) || !isset($order['id'])) {
                        continue;
                    }
            
                    $menu = collect($menus)->firstWhere('id', $order['id']);
                    if ($menu) {
                        // 💡 Safely handle submenus
                        if (isset($order['submenus']) && is_array($order['submenus']) && !empty($order['submenus'])) {
                            $submenuIds = collect($order['submenus'])->pluck('id')->toArray();
                            $menu['submenus'] = collect($menu['submenus'])->sortBy(function ($submenu) use ($submenuIds) {
                                return array_search($submenu['id'], $submenuIds);
                            })->toArray();
                        }
            
                        $sortedMenus[] = $menu;
                    }
                }
            }
            
        } else {
            $sortedMenus = $menus;
        }
    
        return view('settings.preferences', compact('sortedMenus'));
    }
    
    public function saveColumnVisibility(Request $request)
{
    // Validate incoming request data
    $validator = Validator::make($request->all(), [
        'type' => 'required|string|max:255',
        'visible_columns' => 'required|json' // assuming visible_columns is a JSON string
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => true, 'message' => $validator->errors()->first()], 422);
    }

    try {
        // Get the authenticated user's ID
        $userId = getAuthenticatedUser(true,true);

        // Get the table type and visible columns from the request
        $type = $request->input('type');
        $visibleColumns = $request->input('visible_columns');

        // Update or insert the column visibility preferences
        DB::table('user_client_preferences')
            ->updateOrInsert(
                ['user_id' => $userId, 'table_name' => $type],
                ['visible_columns' => $visibleColumns]
            );

        return response()->json(['error' => false, 'message' => 'Column visibility saved successfully.']);

    } catch (\Exception $e) {
        return response()->json(['error' => true, 'message' => 'An error occurred while saving column visibility.'], 500);
    }
}

    public function getPreferences(Request $request)
    {
        $userId = getAuthenticatedUser()->id;
        $tableName = $request->input('table_name');
        $prefix = isClient() ? 'c_' : 'u_';

        // Fetch preferences from database
        $fields = DB::table('preferences')
            ->where('user_id', $prefix . $userId)
            ->where('table_name', $tableName)
            ->value('fields');

        return response()->json(['fields' => json_decode($fields)]);
    }
    public function saveNotificationPreferences(Request $request)
    {
        try {
            // Get the authenticated user's ID
            $userId = getAuthenticatedUser(true, true);
            $enabledNotifications = $request->has('enabled_notifications') ? json_encode($request->input('enabled_notifications')) : NULL;
            DB::table('user_client_preferences')
                ->updateOrInsert(
                    ['user_id' => $userId, 'table_name' => 'notification_preference'],
                    ['enabled_notifications' => $enabledNotifications]
                );

            return response()->json(['error' => false, 'message' => 'Notification preference saved successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'An error occurred while saving notification preference:' . $e->getMessage()], 500);
        }
    }
    public function saveMenuOrder(Request $request)
    {
        $validatedData = $request->validate([
            'menu_order' => 'required|array',
        ]);

        // Get the authenticated user's identifier
        $userId = getAuthenticatedUser()->id;
        $guardColumn = getGuardName() == 'web' ? 'user_id' : 'client_id';
        try {
            // Update or create the menu order
            DB::table('menu_orders')->updateOrInsert(
                [$guardColumn => $userId],
                ['menu_order' => json_encode($validatedData['menu_order'])]
            );

            return response()->json(['error' => false, 'message' => 'Menu order saved successfully!']);
        } catch (QueryException $e) {
            // Return an error response
            return response()->json(['error' => true, 'message' => 'Failed to save menu order. Please try again.'], 500);
        }
    }

    public function resetDefaultMenuOrder(Request $request)
    {
        // Get the authenticated user's identifier
        $userId = getAuthenticatedUser()->id;
        $guardColumn = getGuardName() == 'web' ? 'user_id' : 'client_id';

        try {
            // Delete the menu order record if it exists for the user/client
            DB::table('menu_orders')
                ->where($guardColumn, $userId)
                ->delete();

            return response()->json(['error' => false, 'message' => 'Menu order reset to default successfully!']);
        } catch (QueryException $e) {
            // Return an error response in case of failure
            return response()->json(['error' => true, 'message' => 'Failed to reset menu order. Please try again.'], 500);
        }
    }
}
