@extends('layout')
@section('title')
{{ get_label('holiday_calendar', 'Holiday Calendar') }} - {{ get_label('calendars', 'Calendars') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ url('home') }}">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        {{ get_label('calendars', 'Calendars') }}
                    </li>
                    <li class="breadcrumb-item active">
                        {{ get_label('holiday_calendar', 'Holiday Calendar') }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if(empty($google_calendar_settings['calendar_id']) || empty($google_calendar_settings['api_key']))
                    <div class="alert alert-primary" role="alert">
                        {{ get_label('google_calendar_not_configured', 'Google Calendar is Not Configured Properly.') }}
                        <a href="{{ url('/master-panel/settings/google-calendar') }}" class="ms-2 text-decoration">
                            {{ get_label('click_here_to_configure', 'Click Here to Configure') }}
                        </a>
                    </div>

                    @endif

                    <div id="color-legend">
                        <strong class="legend-title">{{ get_label('event_type','Event Type') }}:</strong>
                        <div class="legend-container">
                            <div class="legend-item">
                                <span class="legend-box bg-primary"></span>
                                <span>
                                    @if(isset($google_calendar_settings['custom_holiday_name']) && !empty($google_calendar_settings['custom_holiday_name']))
                                    {{ $google_calendar_settings['custom_holiday_name'] }}
                                    @else
                                    {{ get_label('public_holidays','Public Holidays') }}
                                    @endif
                                </span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-box bg-success"></span> <span>{{ get_label('leave_accepted','Leave Accepted') }}</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-box bg-warning"></span> <span>{{ get_label('leave_pending','Leave Pending') }}</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-box bg-danger"></span> <span>{{ get_label('leave_rejected','Leave Rejected') }}</span>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="card-body">


                    <div class="" id="googleCalendarDiv"></div>

                </div>
            </div>
        </div>

        <script>
            var google_calendar_id = '{{ $google_calendar_settings['calendar_id'] }}';
            var google_calendar_api_key = '{{ $google_calendar_settings['api_key'] }}';
        </script>


        @endsection