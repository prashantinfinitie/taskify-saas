@extends('layout')
@section('title')
    {{ get_label('leaves_report', 'Leaves Report') }} - {{ get_label('reports', 'Reports') }}
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
                                href="{{ route('reports.leaves-report') }}">{{ get_label('leaves_report', 'Leaves Report') }}</a>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Summary Cards -->
        <div class="d-flex mb-4 flex-wrap gap-3">
            <!-- Total Leaves Tile -->
            <div class="card flex-grow-1 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bx bx-calendar fs-2 text-success me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ get_label('total', 'Total') }}</h6>
                        <p class="card-text mb-0" id="total-leaves">{{ get_label('loading', 'Loading...') }}</p>
                    </div>
                </div>
            </div>

            <!-- Full Leaves Tile -->
            <div class="card flex-grow-1 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bx bx-check-circle fs-2 text-danger me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ get_label('full', 'Full') }}</h6>
                        <p class="card-text mb-0" id="full-leaves">{{ get_label('loading', 'Loading...') }}</p>
                    </div>
                </div>
            </div>

            <!-- Partial Leaves Tile -->
            <div class="card flex-grow-1 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bx bx-minus-circle fs-2 text-primary me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ get_label('partial', 'Partial') }}</h6>
                        <p class="card-text mb-0" id="partial-leaves">{{ get_label('loading', 'Loading...') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approved, Pending, and Rejected Leaves Tiles (Moved Below) -->
        <div class="d-flex mb-4 flex-wrap gap-3">
            <div class="card flex-grow-1 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bx bx-check-circle fs-2 text-warning me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ get_label('approved', 'Approved') }}</h6>
                        <p class="card-text mb-0" id="approved-leaves">{{ get_label('loading', 'Loading...') }}</p>
                    </div>
                </div>
            </div>

            <div class="card flex-grow-1 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bx bx-time fs-2 text-info me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ get_label('pending', 'Pending') }}</h6>
                        <p class="card-text mb-0" id="pending-leaves">{{ get_label('loading', 'Loading...') }}</p>
                    </div>
                </div>
            </div>

            <div class="card flex-grow-1 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <i class="bx bx-x-circle fs-2 text-danger me-3"></i>
                    <div>
                        <h6 class="card-title mb-1">{{ get_label('rejected', 'Rejected') }}</h6>
                        <p class="card-text mb-0" id="rejected-leaves">{{ get_label('loading', 'Loading...') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Filters Row -->
                <div class="row">
                    <!-- Date Range Filter -->
                    <div class="col-md-4 mb-3">
                        <input type="text" id="filter_date_range" class="form-control"
                            placeholder="{{get_label('date_between', 'Date Between')}}" autocomplete="off">
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="input-group input-group-merge">
                            <input type="text" id="report_start_date_between" class="form-control"
                                placeholder="{{get_label('from_date_between', 'From date between')}}" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="input-group input-group-merge">
                            <input type="text" id="report_end_date_between" class="form-control"
                                placeholder="{{get_label('to_date_between', 'To date between')}}" autocomplete="off">
                        </div>
                    </div>
                    <!-- User Filter -->
                    <div class="col-md-4 mb-3">
                        <select class="form-control" id="user_filter" multiple="multiple"
                            data-placeholder="{{get_label('select_users', 'Select Users')}}">
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-4 mb-3">
                        <select class="form-select js-example-basic-multiple" id="status_filter"
                            aria-label="Default select example"
                            data-placeholder="{{get_label('select_statuses', 'Select statuses')}}"
                            data-allow-clear="true" multiple>
                            <option value="approved">{{ get_label('approved', 'Approved') }}</option>
                            <option value="pending">{{ get_label('pending', 'Pending') }}</option>
                            <option value="rejected">{{ get_label('rejected', 'Rejected') }}</option>
                        </select>
                    </div>
                </div>
                <input type="hidden" id="filter_date_range_from">
                <input type="hidden" id="filter_date_range_to">
                <input type="hidden" id="filter_start_date_from">
                <input type="hidden" id="filter_start_date_to">
                <input type="hidden" id="filter_end_date_from">
                <input type="hidden" id="filter_end_date_to">
                <!-- Additional Filters Row -->
                <div class="row mb-2">
                    <!-- Export Button -->
                    <div class="col-md-12 col-lg-12 d-flex align-items-center justify-content-md-end mb-md-0 mb-2">
                        <button class="btn btn-primary" id="export_button" data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            data-bs-original-title="{{ get_label('export_leaves_report', 'Export Leaves Report') }}">
                            <i class="bx bx-export"></i>
                        </button>
                    </div>
                </div>
                @php
                    $visibleColumns = getUserPreferences('leaves_report');
                @endphp
                <!-- Table -->
                <div class="table-responsive text-nowrap">
                    <input type="hidden" id="multi_select">
                    <input type="hidden" id="data_type" value="report">
                    <input type="hidden" id="save_column_visibility" data-type="leaves_report"
                        data-table="leaves_report_table">
                    <table id="leaves_report_table" data-toggle="table"
                        data-url="{{ route('reports.leaves-report-data') }}" data-loading-template="loadingTemplate"
                        data-icons-prefix="bx" data-icons="icons" data-show-refresh="true" data-total-field="total"
                        data-trim-on-search="false" data-data-field="users" data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-search="true" data-side-pagination="server" data-show-columns="true" data-pagination="true"
                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                        data-query-params="leaves_report_query_params">
                        <thead>
                            <tr>
                                <th data-field="id"
                                    data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                    data-sortable="true">{{ get_label('id', 'ID') }}</th>
                                <th data-field="user_name"
                                    data-visible="{{ in_array('user_name', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                    data-sortable="true">{{ get_label('user', 'User') }}</th>
                                <th data-field="total_leaves"
                                    data-visible="{{ in_array('total_leaves', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                    data-formatter="formatTotalLeaves" data-sortable="true">
                                    {{ get_label('total', 'Total') }}</th>
                                <th data-field="full_leaves"
                                    data-visible="{{ in_array('full_leaves', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                    data-sortable="true">{{ get_label('full', 'Full') }}</th>
                                <th data-field="partial_leaves"
                                    data-visible="{{ in_array('partial_leaves', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                    data-formatter="formatPartialLeaves" data-sortable="true">
                                    {{ get_label('partial', 'Partial') }}</th>
                                <th data-field="approved_leaves"
                                    data-visible="{{ in_array('approved_leaves', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                    data-formatter="formatApprovedLeaves" data-sortable="true">
                                    {{ get_label('approved', 'Approved') }}</th>
                                <th data-field="pending_leaves"
                                    data-visible="{{ in_array('pending_leaves', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                    data-formatter="formatPendingLeaves" data-sortable="true">
                                    {{ get_label('pending', 'Pending') }}</th>
                                <th data-field="rejected_leaves"
                                    data-visible="{{ in_array('rejected_leaves', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                    data-formatter="formatRejectedLeaves" data-sortable="true">
                                    {{ get_label('rejected', 'Rejected') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        var leaves_report_export_url = "{{ route('reports.export-leaves-report') }}";
    </script>
    <script src="{{ asset('assets/js/pages/leaves-report.js') }}"></script>
@endsection
