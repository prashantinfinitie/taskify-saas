@extends('layout')
@section('title')
{{ get_label('work_hours_report', 'Work Hours Report') }} - {{ get_label('reports', 'Reports') }}
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
                        {{ get_label('work_hours_report', 'Work Hours Report') }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Summary Cards -->
    <div class="d-flex mb-4 flex-wrap gap-3">
        <div class="card flex-grow-1 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bx bxs-time fs-2 text-primary me-3"></i>
                <div>
                    <h6 class="card-title mb-1">{{ get_label('total_hours', 'Total Hours') }}</h6>
                    <p class="card-text mb-0" id="total-hours">Loading...</p>
                </div>
            </div>
        </div>
        <div class="card flex-grow-1 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bx bx-money fs-2 text-success me-3"></i>
                <div>
                    <h6 class="card-title mb-1">{{ get_label('billable_hours', 'Billable Hours') }}</h6>
                    <p class="card-text mb-0" id="billable-hours">Loading...</p>
                </div>
            </div>
        </div>
        <div class="card flex-grow-1 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bx bx-money fs-2 text-danger me-3"></i>
                <div>
                    <h6 class="card-title mb-1">{{ get_label('non_billable_hours', 'Non Billable Hours') }}</h6>
                    <p class="card-text mb-0" id="non-billable-hours">Loading...</p>
                </div>
            </div>
        </div>
        <div class="card flex-grow-1 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bx bx-task fs-2 text-primary me-3"></i>
                <div>
                    <h6 class="card-title mb-1">{{ get_label('total_tasks', 'Total Tasks') }}</h6>
                    <p class="card-text mb-0" id="total-tasks">Loading...</p>
                </div>
            </div>
        </div>
        <div class="card flex-grow-1 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bx bx-briefcase-alt-2 fs-2 text-success me-3"></i>
                <div>
                    <h6 class="card-title mb-1">{{ get_label('total_projects', 'Total Projects') }}</h6>
                    <p class="card-text mb-0" id="total-projects">Loading...</p>
                </div>
            </div>
        </div>
        <div class="card flex-grow-1 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <i class="bx bx-user fs-2 text-warning me-3"></i>
                <div>
                    <h6 class="card-title mb-1">{{ get_label('total_users', 'Total Users') }}</h6>
                    <p class="card-text mb-0" id="total-users">Loading...</p>
                </div>
            </div>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <!-- Project Filter -->
                <div class="col-md-4 col-lg-3 mb-md-0 mb-2">
                    <select id="filter_project" class="form-control" multiple>

                    </select>
                </div>
                <!-- User Filter -->
                <div class="col-md-4 col-lg-3 mb-md-0 mb-2">
                    <select id="filter_user" class="form-control" multiple>

                    </select>
                </div>
                <!-- Date Range Filter -->
                <div class="col-md-4 col-lg-6 mb-md-0 mb-2">
                    <input type="text" id="filter_date_range" class="form-control" placeholder="{{ get_label('select_date_range', 'Select Date Range') }}">
                </div>

            </div>
            <!-- Additional Filters Row --> 
            <div class="row mb-3">

                <!-- Export Button -->
                <div class="d-flex align-items-center justify-content-md-end mb-md-0 mb-2">
                    <button id="export_button" class="btn btn-primary">{{ get_label('export', 'Export') }}</button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive text-nowrap">
                <table id="work_hours_report_table" class="table-striped table-bordered table" data-toggle="table" data-url="{{ route('reports.work-hours-report-data') }}" data-loading-template="loadingTemplate" data-icons-prefix="bx" data-icons="icons" data-show-refresh="true" data-total-field="total" data-trim-on-search="false" data-data-field="report" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-side-pagination="server" data-show-columns="true" data-pagination="true" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-query-params="work_hours_report_query_params">
                    <thead>
                        <tr>
                            <th rowspan="2" data-field="id" scope="col">{{ get_label('id', 'ID') }}</th>
                            <th rowspan="2" data-field="date" scope="col">{{ get_label('date', 'Date') }}</th>
                            <th rowspan="2" data-field="project.title" scope="col">
                                {{ get_label('project', 'Project') }}</th>
                            <th rowspan="2" data-field="task.title" scope="col">{{ get_label('task', 'Task') }}
                            </th>
                            <th rowspan="2" data-field="task.status" data-formatter="StatusFormatter" scope="col">{{ get_label('status', 'Status') }}</th>
                            <th rowspan="2" data-field="user.name" scope="col">{{ get_label('user', 'User') }}
                            </th>
                            <th colspan="3" scope="col">{{ get_label('time_entries', 'Time Entries') }}</th>
                            <th colspan="2" scope="col">{{ get_label('time', 'Time') }}</th>

                        </tr>
                        <tr>
                            <th data-field="time_entry.type" scope="col">{{ get_label('entry_type', 'Entry Type') }}</th>
                            <th data-field="time_entry.start_time" scope="col">{{ get_label('start_time', 'Start Time') }}</th>
                            <th data-field="time_entry.end_time" scope="col">{{ get_label('end_time', 'End Time') }}</th>
                            <th data-field="time_entry.hours" scope="col">{{ get_label('total', 'Total') }}</th>
                            <th data-field="hours.is_billable" scope="col">{{ get_label('is_billable', 'Is Billable') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>
</div>
<script>
    var work_hours_report_export_url = "{{ route('reports.export-work-hours-report') }}";


</script>
<script src="{{ asset('assets/js/pages/work-hours-report.js') }}"></script>
@endsection

