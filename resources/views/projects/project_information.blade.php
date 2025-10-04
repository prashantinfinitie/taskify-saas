@extends('layout')
@section('title')
    <?= get_label('project_details', 'Project details') ?>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('projects.index') }}">{{ get_label('projects', 'Projects') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ $project->title }}
                        </li>
                    </ol>
                </nav>
            </div>
            @php
                $taskDefaultView =
                    getUserPreferences('tasks', 'default_view') == 'tasks' ? 'tasks/list' : 'tasks/draggable';
            @endphp
            <div>
                <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#create_task_offcanvas"><button
                        type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('create_task', 'Create task') ?>"><i
                            class="bx bx-plus"></i></button></a>
                <a href="{{ url('/master-panel/projects/' . $taskDefaultView . '/' . $project->id) }}"><button
                        type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('tasks', 'Tasks') ?>"><i class="bx bx-task"></i></button></a>
                <a href="{{ route('projects.mind_map', ['id' => $project->id]) }}"><button type="button"
                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('mind_map', 'Mind map') ?>"><i
                            class="bx bx-slider"></i></button></a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                @if ($projectTags->isNotEmpty())
                                    <div class="mb-3">
                                        @foreach ($projectTags as $tag)
                                            <span class="badge bg-{{ $tag->color }}">{{ $tag->title }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                <h2 class="fw-bold">{{ $project->title }}
                                    <a href="javascript:void(0);" class="mx-2">
                                        <i class='bx {{ $project->is_favorite ? 'bxs' : 'bx' }}-star favorite-icon text-warning'
                                            data-id="{{ $project->id }}" data-bs-toggle="tooltip"
                                            data-bs-placement="right"
                                            data-bs-original-title="{{ $project->is_favorite ? get_label('remove_favorite', 'Click to remove from favorite') : get_label('add_favorite', 'Click to mark as favorite') }}"
                                            data-favorite="{{ $project->is_favorite ? 1 : 0 }}"></i>
                                    </a>
                                    {{-- <a href="{{ url('/chat?type=project&id=' . $project->id) }}" target="_blank">
                                        <i class='bx bx-message-rounded-dots text-danger' data-bs-toggle="tooltip"
                                            data-bs-placement="right"
                                            data-bs-original-title="{{ get_label('discussion', 'Discussion') }}"></i>
                                    </a> --}}
                                </h2>
                                <div class="row">
                                    <div class="col-md-6 mb-3 mt-3">
                                        <label class="form-label"
                                            for="start_date"><?= get_label('users', 'Users') ?></label>
                                        <?php
                                    $users = $project->users;
                                    if (count($users) > 0) { ?>
                                        <ul
                                            class="list-unstyled users-list avatar-group d-flex align-items-center m-0 flex-wrap">
                                            @foreach ($users as $user)
                                                <li class="avatar avatar-sm pull-up"
                                                    title="{{ $user->first_name }} {{ $user->last_name }}"><a
                                                        href="/master-panel/users/profile/{{ $user->id }}"
                                                        target="_blank">
                                                        <img src="{{ $user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg') }}"
                                                            class="rounded-circle"
                                                            alt="{{ $user->first_name }} {{ $user->last_name }}">
                                                    </a></li>
                                            @endforeach
                                            <a href="javascript:void(0)"
                                                class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients"
                                                data-id="{{ $project->id }}"><span class="bx bx-edit"></span></a>
                                        </ul>
                                        <?php } else { ?>
                                        <p><span
                                                class="badge bg-primary"><?= get_label('not_assigned', 'Not assigned') ?></span><a
                                                href="javascript:void(0)"
                                                class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients"
                                                data-id="{{ $project->id }}"><span class="bx bx-edit"></span></a></p>
                                        <?php } ?>
                                    </div>
                                    <div class="col-md-6 mb-3 mt-3">
                                        <label class="form-label"
                                            for="end_date"><?= get_label('clients', 'Clients') ?></label>
                                        <?php
                                    $clients = $project->clients;
                                    if (count($clients) > 0) { ?>
                                        <ul
                                            class="list-unstyled users-list avatar-group d-flex align-items-center m-0 flex-wrap">
                                            @foreach ($clients as $client)
                                                <li class="avatar avatar-sm pull-up"
                                                    title="{{ $client->first_name }} {{ $client->last_name }}"><a
                                                        href="/master-panel/clients/profile/{{ $client->id }}"
                                                        target="_blank">
                                                        <img src="{{ $client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg') }}"
                                                            class="rounded-circle"
                                                            alt="{{ $client->first_name }} {{ $client->last_name }}">
                                                    </a></li>
                                            @endforeach
                                            <a href="javascript:void(0)"
                                                class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients"
                                                data-id="{{ $project->id }}"><span class="bx bx-edit"></span></a>
                                        </ul>
                                        <?php } else { ?>
                                        <p><span
                                                class="badge bg-primary"><?= get_label('not_assigned', 'Not assigned') ?></span><a
                                                href="javascript:void(0)"
                                                class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-project update-users-clients"
                                                data-id="{{ $project->id }}"><span class="bx bx-edit"></span></a></p>
                                        <?php } ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><?= get_label('status', 'Status') ?></label>
                                        <div class="input-group">
                                            <select
                                                class="form-select form-select-sm select-bg-label-{{ $project->status->color }}"
                                                id="statusSelect" data-id="{{ $project->id }}"
                                                data-original-status-id="{{ $project->status->id }}"
                                                data-original-color-class="select-bg-label-{{ $project->status->color }}">
                                                @foreach ($statuses as $status)
                                                    @php
                                                        $disabled = canSetStatus($status) ? '' : 'disabled';
                                                    @endphp
                                                    <option value="{{ $status->id }}"
                                                        class="badge bg-label-{{ $status->color }}"
                                                        {{ $project->status->id == $status->id ? 'selected' : '' }}
                                                        {{ $disabled }}>
                                                        {{ $status->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="prioritySelect"
                                            class="form-label"><?= get_label('priority', 'Priority') ?></label>
                                        <div class="input-group">
                                            <select
                                                class="form-select form-select-sm select-bg-label-{{ $project->priority ? $project->priority->color : 'secondary' }}"
                                                id="prioritySelect" data-id="{{ $project->id }}"
                                                data-original-priority-id="{{ $project->priority ? $project->priority->id : '' }}"
                                                data-original-color-class="select-bg-label-{{ $project->priority ? $project->priority->color : 'secondary' }}">
                                                @foreach ($priorities as $priority)
                                                    <option value="{{ $priority->id }}"
                                                        class="badge bg-label-{{ $priority->color }}"
                                                        {{ $project->priority && $project->priority->id == $priority->id ? 'selected' : '' }}>
                                                        {{ $priority->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="my-0" />
                <div class="mb-4 mt-4">
                    <div class="row g-4">
                        <!-- Task Summary -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">{{ get_label('task_summary', 'Task Summary') }}</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $taskCounts = $project->tasks->groupBy('status_id')->map(function ($tasks) {
                                            return $tasks->count();
                                        });
                                    @endphp
                                    <div id="taskSummaryChart" class="mb-3"></div>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">Total Tasks</span>
                                            <span
                                                class="badge bg-primary rounded-pill">{{ $project->tasks->count() }}</span>
                                        </li>
                                        @foreach ($statuses as $status)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>{{ $status->title }}</span>
                                                <span
                                                    class="badge bg-{{ $status->color ?? 'secondary' }} rounded-pill">{{ $taskCounts[$status->id] ?? 0 }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- Project Details -->
                        <div class="col-md-6 col-lg-8">
                            <div class="row g-4">
                                <!-- Starts at -->
                                <div class="col-sm-6 col-lg-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start justify-content-between mb-2">
                                                <div class="avatar flex-shrink-0">
                                                    <i class="bx bx-calendar-check bx-md text-success"></i>
                                                </div>
                                            </div>
                                            <span
                                                class="fw-semibold d-block mb-1"><?= get_label('starts_at', 'Starts at') ?></span>
                                            <h3 class="card-title">{{ format_date($project->start_date) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <!-- Ends at -->
                                <div class="col-sm-6 col-lg-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start justify-content-between mb-2">
                                                <div class="avatar flex-shrink-0">
                                                    <i class="bx bx-calendar-x bx-md text-danger"></i>
                                                </div>
                                            </div>
                                            <span
                                                class="fw-semibold d-block mb-1"><?= get_label('ends_at', 'Ends at') ?></span>
                                            <h3 class="card-title">{{ format_date($project->end_date) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <!-- Duration -->
                                @php
                                    use Carbon\Carbon;
                                    $fromDate = Carbon::parse($project->start_date);
                                    $toDate = Carbon::parse($project->end_date);
                                    $duration = $fromDate->diffInDays($toDate) + 1;
                                @endphp
                                <div class="col-sm-6 col-lg-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start justify-content-between mb-2">
                                                <div class="avatar flex-shrink-0">
                                                    <i class="bx bx-time bx-md text-primary"></i>
                                                </div>
                                            </div>
                                            <span
                                                class="fw-semibold d-block mb-1"><?= get_label('duration', 'Duration') ?></span>
                                            <h3 class="card-title">
                                                {{ $duration . ' day' . ($duration > 1 ? 's' : '') }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <!-- Budget -->
                                <div class="col-sm-6 col-lg-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start justify-content-between mb-2">
                                                <div class="avatar flex-shrink-0">
                                                    <i class="bx bx-purchase-tag-alt bx-md text-warning"></i>
                                                </div>
                                            </div>
                                            <span
                                                class="fw-semibold d-block mb-1"><?= get_label('budget', 'Budget') ?></span>
                                            <h3 class="card-title">
                                                {{ !empty($project->budget) ? format_currency($project->budget) : '-' }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Description -->
                            <div class="card mt-4">
                                <div class="card-body">
                                    <h5 class="card-title"><?= get_label('description', 'Description') ?></h5>
                                    <p>{!! $project->description !== null && $project->description !== '' ? $project->description : '-' !!}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="media_type_id" value="{{ $project->id }}">
        <!-- Tabs -->
        <div class="nav-align-top mt-2">
            <ul class="nav nav-tabs" role="tablist">
                @php
                    $activeTab = '';
                @endphp
                @if ($auth_user->can('manage_tasks'))
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ empty($activeTab) ? 'active' : '' }}" role="tab"
                            data-bs-toggle="tab" data-bs-target="#navs-top-tasks" aria-controls="navs-top-tasks">
                            <i class="menu-icon tf-icons bx bx-task text-primary"></i><?= get_label('tasks', 'Tasks') ?>
                        </button>
                    </li>
                    @php
                        if (empty($activeTab)) {
                            $activeTab = 'tasks';
                        }
                    @endphp
                @endif
                @unless (getAuthenticatedUser()->hasRole('client'))
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $activeTab == 'discussions' ? 'active' : '' }}"
                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-discussions"
                            aria-controls="navs-top-discussions">
                            <i class="menu-icon tf-icons bx bxs-chat text-danger"></i>
                            <?= get_label('discussions', 'Discussions') ?>
                        </button>
                    </li>
                    @php
                        if (empty($activeTab)) {
                            $activeTab = 'discussions';
                        }
                    @endphp
                @endunless
                @if ($auth_user->can('manage_milestones'))
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $activeTab == 'milestones' ? 'active' : '' }}"
                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-milestones"
                            aria-controls="navs-top-milestones">
                            <i
                                class="menu-icon tf-icons bx bx-list-check text-warning"></i><?= get_label('milestones', 'Milestones') ?>
                        </button>
                    </li>
                    @php
                        if (empty($activeTab)) {
                            $activeTab = 'milestones';
                        }
                    @endphp
                @endif
                <li class="nav-item">
                    <button type="button" class="nav-link {{ empty($activeTab) ? 'active' : '' }}" role="tab"
                        data-bs-toggle="tab" data-bs-target="#navs-top-media" aria-controls="navs-top-media">
                        <i class="menu-icon tf-icons bx bx-image-alt text-success"></i><?= get_label('media', 'Media') ?>
                    </button>
                </li>
                @php
                    if (empty($activeTab)) {
                        $activeTab = 'media';
                    }
                @endphp
                <li class="nav-item">
                    <button type="button" class="nav-link {{ empty($activeTab) ? 'active' : '' }}" role="tab"
                        data-bs-toggle="tab" data-bs-target="#navs-top-issues" aria-controls="navs-top-issues">
                        <i class="menu-icon tf-icons bx bx-bug text-danger"></i><?= get_label('issues', 'Issues') ?>
                    </button>
                </li>
                @php
                    if (empty($activeTab)) {
                        $activeTab = 'issues';
                    }
                @endphp

                <li class="nav-item">
                    <button type="button" class="nav-link {{ empty($activeTab) ? 'active' : '' }}" role="tab"
                        data-bs-toggle="tab" data-bs-target="#navs-top-status_timeline"
                        aria-controls="navs-top-status_timeline">
                        <i
                            class="menu-icon tf-icons bx bx-align-justify text-dark"></i><?= get_label('status_timeline', 'Status Timeline') ?>
                    </button>
                </li>
                @php
                    if (empty($activeTab)) {
                        $activeTab = 'status_timeline';
                    }
                @endphp


                @if ($auth_user->can('manage_activity_log'))
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $activeTab == 'activity_log' ? 'active' : '' }}"
                            role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-activity-log"
                            aria-controls="navs-top-activity-log">
                            <i
                                class="menu-icon tf-icons bx bx-line-chart text-info"></i><?= get_label('activity_log', 'Activity log') ?>
                        </button>
                    </li>
                    @php
                        if (empty($activeTab)) {
                            $activeTab = 'activity_log';
                        }
                    @endphp
                @endif
            </ul>
            <div class="tab-content">
                @if ($auth_user->can('manage_tasks'))
                    <div class="tab-pane fade {{ $activeTab == 'tasks' ? 'active show' : '' }}" id="navs-top-tasks"
                        role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div></div>
                            <a href="javascript:void(0);" data-bs-toggle="offcanvas"
                                data-bs-target="#create_task_offcanvas">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                    data-bs-placement="left"
                                    data-bs-original-title="<?= get_label('create_task', 'Create Task') ?>">
                                    <i class="bx bx-plus"></i>
                                </button>
                            </a>
                        </div>
                        <?php
                        $id = 'project_' . $project->id;
                        $tasks = $project->tasks->count();
                        $users = $project->users;
                        $clients = $project->clients;
                        ?>
                        <x-tasks-card :tasks="$tasks" :id="$id" :users="$users" :clients="$clients"
                            :emptyState="0" />
                    </div>
            </div>
            @endif

            @unless (getAuthenticatedUser()->hasRole('client'))
                <div class="tab-pane fade {{ $activeTab == 'discussions' ? 'active show' : '' }}" id="navs-top-discussions"
                    role="tabpanel">
                    <!-- Discussions content -->
                    <x-discussions-card :project="$project" />
                </div>
            @endunless

            @if ($auth_user->can('manage_milestones'))
                @php
                    $visibleColumns = getUserPreferences('milestone');
                @endphp
                <div class="tab-pane fade {{ $activeTab == 'milestones' ? 'active show' : '' }}" id="navs-top-milestones"
                    role="tabpanel">
                    <!-- Milestones content -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div></div>
                            <a href="javascript:void(0);" data-bs-toggle="modal"
                                data-bs-target="#create_milestone_modal">
                                <button type="button" class="btn btn-sm btn-primary action_create_milestones"
                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                    data-bs-original-title="<?= get_label('create_milestone', 'Create milestone') ?>">
                                    <i class="bx bx-plus"></i>
                                </button>
                            </a>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-4 mb-3">
                                <div class="input-group input-group-merge">
                                    <input type="text" id="start_date_between" name="start_date_between"
                                        class="form-control"
                                        placeholder="<?= get_label('start_date_between', 'Start date between') ?>"
                                        autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="input-group input-group-merge">
                                    <input type="text" id="end_date_between" name="end_date_between"
                                        class="form-control"
                                        placeholder="<?= get_label('end_date_between', 'End date between') ?>"
                                        autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <select class="form-select" id="status_filter" aria-label="Default select example">
                                    <option value=""><?= get_label('select_status', 'Select status') ?></option>
                                    <option value="incomplete"><?= get_label('incomplete', 'Incomplete') ?></option>
                                    <option value="complete"><?= get_label('complete', 'Complete') ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive text-nowrap">
                            <input type="hidden" name="start_date_from" id="start_date_from">
                            <input type="hidden" name="start_date_to" id="start_date_to">
                            <input type="hidden" name="end_date_from" id="end_date_from">
                            <input type="hidden" name="end_date_to" id="end_date_to">
                            <input type="hidden" id="data_type" value="milestone">
                            <input type="hidden" id="data_table" value="project_milestones_table">
                            <input type="hidden" id="save_column_visibility">
                            <table id="project_milestones_table" data-toggle="table"
                                data-loading-template="loadingTemplate"
                                data-url="{{ route('projects.get_milestones', ['id' => $project->id]) }}"
                                data-icons-prefix="bx" data-icons="icons" data-show-refresh="true"
                                data-total-field="total" data-trim-on-search="false" data-data-field="rows"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                data-side-pagination="server" data-show-columns="true" data-pagination="true"
                                data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                data-query-params="queryParamsProjectMilestones">
                                <thead>
                                    <tr>
                                        <th data-checkbox="true"></th>
                                        <th data-field="id"
                                            data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('id', 'ID') ?></th>
                                        <th data-field="title"
                                            data-visible="{{ in_array('title', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('title', 'Title') ?></th>
                                        <th data-field="start_date"
                                            data-visible="{{ in_array('start_date', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('start_date', 'Start date') ?></th>
                                        <th data-field="end_date"
                                            data-visible="{{ in_array('end_date', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('end_date', 'End date') ?></th>
                                        <th data-field="cost"
                                            data-visible="{{ in_array('cost', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('cost', 'Cost') ?></th>
                                        <th data-field="progress"
                                            data-visible="{{ in_array('progress', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('progress', 'Progress') ?></th>
                                        <th data-field="status"
                                            data-visible="{{ in_array('status', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('status', 'Status') ?></th>
                                        <th data-field="description" data-sortable="true"
                                            data-visible="{{ in_array('description', $visibleColumns) ? 'true' : 'false' }}">
                                            {{ get_label('description', 'Description') }}</th>
                                        <th data-field="created_by" data-sortable="true"
                                            data-visible="{{ in_array('created_by', $visibleColumns) ? 'true' : 'false' }}">
                                            {{ get_label('created_by', 'Created by') }}</th>
                                        <th data-field="created_at" data-sortable="true"
                                            data-visible="{{ in_array('created_at', $visibleColumns) ? 'true' : 'false' }}">
                                            {{ get_label('created_at', 'Created at') }}</th>
                                        <th data-field="updated_at" data-sortable="true"
                                            data-visible="{{ in_array('updated_at', $visibleColumns) ? 'true' : 'false' }}">
                                            {{ get_label('updated_at', 'Updated at') }}</th>
                                        <th data-field="actions"
                                            data-visible="{{ in_array('actions', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                            {{ get_label('actions', 'Actions') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
            <div class="tab-pane fade {{ $activeTab == 'media' ? 'active show' : '' }}" id="navs-top-media"
                role="tabpanel">
                <!-- Media content -->
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div></div>
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#add_media_modal">
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                data-bs-placement="left"
                                data-bs-original-title="<?= get_label('add_media', 'Add Media') ?>">
                                <i class="bx bx-plus"></i>
                            </button>
                        </a>
                    </div>
                    @php
                        $visibleColumns = getUserPreferences('project_media');
                    @endphp
                    <div class="table-responsive text-nowrap">
                        <input type="hidden" id="data_type" value="project-media">
                        <input type="hidden" id="data_table" value="project_media_table">
                        <input type="hidden" id="save_column_visibility">
                        <table id="project_media_table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ route('projects.get_media', ['id' => $project->id]) }}" data-icons-prefix="bx"
                            data-icons="icons" data-show-refresh="true" data-total-field="total"
                            data-trim-on-search="false" data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-search="true" data-side-pagination="server" data-show-columns="true"
                            data-pagination="true" data-sort-name="id" data-sort-order="desc"
                            data-mobile-responsive="true" data-query-params="queryParamsProjectMedia">
                            <thead>
                                <tr>
                                    <th data-checkbox="true"></th>
                                    <th data-field="id"
                                        data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('id', 'ID') ?></th>
                                    <th data-field="file"
                                        data-visible="{{ in_array('file', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('file', 'File') ?></th>
                                    <th data-field="file_name" data-sortable="true"
                                        data-visible="{{ in_array('file_name', $visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('file_name', 'File name') }}</th>
                                    <th data-field="file_size"
                                        data-visible="{{ in_array('file_size', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('file_size', 'File size') ?></th>
                                    <th data-field="created_at" data-sortable="true"
                                        data-visible="{{ in_array('created_at', $visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('created_at', 'Created at') }}</th>
                                    <th data-field="updated_at" data-sortable="true"
                                        data-visible="{{ in_array('updated_at', $visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('updated_at', 'Updated at') }}</th>
                                    <th data-field="actions"
                                        data-visible="{{ in_array('actions', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="false">{{ get_label('actions', 'Actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Issues Tab --}}
            <div class="tab-pane fade {{ $activeTab == 'issues' ? 'active show' : '' }}" id="navs-top-issues"
                role="tabpanel">
                <!-- Issues content -->
                <x-project-issues :project="$project" />
            </div>
            <div class="tab-pane fade {{ $activeTab == 'status_timeline' ? 'active show' : '' }}"
                id="navs-top-status_timeline" role="tabpanel">
                <!-- Status timeline content -->
                <x-status-timeline :timelines="$project->statusTimelines->sortByDesc('changed_at')" />
            </div>


            @if ($auth_user->can('manage_activity_log'))
                <div class="tab-pane fade {{ $activeTab == 'activity_log' ? 'active show' : '' }}"
                    id="navs-top-activity-log" role="tabpanel">
                    <!-- Activity log content -->
                    <div class="col-12">
                        <div class="row mt-4">
                            <div class="col-md-4 mb-3">
                                <div class="input-group input-group-merge">
                                    <input type="text" id="activity_log_between_date" class="form-control"
                                        placeholder="<?= get_label('date_between', 'Date between') ?>" autocomplete="off">
                                </div>
                            </div>
                            @if (isAdminOrHasAllDataAccess())
                                <div class="col-md-4 mb-3">
                                    <select class="form-select" id="user_filter" aria-label="Default select example">
                                        <option value=""><?= get_label('select_user', 'Select user') ?></option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->first_name . ' ' . $user->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <select class="form-select" id="client_filter" aria-label="Default select example">
                                        <option value=""><?= get_label('select_client', 'Select client') ?>
                                        </option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}">
                                                {{ $client->first_name . ' ' . $client->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-4 mb-3">
                                <select class="form-select" id="activity_filter" aria-label="Default select example">
                                    <option value=""><?= get_label('select_activity', 'Select activity') ?>
                                    </option>
                                    <option value="created"><?= get_label('created', 'Created') ?></option>
                                    <option value="updated"><?= get_label('updated', 'Updated') ?></option>
                                    <option value="duplicated"><?= get_label('duplicated', 'Duplicated') ?></option>
                                    <option value="uploaded"><?= get_label('uploaded', 'Uploaded') ?></option>
                                    <option value="deleted"><?= get_label('deleted', 'Deleted') ?></option>
                                </select>
                            </div>
                        </div>
                        @php
                            $visibleColumns = getUserPreferences('activity_log');
                        @endphp
                        <div class="table-responsive text-nowrap">
                            <input type="hidden" id="activity_log_between_date_from">
                            <input type="hidden" id="activity_log_between_date_to">
                            <input type="hidden" id="data_type" value="activity-log">
                            <input type="hidden" id="data_table" value="activity_log_table">
                            <input type="hidden" id="type_id" value="{{ $project->id }}">
                            <input type="hidden" id="save_column_visibility">
                            <table id="activity_log_table" data-toggle="table" data-loading-template="loadingTemplate"
                                data-url="{{ route('activity_log.list') }}" data-icons-prefix="bx" data-icons="icons"
                                data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                                data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                data-side-pagination="server" data-show-columns="true" data-pagination="true"
                                data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        <th data-checkbox="true"></th>
                                        <th data-field="id"
                                            data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('id', 'ID') ?></th>
                                        <th data-field="actor_id"
                                            data-visible="{{ in_array('actor_id', $visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('actor_id', 'Actor ID') ?></th>
                                        <th data-field="actor_name"
                                            data-visible="{{ in_array('actor_name', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('actor_name', 'Actor name') ?></th>
                                        <th data-field="actor_type"
                                            data-visible="{{ in_array('actor_type', $visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('actor_type', 'Actor type') ?></th>
                                        <th data-field="type_id"
                                            data-visible="{{ in_array('type_id', $visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('type_id', 'Type ID') ?></th>
                                        <th data-field="parent_type_id"
                                            data-visible="{{ in_array('parent_type_id', $visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('parent_type_id', 'Parent type ID') ?>
                                        </th>
                                        <th data-field="activity"
                                            data-visible="{{ in_array('activity', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('activity', 'Activity') ?></th>
                                        <th data-field="type"
                                            data-visible="{{ in_array('type', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('type', 'Type') ?></th>
                                        <th data-field="parent_type"
                                            data-visible="{{ in_array('parent_type', $visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('parent_type', 'Parent type') ?></th>
                                        <th data-field="type_title"
                                            data-visible="{{ in_array('type_title', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('type_title', 'Type title') ?></th>
                                        <th data-field="parent_type_title"
                                            data-visible="{{ in_array('parent_type_title', $visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true">
                                            <?= get_label('parent_type_title', 'Parent type title') ?></th>
                                        <th data-field="message"
                                            data-visible="{{ in_array('message', $visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('message', 'Message') ?></th>
                                        <th data-field="created_at"
                                            data-visible="{{ in_array('created_at', $visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('created_at', 'Created at') ?></th>
                                        <th data-field="updated_at"
                                            data-visible="{{ in_array('updated_at', $visibleColumns) ? 'true' : 'false' }}"
                                            data-sortable="true"><?= get_label('updated_at', 'Updated at') ?></th>
                                        <th data-field="actions"
                                            data-visible="{{ in_array('actions', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                            <?= get_label('actions', 'Actions') ?></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="modal fade" id="create_milestone_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('projects.store_milestone') }}"
                method="POST">
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <input type="hidden" name="dnr">
                <input type="hidden" name="table" value="project_milestones_table">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_milestone', 'Create milestone') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="nameBasic" class="form-label"><?= get_label('title', 'Title') ?> <span
                                    class="asterisk">*</span></label>
                            <input type="text" name="title" class="form-control"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label"><?= get_label('starts_at', 'Starts at') ?> <span
                                    class="asterisk">*</span></label>
                            <input type="text" id="start_date" name="start_date" class="form-control" placeholder=""
                                autocomplete="off">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label"><?= get_label('ends_at', 'Ends at') ?> <span
                                    class="asterisk">*</span></label>
                            <input type="text" id="end_date" name="end_date" class="form-control" placeholder=""
                                autocomplete="off">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label"><?= get_label('status', 'Status') ?> <span
                                    class="asterisk">*</span></label>
                            <select class="form-select" name="status">
                                <option value="incomplete"><?= get_label('incomplete', 'Incomplete') ?></option>
                                <option value="complete"><?= get_label('complete', 'Complete') ?></option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label"><?= get_label('cost', 'Cost') ?> <span
                                    class="asterisk">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input type="text" name="cost" class="form-control"
                                    placeholder="<?= get_label('please_enter_cost', 'Please enter cost') ?>">
                            </div>
                            <span class="text-danger error-message mt-1 text-xs"></span>
                        </div>
                    </div>
                    <label for="description" class="form-label"><?= get_label('description', 'Description') ?></label>
                    <textarea class="form-control description" name="description"
                        placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn"
                        class="btn btn-primary"><?= get_label('create', 'Create') ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="edit_milestone_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('contracts.update') }}" method="POST">
                <input type="hidden" name="id" id="milestone_id">
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <input type="hidden" name="dnr">
                <input type="hidden" name="table" value="project_milestones_table">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_milestone', 'Update milestone') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="nameBasic" class="form-label"><?= get_label('title', 'Title') ?> <span
                                    class="asterisk">*</span></label>
                            <input type="text" name="title" id="milestone_title" class="form-control"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label"><?= get_label('starts_at', 'Starts at') ?> <span
                                    class="asterisk">*</span></label>
                            <input type="text" id="update_milestone_start_date" name="start_date"
                                class="form-control" placeholder="" autocomplete="off">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label"><?= get_label('ends_at', 'Ends at') ?> <span
                                    class="asterisk">*</span></label>
                            <input type="text" id="update_milestone_end_date" name="end_date" class="form-control"
                                placeholder="" autocomplete="off">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label"><?= get_label('status', 'Status') ?> <span
                                    class="asterisk">*</span></label>
                            <select class="form-select" id="milestone_status" name="status">
                                <option value="incomplete"><?= get_label('incomplete', 'Incomplete') ?></option>
                                <option value="complete"><?= get_label('complete', 'Complete') ?></option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label"><?= get_label('cost', 'Cost') ?> <span
                                    class="asterisk">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input type="text" name="cost" id="milestone_cost" class="form-control"
                                    placeholder="<?= get_label('please_enter_cost', 'Please enter cost') ?>">
                            </div>
                            <span class="text-danger error-message mt-1 text-xs"></span>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="nameBasic" class="form-label"><?= get_label('progress', 'Progress') ?></label>
                            <input type="range" name="progress" id="milestone_progress" class="form-range">
                            <h6 class="milestone-progress mt-2"></h6>
                            <span class="text-danger error-message mt-1 text-xs"></span>
                        </div>
                    </div>
                    <label for="description" class="form-label"><?= get_label('description', 'Description') ?></label>
                    <textarea class="form-control description" name="description" id="milestone_description"
                        placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn"
                        class="btn btn-primary"><?= get_label('update', 'Update') ?></button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="add_media_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content form-horizontal" id="media-upload" action="{{ route('projects.upload_media') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1"><?= get_label('add_media', 'Add Media') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-primary alert-dismissible" role="alert">
                        <?= $media_storage_settings['media_storage_type'] == 's3' ? get_label('storage_type_set_as_aws_s3', 'Storage type is set as AWS S3 storage') : get_label('storage_type_set_as_local', 'Storage type is set as local storage') ?>,
                        <a href="/settings/media-storage"
                            target="_blank"><?= get_label('click_here_to_change', 'Click here to change.') ?></a>
                    </div>
                    <div class="dropzone dz-clickable" id="media-upload-dropzone">
                    </div>
                    <div class="form-group mt-4 text-center">
                        <button class="btn btn-primary"
                            id="upload_media_btn"><?= get_label('upload', 'Upload') ?></button>
                    </div>
                    <div class="d-flex justify-content-center">
                        <div class="form-group" id="error_box">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        //labels
        var projectID = {{ $project->id }};
        var seriesData = JSON.stringify(@json(
            $statuses->map(function ($status) use ($taskCounts) {
                return $taskCounts[$status->id] ?? 0;
            })));
        var labelsData = JSON.stringify(@json($statuses->pluck('title')));
        var statusColors = JSON.stringify(@json($statuses->pluck('color')));
        var total = '<?= get_label('total', 'Total') ?>';
        var add_favorite = '<?= get_label('add_favorite', 'Click to mark as favorite') ?>';
        var remove_favorite = '<?= get_label('remove_favorite', 'Click to remove from favorite') ?>';
        var label_delete = '<?= get_label('delete', 'Delete') ?>';
        var label_download = '<?= get_label('download', 'Download') ?>';
    </script>
    <script src="{{ asset('assets/js/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/js/pages/project-information.js') }}"></script>

@endsection
