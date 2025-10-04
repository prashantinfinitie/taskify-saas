@extends('layout')

@section('title')
    {{ get_label('tasks', 'Tasks') }} - {{ get_label('calendar_view', 'Calendar View') }}
@endsection

@section('content')
    <div class="container-fluid py-4">
       <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item"><?= get_label('tasks', 'Tasks') ?></li>
                        <li class="breadcrumb-item active"><?= get_label('calendar_view', 'Calendar View') ?></li>
                    </ol>
                </nav>
            </div>
            <div>
                @php
                    $taskDefaultView = getUserPreferences('tasks', 'default_view');
                @endphp
                @if ($taskDefaultView === 'tasks/calendar-view')
                    <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
                @else
                    <a href="javascript:void(0);">
                        <span class="badge bg-secondary" id="set-default-view" data-type="tasks" data-view="calendar-view">
                            <?= get_label('set_as_default_view', 'Set as Default View') ?>
                        </span>
                    </a>
                @endif
            </div>
            @include('partials.tasks-views-buttons')
        </div>

        <div class="row g-4">
            <div class="col-md-3 col-lg-2">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <div class="mb-4 border-bottom pb-2">
                            <h5 class="fw-bold mb-0 d-flex align-items-center">
                                <i class="bx bx-calendar fs-4 me-2"></i>
                                {{ get_label('task_calendar', 'Task Calendar') }}
                            </h5>
                        </div>

                        
                        <div class="mb-4">
                            <button type="button"
                                class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2 action_create_tasks"
                                data-bs-toggle="offcanvas" data-bs-target="#create_task_offcanvas"
                                title="<?= get_label('create_task', 'Create task') ?>">
                                <i class="bx bx-plus fs-5"></i>
                                <span>{{ get_label('create_tasks', 'Create Tasks') }}</span>
                            </button>
                        </div>

                        <!-- Task Status Filters -->
                        <div class="mb-4">
                            <h6 class="fw-bold d-flex align-items-center mb-3">
                                <i class="bx bx-filter-alt fs-5 me-2"></i>
                                {{ get_label('status', 'Status') }}
                            </h6>
                            <div id="status_filters">
                                @foreach ($statuses as $status)
                                    <div class="form-check mb-3">
                                        <input class="form-check-input status-filter checkbox-{{ $status->color }}"
                                            type="checkbox" value="{{ $status->id }}" id="status_{{ $status->id }}"
                                            aria-label="Filter by status {{ $status->title }}">
                                        <label class="form-check-label d-flex align-items-center justify-content-between"
                                            for="status_{{ $status->id }}">
                                            <span>{{ $status->title }}</span>
                                            <span class="badge bg-light text-muted ms-2"
                                                data-status-id="{{ $status->id }}"></span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Priority Filters -->
                        <div class="mb-4">
                            <h6 class="fw-bold d-flex align-items-center mb-3">
                                <i class="bx bx-star fs-5 me-2"></i>
                                {{ get_label('priority', 'Priority') }}
                            </h6>
                            <div id="priority_filters">
                                @foreach ($priorities as $priority)
                                    <div class="form-check mb-3">
                                        <input class="form-check-input priority-filter checkbox-{{ $priority->color }}"
                                            type="checkbox" value="{{ $priority->id }}" id="priority_{{ $priority->id }}"
                                            aria-label="Filter by priority {{ $priority->title }}">
                                        <label class="form-check-label d-flex align-items-center justify-content-between"
                                            for="priority_{{ $priority->id }}">
                                            <span>{{ $priority->title }}</span>
                                            <span class="badge bg-light text-muted ms-2"
                                                data-priority-id="{{ $priority->id }}"></span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="col-md-9 col-lg-10">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <div id="taskCalenderDiv"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection
