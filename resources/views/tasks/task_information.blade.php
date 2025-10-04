@extends('layout')
@section('title')
    {{ get_label('task_details', 'Task details') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="align-items-center d-flex justify-content-between m-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}">{{ get_label('home', 'Home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ getDefaultViewRoute('tasks') }}">{{ get_label('tasks', 'Tasks') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ $task->title }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h2 class="fw-bold">{{ $task->title }}
                                    {{-- <a href="{{ url('/chat?type=task&id=' . $task->id) }}" class="mx-2" target="_blank">
                                <i class='bx bx-message-rounded-dots text-danger' data-bs-toggle="tooltip" data-bs-placement="right" data-bs-original-title="{{get_label('discussion', 'Discussion')}}"></i>
                                </a> --}}
                                </h2>
                                @if ($task->completion_percentage != null)
                                    <div class="progress h-2vh">
                                        @php
                                            $progressBarClass = '';
                                            if ($task->completion_percentage > 75) {
                                                $progressBarClass = 'bg-success';
                                            } elseif ($task->completion_percentage > 50) {
                                                $progressBarClass = 'bg-warning';
                                            } elseif ($task->completion_percentage > 25) {
                                                $progressBarClass = 'bg-info';
                                            } else {
                                                $progressBarClass = 'bg-danger';
                                            }
                                        @endphp
                                        <div role="progressbar"
                                            class="progress-bar progress-bar-striped progress-bar-animated {{ $progressBarClass }}"
                                            role="progressbar" style="width: {{ $task->completion_percentage }}%;"
                                            aria-valuenow="{{ $task->completion_percentage }}" aria-valuemin="0"
                                            aria-valuemax="100">
                                            {{ get_label('completion_percentage', 'Completion Percentage') }} :
                                            {{ $task->completion_percentage }}%
                                        </div>
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="col-md-6 mb-3 mt-3">
                                        <label class="form-label"
                                            for="start_date">{{ get_label('users', 'Users') }}</label>
                                        <?php
                                                                            $users = $task->users;
                                                                            $clients = $task->project->clients;
                                                                            if (count($users) > 0) {
                                                                            ?>
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
                                                class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-task update-users-clients"
                                                data-id="{{ $task->id }}"><span class="bx bx-edit"></span></a>
                                        </ul>
                                        <?php } else { ?>
                                        <p><span
                                                class="badge bg-primary">{{ get_label('not_assigned', 'Not assigned') }}</span><a
                                                href="javascript:void(0)"
                                                class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-task update-users-clients"
                                                data-id="{{ $task->id }}"><span class="bx bx-edit"></span></a></p>
                                        <?php } ?>
                                    </div>
                                    <div class="col-md-6 mb-3 mt-3">
                                        <label class="form-label"
                                            for="end_date">{{ get_label('clients', 'Clients') }}</label>
                                        <?php
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
                                        </ul>
                                        <?php } else { ?>
                                        <p><span
                                                class="badge bg-primary">{{ get_label('not_assigned', 'Not assigned') }}</span>
                                        </p>
                                        <?php } ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ get_label('status', 'Status') }}</label>
                                        <div class="input-group">
                                            <select
                                                class="form-select form-select-sm select-bg-label-{{ $task->status->color }}"
                                                id="statusSelect" data-id="{{ $task->id }}"
                                                data-original-status-id="{{ $task->status->id }}"
                                                data-original-color-class="select-bg-label-{{ $task->status->color }}"
                                                data-type="task">
                                                @foreach ($statuses as $status)
                                                    @php
                                                        $disabled = canSetStatus($status) ? '' : 'disabled';
                                                    @endphp
                                                    <option value="{{ $status->id }}"
                                                        class="badge bg-label-{{ $status->color }}"
                                                        {{ $task->status->id == $status->id ? 'selected' : '' }}
                                                        {{ $disabled }}>
                                                        {{ $status->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="prioritySelect"
                                            class="form-label">{{ get_label('priority', 'Priority') }}</label>
                                        <div class="input-group">
                                            <select
                                                class="form-select form-select-sm select-bg-label-{{ $task->priority ? $task->priority->color : 'secondary' }}"
                                                id="prioritySelect" data-id="{{ $task->id }}"
                                                data-original-priority-id="{{ $task->priority ? $task->priority->id : '' }}"
                                                data-original-color-class="select-bg-label-{{ $task->priority ? $task->priority->color : 'secondary' }}"
                                                data-type="task">
                                                @foreach ($priorities as $priority)
                                                    <option value="{{ $priority->id }}"
                                                        class="badge bg-label-{{ $priority->color }}"
                                                        {{ $task->priority && $task->priority->id == $priority->id ? 'selected' : '' }}>
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
                    <hr class="my-0" />
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label" for="project">{{ get_label('project', 'Project') }}</label>
                                <div class="input-group input-group-merge">
                                    @php
                                        $project = $task->project;
                                    @endphp
                                    <input class="form-control px-2" type="text" id="project"
                                        value="{{ $project->title }}" readonly="">
                                </div>
                            </div>
                        </div>
                        @isset($task->description)
                            <div class="row">
                                <div class="mb-3">
                                    <label class="form-label"
                                        for="description">{{ get_label('description', 'Description') }}</label>
                                    <div class="input-group input-group-merge">
                                        <div class="form-control" rows="5" readonly>{!! $task->description !!}</div>
                                    </div>
                                </div>
                            </div>
                        @endisset
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label"
                                    for="start_date">{{ get_label('starts_at', 'Starts at') }}</label>
                                <div class="input-group input-group-merge">
                                    <input type="text" name="start_date" class="form-control" placeholder=""
                                        value="{{ format_date($task->start_date) }}" readonly />
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="due-date">{{ get_label('ends_at', 'Ends at') }}</label>
                                <div class="input-group input-group-merge">
                                    <input class="form-control" type="text" name="due_date" placeholder=""
                                        value="{{ format_date($task->due_date) }}" readonly="">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"
                                    for="billing_type">{{ get_label('billing_type', 'Billing Type') }}</label>
                                <span class="form-control" readonly
                                    value="{{ $task->billing_type }}">{{ ucwords($task->billing_type) }}</span>
                            </div>
                        </div>

                    </div>
                    <hr class="my-0">
                    <div>
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <label class="form-label">{{ get_label('reminder_details', 'Reminders Details') }}</label>
                            <span class="badge {{ $task->reminders->first()?->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $task->reminders->first()?->is_active ? get_label('active', 'Active') : get_label('inactive', 'Inactive') }}
                            </span>
                        </div>
                        @if ($task->reminders->isNotEmpty())
                            <div class="card-body">
                                @php
                                    $reminder = $task->reminders->first();
                                    $frequencyType = ucfirst($reminder->frequency_type);
                                    $timeOfDay = \Carbon\Carbon::parse($reminder->time_of_day)->format('h:i A');
                                @endphp

                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ get_label('frequency', 'Frequency') }}</label>
                                            <p class="form-control mb-0" readonly>
                                                @switch($reminder->frequency_type)
                                                    @case('daily')
                                                        <i class="bx bx-time text-dark me-1"></i>
                                                        {{ get_label('daily_at', 'Daily at') }} {{ $timeOfDay }}
                                                    @break

                                                    @case('weekly')
                                                        <i class="bx bx-calendar text-dark me-1"></i>
                                                        @php
                                                            $dayNames = [
                                                                1 => 'Monday',
                                                                2 => 'Tuesday',
                                                                3 => 'Wednesday',
                                                                4 => 'Thursday',
                                                                5 => 'Friday',
                                                                6 => 'Saturday',
                                                                7 => 'Sunday',
                                                            ];
                                                            $dayName = $reminder->day_of_week
                                                                ? get_label(
                                                                    strtolower($dayNames[$reminder->day_of_week]),
                                                                    $dayNames[$reminder->day_of_week],
                                                                )
                                                                : get_label('any_day', 'Any Day');
                                                        @endphp
                                                        {{ get_label('weekly_on', 'Weekly on') }} {{ $dayName }}
                                                        {{ get_label('at', 'at') }} {{ $timeOfDay }}
                                                    @break

                                                    @case('monthly')
                                                        <i class="bx bx-calendar-alt text-dark me-1"></i>
                                                        @php
                                                            $dayOfMonth =
                                                                $reminder->day_of_month ?:
                                                                get_label('any_day', 'Any Day');
                                                            if (is_numeric($dayOfMonth)) {
                                                                $dayOfMonth .= date(
                                                                    'S',
                                                                    mktime(0, 0, 0, 1, $dayOfMonth, 2000),
                                                                ); // Adds st, nd, rd, th
                                                            }
                                                        @endphp
                                                        {{ get_label('monthly_on_the', 'Monthly on the') }} {{ $dayOfMonth }}
                                                        {{ get_label('at', 'at') }} {{ $timeOfDay }}
                                                    @break
                                                @endswitch
                                            </p>
                                        </div>

                                        @if ($reminder->last_sent_at)
                                            <div class="mb-3">
                                                <label
                                                    class="form-label">{{ get_label('last_reminder_sent', 'Last Reminder Sent') }}</label>
                                                <p class="form-control mb-0" readonly>
                                                    <i class='bx bxs-alarm-add text-primary me-1'></i>
                                                    {{ \Carbon\Carbon::parse($reminder->last_sent_at)->diffForHumans() }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ get_label('created_on', 'Created On') }}</label>
                                            <p class="form-control mb-0" readonly>
                                                <i class='bx bxs-calendar-event text-danger me-1'></i>
                                                {{ format_date($reminder->created_at) }}
                                            </p>
                                        </div>

                                        <div class="mb-3">
                                            <label
                                                class="form-label">{{ get_label('last_updated', 'Last Updated') }}</label>
                                            <p class="form-control mb-0" readonly>
                                                <i class='bx bx-calendar text-warning me-1'></i>
                                                {{ format_date($reminder->updated_at, true) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="card-body">
                                <p class="text-muted mb-0">
                                    <i class="fas fa-bell-slash me-2"></i>
                                    {{ get_label('no_reminders_set', 'No reminders set for this task') }}
                                </p>
                            </div>
                        @endif
                    </div>
                    <hr class="my-0">
                    <div>
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <label class="form-label">{{ get_label('recurrence_details', 'Recurrence Details') }}</label>
                            <span class="badge {{ $task->recurringTask?->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $task->recurringTask?->is_active ? get_label('active', 'Active') : get_label('inactive', 'Inactive') }}
                            </span>
                        </div>
                        @if ($task->recurringTask)
                            <div class="card-body">
                                @php
                                    $recurringTask = $task->recurringTask;
                                    $frequencyType = ucfirst($recurringTask->frequency);

                                @endphp

                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ get_label('frequency', 'Frequency') }}</label>
                                            <p class="form-control mb-0" readonly>
                                                @switch($recurringTask->frequency)
                                                    @case('daily')
                                                        <i class="bx bx-time text-dark me-1"></i>
                                                        {{ get_label('daily_at', 'Daily at') }} {{ '00:00' }}
                                                    @break

                                                    @case('weekly')
                                                        <i class="bx bx-calendar text-dark me-1"></i>
                                                        @php
                                                            $dayNames = [
                                                                1 => 'Monday',
                                                                2 => 'Tuesday',
                                                                3 => 'Wednesday',
                                                                4 => 'Thursday',
                                                                5 => 'Friday',
                                                                6 => 'Saturday',
                                                                7 => 'Sunday',
                                                            ];
                                                            $dayName = $recurringTask->day_of_week
                                                                ? get_label(
                                                                    strtolower($dayNames[$recurringTask->day_of_week]),
                                                                    $dayNames[$recurringTask->day_of_week],
                                                                )
                                                                : get_label('any_day', 'Any Day');
                                                        @endphp
                                                        {{ get_label('weekly_on', 'Weekly on') }} {{ $dayName }}
                                                        {{ get_label('at', 'at') }} {{ '00:00' }}
                                                    @break

                                                    @case('monthly')
                                                        <i class="bx bx-calendar-alt text-dark me-1"></i>
                                                        @php
                                                            $dayOfMonth =
                                                                $recurringTask->day_of_month ?:
                                                                get_label('any_day', 'Any Day');
                                                            if (is_numeric($dayOfMonth)) {
                                                                $dayOfMonth .= date(
                                                                    'S',
                                                                    mktime(0, 0, 0, 1, $dayOfMonth, 2000),
                                                                ); // Adds st, nd, rd, th
                                                            }
                                                        @endphp
                                                        {{ get_label('monthly_on_the', 'Monthly on the') }} {{ $dayOfMonth }}
                                                        {{ get_label('at', 'at') }} {{ '00:00' }}
                                                    @break

                                                    @case('yearly')
                                                        <i class="bx bx-calendar-alt text-dark me-1"></i>
                                                        @php
                                                            $dayOfMonth =
                                                                $recurringTask->day_of_month ?:
                                                                get_label('any_day', 'Any Day');
                                                            if (is_numeric($dayOfMonth)) {
                                                                $dayOfMonth .= date(
                                                                    'S',
                                                                    mktime(0, 0, 0, 1, $dayOfMonth, 2000),
                                                                ); // Adds st, nd, rd, th
                                                            }
                                                        @endphp
                                                        {{ get_label('yearly_on_the', 'Yearly on the') }} {{ $dayOfMonth }}
                                                        {{ get_label('of', 'of') }}
                                                        {{ \Carbon\Carbon::create()->month($recurringTask->month_of_year)->format('F') }}
                                                        {{ get_label('at', 'at') }} {{ '00:00' }}
                                                    @break
                                                @endswitch
                                            </p>
                                        </div>

                                        @if ($recurringTask->starts_from)
                                            <div class="mb-3">
                                                <label
                                                    class="form-label">{{ get_label('starts_from', 'Starts From') }}</label>
                                                <p class="form-control mb-0" readonly>
                                                    <i class='bx bxs-alarm-add text-primary me-1'></i>
                                                    {{ format_date($recurringTask->starts_from) }}
                                                </p>
                                            </div>
                                        @endif
                                        <div class="mb-3">
                                            <label
                                                class="form-label">{{ get_label('completed_occurrences', 'Completed Occurrences') }}</label>
                                            <p class="form-control mb-0" readonly>
                                                <i class='bx bxs-analyse text-success me-1'></i>
                                                {{ $recurringTask->completed_occurrences ?? 0 }}
                                                {{ get_label('completed_occurances', 'Completed Occurrences') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-md-6">


                                        <div class="mb-3">
                                            <label class="form-label">{{ get_label('created_on', 'Created On') }}</label>
                                            <p class="form-control mb-0" readonly>
                                                <i class='bx bxs-calendar-event text-danger me-1'></i>
                                                {{ format_date($recurringTask->created_at, true) }}
                                            </p>
                                        </div>

                                        <div class="mb-3">
                                            <label
                                                class="form-label">{{ get_label('last_updated', 'Last Updated') }}</label>
                                            <p class="form-control mb-0" readonly>
                                                <i class='bx bx-calendar text-warning me-1'></i>
                                                {{ format_date($recurringTask->updated_at, true) }}
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label
                                                class="form-label">{{ get_label('number_of_occurrences', 'Number of Occurrences') }}</label>
                                            <p class="form-control mb-0" readonly>
                                                <i class='bx bxs-analyse text-primary me-1'></i>
                                                {{ $recurringTask->number_of_occurrences }}
                                                {{ get_label('number_of_occurances', 'Number of Occurrences') }}
                                            </p>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="card-body">
                                <p class="text-muted mb-0">
                                    <i class="fas fa-bell-slash me-2"></i>
                                    {{ get_label('no_recurrence_set', 'No recurrence set for this task') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
                <input type="hidden" id="media_type_id" value="{{ $task->id }}">
            </div>
            <div class="nav-align-top mt-2">
                <ul class="nav nav-tabs" role="tablist">
                    @php
                        $activeTab = '';
                    @endphp
                    @if (Auth::guard('web')->check())
                        <li class="nav-item">
                            <button type="button" class="nav-link {{ empty($activeTab) ? 'active' : '' }}"
                                role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-discussions"
                                aria-controls="navs-top-discussions">
                                <i
                                    class="menu-icon tf-icons bx bxs-chat text-danger"></i>{{ get_label('discussions', 'Discussions') }}
                            </button>
                        </li>
                        @php
                            if (empty($activeTab)) {
                                $activeTab = 'discussions';
                            }
                        @endphp
                    @endif
                  <li class="nav-item">
    <button type="button" class="nav-link {{ empty($activeTab) ? 'active' : '' }}" role="tab"
        data-bs-toggle="tab" data-bs-target="#navs-top-sub-task" aria-controls="navs-top-sub-task">
        <i class="menu-icon tf-icons bx bx-task text-primary"></i>{{ get_label('sub_tasks', 'Sub-Tasks') }}
    </button>
</li>
                
                    @if ($task->project->enable_tasks_time_entries == 1)
                        <li class="nav-item">
                            <button type="button" class="nav-link {{ empty($activeTab) ? 'active' : '' }}"
                                role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-time-entries"
                                aria-controls="navs-top-time-entries">
                                <i
                                    class="menu-icon tf-icons bx bx-time text-info"></i>{{ get_label('time_entries', 'Time Entries') }}
                            </button>
                        </li>
                        @php
                            if (empty($activeTab)) {
                                $activeTab = 'time_entries';
                            }
                        @endphp
                    @endif
                    @if ($auth_user->can('manage_media'))
                        <li class="nav-item">
                            <button type="button" class="nav-link {{ empty($activeTab) ? 'active' : '' }}"
                                role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-media"
                                aria-controls="navs-top-media">
                                <i
                                    class="menu-icon tf-icons bx bx-image-alt text-success"></i>{{ get_label('media', 'Media') }}
                            </button>
                        </li>
                        @php
                            if (empty($activeTab)) {
                                $activeTab = 'media';
                            }
                        @endphp
                    @endif
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ empty($activeTab) ? 'active' : '' }}" role="tab"
                            data-bs-toggle="tab" data-bs-target="#navs-top-status_timeline"
                            aria-controls="navs-top-status_timeline">
                            <i
                                class="menu-icon tf-icons bx bx-align-justify text-dark"></i>{{ get_label('status_timeline', 'Status Timeline') }}
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
                                    class="menu-icon tf-icons bx bx-line-chart text-info"></i>{{ get_label('activity_log', 'Activity log') }}
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


                    <div class="tab-pane fade active show" id="navs-top-discussions" role="tabpanel">
                        <!-- Discussions content -->
                        <x-task-discussions-card :task="$task" />
                    </div>
                    <div class="tab-pane fade" id="navs-top-media" role="tabpanel">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div></div>
                                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#add_media_modal">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="left"
                                        data-bs-original-title="{{ get_label('add_media', 'Add Media') }}">
                                        <i class="bx bx-plus"></i>
                                    </button>
                                </a>
                            </div>
                            @php
                                $visibleColumns = getUserPreferences('task_media');
                            @endphp
                            <div class="table-responsive text-nowrap">
                                <input type="hidden" id="data_type" value="task-media">
                                <input type="hidden" id="data_table" value="task_media_table">
                                <input type="hidden" id="save_column_visibility">
                                <table id="task_media_table" data-toggle="table" data-loading-template="loadingTemplate"
                                    data-url="{{ route('tasks.get_media', ['id' => $task->id]) }}"
                                    data-icons-prefix="bx" data-icons="icons" data-show-refresh="true"
                                    data-total-field="total" data-trim-on-search="false" data-data-field="rows"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                    data-side-pagination="server" data-show-columns="true" data-pagination="true"
                                    data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                    data-query-params="queryParamsTaskMedia">
                                    <thead>
                                        <tr>
                                            <th data-checkbox="true"></th>
                                            <th data-field="id"
                                                data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                                data-sortable="true">{{ get_label('id', 'ID') }}</th>
                                            <th data-field="file"
                                                data-visible="{{ in_array('file', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                                data-sortable="true">{{ get_label('file', 'File') }}</th>
                                            <th data-field="file_name" data-sortable="true"
                                                data-visible="{{ in_array('file_name', $visibleColumns) ? 'true' : 'false' }}">
                                                {{ get_label('file_name', 'File name') }}</th>
                                            <th data-field="file_size"
                                                data-visible="{{ in_array('file_size', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                                data-sortable="true">{{ get_label('file_size', 'File size') }}</th>
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
                    {{-- @dd($task); --}}
                    <div id="navs-top-sub-task" class="tab-pane fade {{ $activeTab == 'sub_task' ? 'active show' : '' }}">
                        <?php
                            $is_favorites = '';
                            $subtasks = $task->subtasks->toArray();
                            $id = isset($project->id) ? 'project_' . $project->id : '';
                            // dd($subtasks);
                            ?>

                    <div class="d-flex justify-content-end">
                        <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#create_task_offcanvas">
                            <button type="button" class="btn btn-sm btn-primary action_create_tasks"
                                data-bs-toggle="tooltip" data-bs-placement="right"
                                data-bs-original-title="<?= get_label('create_task', 'Create task') ?>">
                                <i class="bx bx-plus"></i>
                            </button>
                        </a>
                    </div>
                    <x-tasks-card :tasks="$subtasks" :id="$id" :project="$project" :favorites="$is_favorites" />
                    
                </div>
                    @if ($task->project->enable_tasks_time_entries == 1)
                        <div class="tab-pane fade" id="navs-top-time-entries" role="tabpanel">
                            <x-task-time-entries-card :task="$task" />
                        </div>
                    @endif
                    <div class="tab-pane fade {{ $activeTab == 'status_timeline' ? 'active show' : '' }}"
                        id="navs-top-status_timeline" role="tabpanel">
                        <!-- Status timeline content -->
                        <x-status-timeline :timelines="$task->statusTimelines->sortByDesc('changed_at')" />
                    </div>
                    @if ($auth_user->can('manage_activity_log'))
                        <div class="tab-pane fade" id="navs-top-activity-log" role="tabpanel">
                            <div class="col-12">
                                <div class="row mt-4">
                                    <div class="col-md-4 mb-3">
                                        <div class="input-group input-group-merge">
                                            <input type="text" id="activity_log_between_date" class="form-control"
                                                placeholder="{{ get_label('date_between', 'Date between') }}"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    @if (isAdminOrHasAllDataAccess())
                                        <div class="col-md-4 mb-3">
                                            <select class="form-select" id="user_filter"
                                                aria-label="Default select example">
                                                <option value="">{{ get_label('select_user', 'Select user') }}
                                                </option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">
                                                        {{ $user->first_name . ' ' . $user->last_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <select class="form-select" id="client_filter"
                                                aria-label="Default select example">
                                                <option value="">{{ get_label('select_client', 'Select client') }}
                                                </option>
                                                @foreach ($clients as $client)
                                                    <option value="{{ $client->id }}">
                                                        {{ $client->first_name . ' ' . $client->last_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div class="col-md-4 mb-3">
                                        <select class="form-select" id="activity_filter"
                                            aria-label="Default select example">
                                            <option value="">{{ get_label('select_activity', 'Select activity') }}
                                            </option>
                                            <option value="created">{{ get_label('created', 'Created') }}</option>
                                            <option value="updated">{{ get_label('updated', 'Updated') }}</option>
                                            <option value="duplicated">{{ get_label('duplicated', 'Duplicated') }}
                                            </option>
                                            <option value="uploaded">{{ get_label('uploaded', 'Uploaded') }}</option>
                                            <option value="deleted">{{ get_label('deleted', 'Deleted') }}</option>
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
                                    <input type="hidden" id="type_id" value="{{ $task->id }}">
                                    <input type="hidden" id="save_column_visibility">
                                    <table id="activity_log_table" data-toggle="table"
                                        data-loading-template="loadingTemplate"
                                        data-url="{{ route('activity_log.list') }}" data-icons-prefix="bx"
                                        data-icons="icons" data-show-refresh="true" data-total-field="total"
                                        data-trim-on-search="false" data-data-field="rows"
                                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                        data-side-pagination="server" data-show-columns="true" data-pagination="true"
                                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                        data-query-params="queryParams">
                                        <thead>
                                            <tr>
                                                <th data-checkbox="true"></th>
                                                <th data-field="id"
                                                    data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">{{ get_label('id', 'ID') }}</th>
                                                <th data-field="actor_id"
                                                    data-visible="{{ in_array('actor_id', $visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">{{ get_label('actor_id', 'Actor ID') }}</th>
                                                <th data-field="actor_name"
                                                    data-visible="{{ in_array('actor_name', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">{{ get_label('actor_name', 'Actor name') }}</th>
                                                <th data-field="actor_type"
                                                    data-visible="{{ in_array('actor_type', $visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">{{ get_label('actor_type', 'Actor type') }}</th>
                                                <th data-field="type_id"
                                                    data-visible="{{ in_array('type_id', $visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">{{ get_label('type_id', 'Type ID') }}</th>
                                                <th data-field="parent_type_id"
                                                    data-visible="{{ in_array('parent_type_id', $visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">
                                                    {{ get_label('parent_type_id', 'Parent type ID') }}</th>
                                                <th data-field="activity"
                                                    data-visible="{{ in_array('activity', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">{{ get_label('activity', 'Activity') }}</th>
                                                <th data-field="type"
                                                    data-visible="{{ in_array('type', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">{{ get_label('type', 'Type') }}</th>
                                                <th data-field="parent_type"
                                                    data-visible="{{ in_array('parent_type', $visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">{{ get_label('parent_type', 'Parent type') }}
                                                </th>
                                                <th data-field="type_title"
                                                    data-visible="{{ in_array('type_title', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">{{ get_label('type_title', 'Type title') }}</th>
                                                <th data-field="parent_type_title"
                                                    data-visible="{{ in_array('parent_type_title', $visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">
                                                    {{ get_label('parent_type_title', 'Parent type title') }}</th>
                                                <th data-field="message"
                                                    data-visible="{{ in_array('message', $visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">{{ get_label('message', 'Message') }}</th>
                                                <th data-field="created_at"
                                                    data-visible="{{ in_array('created_at', $visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">{{ get_label('created_at', 'Created at') }}</th>
                                                <th data-field="updated_at"
                                                    data-visible="{{ in_array('updated_at', $visibleColumns) ? 'true' : 'false' }}"
                                                    data-sortable="true">{{ get_label('updated_at', 'Updated at') }}</th>
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
                </div>
            </div>
            <div class="modal fade" id="add_media_modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <form class="modal-content form-horizontal" id="media-upload"
                        action="{{ route('tasks.upload_media') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel1">{{ get_label('add_media', 'Add Media') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-primary alert-dismissible" role="alert">
                                {{ $media_storage_settings['media_storage_type'] == 's3' ? get_label('storage_type_set_as_aws_s3', 'Storage type is set as AWS S3 storage') : get_label('storage_type_set_as_local', 'Storage type is set as local storage') }},
                                <a href="/settings/media-storage"
                                    target="_blank">{{ get_label('click_here_to_change', 'Click here to change.') }}</a>
                            </div>
                            <div class="dropzone dz-clickable" id="media-upload-dropzone">
                            </div>
                            <div class="form-group mt-4 text-center">
                                <button class="btn btn-primary"
                                    id="upload_media_btn">{{ get_label('upload', 'Upload') }}</button>
                            </div>
                            <div class="d-flex justify-content-center">
                                <div class="form-group" id="error_box">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                {{ get_label('close', 'Close') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal fade" id="add_task_time_entries" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <form class="modal-content form-submit-event" id="time-entries"
                        action="{{ route('tasks.time_entries.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="task_id" value="{{ $task->id }}">
                        <input type="hidden" name="dnr">
                        <input type="hidden" name="table" value="task-time-entries">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel1">
                                {{ get_label('add_task_time_entry', 'Add Task Time Entry') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <!-- Entry Date -->
                                <div class="col-md-6">
                                    <label for="entry_date"
                                        class="form-label">{{ get_label('entry_date', 'Entry Date') }}</label>
                                    <span class="asterisk">*</span>
                                    <input type="text" name="entry_date" class="form-control" id="entry_date"
                                        required>
                                    @error('entry_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!-- Entry Type -->
                                <div class="col-md-6">
                                    <label for="entry_type"
                                        class="form-label">{{ get_label('entry_type', 'Entry Type') }}</label>
                                    <span class="asterisk">*</span>
                                    <select name="entry_type" id="entry_type" class="form-select" required>
                                        <option value="standard">{{ get_label('standard', 'Standard') }}</option>
                                        <option value="flexible">{{ get_label('flexible', 'Flexible') }}</option>
                                    </select>
                                </div>
                                <!-- Standard Hours -->
                                <div class="col-md-12" id="standard_hours_div">
                                    <label for="standard_hours"
                                        class="form-label">{{ get_label('standard_hours', 'Standard Hours') }}</label>
                                    <span class="asterisk">*</span>
                                    <input type="time" name="standard_hours" class="form-control" id="standard_hours"
                                        required>
                                    @error('standard_hours')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!-- Start Time -->
                                <div class="col-md-6" id="start_time_div">
                                    <label for="start_time"
                                        class="form-label">{{ get_label('start_time', 'Start Time') }}</label>
                                    <span class="asterisk">*</span>
                                    <input type="time" name="start_time" class="form-control" id="start_time"
                                        required>
                                    @error('start_time')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!-- End Time -->
                                <div class="col-md-6" id="end_time_div">
                                    <label for="end_time"
                                        class="form-label">{{ get_label('end_time', 'End Time') }}</label>
                                    <span class="asterisk">*</span>
                                    <input type="time" name="end_time" class="form-control" id="end_time" required>
                                    @error('end_time')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!-- Billable Checkbox -->
                                <input type="hidden" name="is_billable" value="0">
                                @if ($task->billing_type == 'billable')
                                    <div class="col-md-6">
                                        <label for="is_billable"
                                            class="form-label">{{ get_label('billable', 'Billable') }}</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_billable"
                                                name="is_billable" value="1">
                                            <label class="form-check-label"
                                                for="is_billable">{{ get_label('yes', 'Yes') }}</label>
                                        </div>
                                    </div>
                                @endif
                                <!-- Description -->
                                <div class="col-md-12">
                                    <label for="description"
                                        class="form-label">{{ get_label('description', 'Description') }}</label>
                                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                {{ get_label('close', 'Close') }}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                {{ get_label('save', 'Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            var label_delete = '{{ get_label('delete', 'Delete') }}';
            var task_parent_id = "{{ $task->id }}";
        </script>
        <script src="{{ asset('assets/js/pages/task-information.js') }}"></script>
    @endsection
