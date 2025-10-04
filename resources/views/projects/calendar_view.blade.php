@extends('layout')
@section('title')
    {{ get_label('projects', 'Projects') }} - {{ get_label('calendar_view', 'Calendar View') }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}">{{ get_label('home', 'Home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('projects.index') }}">{{ get_label('projects', 'projects') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('calendar_view', 'Calendar View') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a
                    href="{{ url(request()->has('status') ? route('projects.index', ['status' => request()->status]) : route('projects.index')) }}">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('grid_view', 'Grid view') ?>">
                        <i class='bx bxs-grid-alt'></i>
                    </button>
                </a>
                <a href="{{ route('projects.kanban_view', ['status' => request()->status, 'sort' => request()->sort]) }}">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('kanban_view', 'Kanban View') ?>">
                        <i class='bx bxs-dashboard'></i>
                    </button>
                </a>
                <a href="{{ route('projects.gantt_chart') }}">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('gantt_chart_view', 'Gantt Chart View') ?>">
                        <i class='bx bxs-collection'></i>
                    </button>
                </a>
                <a href="{{ route('projects.index') }}">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="{{ get_label('list_view', 'List view') }}">
                        <i class='bx bx-list-ul'></i>
                    </button>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-4 border-bottom pb-2">
                            <h5 class="fw-bold mb-0">
                                <i class="bx bx-calendar me-2 text-primary"></i>
                                {{ get_label('project_calendar', 'Project Calendar') }}
                            </h5>
                        </div>
                        <div class="mb-4">
                            <a href="javascript:void(0);" data-bs-toggle="offcanvas"
                                data-bs-target="#create_project_offcanvas">
                                <button type="button"
                                    class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2"
                                    data-bs-placement="left" title="{{ get_label('create_project', 'Create project') }}">
                                    <i class='bx bx-plus'></i>
                                    <span>{{ get_label('create_project', 'Create Project') }}</span>
                                </button>
                            </a>
                        </div>

                        <!-- Project Status Filters -->
                        <div class="mb-4">
                            <h6 class="fw-bold"> <i class="bx bx-filter-alt me-1"></i>{{ get_label('status', 'Status') }}
                            </h6>
                            <div id="status_filters">
                                @foreach ($statuses as $status)
                                    <div class="form-check mb-3">
                                        <input class="form-check-input status-filter checkbox-{{ $status->color }}"
                                            type="checkbox" value="{{ $status->id }}" id="status_{{ $status->id }}">
                                        <label class="form-check-label d-flex align-items-center"
                                            for="status_{{ $status->id }}">
                                            <span>{{ $status->title }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold"> <i class="bx bx-star me-1"></i>{{ get_label('priority', 'Priority') }}
                            </h6>
                            <div id="priority_filters">
                                @foreach ($priorities as $priority)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input priority-filter checkbox-{{ $priority->color }}"
                                            type="checkbox" value="{{ $priority->id }}" id="priority_{{ $priority->id }}">
                                        <label class="form-check-label d-flex align-items-center"
                                            for="priority_{{ $priority->id }}">
                                            <span>{{ $priority->title }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Calendar Content -->
            <div class="col-md-10">
                <div class="card">
                    <div class="card-body">
                        <div id="projectsCalenderDiv"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
