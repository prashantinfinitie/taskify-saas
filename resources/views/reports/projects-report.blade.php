@extends('layout')
@section('title')
    {{ get_label('projects_report', 'Projects Report') }} - {{ get_label('reports', 'Reports') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}">{{ get_label('home', 'Home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#">{{ get_label('reports', 'Reports') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            <a
                                href="{{ route('reports.projects-report') }}">{{ get_label('projects_report', 'Projects Report') }}</a>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Summary Cards -->
        <div class="d-flex mb-4 flex-wrap gap-3">
            <div class="card flex-grow-1 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bx bx-layer fs-2 text-primary me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ get_label('total_projects', 'Total Projects') }}</h6>
                        <p class="card-text mb-0" id="total-projects">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="card flex-grow-1 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bx bx-task fs-2 text-success me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ get_label('total_tasks', 'Total Tasks') }}</h6>
                        <p class="card-text mb-0" id="total-tasks">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="card flex-grow-1 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bx bx-group fs-2 text-warning me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ get_label('total_team_members', 'Total Team Members') }}</h6>
                        <p class="card-text mb-0" id="total-team-members">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="card flex-grow-1 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bx bx-time-five fs-2 text-danger me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">
                            {{ get_label('average_overdue_days_per_project', 'Avg. Overdue Days/Project') }}</h6>
                        <p class="card-text mb-0" id="average-overdue-days-per-project">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="card flex-grow-1 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bx bx-calendar-exclamation fs-2 text-info me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ get_label('overdue_projects_percentage', 'Overdue Projects (%)') }}
                        </h6>
                        <p class="card-text mb-0" id="overdue-projects-percentage">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="card flex-grow-1 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bx bx-calendar fs-2 text-dark me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ get_label('total_overdue_days', 'Total Overdue Days') }}</h6>
                        <p class="card-text mb-0" id="total-overdue-days">Loading...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Filters Row -->
                <div class="row mb-3">
                    <!-- Project Filter -->
                    <div class="col-md-4 col-lg-3 mb-md-0 mb-2">
                        <select id="filter_project" class="form-control">

                        </select>
                    </div>
                    <!-- User Filter -->
                    <div class="col-md-4 col-lg-3 mb-md-0 mb-2">
                        <select id="filter_user" class="form-control">

                        </select>
                    </div>
                    <!-- Date Range Filter -->
                    <div class="col-md-4 col-lg-6 mb-md-0 mb-2">
                        <input type="text" id="filter_date_range" class="form-control"
                            placeholder="{{ get_label('select_date_range', 'Select Date Range') }}">
                    </div>
                </div>
                <!-- Additional Filters Row -->
                <div class="row mb-3">
                    <!-- Client Filter -->
                    <div class="col-md-4 col-lg-3 mb-md-0 mb-2">
                        <select id="filter_client" class="form-control">

                        </select>
                    </div>
                    <!-- Status Filter -->
                    {{-- @dd($statuses) --}}
                    <div class="col-md-4 col-lg-3 mb-md-0 mb-2">
                        <select id="filter_status" class="form-control">



                        </select>
                    </div>
                    <!-- Export Button -->
                    <div class="col-md-4 col-lg-6 d-flex align-items-center justify-content-md-end mb-md-0 mb-2">
                        <button id="export_button" class="btn btn-primary">{{ get_label('export', 'Export') }}</button>
                    </div>
                </div>
                <!-- Table -->
                <div class="table-responsive text-nowrap">
                    <table id="projects_report_table" data-toggle="table"
                        data-url="{{ route('reports.project-report-data') }}" data-loading-template="loadingTemplate"
                        data-icons-prefix="bx" data-icons="icons" data-show-refresh="true" data-total-field="total"
                        data-trim-on-search="false" data-data-field="projects" data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-search="true" data-side-pagination="server" data-show-columns="true" data-pagination="true"
                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                        data-query-params="project_report_query_params">
                        <thead>
                            <tr>
                                <th rowspan="2" data-field="id" data-sortable="true">{{ get_label('id', 'ID') }}</th>
                                <th rowspan="2" data-field="title" data-sortable="true">{{ get_label('title', 'Title') }}</th>
                                <th colspan="2">{{ get_label('dates', 'Dates') }}</th>
                                <th rowspan="2" data-field="status" data-sortable="true">{{ get_label('status', 'Status') }}</th>
                                <th rowspan="2" data-field="priority" data-sortable="true">{{ get_label('priority', 'Priority') }}</th>
                                <th rowspan="2" data-field="budget.total" data-sortable="true">{{ get_label('budget', 'Budget') }}</th>
                                <th colspan="3">{{ get_label('time', 'Time') }}</th>
                                <th colspan="4">{{ get_label('tasks', 'Tasks') }}</th>
                                <th colspan="2">{{ get_label('team', 'Team') }}</th>
                                <!-- Group Clients and Total Clients under one header -->
                                <th colspan="2">{{ get_label('clients', 'Clients') }}</th>
                            </tr>
                            <tr>
                                <th data-field="start_date" data-sortable="true">{{ get_label('start_date', 'Start Date') }}</th>
                                <th data-field="end_date" data-sortable="true">{{ get_label('end_date', 'End Date') }}</th>
                                <th data-field="time.total_days" data-sortable="true">{{ get_label('total_days', 'Total Days') }}</th>
                                <th data-field="time.days_elapsed" data-sortable="true">{{ get_label('days_elapsed', 'Days Elapsed') }}</th>
                                <th data-field="time.days_remaining" data-sortable="true">{{ get_label('days_remaining', 'Days Remaining') }}</th>
                                <th data-field="tasks.total" data-sortable="true">{{ get_label('total_tasks', 'Total') }}</th>
                                <th data-field="tasks.due" data-sortable="true">{{ get_label('due_tasks', 'Due Tasks') }}</th>
                                <th data-field="tasks.overdue" data-sortable="true">{{ get_label('overdue_tasks', 'Overdue Tasks') }}</th>
                                <th data-field="tasks.overdue_days" data-sortable="true">{{ get_label('overdue_days', 'Overdue Days') }}</th>
                                <th data-field="team.total_members" data-sortable="true">{{ get_label('team_members', 'Team Members') }}</th>
                                <th data-field="users" data-formatter="UserFormatter">{{ get_label('users', 'Users') }}</th>
                                <!-- Separate Clients and Total Clients under their new group -->
                                <th data-field="clients" data-formatter="ClientFormatter">{{ get_label('clients', 'Clients') }}</th>
                                <th data-field="total_clients" >{{ get_label('total_clients', 'Total Clients') }}</th>
                            </tr>
                        </thead>

                    </table>
                </div>
            </div>
        </div>

    </div>
    <script>
        var projects_report_export_url = "{{ route('reports.export-projects-report') }}";
    </script>
    <script src="{{ asset('assets/js/pages/projects-report.js') }}"></script>
@endsection
