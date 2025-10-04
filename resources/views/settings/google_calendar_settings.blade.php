@extends('layout')
@section('title')
{{ get_label('google_calendar', 'Google Calendar') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ url('home') }}"> {{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        {{ get_label('settings', 'Settings') }}
                    </li>
                    <li class="breadcrumb-item active">
                        {{ get_label('google_calendar', 'Google Calendar') }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="alert alert-primary" role="alert">
                {{ get_label('documentation_for_integration_with_google_calendar', 'Documentation for integration with Google Calendar') }}.
                <a href="javascript:void(0)" data-bs-toggle="modal"
                    data-bs-target="#google_calender_instruction_modal">{{ get_label('click_for_help', 'Click Here for Help') }}</a>
            </div>
            <form action="{{ route('google_calendar.store') }}" class="form-submit-event" method="POST">
                <input type="hidden" name="dnr">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-check-label" for="apiKey"> {{ get_label('api_key', 'API Key') }}</label>
                        <input class="form-control" type="text" name="api_key"
                            placeholder=" {{ get_label('please_enter_your_google_api_key', 'Please Enter Your Google API Key') }}"
                            value="{{ $google_calendar_settings['api_key'] }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-check-label"
                            for="calendar_id">{{ get_label('google_calendar_id', 'Google Calendar ID', '') }}</label>
                        <input class="form-control" type="text" name="calendar_id"
                            placeholder="{{ get_label('please_enter_your_google_calendar_id', 'Please Enter Your Google Calendar ID') }}"
                            value="{{ $google_calendar_settings['calendar_id'] }}">
                

                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-check-label"
                            for="custom_holiday_name">{{ get_label('custom_holiday_name', 'Custom Holiday Name', '') }}</label>
                        <input class="form-control" type="text" name="custom_holiday_name"
                            placeholder="{{ get_label('please_enter_custom_holiday_name', 'Please Enter Custom Holiday Name') }}"
                            value="{{ isset($google_calendar_settings['custom_holiday_name']) ? $google_calendar_settings['custom_holiday_name'] : '' }}">
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2"
                            id="submit_btn"><?= get_label('update', 'Update') ?></button>
                        <button type="reset"
                            class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="google_calender_instruction_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ get_label('google_calendar_integration', 'Google Calendar Integration') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4>📌 Step 1: Create a Google Cloud Project</h4>
                <ol>
                    <li>Go to <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>.</li>
                    <li>Click <b>Select a Project</b> → <b>New Project</b>.</li>
                    <li>Enter a <b>Project Name</b> (e.g., Taskify Calendar).</li>
                    <li>Click <b>Create</b> and wait for the project to be initialized.</li>
                </ol>
                <a href="{{ asset('storage/google-calendar/create_new_project.png') }}" data-lightbox="google-calendar"
                    data-title="Create Google Project">
                    <img src="{{ asset('storage/google-calendar/create_new_project.png') }}"
                        alt="Create Google Project" class="img-fluid mb-3 rounded border shadow-sm">
                </a>

                <h4>📌 Step 2: Enable Google Calendar API</h4>
                <ol>
                    <li>Inside your project, go to <b>API & Services</b> → <b>Library</b>.</li>
                    <li>Search for <b>Google Calendar API</b> and select it.</li>
                    <li>Click <b>Enable</b>.</li>
                </ol>
                <a href="{{ asset('storage/google-calendar/enable-google-calendar-api.png') }}"
                    data-lightbox="google-calendar" data-title="Enable Google Calendar API">
                    <img src="{{ asset('storage/google-calendar/enable-google-calendar-api.png') }}"
                        alt="Enable Calendar API" class="img-fluid mb-3 rounded border shadow-sm">
                </a>

                <h4>📌 Step 3: Generate API Credentials</h4>
                <ol>
                    <li>Go to <b>API & Services</b> → <b>Credentials</b>.</li>
                    <li>Click <b>Create Credentials</b> → <b>API Key</b>.</li>
                    <li>Your API Key will appear. <b>Copy it</b> for later use.</li>
                    <li>(Optional) Click <b>Restrict Key</b> and select <b>HTTP Referrer</b>.</li>
                    <li>Enter your domain (e.g., <code>https://yourdomain.com/*</code>).</li>
                    <li>Click <b>Save</b>.</li>
                </ol>
                <a href="{{ asset('storage/google-calendar/create-api-key.png') }}" data-lightbox="google-calendar"
                    data-title="Generate API Key">
                    <img src="{{ asset('storage/google-calendar/create-api-key.png') }}" alt="Generate API Key"
                        class="img-fluid mb-3 rounded border shadow-sm">
                </a>

                <h4>📌 Step 4: Make Your Google Calendar Public</h4>
                <ol>
                    <li>Go to <a href="https://calendar.google.com/" target="_blank">Google Calendar</a>.</li>
                    <li>Under <b>My Calendars</b>, hover over your calendar and click <b>Settings & Sharing</b>.</li>
                    <li>Under <b>Access Permissions</b>, check <b>Make available to public</b>.</li>
                    <li>Ensure <b>See all event details</b> is selected.</li>
                </ol>
                <a href="{{ asset('storage/google-calendar/make-google-calendar-public.png') }}"
                    data-lightbox="google-calendar" data-title="Make Google Calendar Public">
                    <img src="{{ asset('storage/google-calendar/make-google-calendar-public.png') }}"
                        alt="Make Google Calendar Public" class="img-fluid mb-3 rounded border shadow-sm">
                </a>

                <h4>📌 Step 5: Get Your Google Calendar ID</h4>
                <ol>
                    <li>Go to <b>Google Calendar</b> → <b>Settings & Sharing</b>.</li>
                    <li>Scroll down to <b>Integrate Calendar</b>.</li>
                    <li>Copy the <b>Calendar ID</b> (e.g., <code>abcd1234@group.calendar.google.com</code>).</li>
                </ol>
                <a href="{{ asset('storage/google-calendar/get-calendar-id.png') }}" data-lightbox="google-calendar"
                    data-title="Find Google Calendar ID">
                    <img src="{{ asset('storage/google-calendar/get-calendar-id.png') }}" alt="Find Google Calendar ID"
                        class="img-fluid mb-3 rounded border shadow-sm">
                </a>

                <h4>📌 Step 6: Update Taskify Settings</h4>
                <ol>
                    <li>Go to Taskify and paste your <b>API Key</b> and <b>Calendar ID</b>.</li>
                    <li>Click <b>Update</b>.</li>
                </ol>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection