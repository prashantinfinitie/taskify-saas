@extends('layout')
@section('title')
    {{ get_label('dashboard', 'Dashboard') }}
@endsection
@section('content')
    @authBoth
    <style>
.apexcharts-legend.apx-legend-position-right {
    max-height: 160px;
    overflow-y: hidden !important;
}

.apexcharts-legend.apx-legend-position-right:hover {
    overflow-y: auto !important;
}


    </style>
    <div class="container-fluid">
        <div class="col-lg-12 col-md-12 order-1">
            @php
                $tiles = [
                    'manage_projects' => [
                        'permission' => 'manage_projects',
                        'icon' => 'bx bx-briefcase-alt-2 text-success',
                        'icon-bg' => 'bg-label-success',
                        'label' => get_label('total_projects', 'Total projects'),
                        'count' => is_countable($projects) && count($projects) > 0 ? count($projects) : 0,
                        'url' => getDefaultViewRoute('projects'),
                        'link_color' => 'text-success',
                        'gradient_class' => 'gradient-card-success'
                    ],
                    'manage_tasks' => [
                        'permission' => 'manage_tasks',
                        'icon' => 'bx bx-task text-primary',
                        'icon-bg' => 'bg-label-primary',
                        'label' => get_label('total_tasks', 'Total tasks'),
                        'count' => $tasks,
                        'url' => getDefaultViewRoute('tasks'),
                        'link_color' => 'text-primary',
                         'gradient_class' => 'gradient-card-primary'
                    ],
                    'manage_users' => [
                        'permission' => 'manage_users',
                        'icon' => 'bx bxs-user-detail text-warning',
                        'icon-bg' => 'bg-label-warning',
                        'label' => get_label('total_users', 'Total users'),
                        'count' => is_countable($users) && count($users) > 0 ? count($users) : 0,
                        'url' => route('users.index'),
                        'link_color' => 'text-warning',
                        'gradient_class' => 'gradient-card-warning'
                    ],
                    'manage_clients' => [
                        'permission' => 'manage_clients',
                        'icon' => 'bx bxs-user-detail text-info',
                        'icon-bg' => 'bg-label-info',
                        'label' => get_label('total_clients', 'Total clients'),
                        'count' => is_countable($clients) && count($clients) > 0 ? count($clients) : 0,
                        'url' => route('clients.index'),
                        'link_color' => 'text-info',
                         'gradient_class' => 'gradient-card-info'
                    ],
                    'manage_meetings' => [
                        'permission' => 'manage_meetings',
                        'icon' => 'bx bx-shape-polygon text-warning',
                        'icon-bg' => 'bg-label-warning',
                        'label' => get_label('total_meetings', 'Total meetings'),
                        'count' => is_countable($meetings) && count($meetings) > 0 ? count($meetings) : 0,
                        'url' => route('meetings.index'),
                        'link_color' => 'text-warning',
                        'gradient_class' => 'gradient-card-warning'
                    ],
                    'total_todos' => [
                        'permission' => null, // No specific permission required
                        'icon' => 'bx bx-list-check text-info',
                        'icon-bg' => 'bg-label-info',
                        'label' => get_label('total_todos', 'Total todos'),
                        'count' => is_countable($total_todos) && count($total_todos) > 0 ? count($total_todos) : 0,
                        'url' => route('todos.index'),
                        'link_color' => 'text-info',
                        'gradient_class' => 'gradient-card-info'
                    ],
                ];

                // Filter tiles based on user permissions
                $filteredTiles = array_filter($tiles, function ($tile) use ($auth_user) {
                    return !$tile['permission'] || $auth_user->can($tile['permission']);
                });

                // Get the first 4 tiles
                $filteredTiles = array_slice($filteredTiles, 0, 4);
            @endphp

            <div class="row mt-4">
                @foreach ($filteredTiles as $tile)
                    <div class="col-lg-3 col-md-6 col-6 mb-4">
                        <div class="card gradient-card {{ $tile['gradient_class'] }}">
                            <div class="card-body d-flex align-items-start justify-content-between">
                                <!-- Text Content -->
                                <div>
                                    <h6 class="mb-1">{{ $tile['label'] }}</h6>
                                    <h3 class="fw-bold my-2">{{ $tile['count'] }}</h3>
                                    <a href="{{ $tile['url'] }}" class="text-decoration-none {{ $tile['link_color'] }}">
                                        <small><i class="bx bx-right-arrow-alt"></i>
                                            {{ get_label('view_more', 'View more') }}</small>
                                    </a>
                                </div>

                                <!-- Icon Wrapper -->

                                <div class="avatar">
                                    <span class="avatar-initial {{ $tile['icon-bg'] }} rounded">
                                        <i class="icon-base bx-sm {{ $tile['icon'] }}"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="row">
                {{-- Project Statistics Chart --}}
                @if ($auth_user->can('manage_projects'))
                    <div class="col-md-4 col-sm-12">
                        <div class="card statisticsDiv mb-4 overflow-hidden">
                            <div class="card-header pb-1 pt-3">
                                <div class="card-title mb-0">
                                    <h5 class="m-0 me-2">{{ get_label('project_statistics', 'Project statistics') }}</h5>
                                </div>
                                <div class="my-3">
                                    <div id="projectStatisticsChart"></div>
                                </div>
                            </div>
                            <div class="card-body" id="project-statistics">
                                <?php
                                // Calculate status counts and total projects count
                                $statusCounts = [];
                                $total_projects_count = 0;
                                foreach ($statuses as $status) {
                                    $projectCount = isAdminOrHasAllDataAccess() ? count($status->projects) : $auth_user->status_projects($status->id)->count();
                                    $statusCounts[$status->id] = $projectCount;
                                    $total_projects_count += $projectCount; // Accumulate the count of projects
                                }
                                // Sort statuses by count in descending order
                                arsort($statusCounts);
                                ?>
                                <ul class="m-0 p-0">
                                    @foreach ($statusCounts as $statusId => $count)
                                        <?php $status = $statuses->where('id', $statusId)->first(); ?>
                                        <li class="d-flex mb-4 pb-1">
                                            <div class="avatar me-3 flex-shrink-0">
                                                <span class="avatar-initial bg-label-primary rounded"><i
                                                        class="bx bx-briefcase-alt-2 text-{{ $status->color }}"></i></span>
                                            </div>
                                            <div
                                                class="d-flex w-100 align-items-center justify-content-between flex-wrap gap-2">
                                                <div class="me-2">
                                                    <a
                                                        href="{{ url(getUserPreferences('projects', 'default_view') . '?status=' . $status->id) }}">
                                                        <h6 class="mb-0">{{ $status->title }}</h6>
                                                    </a>
                                                </div>
                                                <div class="user-progress">
                                                    <small class="fw-semibold">{{ $count }}</small>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                    <li class="d-flex mb-4 pb-1">
                                        <div class="avatar me-3 flex-shrink-0">
                                            <span class="avatar-initial bg-label-primary rounded"><i
                                                    class="bx bx-menu"></i></span>
                                        </div>
                                        <div
                                            class="d-flex w-100 align-items-center justify-content-between flex-wrap gap-2">
                                            <div class="me-2">
                                                <h5 class="mb-0"> {{ get_label('total', 'Total') }}</h5>
                                            </div>
                                            <div class="user-progress">
                                                <div class="status-count">
                                                    <h5 class="mb-0">{{ $total_projects_count }}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- Task Statistics Chart --}}
                @if ($auth_user->hasRole('admin') || $auth_user->can('manage_tasks'))
                    <div class="col-md-6 col-lg-4 col-xl-4 order-0">
                        <div class="card statisticsDiv mb-4 overflow-hidden">
                            <div class="card-header pb-1 pt-3">
                                <div class="card-title mb-0">
                                    <h5 class="m-0 me-2">{{ get_label('task_statistics', 'Task statistics') }}</h5>
                                </div>
                                <div class="my-3">
                                    <div id="taskStatisticsChart"></div>
                                </div>
                            </div>
                            <div class="card-body" id="task-statistics">
                                <?php
                                // Calculate status counts and total tasks count
                                $statusCounts = [];
                                $total_tasks_count = 0;
                                foreach ($statuses as $status) {
                                    $statusCount = isAdminOrHasAllDataAccess() ? count($status->tasks) : $auth_user->status_tasks($status->id)->count();
                                    $statusCounts[$status->id] = $statusCount;
                                    $total_tasks_count += $statusCount; // Accumulate the count of tasks
                                }
                                // Sort statuses by count in descending order
                                arsort($statusCounts);
                                ?>
                                <ul class="m-0 p-0">
                                    @foreach ($statusCounts as $statusId => $count)
                                        <?php $status = $statuses->where('id', $statusId)->first(); ?>
                                        <li class="d-flex mb-4 pb-1">
                                            <div class="avatar me-3 flex-shrink-0">
                                                <span class="avatar-initial bg-label-primary rounded"><i
                                                        class="bx bx-task text-{{ $status->color }}"></i></span>
                                            </div>
                                            <div
                                                class="d-flex w-100 align-items-center justify-content-between flex-wrap gap-2">
                                                <div class="me-2">
                                                    <a
                                                        href="{{ url(getUserPreferences('tasks', 'default_view') . '?status=' . $status->id) }}">
                                                        <h6 class="mb-0">{{ $status->title }}</h6>
                                                    </a>
                                                </div>
                                                <div class="user-progress">
                                                    <small class="fw-semibold">{{ $count }}</small>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                    <li class="d-flex mb-4 pb-1">
                                        <div class="avatar me-3 flex-shrink-0">
                                            <span class="avatar-initial bg-label-primary rounded"><i
                                                    class="bx bx-menu"></i></span>
                                        </div>
                                        <div
                                            class="d-flex w-100 align-items-center justify-content-between flex-wrap gap-2">
                                            <div class="me-2">
                                                <h5 class="mb-0">{{ get_label('total', 'Total') }}</h5>
                                            </div>
                                            <div class="user-progress">
                                                <div class="status-count">
                                                    <h5 class="mb-0">{{ $total_tasks_count }}</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- Todos Overview --}}
                <div class="col-md-6 col-lg-4 col-xl-4 order-0">
                    <div class="card statisticsDiv mb-4 overflow-hidden">
                        <div class="card-header pb-1 pt-3">
                            <div class="card-title d-flex justify-content-between mb-0">
                                <h5 class="m-0 me-2">{{ get_label('todos_overview', 'Todos overview') }}</h5>
                                <div>
                                    <span data-bs-toggle="modal" data-bs-target="#create_todo_modal"><a
                                            href="javascript:void(0);" class="btn btn-sm btn-primary"
                                            data-bs-toggle="tooltip" data-bs-placement="left"
                                            data-bs-original-title="{{ get_label('create_todo', 'Create todo') }}"><i
                                                class='bx bx-plus'></i></a></span>
                                    <a href="{{ route('todos.index') }}"><button type="button" class="btn btn-sm btn-primary"
                                            data-bs-toggle="tooltip" data-bs-placement="right"
                                            data-bs-original-title="{{ get_label('view_more', 'View more') }}"><i
                                                class="bx bx-list-ul"></i></button></a>
                                </div>
                            </div>
                            <div class="my-3">
                                <div id="todoStatisticsChart"></div>
                            </div>
                        </div>
                        <div class="card-body" id="todos-statistics">
                            <?php $total_tasks_count = 0; ?>
                            <ul class="m-0 p-0">
                                @if (is_countable($todos) && count($todos) > 0)
                                    @foreach ($todos as $todo)
                                        <li class="d-flex mb-4 pb-1">
                                            <div class="avatar flex-shrink-0">
                                                <input type="checkbox" id="{{ $todo->id }}"
                                                    onclick='update_status(this)' name="{{ $todo->id }}"
                                                    class="form-check-input mt-0"
                                                    {{ $todo->is_completed ? 'checked' : '' }}>
                                            </div>
                                            <div
                                                class="d-flex w-100 align-items-center justify-content-between flex-wrap gap-2">
                                                <div class="me-2">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <h6 class="{{ $todo->is_completed ? 'striked' : '' }} mb-0"
                                                            id="{{ $todo->id }}_title">{{ $todo->title }}</h6>
                                                        <div class="user-progress d-flex align-items-center gap-1">
                                                            <a href="javascript:void(0);" class="edit-todo"
                                                                data-bs-toggle="modal" data-bs-target="#edit_todo_modal"
                                                                data-id="{{ $todo->id }}"
                                                                title="{{ get_label('update', 'Update') }}"><i
                                                                    class='bx bx-edit mx-1'></i></a>
                                                            <a href="javascript:void(0);" class="delete"
                                                                data-id="{{ $todo->id }}" data-type="todos"
                                                                title="{{ get_label('delete', 'Delete') }}"><i
                                                                    class='bx bx-trash text-danger mx-1'></i></a>
                                                        </div>
                                                    </div>
                                                    <small
                                                        class="text-muted d-block my-1">{{ format_date($todo->created_at, true) }}</small>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                @else
                                    <div class="h-100 d-flex justify-content-center align-items-center">
                                        <div>
                                            {{ get_label('todos_not_found', 'Todos not found!') }}
                                        </div>
                                    </div>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                {{-- Income Expense Chart --}}
                @if ($auth_user->hasRole('admin'))
                    <div class="col-md-6 mb-4 mt-0">
                        <input type="hidden" id="filter_date_range_from">
                        <input type="hidden" id="filter_date_range_to">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">{{ get_label('income_vs_expense', 'Income vs Expense') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="col-md-6 mb-md-0 mb-2">
                                    <input type="text" id="filter_date_range_income_expense" class="form-control"
                                        placeholder="{{ get_label('date_between', 'Date Between') }}" autocomplete="off">
                                </div>
                                <div id="income-expense-chart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4 mt-0">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title d-flex justify-content-between align-items-center">
                                    {{ get_label('recent_activities', 'Recent Activities') }}
                                    <i class='bx bx-bar-chart-alt-2 bx-sm text-body me-4'></i>
                                </h5>
                            </div>

                            <!-- Bootstrap 5 Utility Classes for Scrollable Content -->
                            <div class="card-body max-height-450 p-3" id="recent-activity">
                                <ul class="timeline mb-0">
                                    
                                    @forelse ($activities as $activity)
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point
                                        @switch($activity->activity)
                                            @case('created') timeline-point-success @break
                                            @case('updated') timeline-point-info @break
                                            @case('deleted') timeline-point-danger @break
                                            @case('updated status') timeline-point-warning @break
                                            @default timeline-point-primary
                                        @endswitch">
                                    </span>
                                
                                    <div class="timeline-event d-flex">
                                        
                                        <div class="me-3">
                                            <img 
                                                src="{{ $activity->actor && $activity->actor->photo 
                                                    ? asset('storage/' . $activity->actor->photo) 
                                                    : asset('storage/photos/no-image.jpg') }}"
                                                alt="actor-avatar" 
                                                class="rounded-circle" 
                                                width="30" 
                                                height="30"
                                            />
                                        </div>
                                    
                                        
                                        <div class="flex-grow-1">
                                            <div class="timeline-header d-flex justify-content-between align-items-center">
                                                <h6 class="fw-semibold mb-1">{{ $activity->message }}</h6>
                                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                            </div>
                                            <div class="timeline-body">
                                                <p class="text-muted">{{ format_date($activity->created_at, true) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                    @empty
                                        <li class="timeline-item timeline-item-transparent text-center">
                                            <span class="timeline-point timeline-point-primary"></span>
                                            <div class="timeline-event">
                                                <div class="timeline-header">
                                                    <h6 class="text-muted mb-0">
                                                        {{ get_label('no_activities', 'No recent activities') }}
                                                    </h6>
                                                </div>
                                            </div>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                            <div class="card-footer">
                                <div class="text-start text-sm">
                                    <a href="{{ route('activity_log.index') }}" class="btn btn-outline-primary btn-sm mt-3">
                                        {{ get_label('view_all', 'View All') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @if (!isClient() && $auth_user->can('manage_users'))
            <div class="nav-align-top">
                <ul class="nav nav-tabs" role="tablist">

                    <li class="nav-item">
                        <button type="button" class="nav-link parent-link active" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-top-upcoming-birthdays" aria-controls="navs-top-upcoming-birthdays"
                            aria-selected="true">
                            <i class="menu-icon tf-icons bx bx-cake text-success"></i>
                            {{ get_label('upcoming_birthdays', 'Upcoming birthdays') }}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link parent-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-top-upcoming-work-anniversaries"
                            aria-controls="navs-top-upcoming-work-anniversaries" aria-selected="false">
                            <i class="menu-icon tf-icons bx bx-star text-warning"></i>
                            {{ get_label('upcoming_work_anniversaries', 'Upcoming work anniversaries') }}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link parent-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-top-members-on-leave" aria-controls="navs-top-members-on-leave"
                            aria-selected="false">
                            <i class="menu-icon tf-icons bx bx-home text-danger"></i>
                            {{ get_label('members_on_leave', 'Members on leave') }}
                        </button>
                    </li>

                </ul>
                <div class="tab-content">

                    <div class="tab-pane fade active show" id="navs-top-upcoming-birthdays" role="tabpanel">
                        <ul class="nav nav-tabs justify-content-start rounded-pill mb-3 gap-2" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button type="button"
                                    class="nav-link active rounded-2 bg-primary list-button px-4 py-2 text-white"
                                    role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-top-upcoming-birthdays-list"
                                    aria-controls="navs-top-upcoming-birthdays-list" aria-selected="true">
                                    {{ get_label('list', 'List') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link rounded-2 calendar-button px-4 py-2"
                                    role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-top-upcoming-birthdays-calendar"
                                    aria-controls="navs-top-upcoming-birthdays-calendar" aria-selected="false">
                                    {{ get_label('calendar', 'Calendar') }}
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content p-0 shadow-none">
                            <div class="tab-pane fade active show" id="navs-top-upcoming-birthdays-list" role="tabpanel">
                                @if (!$auth_user->dob)
                                    <div class="alert alert-primary alert-dismissible" role="alert">
                                        {{ get_label('dob_not_set_alert', 'Your DOB is not set') }},
                                        <a href="{{ route('users.edit', ['id' => $auth_user->id]) }}">
                                            {{ get_label('click_here_to_set_it_now', 'Click here to set it now') }}</a>.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif
                                <x-upcoming-birthdays-card :users="$users" />
                            </div>
                            <div class="tab-pane fade" id="navs-top-upcoming-birthdays-calendar" role="tabpanel">
                                <div id="upcomingBirthdaysCalendar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="navs-top-upcoming-work-anniversaries" role="tabpanel">
                        <ul class="nav nav-tabs justify-content-start rounded-pill mb-3 gap-2" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button type="button"
                                    class="nav-link active rounded-2 bg-primary list-button px-4 py-2 text-white"
                                    role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-top-upcoming-work-anniversaries-list"
                                    aria-controls="navs-top-upcoming-work-anniversaries-list" aria-selected="true">
                                    {{ get_label('list', 'List') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link rounded-2 calendar-button px-4 py-2"
                                    role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-top-upcoming-work-anniversaries-calendar"
                                    aria-controls="navs-top-upcoming-work-anniversaries-calendar" aria-selected="false">
                                    {{ get_label('calendar', 'Calendar') }}
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content p-0 shadow-none">
                            <div class="tab-pane fade active show" id="navs-top-upcoming-work-anniversaries-list"
                                role="tabpanel">
                                @if (!$auth_user->doj)
                                    <div class="alert alert-primary alert-dismissible" role="alert">
                                        {{ get_label('doj_not_set_alert', 'Your DOJ is not set') }}, <a
                                            href="{{ route('users.edit', ['id' => $auth_user->id]) }}">{{ get_label('click_here_to_set_it_now', 'Click here to set it now') }}</a>.<button
                                            type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif
                                <x-upcoming-work-anniversaries-card :users="$users" />
                            </div>
                            <div class="tab-pane fade" id="navs-top-upcoming-work-anniversaries-calendar"
                                role="tabpanel">
                                <div id="upcomingWorkAnniversariesCalendar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="navs-top-members-on-leave" role="tabpanel">
                        <ul class="nav nav-tabs justify-content-start rounded-pill mb-3 gap-2" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button type="button"
                                    class="nav-link active rounded-2 bg-primary list-button px-4 py-2 text-white"
                                    role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-members-on-leave-list"
                                    aria-controls="navs-top-members-on-leave-list" aria-selected="true">
                                    {{ get_label('list', 'List') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link rounded-2 calendar-button px-4 py-2"
                                    role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-top-members-on-leave-calendar"
                                    aria-controls="navs-top-members-on-leave-calendar" aria-selected="false">
                                    {{ get_label('calendar', 'Calendar') }}
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content p-0 shadow-none">
                            <div class="tab-pane fade active show" id="navs-top-members-on-leave-list" role="tabpanel">
                                <x-members-on-leave-card :users="$users" />
                            </div>
                            <div class="tab-pane fade" id="navs-top-members-on-leave-calendar" role="tabpanel">
                                <div id="membersOnLeaveCalendar"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif
        {{-- @if ($auth_user->can('manage_projects') || $auth_user->can('manage_tasks'))
            <div class="nav-align-top my-4">
                <ul class="nav nav-tabs" role="tablist">
                    @if ($auth_user->can('manage_projects'))
                        <li class="nav-item">
                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-top-projects" aria-controls="navs-top-projects"
                                aria-selected="true">
                                <i
                                    class="menu-icon tf-icons bx bx-briefcase-alt-2 text-success"></i>{{ get_label('projects', 'Projects') }}
                            </button>
                        </li>
                    @endif
                    @if ($auth_user->can('manage_tasks'))
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-top-tasks" aria-controls="navs-top-tasks" aria-selected="false">
                                <i class="menu-icon tf-icons bx bx-task text-primary"></i>{{ get_label('tasks', 'Tasks') }}
                            </button>
                        </li>
                    @endif
                </ul>
                <div class="tab-content">
                    @if ($auth_user->can('manage_projects'))
                        <div class="tab-pane fade active show" id="navs-top-projects" role="tabpanel">
                            <div class="">
                                <div class="d-flex justify-content-between">
                                    <h4 class="fw-bold">{{ $auth_user->first_name }}'s
                                        {{ get_label('projects', 'Projects') }}</h4>
                                </div>
                                @if (is_countable($projects) && count($projects) > 0)
                                    <?php
                                    $type = isUser() ? 'user' : 'client';
                                    $id = isAdminOrHasAllDataAccess() ? '' : $type . '_' . $auth_user->id;
                                    ?>
                                    <x-projects-card :projects="$projects" :id="$id" :users="$users"
                                        :clients="$clients" />
                                @else
                                    <?php
                                    $type = 'Projects'; ?>
                                    <x-empty-state-card :type="$type" />
                                @endif
                            </div>
                        </div>
                    @endif
                    @if ($auth_user->can('manage_tasks'))
                        <div class="tab-pane fade {{ !$auth_user->can('manage_projects') ? 'active show' : '' }}"
                            id="navs-top-tasks" role="tabpanel">
                            <div class="">
                                <div class="d-flex justify-content-between">
                                    <h4 class="fw-bold">{{ $auth_user->first_name }}'s {{ get_label('tasks', 'Tasks') }}
                                    </h4>
                                </div>
                                @if ($tasks > 0)
                                    <?php
                                    $type = isUser() ? 'user' : 'client';
                                    $id = isAdminOrHasAllDataAccess() ? '' : $type . '_' . $auth_user->id;
                                    ?>
                                    <x-tasks-card :tasks="$tasks" :id="$id" :users="$users" :clients="$clients"
                                        :projects="$projects" />
                                @else
                                    <?php
                                    $type = 'Tasks'; ?>
                                    <x-empty-state-card :type="$type" />
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif --}}
        <!-- ------------------------------------------- -->
        <?php
        $titles = [];
        $project_counts = [];
        $task_counts = [];
        $bg_colors = [];
        $total_projects = 0;
        $total_tasks = 0;
        $total_todos = count($todos);
        $done_todos = 0;
        $pending_todos = 0;
        $todo_counts = [];
        $ran = [
            '#63ed7a',
            '#ffa426',
            '#fc544b',
            '#6777ef',
            '#FF00FF',
            '#53ff1a',
            '#ff3300',
            '#0000ff',
            '#00ffff',
            '#99ff33',
            '#003366',
            '#cc3300',
            '#ffcc00',
            '#ff9900',
            '#3333cc',
            '#ffff00',
            '#FF5733',
            '#33FF57',
            '#5733FF',
            '#FFFF33',
            '#A6A6A6',
            '#FF99FF',
            '#6699FF',
            '#666666',
            '#FF6600',
            '#9900CC',
            '#FF99CC',
            '#FFCC99',
            '#99CCFF',
            '#33CCCC',
            '#CCFFCC',
            '#99CC99',
            '#669999',
            '#CCCCFF',
            '#6666FF',
            '#FF6666',
            '#99CCCC',
            '#993366',
            '#339966',
            '#99CC00',
            '#CC6666',
            '#660033',
            '#CC99CC',
            '#CC3300',
            '#FFCCCC',
            '#6600CC',
            '#FFCC33',
            '#9933FF',
            '#33FF33',
            '#FFFF66',
            '#9933CC',
            '#3300FF',
            '#9999CC',
            '#0066FF',
            '#339900',
            '#666633',
            '#330033',
            '#FF9999',
            '#66FF33',
            '#6600FF',
            '#FF0033',
            '#009999',
            '#CC0000',
            '#999999',
            '#CC0000',
            '#CCCC00',
            '#00FF33',
            '#0066CC',
            '#66FF66',
            '#FF33FF',
            '#CC33CC',
            '#660099',
            '#663366',
            '#996666',
            '#6699CC',
            '#663399',
            '#9966CC',
            '#66CC66',
            '#0099CC',
            '#339999',
            '#00CCCC',
            '#CCCC99',
            '#FF9966',
            '#99FF00',
            '#66FF99',
            '#336666',
            '#00FF66',
            '#3366CC',
            '#CC00CC',
            '#00FF99',
            '#FF0000',
            '#00CCFF',
            '#000000',
            '#FFFFFF',
        ];
        foreach ($statuses as $status) {
            $project_count = isAdminOrHasAllDataAccess() ? count($status->projects) : $auth_user->status_projects($status->id)->count();
            array_push($project_counts, $project_count);
            $task_count = isAdminOrHasAllDataAccess() ? count($status->tasks) : $auth_user->status_tasks($status->id)->count();
            array_push($task_counts, $task_count);
            array_push($titles, "'" . $status->title . "'");
            $v = array_shift($ran);
            array_push($bg_colors, "'" . $v . "'");
            $total_projects += $project_count;
            $total_tasks += $task_count;
        }
        $titles = implode(',', $titles);
        $project_counts = implode(',', $project_counts);
        $task_counts = implode(',', $task_counts);
        $bg_colors = implode(',', $bg_colors);
        foreach ($todos as $todo) {
            $todo->is_completed ? ($done_todos += 1) : ($pending_todos += 1);
        }
        array_push($todo_counts, $done_todos);
        array_push($todo_counts, $pending_todos);
        $todo_counts = implode(',', $todo_counts);
        ?>
    </div>
    <script>
        var labels = [<?= $titles ?>];
        var project_data = [<?= $project_counts ?>];
        var task_data = [<?= $task_counts ?>];
        var bg_colors = [<?= $bg_colors ?>];
        var total_projects = [<?= $total_projects ?>];
        var total_tasks = [<?= $total_tasks ?>];
        var total_todos = [<?= $total_todos ?>];
        var todo_data = [<?= $todo_counts ?>];
        //labels
        var done = '<?= get_label('done ', 'Done ') ?>';
        var pending = '<?= get_label(' pending ', 'Pending ') ?>';
        var total = '<?= get_label('total ', 'Total ') ?>';
    </script>
    <script src="{{ asset('assets/js/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/js/pages/dashboard.js') }}"></script>
@else
    <div class="w-100 h-100 d-flex align-items-center justify-content-center"><span>You must <a href="/login">Log in</a>
            or <a href="/register">Register</a> to access {{ $general_settings['company_title'] }}!</span></div>
@endauth
@endsection
