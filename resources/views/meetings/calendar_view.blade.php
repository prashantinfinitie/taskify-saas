@extends('layout')
@section('title')
    {{ get_label('meetings', 'Meetings') }} - {{ get_label('calendar_view', 'Calendar View') }}
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
                            <a href="{{ route('meetings.index') }}">{{ get_label('meetings', 'Meetings') }}</a>

                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('calendar_view', 'Calendar View') }}
                        </li>
                    </ol>
                </nav>

            </div>
            <div>
                @php
                    $meetingsDefaultView = getUserPreferences('meetings', 'default_view');
                @endphp
                @if ($meetingsDefaultView === 'calendar')
                    <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
                @else
                    <a href="javascript:void(0);"><span class="badge bg-secondary" id="set-default-view"
                            data-type="meetings"
                            data-view="calendar"><?= get_label('set_as_default_view', 'Set as Default View') ?></span></a>
                @endif
            </div>

            <div>
                {{-- <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#createMeetingModal"><button type="button" class="btn btn-sm btn-primary action_create_meetings" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title="<?= get_label('create_meeting', 'Create meeting') ?>"><i class='bx bx-plus'></i></button></a> --}}

                <a href="{{ route('meetings.index') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="{{ get_label('meetings', 'Meetings') }}"><i
                            class='bx bx-shape-polygon'></i></button></a>
            </div>

        </div>
        <div class="row">
            <div class="col-md-3 col-lg-2">
                <div class="card shadow-sm border-0">

                    <div class="card-body">
                        <div class="mb-4 border-bottom pb-2">
                            <h5 class="fw-bold mb-0">
                                <i class="bx bx-calendar me-2"></i>
                                {{ get_label('meeting_calendar', 'Meeting Calendar') }}
                            </h5>
                        </div>
                        <!-- Create Meeting Button -->
                        <div class="mb-4">

                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#createMeetingModal">
                                <button type="button"
                                    class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i class='bx bx-plus fs-5'></i>
                                    <span>{{ get_label('create_meeting', 'Create Meeting') }}</span>
                                </button>
                            </a>
                        </div>

                        <!-- Meeting Statistics -->
                        <div class="mb-4">
                            <h6 class="d-flex fw-bold align-items-center mb-3">
                                <i class="bx bx-stats me-2"></i>
                                {{ get_label('statistics', 'Statistics') }}
                            </h6>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <span>{{ get_label('total_meetings', 'Total Meetings') }}</span>
                                    <span id="total-meetings-count" class="badge bg-primary rounded-pill">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <span>{{ get_label('ongoing', 'Ongoing') }}</span>
                                    <span id="ongoing-count" class="badge bg-success rounded-pill">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <span>{{ get_label('upcoming', 'Upcoming') }}</span>
                                    <span id="upcoming-count" class="badge bg-info rounded-pill">0</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <span>{{ get_label('completed', 'Completed') }}</span>
                                    <span id="completed-count" class="badge bg-secondary rounded-pill">0</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Meeting Status Filters with Checkboxes -->
                        <div class="mb-4">
                            <h6 class="d-flex fw-bold align-items-center mb-3">
                                <i class="bx bx-filter-alt me-2"></i>
                                {{ get_label('meeting_status', 'Meeting Status') }}
                            </h6>
                            <div id="meeting_status_filters">
                                <div class="form-check mb-3">
                                    <input class="form-check-input meeting-status-filter checkbox-success" type="checkbox"
                                        value="ongoing" id="status_ongoing">
                                    <label class="form-check-label d-flex align-items-center" for="status_ongoing">
                                        <span>{{ get_label('ongoing', 'Ongoing') }}</span>
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input meeting-status-filter checkbox-info" type="checkbox"
                                        value="yet_to_start" id="status_upcoming">
                                    <label class="form-check-label d-flex align-items-center" for="status_upcoming">
                                        <span>{{ get_label('upcoming', 'Upcoming') }}</span>
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input meeting-status-filter checkbox-gray" type="checkbox"
                                        value="ended" id="status_completed">
                                    <label class="form-check-label d-flex align-items-center" for="status_completed">
                                        <span>{{ get_label('completed', 'Completed') }}</span>
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
                        <div id="meetingsCalenderDiv"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
