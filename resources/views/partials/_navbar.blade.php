<!-- Navbar -->
@php
    use App\Models\Language;
    $authenticatedUser = getAuthenticatedUser();
    $current_language = Language::where('code', app()->getLocale())->get(['name', 'code']);
    $default_language = getAuthenticatedUser()->lang;
    $unreadNotificationsCount = $authenticatedUser->notifications->where('pivot.read_at', null)->count();
    $unreadNotifications = $authenticatedUser
        ->notifications()
        ->wherePivot('read_at', null)
        ->getQuery()
        ->orderBy('id', 'desc')
        ->take(3)
        ->get();
    $unreadAnnouncementsCount = $authenticatedUser->announcements
        ? $authenticatedUser->announcements->where('pivot.read_at', null)->count()
        : null;
    if ($authenticatedUser->hasRole('client')) {
        $unreadAnnouncements = null;
    } else {
        $unreadAnnouncements = $authenticatedUser
            ->announcements()
            ->wherePivot('read_at', null)
            ->getQuery()
            ->orderBy('announcements.id', 'desc')
            ->take(3)
            ->get();
    }

@endphp
{{-- @dd($unreadNotifications) --}}
@authBoth
<div id="section-not-to-print">
    <nav class="layout-navbar container-fluid navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
        id="layout-navbar">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-xl-0 d-xl-none me-3">
            <a class="nav-item nav-link me-xl-4 px-0" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
            </a>
        </div>
        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <div class="nav-item d-flex align-items-center">
                <i class="bx bx-search"></i>
                <span class="cursor-pointer mx-2" id="global-search">
                    {{ get_label('search','Search') }}
                    <span class="d-none d-sm-inline">[CTRL + K]</span>
                </span>
            </div>

            <ul class="navbar-nav align-items-center ms-auto flex-row">
                @if (config('constants.ALLOW_MODIFICATION') === 0)
                    <li><span class="badge bg-danger demo-mode">Demo mode</span><span class="demo-mode-icon-only">
                            <i class='bx bx-error-alt text-danger'></i>
                        </span></li>
                @endif
                @if (!$authenticatedUser->hasRole('superadmin'))
                    <li class="nav-item navbar-dropdown dropdown-user dropdown ml-1">
                    <li class="nav-item navbar-dropdown dropdown">
                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                            data-bs-toggle="dropdown">
                            <i class='bx bx-bell bx-sm' data-bs-toggle="tooltip" data-bs-placement="bottom"
                                data-bs-original-title="{{ get_label('notifications', 'Notifications') }}"></i> <span
                                id="unreadNotificationsCount"
                                class="badge badge-center bg-danger bx-sm fs-tiny h-px-20 rounded-pill top-13 {{ $unreadNotificationsCount > 0 ? '' : 'd-none' }}">{{ $unreadNotificationsCount }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end w-px-400">
                            <li class="dropdown-header dropdown-header-highlighted">
                                <span class="align-items-center d-flex fs-large">
                                    <i class="bx bx-bell me-2"></i>
                                    {{ get_label('notifications', 'Notifications') }}
                                </span>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                            </li>
                            <div id="unreadNotificationsContainer">
                                @if ($unreadNotificationsCount > 0)
                                    @foreach ($unreadNotifications as $notification)
                                        <li>
                                            @php

                                                // Mapping of notification types to their respective routes
                                                $routes = [
                                                    'project' => '/master-panel/projects/information/$id',
                                                    'task' => '/master-panel/tasks/information/$id',
                                                    'workspace' => '/master-panel/workspaces',
                                                    'meeting' => '/master-panel/meetings',
                                                    'project_comment_mention' =>
                                                        '/master-panel/projects/information/$id',
                                                    'task_comment_mention' => '/master-panel/tasks/information/$id',
                                                    'leave_request' => '/master-panel/leave-requests',
                                                    'announcement' => '/master-panel/announcements',
                                                    'task_reminder' => '/master-panel/tasks/information/$id',
                                                    'recurring_task' => '/master-panel/tasks/information/$id',
                                                ];
                                                // Fallback route if the type is not matched in the array
                                                $defaultRoute = '/master-panel/notifications';
                                                // Determine the base URL based on the notification type, or fallback to the default
                                                $baseUrl = $routes[$notification->type] ?? $defaultRoute;

                                                // Check if the URL contains the '$id' placeholder and replace it with the actual id if
                                                // available
                                                if (
                                                    strpos($baseUrl, '$id') !== false &&
                                                    !empty($notification->type_id)
                                                ) {
                                                    $url = str_replace('$id', $notification->type_id, $baseUrl);
                                                } else {
                                                    $url = $baseUrl; // No id to append or not a route that requires it
                                                }
                                            @endphp

                                            <a class="dropdown-item update-notification-status"
                                                data-id="{{ $notification->id }}" href="{{ $url }}">
                                                <div class="d-flex align-items-center">
                                                    <div class="h6 text-truncate mb-0 me-auto">
                                                        <!-- Add text-truncate class -->
                                                        {{ $notification->title }}
                                                        <small
                                                            class="text-muted mx-2">{{ $notification->created_at->diffForHumans() }}</small>
                                                    </div>
                                                    <i class="bx bx-bell me-2"></i>
                                                </div>
                                                <div class="text-truncate">
                                                    <small>
                                                        <!-- Add text-truncate class -->
                                                        {{ strlen($notification->message) > 50 ? substr($notification->message, 0, 50) . '...' : $notification->message }}
                                                    </small>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <div class="dropdown-divider"></div>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="d-flex align-items-center justify-content-center p-5">
                                        <span>{{ get_label('no_unread_notifications', 'No unread notifications') }}</span>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                @endif
                            </div>
                            <li class="d-flex justify-content-between">
                                <a href="{{ route('notifications.index') }}"
                                    class="p-3"><b>{{ get_label('view_all', 'View all') }}</b></a>
                                <a href="#" class="p-3 text-end"
                                    id="mark-all-notifications-as-read"><b>{{ get_label('mark_all_as_read', 'Mark all as read') }}</b></a>
                            </li>
                        </ul>
                    </li>
                    </li>
                    @if ($authenticatedUser->can('manage_announcements'))
                        <li class="nav-item navbar-dropdown dropdown-user dropdown ml-1">
                        <li class="dropdown nav-item navbar-dropdown me-3">
                            <a class="nav-link dropdown-toggle hide-arrow position-relative" href="javascript:void(0);" data-bs-toggle="dropdown">
                                <div class="position-relative d-inline-block">
                                    <i class='bx bxs-megaphone bx-sm'
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        data-bs-original-title="{{ get_label('announcements', 'Announcements') }}">
                                    </i>
                                    <span id="unreadAnnouncementsCount"
                                        class="badge bg-warning fs-tiny position-absolute top-0 start-100 translate-middle rounded-pill {{ $unreadAnnouncementsCount > 0 ? '' : 'd-none' }}">
                                        {{ $unreadAnnouncementsCount }}
                                    </span>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end w-px-400">
                                <li class="dropdown-header dropdown-header-highlighted">
                                    <span class="align-items-center d-flex fs-large">
                                        <i class="bx bxs-megaphone me-2"></i>
                                        {{ get_label('announcements', 'Announcements') }}
                                    </span>
                                </li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                </li>
                                <div id="unreadAnnouncementsContainer">
                                    @if ($unreadAnnouncementsCount > 0)
                                        @foreach ($unreadAnnouncements as $announcement)
                                            <li>
                                                <a class="dropdown-item update-announcement-status"
                                                    data-id="{{ $announcement->announcement_id }}"
                                                    href="{{ route('announcements.index') }}">
                                                    <div class="d-flex align-items-center">
                                                        <div class="fw-semibold text-truncate me-auto">
                                                            <!-- Add text-truncate class -->
                                                            {{ $announcement->title }}
                                                            <small
                                                                class="text-muted mx-2">{{ $announcement->created_at->diffForHumans() }}</small>
                                                        </div>
                                                        <i class="bx bxs-megaphone me-2"></i>
                                                    </div>
                                                    <div class="text-truncate mt-2">
                                                        <!-- Add text-truncate class -->
                                                        {{ strlen($announcement->content) > 50 ? substr($announcement->content, 0, 50) . '...' : $announcement->content }}
                                                    </div>
                                                </a>
                                            </li>
                                            <li>
                                                <div class="dropdown-divider"></div>
                                            </li>
                                        @endforeach
                                    @else
                                        <li class="d-flex align-items-center justify-content-center p-5">
                                            <span>{{ get_label('no_unread_announcements', 'No unread announcements') }}</span>
                                        </li>
                                        <li>
                                            <div class="dropdown-divider"></div>
                                        </li>
                                    @endif
                                </div>
                                <li class="d-flex justify-content-between">
                                    <a href="{{ route('announcements.index') }}"
                                        class="p-3"><b>{{ get_label('view_all', 'View all') }}</b></a>
                                    <a href="#" class="p-3 text-end"
                                        id="mark-all-announcement-as-read"><b>{{ get_label('mark_all_as_read', 'Mark all as read') }}</b></a>
                                </li>
                            </ul>
                        </li>
                        </li>
                    @endif
                @endif
                <li class="nav-item navbar-dropdown dropdown-user dropdown ml-1">
                    <div class="btn-group dropend px-1">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            <span class="icon-only"><i class='bx bx-globe'></i></span> <span class="language-name">
                                {{ $current_language[0]['name'] ?? '' }}
                            </span>
                        </button>
                        <ul class="dropdown-menu language-dropdown">
                            @foreach ($languages as $language)
                                <li class="dropdown-item">
                                    <a href="{{ route('languages.switch', ['code' => $language->code]) }}">
                                        @if ($language->code == app()->getLocale())
                                            <i class="menu-icon tf-icons bx bx-check-square text-primary"></i>
                                        @else
                                            <i class="menu-icon tf-icons bx bx-square text-solid"></i>
                                        @endif
                                        {{ $language->name }}
                                    </a>
                                </li>
                            @endforeach
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            @if ($current_language[0]['code'] == $default_language)
                                <li>
                                    <span class="badge bg-primary mx-5 mb-1 mt-1" data-bs-toggle="tooltip"
                                        data-bs-placement="left"
                                        data-bs-original-title="{{ get_label('current_language_is_your_primary_language', 'Current language is your primary language') }}">
                                        {{ get_label('primary', 'Primary') }}
                                    </span>
                                </li>
                            @else
                                <a href="javascript:void(0);">
                                    <span class="badge bg-secondary mx-5 mb-1 mt-1" id="set-as-default"
                                        data-lang="{{ app()->getLocale() }}"
                                        data-url="{{ route('languages.set_default') }}" data-bs-toggle="tooltip"
                                        data-bs-placement="left"
                                        data-bs-original-title="{{ get_label('set_current_language_as_your_primary_language', 'Set current language as your primary language') }}">
                                        {{ get_label('set_as_primary', 'Set as primary') }}
                                    </span>
                                </a>
                            @endif
                        </ul>

                    </div>
                    </button>
                </li>
                <li class="nav-item navbar-dropdown dropdown-user dropdown mx-2 mt-3">
                    <p class="nav-item">
                        <span class="nav-mobile-hidden">
                            {{ get_label('hi', 'Hi') }} 👋
                        </span>
                        <span class="nav-mobile-hidden">{{ getAuthenticatedUser()->first_name }}</span>
                    </p>
                </li>
                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                        data-bs-toggle="dropdown">
                        <div class="avatar avatar-online">
                            <img src="{{ getAuthenticatedUser()->photo ? asset('storage/' . getAuthenticatedUser()->photo) : asset('storage/photos/no-image.jpg') }}"
                                alt class="w-px-40 rounded-circle" />
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#">
                                <div class="d-flex">
                                    <div class="me-3 flex-shrink-0">
                                        <div class="avatar avatar-online">
                                            <img src="{{ getAuthenticatedUser()->photo ? asset('storage/' . getAuthenticatedUser()->photo) : asset('storage/photos/no-image.jpg') }}"
                                                alt class="w-px-40 rounded-circle" />
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="fw-semibold d-block">{{ getAuthenticatedUser()->first_name }}
                                            {{ getAuthenticatedUser()->last_name }}</span>
                                        <small class="text-muted text-capitalize">
                                            {{ getAuthenticatedUser()->getRoleNames()->first() }}
                                        </small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <a class="dropdown-item"
                                href="{{ Request::segment(1) === 'superadmin'
                                    ? route('profile_superadmin.show', ['user' => getAuthenticatedUser()->id])
                                    : route('profile.show', ['user' => getAuthenticatedUser()->id]) }}">
                                <i class="bx bx-user me-2"></i>
                                <span class="align-middle">
                                    {{ get_label('my_profile', 'My Profile') }}
                                </span>
                            </a>
                        </li>
                        @if (!(Request::segment(1) == 'superadmin'))
                            <li>
                                <a class="dropdown-item" href="{{ route('preferences.index') }}">
                                    <i class='bx bx-cog me-2'></i>
                                    <span class="align-middle">
                                        {{ get_label('preferences', 'Preferences') }}
                                    </span>
                                </a>
                            </li>
                        @endif
                        <li>
                            <a class="dropdown-item" href="{{ route('clear.cache') }}"
                                onclick="event.preventDefault(); document.getElementById('clear-cache-form').submit();">
                                <i class='bx bx-refresh me-2'></i>
                                <span class="align-middle">
                                    {{ get_label('clear_system_cache', 'Clear System Cache') }}
                                </span>
                            </a>
                            <form id="clear-cache-form" action="{{ route('clear.cache') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="dropdown-item">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i
                                        class="bx bx-log-out-circle"></i>
                                    {{ get_label('logout', 'Logout') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
                <!--/ User -->
            </ul>
        </div>
    </nav>
</div>
<!-- Global Search Modal -->
<div class="modal fade" id="globalSearchModal" tabindex="-1" aria-labelledby="searchModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header mb-0 mt-1">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-header border-0 py-0 pb-0">
                <div class="search-wrapper w-100 position-relative">
                    <input type="text" class="form-control form-control-lg ps-5" id="modalSearchInput"
                        placeholder="Search [CTRL + K]" autocomplete="off">
                    <i class="bx bx-search position-absolute top-50 translate-middle-y ms-3"></i>
                      <i class="bx bx-x position-absolute top-50 translate-middle-y end-0 me-3 text-muted d-none" id="clearSearch"></i>
                    <span class="position-absolute top-50 translate-middle-y text-muted end-0 me-3" id="closeSearch">
                        [esc]
                    </span>
                </div>
            </div>
            <div class="modal-body">
                @if(!($authenticatedUser->hasRole('superadmin')))

                <h6 class="text-muted mb-3">{{ get_label('popular_pages', 'POPULAR PAGES') }}</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="list-group">
                            <a href="{{ getDefaultViewRoute('projects') }}"
                                class="list-group-item list-group-item-action">
                                <i class="bx bx-briefcase-alt-2 me-2"></i> {{ get_label('projects', 'Projects') }}
                            </a>
                            <a href="{{ route('workspaces.index') }}" class="list-group-item list-group-item-action">
                                <i class="bx bx-check-square me-2"></i> {{ get_label('workspaces', 'Workspaces') }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="list-group">
                            <a href="{{ getDefaultViewRoute('tasks') }}"
                                class="list-group-item list-group-item-action">
                                <i class="bx bx-task me-2"></i> {{ get_label('tasks', 'Tasks') }}
                            </a>
                            <a href="{{ route('meetings.index') }}" class="list-group-item list-group-item-action">
                                <i class="bx bx-shape-polygon me-2"></i> {{ get_label('meetings', 'Meetings') }}
                            </a>
                        </div>
                    </div>
                </div>
                @endif


                <div id="searchResults" class="d-none mt-4">
                    <h6 class="text-muted mb-3">{{ get_label('search_results', 'SEARCH RESULTS') }}</h6>
                    <div class="h-px-500 list-group overflow-auto" id="searchResultsList"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
@endauth
<script>
    var labelSearch = '{{ get_label('search', 'Search') }}';
</script>
<script src="{{ asset('assets/js/pages/navbar.js') }}"></script>
<!-- / Navbar -->
