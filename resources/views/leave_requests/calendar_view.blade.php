@extends('layout')
@section('title')
    {{ get_label('leave_requests', 'Leave Requests') }} - {{ get_label('calendar_view', 'Calendar View') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home.index') }}">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('leave_requests.index') }}">{{ get_label('leave_requests', 'Leave Requests') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ get_label('calendar_view', 'Calendar View') }}
                    </li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('leave_requests.index') }}" data-bs-toggle="tooltip" data-bs-placement="left"
                    data-bs-original-title="{{ get_label('list_view', 'List View') }}">
                    <button type="button" class="btn btn-sm btn-primary">
                        <i class='bx bx-list-ul'></i>
                    </button>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 col-lg-2">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <!-- Create Leave Request Button -->
                        <div class="mb-4 border-bottom pb-2">
                            <h5 class="fw-bold mb-0">
                                <i class="bx bx-calendar me-2"></i>
                                 {{ get_label('leave_calendar', 'Leave Calendar') }}
                            </h5>
                        </div>

                        <!-- Leave Request Statistics -->
                        <div class="mb-4">
                            <h6 class="d-flex align-items-center fw-bold mb-3">
                                <i class="bx bx-stats me-2"></i>
                                {{ get_label('statistics', 'Statistics') }}
                            </h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <span>{{ get_label('total', 'Total') }}</span>
                                    <span id="total-leave-count" class="badge bg-primary rounded-pill">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <span>{{ get_label('pending', 'Pending') }}</span>
                                    <span id="pending-count" class="badge bg-warning rounded-pill">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <span>{{ get_label('approved', 'Approved') }}</span>
                                    <span id="approved-count" class="badge bg-success rounded-pill">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <span>{{ get_label('rejected', 'Rejected') }}</span>
                                    <span id="rejected-count" class="badge bg-danger rounded-pill">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <span>{{ get_label('full', 'Full') }}</span>
                                    <span id="full-count" class="badge bg-primary rounded-pill">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <span>{{ get_label('partial', 'Partial') }}</span>
                                    <span id="partial-count" class="badge bg-info rounded-pill">0</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Status Filters -->
                        <div class="mb-4">
                            <h6 class="d-flex align-items-center fw-bold mb-3">
                                <i class="bx bx-filter-alt me-2"></i>
                                {{ get_label('status', 'Status') }}
                            </h6>
                            <div id="status_filters">
                                <div class="form-check form-check-warning">
                                    <input class="form-check-input status-filter checkbox-warning" type="checkbox"
                                        value="pending" id="status_pending">
                                    <label class="form-check-label d-flex align-items-center" for="status_pending">
                                        <span>{{ get_label('pending', 'Pending') }}</span>
                                    </label>
                                </div>

                                <div class="form-check form-check-success">
                                    <input class="form-check-input status-filter checkbox-success" type="checkbox"
                                        value="approved" id="status_approved">
                                    <label class="form-check-label d-flex align-items-center" for="status_approved">
                                        <span>{{ get_label('approved', 'Approved') }}</span>
                                    </label>
                                </div>

                                <div class="form-check form-check-danger">
                                    <input class="form-check-input status-filter checkbox-danger" type="checkbox"
                                        value="rejected" id="status_rejected">
                                    <label class="form-check-label d-flex align-items-center" for="status_rejected">
                                        <span>{{ get_label('rejected', 'Rejected') }}</span>
                                    </label>
                                </div>

                            </div>
                        </div>

                        <!-- Type Filters -->
                        <div class="mb-4">
                            <h6 class="d-flex align-items-center fw-bold">
                                <i class="bx bx-filter-alt me-2"></i>
                                {{ get_label('type', 'Type') }}
                            </h6>
                            <div id="type_filters">
                                <div class="form-check">
                                    <input class="form-check-input type-filter" type="checkbox" value="full"
                                        id="type_full">
                                    <label class="form-check-label d-flex align-items-center" for="type_full">
                                        <span>{{ get_label('full', 'Full') }}</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input type-filter" type="checkbox" value="partial"
                                        id="type_partial">
                                    <label class="form-check-label d-flex align-items-center" for="type_partial">
                                        <span>{{ get_label('partial', 'Partial') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9 col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div id="leaveRequestCalenderDiv"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
