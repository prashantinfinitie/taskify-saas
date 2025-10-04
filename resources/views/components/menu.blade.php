<?php

use App\Models\User;
use App\Models\Subscription;
use App\Models\Admin;
use App\Models\Ticket;
use App\Models\Workspace;
use App\Models\LeaveRequest;
use Chatify\ChatifyMessenger;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$user = getAuthenticatedUser();
$adminId = getAdminIdByUserRole();
if (isAdminOrHasAllDataAccess()) {
    $workspaces = Workspace::where('admin_id', $adminId)->skip(0)->take(5)->get();
    $total_workspaces = Workspace::where('admin_id', $adminId)->count();
} else {
    $workspaces = $user->workspaces;
    $total_workspaces = count($workspaces);
    $workspaces = $user->workspaces()->skip(0)->take(5)->get();
}
$current_workspace = Workspace::find(session()->get('workspace_id'));
$current_workspace_title = $current_workspace->title ?? 'No workspace(s) found';
$messenger = new ChatifyMessenger();
$unread = $messenger->totalUnseenMessages();
$pending_todos_count = $user->todos(0)->count();
$ongoing_meetings_count = $user->meetings('ongoing')->count();
$query = LeaveRequest::where('status', 'pending')->where('workspace_id', session()->get('workspace_id'));
if (!is_admin_or_leave_editor()) {
    $query->where('user_id', $user->id);
}
$pendingLeaveRequestsCount = $query->count();
$prefix = null;
$openTicketsCount = Ticket::where('status', 'open')->count();
$currentRoute = Route::current();
if ($currentRoute) {
    $uriSegments = explode('/', $currentRoute->uri());
    $prefix = count($uriSegments) > 1 ? $uriSegments[0] : '';
}
?>
@if ($user->hasrole('superadmin') || $user->hasrole('manager'))
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme menu-container">
        <div class="app-brand demo">
            <a href="{{ route('home.index') }}" class="app-brand-link">
                <span class="app-brand-logo demo">
                    <img src="{{ asset($general_settings['full_logo']) }}" width="200px" alt="" />
                </span>
            </a>
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large d-block d-xl-none ms-auto">
                <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
        </div>
        <ul class="menu-inner py-1">
            <hr class="dropdown-divider" />
            <!-- Dashboard -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text"><?= get_label('dashboard', 'Dashboard') ?></span>
            </li>
            <li class="menu-item {{ Request::is($prefix . '/master-panel/home') ? 'active' : '' }}">
                <a href="{{ route('superadmin.panel') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle"></i>
                    <div><?= get_label('dashboard', 'Dashboard') ?></div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text"><?= get_label('customer_management', 'Customer Management') ?></span>
            </li>
            <li
                class="menu-item {{ Request::is($prefix . '/customers') || Request::is($prefix . '/customers/*') ? 'active' : '' }}">
                <a href="{{ route('customers.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-circle"></i>
                    <div><?= get_label('customers', 'Customers') ?></div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span
                    class="menu-header-text"><?= get_label('subscription_management', 'Subscription Management') ?></span>
            </li>
            <li
                class="menu-item {{ Request::is($prefix . '/plans') || Request::is($prefix . '/plans/*') ? 'active' : '' }}">
                <a href="{{ route('plans.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-task"></i>
                    <div><?= get_label('plans', 'Plans') ?></div>
                </a>
            </li>
            <li
                class="menu-item {{ Request::is($prefix . '/subscriptions') || Request::is($prefix . '/subscriptions/*') ? 'active' : '' }}">
                <a href="{{ route('subscriptions.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-spreadsheet"></i>
                    <div><?= get_label('subscriptions', 'Subscriptions') ?></div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text"><?= get_label('financial', 'Financial') ?></span>
            </li>
            <li
                class="menu-item {{ Request::is($prefix . '/transactions') || Request::is($prefix . '/transactions/*') ? 'active' : '' }}">
                <a href="{{ route('transactions.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-money"></i>
                    <div><?= get_label('transactions', 'Transactions') ?></div>
                </a>
            </li>

            @hasRole('superadmin')
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text"><?= get_label('administration', 'Administration') ?></span>
                </li>
                <li
                    class="menu-item {{ Request::is($prefix . '/managers') || Request::is($prefix . '/managers/*') ? 'active' : '' }}">
                    <a href="{{ route('managers.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-user"></i>
                        <div>
                            {{ get_label('managers', 'Managers') }}
                        </div>
                    </a>
                </li>
            @endhasRole

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text"><?= get_label('support', 'Support') ?></span>
            </li>
            <li class="menu-item {{ Request::is('support') || Request::is('support/*') ? 'active' : '' }}">
                <a href="{{ route('support.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-support"></i>
                    <div>
                        {{ get_label('support', 'Support') }}
                        @if ($openTicketsCount > 0)
                            <span
                                class="badge badge-center bg-danger w-px-20 h-px-20 rounded-circle flex-shrink-0">{{ $openTicketsCount }}</span>
                        @endif
                    </div>
                </a>
            </li>

            @hasRole('superadmin')
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text"><?= get_label('settings', 'Settings') ?></span>
                </li>
                <li
                    class="menu-item {{ Request::is($prefix . '/settings') || Request::is($prefix . '/roles/*') || Request::is($prefix . '/settings/*') ? 'active open' : '' }}">
                    <a href="javascript:void(0)" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-cog"></i>
                        <div data-i18n="User interface"><?= get_label('settings', 'Settings') ?></div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item {{ Request::is($prefix . '/settings/general') ? 'active' : '' }}">
                            <a href="{{ route('settings.index') }}" class="menu-link">
                                <div><?= get_label('general', 'General') ?></div>
                            </a>
                        </li>
                        <li
                            class="menu-item {{ Request::is($prefix . '/settings/frontend-general-settings') ? 'active' : '' }}">
                            <a href="{{ route('frontend_general_settings.index') }}" class="menu-link">
                                <div><?= get_label('frontend_general', 'Frontend General') ?></div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is($prefix . '/settings/security') ? 'active' : '' }}">
                            <a href="{{ route('security.index') }}" class="menu-link">
                                <div><?= get_label('security_settings', 'Security Settings') ?></div>
                            </a>
                        </li>
                        <li
                            class="menu-item {{ Request::is($prefix . '/settings/permission') || Request::is('roles/*') ? 'active' : '' }}">
                            <a href="{{ route('roles.index') }}" class="menu-link">
                                <div><?= get_label('permissions', 'Permissions') ?></div>
                            </a>
                        </li>
                        <li
                            class="menu-item {{ Request::is($prefix . '/settings/languages') || Request::is($prefix . '/settings/languages/create') ? 'active' : '' }}">
                            <a href="{{ route('languages.index') }}" class="menu-link">
                                <div><?= get_label('languages', 'Languages') ?></div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is($prefix . '/settings/ai-model-settings') ? 'active' : '' }}">
                            <a href="{{ route('settings.ai_models_setting') }}" class="menu-link">
                                <div><?= get_label('ai_model_settings', 'AI Model Settings') ?></div>
                            </a>
                        </li>

                        <li class="menu-item {{ Request::is($prefix . '/settings/email') ? 'active' : '' }}">
                            <a href="{{ route('settings.email') }}" class="menu-link">
                                <div><?= get_label('email', 'Email') ?></div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is($prefix . '/settings/sms-gateway') ? 'active' : '' }}">
                            <a href="{{ route('sms_gateway.index') }}" class="menu-link">
                                <div><?= get_label('notifications_settings', 'Notifications Settings') ?></div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is($prefix . '/settings/pusher') ? 'active' : '' }}">
                            <a href="{{ route('settings.pusher') }}" class="menu-link">
                                <div><?= get_label('pusher', 'Pusher') ?></div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is($prefix . '/settings/media-storage') ? 'active' : '' }}">
                            <a href="{{ route('settings.media_storage') }}" class="menu-link">
                                <div><?= get_label('media_storage', 'Media storage') ?></div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is($prefix . '/settings/payment-methods') ? 'active' : '' }}">
                            <a href="{{ route('payment_method.index') }}" class="menu-link">
                                <div><?= get_label('payment_methods', 'Payment Methods') ?></div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is($prefix . '/settings/privacy-policy') ? 'active' : '' }}">
                            <a href="{{ route('privacy_policy.index') }}" class="menu-link">
                                <div><?= get_label('privacy_policy', 'Privacy Policy') ?></div>
                            </a>
                        </li>
                        <li
                            class="menu-item {{ Request::is($prefix . '/settings/terms-and-conditions') ? 'active' : '' }}">
                            <a href="{{ route('terms_and_conditions.index') }}" class="menu-link">
                                <div><?= get_label('terms_and_conditions', 'Terms And Conditions') ?></div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is($prefix . '/settings/refund-policy') ? 'active' : '' }}">
                            <a href="{{ route('refund_policy.index') }}" class="menu-link">
                                <div><?= get_label('refund_policy', 'Refund Policy') ?></div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is($prefix . '/settings/templates') ? 'active' : '' }}">
                            <a href="{{ route('templates.index') }}" class="menu-link">
                                <div><?= get_label('notification_templates', 'Notification Templates') ?></div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is($prefix . '/settings/pwa-settings') ? 'active' : '' }}">
                            <a href="{{ route('pwa_settings.showSettings') }}" class="menu-link">
                                <div><?= get_label('pwa_settings', 'PWA Settings') ?></div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is($prefix . '/settings/system-updater') ? 'active' : '' }}">
                            <a href="{{ route('update.index') }}" class="menu-link">
                                <div><?= get_label('system_updater', 'System updater') ?></div>
                            </a>
                        </li>
                        <li class="menu-item {{ Request::is($prefix . '/settings/plugins') ? 'active' : '' }}">
                            <a href="{{ route('plugins.index') }}" class="menu-link">
                                <div><?= get_label('plugin', 'Plugin') ?></div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endhasRole
        </ul>
    </aside>
@else
    @php
        $modules = get_subscriptionModules();
    @endphp
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme menu-container">
        <div class="app-brand demo">
            <a href="{{ route('home.index') }}" class="app-brand-link">
                <span class="app-brand-logo demo">
                    <img src="{{ asset($general_settings['full_logo']) }}" width="200px" alt="" />
                </span>
                <!-- <span class="app-brand-text demo menu-text fw-bolder ms-2">Taskify</span> -->
            </a>
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large d-block d-xl-none ms-auto">
                <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
        </div>
        {{-- @dd(session()->get('workspace_id')) --}}
        <div class="d-flex flex-column gap-2 px-3">
            <!-- Workspace Switch Button -->
            <div class="btn-group dropend w-100">
                <button type="button" class="btn btn-primary dropdown-toggle w-100 text-start"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?= $current_workspace_title ?>
                </button>
                <ul class="dropdown-menu">
                    @foreach ($workspaces as $workspace)
                        <?php $checked = $workspace->id == session()->get('workspace_id') ? "<i class='menu-icon tf-icons bx bx-check-square text-primary'></i>" : "<i class='menu-icon tf-icons bx bx-square text-solid'></i>"; ?>
                        <li><a class="dropdown-item"
                                href="{{ route('workspaces.switch', ['id' => $workspace->id]) }}"><?= $checked ?>{{ $workspace->title }}
                                <?= $workspace->is_primary ? ' <span class="badge bg-success">' . get_label('primary', 'Primary') . '</span>' : '' ?></a>
                        </li>
                    @endforeach
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    @if ($user->can('manage_workspaces'))
                        <li><a class="dropdown-item" href="{{ route('workspaces.index') }}"><i
                                    class='menu-icon tf-icons bx bx-bar-chart-alt-2 text-success'></i><?= get_label('manage_workspaces', 'Manage workspaces') ?>
                                <?= $total_workspaces > 5 ? '<span class="badge badge-center bg-primary"> + ' . ($total_workspaces - 5) . '</span>' : '' ?></a>
                        </li>
                        @if ($user->can('create_workspaces'))
                            <li><a class="dropdown-item" href="{{ route('workspaces.create') }}"><i
                                        class='menu-icon tf-icons bx bx-plus text-warning'></i><?= get_label('create_workspace', 'Create workspace') ?></a>
                            </li>
                        @endif
                        @if ($user->can('create_workspaces'))

                            @php
                                $workspaceId = session('workspace_id');
                            @endphp

                            <li>
                                @if ($workspaceId)
                                    <a class="dropdown-item"
                                        href="{{ route('workspaces.edit', ['id' => $workspaceId]) }}">
                                        <i class="menu-icon tf-icons bx bx-edit text-info"></i>
                                        {{ get_label('edit_workspace', 'Edit workspace') }}
                                    </a>
                                @else
                                    <a class="dropdown-item disabled" href="#"
                                        onclick="alert('No workspace selected'); return false;">
                                        <i class="menu-icon tf-icons bx bx-edit text-secondary"></i>
                                        {{ get_label('edit_workspace', 'Edit workspace') }}
                                    </a>
                                @endif
                            </li>

                        @endif
                    @endif
                    <li><a class="dropdown-item" href="#"
                            data-route-prefix="{{ Route::getCurrentRoute()->getPrefix() }}"
                            id="remove-participant"><i
                                class='menu-icon tf-icons bx bx-exit text-danger'></i><?= get_label('remove_me_from_workspace', 'Remove me from workspace') ?></a>
                    </li>
                </ul>
            </div>
            <!-- Search Menu -->
            <div class="w-100 position-relative">
                <input type="text" id="menu-search" class="form-control"
                    placeholder="{{ get_label('search_menu', 'Search Menu') }}...">
                <!-- Keyboard shortcut indicator -->
                <span class="position-absolute top-50 translate-middle-y end-0 me-2"
                    style="pointer-events: none; z-index: 2;">
                    <kbd class="bg-light text-muted small border px-1">/</kbd>
                </span>
                <button type="button" id="clear-search"
                    class="btn btn-sm position-absolute top-50 translate-middle-y end-0 me-2" style="display: none;">
                    <i class="bx bx-x"></i>
                </button>
            </div>
        </div>

       @php
            // Load saved menu order
            $menuOrder = json_decode(
                DB::table('menu_orders')
                    ->where(getGuardName() == 'web' ? 'user_id' : 'client_id', getAuthenticatedUser()->id)
                    ->value('menu_order'),
                true,
            );

            // Get core menus
            $menus = getMenus();

            // Ensure modules array
            $modules = is_array($modules) ? $modules : (array) ($modules ?? []);

            // Load Taskify module config
            $moduleConfig = config('taskify.modules') ?? [];
            $moduleKeys = array_keys($moduleConfig); // ['tasks','notes','meetings',...]

            // Helper: check if menu is module-driven
            $isModuleMenu = function ($menu) use ($moduleKeys) {
                return isset($menu['id']) && in_array($menu['id'], $moduleKeys, true);
            };

            // Helper: check if menu should be visible
            $isVisible = function ($menu) use ($isModuleMenu, $modules) {
                if (!$isModuleMenu($menu)) {
                    return true; // Core menu → always visible
                }
                return in_array($menu['id'], $modules, true); // Module menu → only if subscribed
            };

            // Load plugin menus
            $pluginMenus = [];
            $pluginPath = base_path('plugins');
            if (File::exists($pluginPath)) {
                $pluginDirs = glob($pluginPath . '/*', GLOB_ONLYDIR);
                foreach ($pluginDirs as $pluginDir) {
                    $pluginJsonFile = $pluginDir . '/plugin.json';
                    if (!File::exists($pluginJsonFile)) {
                        continue;
                    }

                    $pluginData = json_decode(File::get($pluginJsonFile), true);
                    $pluginModuleKey = $pluginData['module'] ?? null;

                    // Plugin visible only if enabled AND module subscribed
                    if (
                        !empty($pluginData['enabled']) &&
                        $pluginModuleKey &&
                        in_array($pluginModuleKey, $modules, true)
                    ) {
                        $menuFile = $pluginDir . '/menus.php';
                        if (File::exists($menuFile)) {
                            $pluginMenuItems = include $menuFile;
                            if (is_array($pluginMenuItems)) {
                                // Tag plugin menus with module key
                                foreach ($pluginMenuItems as &$item) {
                                    $item['module'] = $pluginModuleKey;
                                }
                                $pluginMenus = array_merge($pluginMenus, $pluginMenuItems);
                            }
                        }
                    }
                }
            }

            // Merge core + plugin menus BEFORE filtering
            $allMenus = array_merge($menus, $pluginMenus);

            // dd($allMenus);

            // Filter menus & submenus based on visibility
            $filteredMenus = collect($allMenus)
                ->map(function ($menu) use ($isVisible) {
                    if (isset($menu['submenus']) && is_array($menu['submenus'])) {
                        $menu['submenus'] = collect($menu['submenus'])->filter($isVisible)->values()->all();
                    }
                    return $menu;
                })
                ->filter($isVisible)
                ->values()
                ->all();

            // Apply menu order if available
            $sortedMenus = [];
            if ($menuOrder) {
                $orderedMenuIds = [];

                foreach ($menuOrder as $categoryData) {
                    if (!isset($categoryData['menus']) || !is_array($categoryData['menus'])) {
                        continue;
                    }

                    foreach ($categoryData['menus'] as $order) {
                        if (!isset($order['id'])) {
                            continue;
                        }

                        $menu = collect($filteredMenus)->firstWhere('id', $order['id']);
                        if ($menu) {
                            $orderedMenuIds[] = $order['id'];

                            // Sort submenus if defined in order
                            if (!empty($order['submenus'])) {
                                $submenuIds = collect($order['submenus'])->pluck('id')->toArray();
                                $menu['submenus'] = collect($menu['submenus'] ?? [])
                                    ->whereNotNull('id')
                                    ->sortBy(fn($submenu) => array_search($submenu['id'], $submenuIds) ?? PHP_INT_MAX)
                                    ->values()
                                    ->all();
                            }
                            $sortedMenus[] = $menu;
                        }
                    }
                }

                // Group them by category to maintain category structure
                $unorderedMenus = collect($filteredMenus)
                    ->filter(fn($menu) => !in_array($menu['id'], $orderedMenuIds))
                    ->values()
                    ->all();

                $sortedMenus = array_merge($sortedMenus, $unorderedMenus);
            } else {
                $sortedMenus = $filteredMenus;
            }

            // Group menus by category for Blade
            $menusByCategory = [];
            foreach ($sortedMenus as $menu) {
                // Check visibility
                if (!isset($menu['show']) || $menu['show'] === 1) {
                    $category = $menu['category'] ?? 'uncategorized';

                    // Initialize category array if not exists
                    if (!isset($menusByCategory[$category])) {
                        $menusByCategory[$category] = [];
                    }

                    // Append menu to the category
                    $menusByCategory[$category][] = $menu;
                }
            }
        @endphp


        <ul class="menu-inner pb-1">
            <hr class="dropdown-divider" />

            @foreach ($menusByCategory as $category => $categoryMenus)
                {{-- Category heading --}}
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ get_label($category, ucfirst($category)) }}</span>
                </li>

                {{-- Menu items in this category --}}
                @foreach ($categoryMenus as $menu)
                    <li class="{{ $menu['class'] }}">
                        <a href="{{ $menu['url'] ?? 'javascript:void(0)' }}"
                            class="menu-link {{ isset($menu['submenus']) ? 'menu-toggle' : '' }}">
                            <i class="menu-icon tf-icons {{ $menu['icon'] }}"></i>
                            <div>
                                {{ $menu['label'] }}
                                @if (isset($menu['badge']) && $menu['badge'])
                                    {!! $menu['badge'] !!}
                                @endif
                            </div>
                        </a>
                        @if (isset($menu['submenus']))
                            <ul class="menu-sub">
                                @foreach ($menu['submenus'] as $submenu)
                                    @if (!isset($submenu['show']) || $submenu['show'] === 1)
                                        <li class="{{ $submenu['class'] }}">
                                            <a href="{{ $submenu['url'] }}" class="menu-link">
                                                <div>{{ $submenu['label'] }}</div>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            @endforeach
        </ul>
    </aside>
@endif
