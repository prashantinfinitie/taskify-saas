@extends('layout')
@section('title')
    {{ get_label('activity_log', 'Activity Log') }} - {{ get_label('calendar_view', 'Calendar View') }}
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
                        <a href="{{ route('activity_log.index') }}">{{ get_label('activity_log', 'Activity Log') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ get_label('calendar_view', 'Calendar View') }}
                    </li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('activity_log.index') }}" data-bs-toggle="tooltip" data-bs-placement="left"
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
                        <!-- Activity Log Statistics -->
                        <div class="mb-4">
                            <div class="mb-4 border-bottom pb-2">
                                <h5 class="fw-bold mb-0">
                                    <i class="bx bx-calendar me-2"></i>
                                    {{ get_label('activity_calendar', 'Activity Calendar') }}
                                </h5>
                            </div>
                            <h6 class="d-flex fw-bold align-items-center mb-3">
                                <i class="bx bx-stats me-2"></i>
                                {{ get_label('statistics', 'Statistics') }}
                            </h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <span>{{ get_label('total', 'Total') }}</span>
                                    <span id="total-count" class="badge bg-primary rounded-pill">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <span>{{ get_label('created', 'Created') }}</span>
                                    <span id="created-count" class="badge rounded-pill bg-success">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <span>{{ get_label('updated', 'Updated') }}</span>
                                    <span id="updated-count" class="badge rounded-pill bg-warning">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <span>{{ get_label('deleted', 'Deleted') }}</span>
                                    <span id="deleted-count" class="badge rounded-pill bg-danger">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <span>{{ get_label('duplicated', 'Duplicated') }}</span>
                                    <span id="duplicated-count" class="badge rounded-pill bg-info">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <span>{{ get_label('uploaded', 'Uploaded') }}</span>
                                    <span id="uploaded-count" class="badge rounded-pill bg-primary">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <span>{{ get_label('updated_status', 'Updated Status') }}</span>
                                    <span id="updated_status-count" class="badge rounded-pill bg-info">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <span>{{ get_label('updated_priority', 'Updated Priority') }}</span>
                                    <span id="updated_priority-count" class="badge rounded-pill bg-info">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <span>{{ get_label('signed', 'Signed') }}</span>
                                    <span id="signed-count" class="badge rounded-pill bg-secondary">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <span>{{ get_label('unsigned', 'Unsigned') }}</span>
                                    <span id="unsigned-count" class="badge rounded-pill bg-dark">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <span>{{ get_label('stopped', 'Stopped') }}</span>
                                    <span id="stopped-count" class="badge rounded-pill bg-info">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <span>{{ get_label('started', 'Started') }}</span>
                                    <span id="started-count" class="badge rounded-pill bg-info">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                    <span>{{ get_label('paused', 'Paused') }}</span>
                                    <span id="paused-count" class="badge rounded-pill bg-info">0</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Activity Filters -->
                        <div class="mb-4">
                            <h6 class="d-flex fw-bold align-items-center mb-3">
                                <i class="bx bx-filter-alt me-2"></i>
                                {{ get_label('activity_filters', 'Activity Filters') }}
                            </h6>
                            <div id="activity_filters">
                                <div class="form-check form-check-created mb-3">
                                    <input class="form-check-input activity-filter checkbox-success" type="checkbox"
                                        value="Created" id="activity_created">
                                    <label class="form-check-label d-flex align-items-center" for="activity_created">
                                        <span>{{ get_label('created', 'Created') }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-updated mb-3">
                                    <input class="form-check-input activity-filter checkbox-warning" type="checkbox"
                                        value="Updated" id="activity_updated">
                                    <label class="form-check-label d-flex align-items-center" for="activity_updated">
                                        <span>{{ get_label('updated', 'Updated') }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-deleted mb-3">
                                    <input class="form-check-input activity-filter checkbox-danger" type="checkbox"
                                        value="Deleted" id="activity_deleted">
                                    <label class="form-check-label d-flex align-items-center" for="activity_deleted">
                                        <span>{{ get_label('deleted', 'Deleted') }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-duplicated mb-3">
                                    <input class="form-check-input activity-filter checkbox-info" type="checkbox"
                                        value="Duplicated" id="activity_duplicated">
                                    <label class="form-check-label d-flex align-items-center" for="activity_duplicated">
                                        <span>{{ get_label('duplicated', 'Duplicated') }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-uploaded mb-3">
                                    <input class="form-check-input activity-filter" type="checkbox" value="Uploaded"
                                        id="activity_uploaded">
                                    <label class="form-check-label d-flex align-items-center" for="activity_uploaded">
                                        <span>{{ get_label('uploaded', 'Uploaded') }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-updated_status mb-3">
                                    <input class="form-check-input activity-filter checkbox-info" type="checkbox"
                                        value="Updated status" id="activity_updated_status">
                                    <label class="form-check-label d-flex align-items-center"
                                        for="activity_updated_status">
                                        <span>{{ get_label('updated_status', 'Updated Status') }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-updated_priority mb-3">
                                    <input class="form-check-input activity-filter checkbox-info" type="checkbox"
                                        value="Updated priority" id="activity_updated_priority">
                                    <label class="form-check-label d-flex align-items-center"
                                        for="activity_updated_priority">
                                        <span>{{ get_label('updated_priority', 'Updated Priority') }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-signed mb-3">
                                    <input class="form-check-input activity-filter checkbox-gray" type="checkbox"
                                        value="Signed" id="activity_signed">
                                    <label class="form-check-label d-flex align-items-center" for="activity_signed">
                                        <span>{{ get_label('signed', 'Signed') }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-unsigned mb-3">
                                    <input class="form-check-input activity-filter checkbox-black" type="checkbox"
                                        value="Unsigned" id="activity_unsigned">
                                    <label class="form-check-label d-flex align-items-center" for="activity_unsigned">
                                        <span>{{ get_label('unsigned', 'Unsigned') }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-stopped mb-3">
                                    <input class="form-check-input activity-filter checkbox-info" type="checkbox"
                                        value="Stopped" id="activity_stopped">
                                    <label class="form-check-label d-flex align-items-center" for="activity_stopped">
                                        <span>{{ get_label('stopped', 'Stopped') }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-started mb-3">
                                    <input class="form-check-input activity-filter checkbox-info" type="checkbox"
                                        value="Started" id="activity_started">
                                    <label class="form-check-label d-flex align-items-center" for="activity_started">
                                        <span>{{ get_label('started', 'Started') }}</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-paused mb-3">
                                    <input class="form-check-input activity-filter checkbox-info" type="checkbox"
                                        value="Paused" id="activity_paused">
                                    <label class="form-check-label d-flex align-items-center" for="activity_paused">
                                        <span>{{ get_label('paused', 'Paused') }}</span>
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
                        <div id="activityLogCalendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
