@extends('layout')
@section('title')
    <?= $is_favorite == 1 ? get_label('favorite_projects', 'Favorite projects') : get_label('projects', 'Projects') ?> -
    <?= get_label('grid_view', 'Grid view') ?>
@endsection
@php
    $user = getAuthenticatedUser();
@endphp
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        @if (!($is_favorite == 1))
                            <li class="breadcrumb-item"><a
                                    href="{{ getDefaultViewRoute('projects') }}"><?= get_label('projects', 'Projects') ?></a>
                            </li>
                        @else
                            <li class="breadcrumb-item active"><a
                                    href="{{ route('projects.index', ['type' => 'favorite']) }}"><?= get_label('favorite', 'Favorite') ?></a>
                            </li>
                        @endif

                    </ol>
                </nav>
            </div>
            <div>
                @php
                    $projectDefaultView = getUserPreferences('projects', 'default_view');
                @endphp
                @if (!$projectDefaultView || $projectDefaultView === 'grid')
                    <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
                @else
                    <a href="javascript:void(0);"><span class="badge bg-secondary" id="set-default-view"
                            data-type="projects"
                            data-view="grid"><?= get_label('set_as_default_view', 'Set as Default View') ?></span></a>
                @endif
            </div>
            <div>
                @php
                    $url =
                        $is_favorite == 1
                            ? url('/master-panel/projects/list/favorite')
                            : url('/master-panel/projects/list');
                    $additionalParams = request()->has('status')
                        ? '/master-panel/projects/list?status=' . request()->status
                        : '';
                    $finalUrl = url($additionalParams ?: $url);
                    $currentPath = request()->path();
                    $showCreateButton = !in_array($currentPath, ['projects/list/favorite', 'projects/favorite']);
                @endphp
                @if ($showCreateButton)
                    <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#create_project_offcanvas"><button
                            type="button" class="btn btn-sm btn-primary action_create_projects" data-bs-toggle="tooltip"
                            data-bs-placement="left"
                            data-bs-original-title="<?= get_label('create_project', 'Create project') ?>"><i
                                class='bx bx-plus'></i></button></a>
                @endif
                <a href="{{ $finalUrl }}"><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                        data-bs-placement="left" data-bs-original-title="<?= get_label('list_view', 'List view') ?>"><i
                            class='bx bx-list-ul'></i></button></a>
                <a href="{{ route('projects.kanban_view', ['status' => request()->status, 'sort' => request()->sort]) }}"><button
                        type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('kanban_view', 'Kanban View') ?>"><i
                            class='bx bxs-dashboard'></i></button></a>
                <a href="{{ route('projects.gantt_chart') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('gantt_chart_view', 'Gantt Chart View') ?>"><i
                            class='bx bxs-collection'></i></button></a>
                <a href="{{ route('projects.calendar_view') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="{{ get_label('calendar_view', 'Calendar log') }}"><i
                            class='bx bx-calendar'></i></button></a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <select class="form-select select2-ajax" id="status_filter" aria-label="Filter by status">
                    <option value=""><?= get_label('filter_by_status', 'Filter by status') ?></option>
                </select>

            </div>
            <div class="col-md-3 mb-3">
                <select class="form-select" id="sort" aria-label="Default select example">
                    <option value=""><?= get_label('sort_by', 'Sort by') ?></option>
                    <option value="newest" <?= request()->sort && request()->sort == 'newest' ? 'selected' : '' ?>>
                        <?= get_label('newest', 'Newest') ?></option>
                    <option value="oldest" <?= request()->sort && request()->sort == 'oldest' ? 'selected' : '' ?>>
                        <?= get_label('oldest', 'Oldest') ?></option>
                    <option value="recently-updated"
                        <?= request()->sort && request()->sort == 'recently-updated' ? 'selected' : '' ?>>
                        <?= get_label('most_recently_updated', 'Most recently updated') ?></option>
                    <option value="earliest-updated"
                        <?= request()->sort && request()->sort == 'earliest-updated' ? 'selected' : '' ?>>
                        <?= get_label('least_recently_updated', 'Least recently updated') ?></option>
                </select>
            </div>
            <div class="col-md-5 mb-3">
                <select id="selected_tags" class="form-control js-example-basic-multiple" name="tag[]" multiple="multiple"
                    data-placeholder="<?= get_label('filter_by_tags', 'Filter by tags') ?>">
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}" @if (in_array($tag->id, $selectedTags)) selected @endif>
                            {{ $tag->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <div>
                    <button type="button" id="tags_filter" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                        data-bs-placement="left" data-bs-original-title="<?= get_label('filter', 'Filter') ?>"><i
                            class='bx bx-filter-alt'></i></button>
                </div>
            </div>
        </div>
        @if (is_countable($projects) && count($projects) > 0)
            @php
                $showSettings =
                    $user->can('edit_projects') || $user->can('delete_projects') || $user->can('create_projects');
                $canEditProjects = $user->can('edit_projects');
                $canDeleteProjects = $user->can('delete_projects');
                $canDuplicateProjects = $user->can('create_projects');
            @endphp
            <div class="d-flex row mt-4">
                @foreach ($projects as $project)
                    <div class="col-md-6">
                        <div class="card mb-3 shadow-sm">
                            <div class="card-body">
                                @if (count($project->tags) > 0)
                                    <div class="mb-3">
                                        @foreach ($project->tags as $tag)
                                            <span class="badge bg-{{ $tag->color }} mt-1">{{ $tag->title }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h4 class="card-title">
                                        <a href="{{ url('/master-panel/projects/information/' . $project->id) }}"
                                            target="_blank" class="text-body">
                                            <strong>{{ $project->title }}</strong>
                                        </a>
                                    </h4>
                                    <div class="d-flex align-items-center">
                                        @if ($showSettings)
                                            <div class="dropdown">
                                                <a href="javascript:void(0);" class="mx-2" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class='bx bx-cog'></i>
                                                </a>
                                                <ul class="dropdown-menu">
                                                    @if ($canEditProjects)
                                                        <a href="javascript:void(0);" class="edit-project"
                                                            data-id="{{ $project->id }}">
                                                            <li class="dropdown-item">
                                                                <i class='menu-icon tf-icons bx bx-edit text-primary'></i>
                                                                {{ get_label('edit', 'Edit') }}
                                                            </li>
                                                        </a>
                                                    @endif
                                                    @if ($canDeleteProjects)
                                                        <a href="javascript:void(0);" class="delete" data-reload="true"
                                                            data-type="projects" data-id="{{ $project->id }}">
                                                            <li class="dropdown-item">
                                                                <i class='menu-icon tf-icons bx bx-trash text-danger'></i>
                                                                {{ get_label('delete', 'Delete') }}
                                                            </li>
                                                        </a>
                                                    @endif
                                                    @if ($canDuplicateProjects)
                                                        <a href="javascript:void(0);" class="duplicate"
                                                            data-type="projects" data-id="{{ $project->id }}"
                                                            data-title="{{ $project->title }}" data-reload="true">
                                                            <li class="dropdown-item">
                                                                <i class='menu-icon tf-icons bx bx-copy text-warning'></i>
                                                                {{ get_label('duplicate', 'Duplicate') }}
                                                            </li>
                                                        </a>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif
                                        <a href="javascript:void(0);" class="quick-view mx-2"
                                            data-id="{{ $project->id }}" data-type="project">
                                            <i class='bx bx-info-circle text-info' data-bs-toggle="tooltip"
                                                data-bs-placement="right" title="Quick View"></i>
                                        </a>
                                        <a href="javascript:void(0);" class="mx-2">
                                            <i class='bx {{ $project->is_favorite ? 'bxs' : 'bx' }}-star favorite-icon text-warning'
                                                data-id="{{ $project->id }}" data-bs-toggle="tooltip"
                                                data-bs-placement="right"
                                                data-bs-original-title="{{ $project->is_favorite ? get_label('remove_favorite', 'Click to remove from favorite') : get_label('add_favorite', 'Click to mark as favorite') }}"
                                                data-favorite="{{ $project->is_favorite }}"></i>
                                        </a>
                                        <a href="{{ route('projects.info', ['id' => $project->id]) }}#navs-top-discussions"
                                            target="_blank" class="text-danger mx-2">
                                            <i class='bx bx-message-rounded-dots' data-bs-toggle="tooltip"
                                                data-bs-placement="right" title="Discussion"></i>
                                        </a>
                                    </div>
                                </div>
                                @if ($project->budget != '')
                                    <span
                                        class='badge bg-label-primary me-1'>{{ format_currency($project->budget) }}</span>
                                @endif
                                <div class="my-{{ $project->budget != '' ? '3' : '2' }}">

                                    <div class="row align-items-center mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">{{ get_label('status', 'Status') }}</label>
                                            <div class="d-flex align-items-center">
                                                <div class="status-selector" id="statusSelector">
                                                    @if (isset($project->status))
                                                        <span
                                                            class="status-tag badge bg-label-{{ $project->status->color }} selected"
                                                            data-value="{{ $project->status->id }}">
                                                            {{ $project->status->title }}
                                                        </span>
                                                    @else
                                                        <span
                                                            class="status-tag badge bg-label-dark selected">{{ get_label('no_status', 'No status') }}</span>
                                                    @endif

                                                </div>
                                                @if ($project->note)
                                                    <div>
                                                        <span class="ms-2" data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            data-bs-original-title="{{ $project->note }}"><i
                                                                class="text-primary bx bxs-notepad"></i></span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ get_label('priority', 'Priority') }}</label>
                                            <div class="priority-selector" id="prioritySelector">
                                                @if (isset($project->priority))
                                                    <span
                                                        class="priority-tag badge bg-label-{{ $project->priority->color }} selected"
                                                        data-value="{{ $project->priority->id }}">
                                                        {{ $project->priority->title }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="priority-tag badge bg-label-dark selected">{{ get_label('no_priority', 'No priority') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Tasks Insights -->
                                <div class="mt-3">
                                    <label class="form-label">{{ get_label('tasks_insights', 'Tasks Insights') }}</label>
                                    <div class="progress h-100">
                                        @if ($project->tasks->count() > 0)
                                            @php
                                                $totalTasks = $project->tasks->count();
                                                $groupedTasks = $project->tasks->groupBy('status_id');
                                            @endphp
                                            @foreach ($groupedTasks as $statusId => $tasks)
                                                @php
                                                    $status = App\Models\Status::find($statusId);
                                                    $taskCount = $tasks->count();
                                                    $percentage = ($taskCount / $totalTasks) * 100;
                                                @endphp
                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-{{ $status->color ?? 'secondary' }}"
                                                    role="progressbar" style="width: {{ $percentage }}%;"
                                                    aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                    aria-valuemax="100"
                                                    title="{{ $status->title ?? 'No Title' }}: {{ $taskCount }} tasks">
                                                    {{ $status->title ?? 'No Title' }} ({{ $taskCount }})
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-secondary w-100"
                                                role="progressbar w-100" aria-valuenow="100" aria-valuemin="0"
                                                aria-valuemax="100"
                                                title="{{ get_label('no_tasks_yet', 'No Tasks Yet') }}">
                                                {{ get_label('no_tasks_yet', 'No Tasks Yet') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between my-4">
                                    <span>
                                        <i class='bx bx-task text-primary'></i>
                                        <b>{{ isAdminOrHasAllDataAccess() ? count($project->tasks) : $auth_user->project_tasks($project->id)->count() }}</b>
                                        {{ get_label('tasks', 'Tasks') }}
                                    </span>
                                    <a href="{{ url('/master-panel/projects/tasks/draggable/' . $project->id) }}"
                                        class="btn btn-sm rounded-pill btn-outline-primary">{{ get_label('tasks', 'Tasks') }}</a>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <p class="card-text">
                                            {{ get_label('users', 'Users') }}:
                                        <ul class="list-unstyled users-list avatar-group d-flex align-items-center m-0">
                                            @php
                                                $users = $project->users;
                                                $count = count($users);
                                                $displayed = 0;
                                            @endphp
                                            @if ($count > 0)
                                                @foreach ($users as $user)
                                                    @if ($displayed < 10)
                                                        <li class="avatar avatar-sm pull-up"
                                                            title="{{ $user->first_name }} {{ $user->last_name }}">
                                                            <a href="{{ route('users.show', ['id' => $user->id]) }}"
                                                                target="_blank">
                                                                <img src="{{ $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg') }}"
                                                                    class="rounded-circle"
                                                                    alt="{{ $user->first_name }} {{ $user->last_name }}">
                                                            </a>
                                                        </li>
                                                        @php $displayed++; @endphp
                                                    @else
                                                        @php
                                                            $remaining = $count - $displayed;
                                                        @endphp
                                                        <span
                                                            class="badge badge-center rounded-pill bg-primary mx-1">+{{ $remaining }}</span>
                                                        @break
                                                    @endif
                                                @endforeach
                                                <a href="javascript:void(0)"
                                                    class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients ms-1"
                                                    data-id="{{ $project->id }}">
                                                    <span class="bx bx-edit"></span>
                                                </a>
                                            @else
                                                <span
                                                    class="badge bg-primary">{{ get_label('not_assigned', 'Not assigned') }}</span>
                                                <a href="javascript:void(0)"
                                                    class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients ms-1"
                                                    data-id="{{ $project->id }}">
                                                    <span class="bx bx-edit"></span>
                                                </a>
                                            @endif
                                        </ul>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="card-text">
                                            {{ get_label('clients', 'Clients') }}:
                                        <ul class="list-unstyled users-list avatar-group d-flex align-items-center m-0">
                                            @php
                                                $clients = $project->clients;
                                                $count = count($clients);
                                                $displayed = 0;
                                            @endphp
                                            @if ($count > 0)
                                                @foreach ($clients as $client)
                                                    @if ($displayed < 10)
                                                        <li class="avatar avatar-sm pull-up"
                                                            title="{{ $client->first_name }} {{ $client->last_name }}">
                                                            <a href="{{ route('clients.profile', ['id' => $client->id]) }}"
                                                                target="_blank">
                                                                <img src="{{ $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg') }}"
                                                                    class="rounded-circle"
                                                                    alt="{{ $client->first_name }} {{ $client->last_name }}">
                                                            </a>
                                                        </li>
                                                        @php $displayed++; @endphp
                                                    @else
                                                        @php
                                                            $remaining = $count - $displayed;
                                                        @endphp
                                                        <span
                                                            class="badge badge-center rounded-pill bg-primary mx-1">+{{ $remaining }}</span>
                                                        @break
                                                    @endif
                                                @endforeach
                                                <a href="javascript:void(0)"
                                                    class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients ms-1"
                                                    data-id="{{ $project->id }}">
                                                    <span class="bx bx-edit"></span>
                                                </a>
                                            @else
                                                <span
                                                    class="badge bg-primary">{{ get_label('not_assigned', 'Not assigned') }}</span>
                                                <a href="javascript:void(0)"
                                                    class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients ms-1"
                                                    data-id="{{ $project->id }}">
                                                    <span class="bx bx-edit"></span>
                                                </a>
                                            @endif
                                        </ul>
                                        </p>
                                    </div>



                                    <div class="row">
                                        <div class="col-md-4 text-start">
                                            <i class="bx bx-calendar text-success"></i>
                                            {{ get_label('starts_at', 'Starts at') }}:
                                            {{ format_date($project->start_date) }}
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <i class="bx bx-calendar text-danger"></i>
                                            {{ get_label('ends_at', 'Ends at') }}:
                                            {{ format_date($project->end_date) }}
                                        </div>

                                        @php
                                            $endDate = Carbon\Carbon::parse($project->end_date);
                                            $currentDate = Carbon\Carbon::now();
                                                                            
                                            $daysDifference = (int) round($currentDate->diffInDays($endDate, false));
                                            $isOverdue = $daysDifference < 0;
                                        @endphp
                                                                            
                                        <div class="col-md-4 text-end">
                                            <span>
                                                <i class="bx bx-calendar-event text-{{ $isOverdue ? 'warning' : 'primary' }}"></i>
                                                <b>{{ abs($daysDifference) }}</b>
                                                {{ $isOverdue ? get_label('days_overdue', 'Days Overdue') : get_label('days_left', 'Days Left') }}
                                            </span>
                                        </div>

                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div>
                    {{ $projects->links() }}
                </div>
            </div>

            <!-- delete project modal -->
        @else
            <?php $type = 'projects'; ?>
            <x-empty-state-card :type="$type" />
        @endif
    </div>
    <script>
        var add_favorite = '<?= get_label('add_favorite', 'Click to mark as favorite') ?>';
        var remove_favorite = '<?= get_label('remove_favorite', 'Click to remove from favorite') ?>';
    </script>
    <script src="{{ asset('assets/js/pages/project-grid.js') }}"></script>
@endsection
