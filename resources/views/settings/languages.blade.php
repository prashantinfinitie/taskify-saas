@extends('layout')

@section('title')
    <?= get_label('languages', 'Languages') ?>
@endsection
@php
    $general_settings = get_settings('general_settings');

@endphp

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}">
                                <?= get_label('home', 'Home') ?>
                            </a>

                        </li>
                        <li class="breadcrumb-item">
                            <?= get_label('settings', 'Settings') ?>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('languages', 'Languages') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                @if (app()->getLocale() == $default_language)
                    <span class="badge bg-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('current_language_is_your_primary_language', 'Current language is your primary language') ?>">
                        <?= get_label('primary', 'Primary') ?>
                    </span>

                @else
                    <a href="javascript:void(0);"><span class="badge bg-secondary" id="set-as-default"
                            data-lang="{{ app()->getLocale() }}" data-url="{{ route('languages.set_default') }}"
                            data-bs-toggle="tooltip" data-bs-placement="left"
                            data-bs-original-title="
                                    <?= get_label('set_current_language_as_your_primary_language', 'Set current language as your primary language') ?>">
                            <?= get_label('set_as_primary', 'Set as primary') ?>
                        </span></a>

                @endif
            </div>
            <form action="{{ route('languages.save_labels') }}" class="form-submit-event" method="POST">
                <input type="hidden" name="redirect_url" value="{{ route('languages.index') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="langcode" value="{{ Session::get('locale') }}">
                <div>

                    <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('save_language', 'Save language') ?>"><i
                            class='bx bx-save'></i></button>
                    <span data-bs-toggle="modal" data-bs-target="#create_language_modal"><a href="javascript:void(0);"
                            class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                            data-bs-original-title="<?= get_label('create_language', 'Create language') ?>"><i
                                class='bx bx-plus'></i></a></span>
                    <a href="{{ route('languages.manage') }}"><button type="button" class="btn btn-sm btn-primary"
                            data-bs-toggle="tooltip" data-bs-placement="right"
                            data-bs-original-title="<?= get_label('manage_languages', 'Manage languages') ?>"><i
                                class="bx bx-list-ul"></i></button></a>
                </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-2 mb-xl-0 mb-4">
                        <small class="text-light fw-semibold">
                            <?= get_label('jump_to', 'Jump to') ?>
                        </small>

                        <div class="demo-inline-spacing mt-3">
                            <div class="list-group">
                                @foreach ($languages as $language)
                                    <a href="{{ route('languages.change', ['code' => $language->code]) }}"
                                        class="list-group-item list-group-item-action {{ Session::get('locale') == $language->code ? 'active' : '' }}">{{ $language->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-10">
                        <small class="text-light fw-semibold">
                            <?= get_label('labels', 'Labels') ?>
                        </small>

                        <div class="mb-3 mt-2">
                            <div class="row">
                                {!! create_label('dashboard', 'Dashboard', Session::get('locale', Session::get('locale'))) !!}
                                {!! create_label('total_projects', 'Total projects', Session::get('locale', Session::get('locale'))) !!}
                            </div>
                            <div class="row">
                                {!! create_label('total_tasks', 'Total tasks', Session::get('locale')) !!}
                                {!! create_label('total_users', 'Total users', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('total_clients', 'Total clients', Session::get('locale')) !!}
                                {!! create_label('projects', 'Projects', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('tasks', 'Tasks', Session::get('locale')) !!}
                                {!! create_label('session_expired', 'Session expired', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('log_in', 'Log in', Session::get('locale')) !!}
                                {!! create_label('search_results', 'Search results', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('no_results_found', 'No Results Found!', Session::get('locale')) !!}
                                {!! create_label('create_project', 'Create project', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('create', 'Create', Session::get('locale')) !!}
                                {!! create_label('title', 'Title', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('status', 'Status', Session::get('locale')) !!}
                                {!! create_label('create_status', 'Create status', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('budget', 'Budget', Session::get('locale')) !!}
                                {!! create_label('starts_at', 'Starts at', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('ends_at', 'Ends at', Session::get('locale')) !!}
                                {!! create_label('description', 'Description', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('select_users', 'Select users', Session::get('locale')) !!}
                                {!! create_label('select_clients', 'Select clients', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'you_will_be_project_participant_automatically',
        'You will be project participant automatically.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('create', 'Create', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('grid_view', 'Grid view', Session::get('locale')) !!}
                                {!! create_label('update', 'Update', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('delete', 'Delete', Session::get('locale')) !!}
                                {!! create_label('warning', 'Warning!', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'delete_project_alert',
        'Are you sure you want to delete this project?',
        Session::get('locale'),
    ) !!}
                                {!! create_label('close', 'Close', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('yes', 'Yes', Session::get('locale')) !!}
                                {!! create_label('users', 'Users', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('view', 'View', Session::get('locale')) !!}
                                {!! create_label('create_task', 'Create task', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('time', 'Time', Session::get('locale')) !!}
                                {!! create_label('clients', 'Clients', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('list_view', 'List view', Session::get('locale')) !!}
                                {!! create_label('draggable', 'Draggable', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('create_task', 'Create task', Session::get('locale')) !!}
                                {!! create_label('task', 'Task', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('project', 'Project', Session::get('locale')) !!}
                                {!! create_label('actions', 'Actions', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('delete_task_alert', 'Are you sure you want to delete this task?', Session::get('locale')) !!}
                                {!! create_label('update_project', 'Update project', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('cancel', 'Cancel', Session::get('locale')) !!}
                                {!! create_label('update_task', 'Update task', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('project', 'Project', Session::get('locale')) !!}
                                {!! create_label('messages', 'MESSAGES', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('contacts', 'Contacts', Session::get('locale')) !!}
                                {!! create_label('favorites', 'Favorites', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('all_messages', 'All Messages', Session::get('locale')) !!}
                                {!! create_label('search', 'Search', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('type_to_search', 'Type to search', Session::get('locale')) !!}
                                {!! create_label('connected', 'Connected', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('connecting', 'Connecting', Session::get('locale')) !!}
                                {!! create_label('no_internet_access', 'No internet access', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'please_select_a_chat_to_start_messaging',
        'Please select a chat to start messaging',
        Session::get('locale'),
    ) !!}
                                {!! create_label('user_details', 'User Details', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('delete_conversation', 'Delete Conversation', Session::get('locale')) !!}
                                {!! create_label('shared_photos', 'Shared Photos', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('you', 'You', Session::get('locale')) !!}
                                {!! create_label('save_messages_secretly', 'Save messages secretly', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('attachment', 'Attachment', Session::get('locale')) !!}
                                {!! create_label(
        'are_you_sure_you_want_to_delete_this',
        'Are you sure you want to delete this?',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('you_can_not_undo_this_action', 'You can not undo this action', Session::get('locale')) !!}
                                {!! create_label('upload_new', 'Upload New', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('dark_mode', 'Dark Mode', Session::get('locale')) !!}
                                {!! create_label('save_changes', 'Save Changes', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('save_changes', 'Save Changes', Session::get('locale')) !!}
                                {!! create_label('type_a_message', 'Type a message', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('create_meeting', 'Create meeting', Session::get('locale')) !!}
                                {!! create_label('meetings', 'Meetings', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'you_will_be_meeting_participant_automatically',
        'You will be meeting participant automatically.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('update_meeting', 'Update meeting', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('create_workspace', 'Create workspace', Session::get('locale')) !!}
                                {!! create_label('workspaces', 'Workspaces', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'you_will_be_workspace_participant_automatically',
        'You will be workspace participant automatically.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('update_workspace', 'Update workspace', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('create_todo', 'Create todo', Session::get('locale')) !!}
                                {!! create_label('todo_list', 'Todo list', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('priority', 'Priority', Session::get('locale')) !!}
                                {!! create_label('low', 'Low', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('medium', 'Medium', Session::get('locale')) !!}
                                {!! create_label('high', 'High', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('todo', 'Todo', Session::get('locale')) !!}
                                {!! create_label('delete_todo_warning', 'Are you sure you want to delete this todo?', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                            </div>
                            <div class="row">
                                {!! create_label('account', 'Account', Session::get('locale')) !!}
                                {!! create_label('account_settings', 'Account settings', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('profile_details', 'Profile details', Session::get('locale')) !!}
                                {!! create_label('update_profile_photo', 'Update profile photo', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('allowed_jpg_png', 'Allowed JPG or PNG.', Session::get('locale')) !!}
                                {!! create_label('first_name', 'First name', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('last_name', 'Last name', Session::get('locale')) !!}
                                {!! create_label('phone_number', 'Phone number', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('email', 'E-mail', Session::get('locale')) !!}
                                {!! create_label('role', 'Role', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('address', 'Address', Session::get('locale')) !!}
                                {!! create_label('city', 'City', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('state', 'State', Session::get('locale')) !!}
                                {!! create_label('country', 'Country', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('zip_code', 'Zip code', Session::get('locale')) !!}
                                {!! create_label('state', 'State', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('delete_account', 'Delete account', Session::get('locale')) !!}
                                {!! create_label(
        'delete_account_alert',
        'Are you sure you want to delete your account?',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'delete_account_alert_sub_text',
        'Once you delete your account, there is no going back. Please be certain.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('create_user', 'Create user', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('password', 'Password', Session::get('locale')) !!}
                                {!! create_label('confirm_password', 'Confirm password', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('profile_picture', 'Profile picture', Session::get('locale')) !!}
                                {!! create_label('profile', 'Profile', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('assigned', 'Assigned', Session::get('locale')) !!}
                                {!! create_label('delete_user_alert', 'Are you sure you want to delete this user?', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('client_projects', 'Client projects', Session::get('locale')) !!}
                                {!! create_label('create_client', 'Create client', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('client', 'Client', Session::get('locale')) !!}
                                {!! create_label('company', 'Company', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('phone_number', 'Phone number', Session::get('locale')) !!}
                                {!! create_label('delete_client_alert', 'Are you sure you want to delete this client?', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('draggable', 'Draggable', Session::get('locale')) !!}
                                {!! create_label('settings', 'Settings', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('smtp_host', 'SMTP host', Session::get('locale')) !!}
                                {!! create_label('smtp_port', 'SMTP port', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('email_content_type', 'Email content type', Session::get('locale')) !!}
                                {!! create_label('smtp_encryption', 'SMTP Encryption', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('general', 'General', Session::get('locale')) !!}
                                {!! create_label('company_title', 'Company title', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('full_logo', 'Full logo', Session::get('locale')) !!}
                                {!! create_label('half_logo', 'Half logo', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('favicon', 'Favicon', Session::get('locale')) !!}
                                {!! create_label('system_time_zone', 'System time zone', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('select_time_zone', 'Select time zone', Session::get('locale')) !!}
                                {!! create_label('currency_full_form', 'Currency full form', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('currency_symbol', 'Currency symbol', Session::get('locale')) !!}
                                {!! create_label('currency_code', 'Currency code', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('permission_settings', 'Permission settings', Session::get('locale')) !!}
                                {!! create_label('create_role', 'Create role', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('permissions', 'Permissions', Session::get('locale')) !!}
                                {!! create_label('no_permissions_assigned', 'No Permissions Assigned!', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('delete_role_alert', 'Are you sure you want to delete this role?', Session::get('locale')) !!}
                                {!! create_label('pusher', 'Pusher', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'important_settings_for_chat_feature_to_be_work',
        'Important settings for chat feature to be work',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'click_here_to_find_these_settings_on_your_pusher_account',
        'Click here to find these settings on your pusher account',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('pusher_app_id', 'Pusher app id', Session::get('locale')) !!}
                                {!! create_label('pusher_app_key', 'Pusher app key', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('pusher_app_secret', 'Pusher app secret', Session::get('locale')) !!}
                                {!! create_label('pusher_app_cluster', 'Pusher app cluster', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('no_meetings_found', 'No meetings found!', Session::get('locale')) !!}
                                {!! create_label(
        'delete_meeting_alert',
        'Are you sure you want to delete this meeting?',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('manage_workspaces', 'Manage workspaces', Session::get('locale')) !!}
                                {!! create_label('edit_workspace', 'Edit workspace', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('remove_me_from_workspace', 'Remove me from workspace', Session::get('locale')) !!}
                                {!! create_label('chat', 'Chat', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('todos', 'Todos', Session::get('locale')) !!}
                                {!! create_label('languages', 'Languages', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('no_projects_found', 'No projects Found!', Session::get('locale')) !!}
                                {!! create_label('no_tasks_found', 'No tasks Found!', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('no_workspace_found', 'No workspaces found!', Session::get('locale')) !!}
                                {!! create_label(
        'delete_workspace_alert',
        'Are you sure you want to delete this workspace?',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('preview', 'Preview', Session::get('locale')) !!}
                                {!! create_label('primary', 'Primary', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('secondary', 'Secondary', Session::get('locale')) !!}
                                {!! create_label('success', 'Success', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('danger', 'Danger', Session::get('locale')) !!}
                                {!! create_label('warning', 'Warning', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('info', 'Info', Session::get('locale')) !!}
                                {!! create_label('dark', 'Dark', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('labels', 'Labels', Session::get('locale')) !!}
                                {!! create_label('jump_to', 'Jump to', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('save_language', 'Save language', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'current_language_is_your_primary_language',
        'Current language is your primary language',
        Session::get('locale'),
    ) !!}
                                {!! create_label('set_as_primary', 'Set as primary', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'set_current_language_as_your_primary_language',
        'Set current language as your primary language',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'set_primary_lang_alert',
        'Are you want to set as your primary language?',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('home', 'Home', Session::get('locale')) !!}
                                {!! create_label('project_details', 'Project details', Session::get('locale')) !!}

                            </div>
                            <div class="row">
                                {!! create_label('list', 'List', Session::get('locale')) !!}
                                {!! create_label('drag_drop_update_task_status', 'Drag and drop to update task status', Session::get('locale')) !!}

                            </div>
                            <div class="row">
                                {!! create_label('update_role', 'Update role', Session::get('locale')) !!}
                                {!! create_label('date_format', 'Date format', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'this_date_format_will_be_used_in_the_system_everywhere',
        'This date format will be used in the system everywhere',
        Session::get('locale'),
    ) !!}
                                {!! create_label('select_date_format', 'Select date format', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('select_status', 'Select status', Session::get('locale')) !!}
                                {!! create_label('sort_by', 'Sort by', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('newest', 'Newest', Session::get('locale')) !!}
                                {!! create_label('oldest', 'Oldest', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('most_recently_updated', 'Most recently updated', Session::get('locale')) !!}
                                {!! create_label('least_recently_updated', 'Least recently updated', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'important_settings_for_email_feature_to_be_work',
        'Important settings for email feature to be work',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'click_here_to_test_your_email_settings',
        'Click here to test your email settings',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('data_not_found', 'Data Not Found', Session::get('locale')) !!}
                                {!! create_label('oops!', 'Oops!', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('data_does_not_exists', 'Data does not exists', Session::get('locale')) !!}
                                {!! create_label('create_now', 'Create now', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('select_project', 'Select project', Session::get('locale')) !!}
                                {!! create_label('select', 'Select', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('not_assigned', 'Not assigned', Session::get('locale')) !!}
                                {!! create_label(
        'confirm_leave_workspace',
        'Are you sure you want leave this workspace?',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('not_workspace_found', 'No workspace(s) found', Session::get('locale')) !!}
                                {!! create_label(
        'must_workspace_participant',
        'You must be participant in atleast one workspace',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'pending_email_verification',
        'Pending email verification. Please check verification mail sent to you!',
        Session::get('locale'),
    ) !!}
                                {!! create_label('resend_verification_link', 'Resend verification link', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('id', 'ID', Session::get('locale')) !!}
                                {!! create_label('projects_grid_view', 'Projects grid view', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('tasks_list', 'Tasks list', Session::get('locale')) !!}
                                {!! create_label('task_details', 'Task details', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('update_todo', 'Update todo', Session::get('locale')) !!}
                                {!! create_label('user_profile', 'User profile', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('update_user_profile', 'Update user profile', Session::get('locale')) !!}
                                {!! create_label('update_profile', 'Update profile', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('client_profile', 'Client profile', Session::get('locale')) !!}
                                {!! create_label('update_client_profile', 'Update client profile', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('todos_not_found', 'Todos not found!', Session::get('locale')) !!}
                                {!! create_label('view_more', 'View more', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('project_statistics', 'Project statistics', Session::get('locale')) !!}
                                {!! create_label('task_statistics', 'Task statistics', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('status_wise_projects', 'Status wise projects', Session::get('locale')) !!}
                                {!! create_label('status_wise_tasks', 'Status wise tasks', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('manage_status', 'Manage status', Session::get('locale')) !!}
                                {!! create_label('ongoing', 'Ongoing', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('ended', 'Ended', Session::get('locale')) !!}
                                {!! create_label('footer_text', 'Footer text', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('view_current_full_logo', 'View current full logo', Session::get('locale')) !!}
                                {!! create_label('current_full_logo', 'Current full logo', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('view_current_half_logo', 'View current half logo', Session::get('locale')) !!}
                                {!! create_label('current_half_logo', 'Current half logo', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('view_current_favicon', 'View current favicon', Session::get('locale')) !!}
                                {!! create_label('current_favicon', 'Current favicon', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('manage_statuses', 'Manage statuses', Session::get('locale')) !!}
                                {!! create_label('statuses', 'Statuses', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('update_status', 'Update status', Session::get('locale')) !!}
                                {!! create_label(
        'delete_status_warning',
        'Are you sure you want to delete this status?',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('select_user', 'Select user', Session::get('locale')) !!}
                                {!! create_label('select_client', 'Select client', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('tags', 'Tags', Session::get('locale')) !!}
                                {!! create_label('create_tag', 'Create tag', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('manage_tags', 'Manage tags', Session::get('locale')) !!}
                                {!! create_label('update_tag', 'Update tag', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('delete_tag_warning', 'Are you sure you want to delete this tag?', Session::get('locale')) !!}
                                {!! create_label('filter_by_tags', 'Filter by tags', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('filter', 'Filter', Session::get('locale')) !!}
                                {!! create_label('type_to_search', 'Type to search', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('select_tags', 'Select tags', Session::get('locale')) !!}
                                {!! create_label('start_date_between', 'Start date between', Session::get('locale')) !!}

                            </div>
                            <div class="row">
                                {!! create_label('end_date_between', 'End date between', Session::get('locale')) !!}
                                {!! create_label(
        'reload_page_to_change_chart_colors',
        'Reload the page to change chart colors!',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('todos_overview', 'Todos overview', Session::get('locale')) !!}
                                {!! create_label('done', 'Done', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('pending', 'Pending', Session::get('locale')) !!}
                                {!! create_label('total', 'Total', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('not_authorized', 'You are not authorized to perform this action.', Session::get('locale')) !!}
                                {!! create_label('un_authorized_action', 'Un authorized action!', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'not_authorized_notice',
        'Sorry for the inconvenience but you are not authorized to perform this action',
        Session::get('locale'),
    ) !!}
                                {!! create_label('not_specified', 'Not specified', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('manage_projects', 'Manage projects', Session::get('locale')) !!}
                                {!! create_label('total_todos', 'Total todos', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('total_meetings', 'Total meetings', Session::get('locale')) !!}
                                {!! create_label('add_favorite', 'Click to mark as favorite', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('remove_favorite', 'Click to remove from favorite', Session::get('locale')) !!}
                                {!! create_label('favorite_projects', 'Favorite projects', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('favorite', 'Favorite', Session::get('locale')) !!}
                                {!! create_label('duplicate', 'Duplicate', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('duplicate_warning', 'Are you sure you want to duplicate?', Session::get('locale')) !!}
                                {!! create_label('leave_requests', 'Leave requests', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('leave_request', 'Leave request', Session::get('locale')) !!}
                                {!! create_label('create_leave_requet', 'Create leave request', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('leave_from_date', 'Leave from date', Session::get('locale')) !!}
                                {!! create_label('leave_reason', 'Leave reason', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('days', 'Days', Session::get('locale')) !!}
                                {!! create_label('to', 'To', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('name', 'Name', Session::get('locale')) !!}
                                {!! create_label('duration', 'Duration', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('reason', 'Reason', Session::get('locale')) !!}
                                {!! create_label('action_by', 'Action by', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('approved', 'Approved', Session::get('locale')) !!}
                                {!! create_label('rejected', 'Rejected', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('update_leave_requet', 'Update leave request', Session::get('locale')) !!}
                                {!! create_label('select_leave_editors', 'Select leave editors', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('leave_editor_info', 'You are leave editor', Session::get('locale')) !!}
                                {!! create_label('from_date_between', 'From date between', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('to_date_between', 'To date between', Session::get('locale')) !!}
                                {!! create_label('contracts', 'Contracts', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('create_contract', 'Create contract', Session::get('locale')) !!}
                                {!! create_label('contract_types', 'Contract types', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('create_contract_type', 'Create contract type', Session::get('locale')) !!}
                                {!! create_label('type', 'Type', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('update_contract_type', 'Update contract type', Session::get('locale')) !!}
                                {!! create_label('created_at', 'Created at', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('signed', 'Signed', Session::get('locale')) !!}
                                {!! create_label('partially_signed', 'Partially signed', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('not_signed', 'Not signed', Session::get('locale')) !!}
                                {!! create_label('value', 'Value', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('select_contract_type', 'Select contract type', Session::get('locale')) !!}
                                {!! create_label('update_contract', 'Update contract', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('promisor_sign_status', 'Promisor sign status', Session::get('locale')) !!}
                                {!! create_label('promisee_sign_status', 'Promisee sign status', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('manage_contract_types', 'Manage contract types', Session::get('locale')) !!}
                                {!! create_label('contract', 'Contract', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('contract_id_prefix', 'CTR - ', Session::get('locale')) !!}
                                {!! create_label('promiser_sign', 'Promisor sign', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('promiser_sign', 'Promisor sign', Session::get('locale')) !!}
                                {!! create_label('promisee_sign', 'Promisee sign', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('created_by', 'Created by', Session::get('locale')) !!}
                                {!! create_label('updated_at', 'Updated at', Session::get('locale')) !!}

                            </div>

                            <div class="row">
                                {!! create_label('last_updated_at', 'Last updated at', Session::get('locale')) !!}
                                {!! create_label('create_signature', 'Create signature', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('reset', 'Reset', Session::get('locale')) !!}
                                {!! create_label('delete_signature', 'Delete signature', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('payslips', 'Payslips', Session::get('locale')) !!}
                                {!! create_label('print_contract', 'Print contract', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('create_payslip', 'Create payslip', Session::get('locale')) !!}
                                {!! create_label('payslip_month', 'Payslip month', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('working_days', 'Working days', Session::get('locale')) !!}
                                {!! create_label('lop_days', 'Loss of pay days', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('paid_days', 'Paid days', Session::get('locale')) !!}
                                {!! create_label('please_select', 'Please select', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('basic_salary', 'Basic salary', Session::get('locale')) !!}
                                {!! create_label('leave_deduction', 'Leave deduction', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('over_time_hours', 'Over time hours', Session::get('locale')) !!}
                                {!! create_label('over_time_rate', 'Over time rate', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('over_time_payment', 'Over time payment', Session::get('locale')) !!}
                                {!! create_label('bonus', 'Bonus', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('incentives', 'Incentives', Session::get('locale')) !!}
                                {!! create_label('payment_method', 'Payment method', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('payment_date', 'Payment date', Session::get('locale')) !!}
                                {!! create_label('paid', 'Paid', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('unpaid', 'Unpaid', Session::get('locale')) !!}
                                {!! create_label('payment_status', 'Payment status', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('create_payment_method', 'Create payment method', Session::get('locale')) !!}
                                {!! create_label('manage_payment_methods', 'Manage payment methods', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('payment_methods', 'Payment methods', Session::get('locale')) !!}
                                {!! create_label('allowances', 'Allowances', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('update_payment_method', 'Update payment method', Session::get('locale')) !!}
                                {!! create_label('manage_payslips', 'Manage payslips', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('manage_contracts', 'Manage contracts', Session::get('locale')) !!}
                                {!! create_label('allowance', 'Allowance', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('deduction', 'Deduction', Session::get('locale')) !!}
                                {!! create_label('amount', 'Amount', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('manage_allowances', 'Manage allowances', Session::get('locale')) !!}
                                {!! create_label('update_allowance', 'Update allowance', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('create_allowance', 'Create allowance', Session::get('locale')) !!}
                                {!! create_label('manage_deductions', 'Manage deductions', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('create_deduction', 'Create deduction', Session::get('locale')) !!}
                                {!! create_label('percentage', 'Percentage', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('deductions', 'Deductions', Session::get('locale')) !!}
                                {!! create_label('update_deduction', 'Update deduction', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('add', 'Add', Session::get('locale')) !!}
                                {!! create_label('remove', 'Remove', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('total_allowances', 'Total allowances', Session::get('locale')) !!}
                                {!! create_label('total_deductions', 'Total deductions', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('total_earning', 'Total earning', Session::get('locale')) !!}
                                {!! create_label('net_payable', 'Net payable', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('payslip_id_prefix', 'PSL (payslip ID prefix)', Session::get('locale')) !!}
                                {!! create_label('team_member', 'Team member', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('update_payslip', 'Update payslip', Session::get('locale')) !!}
                                {!! create_label('payslip', 'Payslip', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('payslip_for', 'Payslip for', Session::get('locale')) !!}
                                {!! create_label('print_payslip', 'Print payslip', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('total_allowances_and_deductions', 'Total allowances and deductions', Session::get('locale')) !!}
                                {!! create_label('no_deductions_found_payslip', 'No deductions found for this payslip.', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('no_allowances_found_payslip', 'No allowances found for this payslip.', Session::get('locale')) !!}
                                {!! create_label('total_earnings', 'Total earnings', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('select_team_member', 'Select team member', Session::get('locale')) !!}
                                {!! create_label('select_payment_status', 'Select payment status', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('select_created_by', 'Select created by', Session::get('locale')) !!}
                                {!! create_label('notes', 'Notes', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('create_note', 'Create note', Session::get('locale')) !!}
                                {!! create_label('upcoming_birthdays', 'Upcoming birthdays', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('upcoming_work_anniversaries', 'Upcoming work anniversaries', Session::get('locale')) !!}
                                {!! create_label('birthday_count', 'Birthday count', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('days_left', 'Days left', Session::get('locale')) !!}
                                {!! create_label('till_upcoming_days_def_30', 'Till upcoming days : default 30', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('work_anniversary_date', 'Work anniversary date', Session::get('locale')) !!}
                                {!! create_label('birth_day_date', 'Birth day date', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('select_member', 'Select member', Session::get('locale')) !!}
                                {!! create_label('update_note', 'Update note', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('today', 'Today', Session::get('locale')) !!}
                                {!! create_label('tomorow', 'Tomorrow', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('day_after_tomorow', 'Day after tomorrow', Session::get('locale')) !!}
                                {!! create_label('on_leave', 'On leave', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('on_leave_tomorrow', 'On leave from tomorrow', Session::get('locale')) !!}
                                {!! create_label('on_leave_day_after_tomorow', 'On leave from day after tomorrow', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('dob_not_set_alert', 'You DOB is not set', Session::get('locale')) !!}
                                {!! create_label('click_here_to_set_it_now', 'Click here to set it now', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('system_updater', 'System updater', Session::get('locale')) !!}
                                {!! create_label('update_the_system', 'Update the system', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('hi', 'Hi', Session::get('locale')) !!}
                                {!! create_label('active', 'Active', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('deactive', 'Deactive', Session::get('locale')) !!}
                                {!! create_label(
        'status_not_active',
        'Your account is currently inactive. Please contact admin for assistance.',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('demo_restriction', 'This operation is not allowed in demo mode.', Session::get('locale')) !!}
                                {!! create_label('please_enter_title', 'Please enter title', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('please_enter_description', 'Please enter description', Session::get('locale')) !!}
                                {!! create_label('please_enter_title', 'Please enter title', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('time_tracker', 'Time tracker', Session::get('locale')) !!}
                                {!! create_label('start', 'Start', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('stop', 'Stop', Session::get('locale')) !!}
                                {!! create_label('pause', 'Pause', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('hours', 'Hours', Session::get('locale')) !!}
                                {!! create_label('minutes', 'Minutes', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('second', 'Second', Session::get('locale')) !!}
                                {!! create_label('message', 'Message', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('view_timesheet', 'View timesheet', Session::get('locale')) !!}
                                {!! create_label('timesheet', 'Timesheet', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('stop_timer_alert', 'Are you sure you want to stop the timer?', Session::get('locale')) !!}
                                {!! create_label('user', 'User', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('started_at', 'Started at', Session::get('locale')) !!}
                                {!! create_label('ended_at', 'Ended at', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('yet_to_start', 'Yet to start', Session::get('locale')) !!}
                                {!! create_label('select_all', 'Select all', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('users_associated_with_project', 'Users associated with project', Session::get('locale')) !!}
                                {!! create_label('admin_has_all_permissions', 'Admin has all the permissions', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('current_version', 'Current version', Session::get('locale')) !!}
                                {!! create_label('delete_selected', 'Delete selected', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'delete_selected_alert',
        'Are you sure you want to delete selected record(s)?',
        Session::get('locale'),
    ) !!}
                                {!! create_label('please_wait', 'Please wait...', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('please_select_records_to_delete', 'Please select records to delete.', Session::get('locale')) !!}
                                {!! create_label('something_went_wrong', 'Something went wrong.', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('please_correct_errors', 'Please correct errors.', Session::get('locale')) !!}
                                {!! create_label(
        'project_removed_from_favorite_successfully',
        'Project removed from favorite successfully.',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'project_marked_as_favorite_successfully',
        'Project marked as favorite successfully.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('data_access', 'Data Access', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('all_data_access', 'All Data Access', Session::get('locale')) !!}
                                {!! create_label('allocated_data_access', 'Allocated Data Access', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('date_between', 'Date between', Session::get('locale')) !!}
                                {!! create_label('actor_id', 'Actor ID', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('actor_name', 'Actor name', Session::get('locale')) !!}
                                {!! create_label('actor_type', 'Actor type', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('type_id', 'Type ID', Session::get('locale')) !!}
                                {!! create_label('activity', 'Activity', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('type_title', 'Type title', Session::get('locale')) !!}
                                {!! create_label('select_activity', 'Select activity', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('created', 'Created', Session::get('locale')) !!}
                                {!! create_label('updated', 'Updated', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('duplicated', 'Duplicated', Session::get('locale')) !!}
                                {!! create_label('deleted', 'Deleted', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('updated_status', 'Updated status', Session::get('locale')) !!}
                                {!! create_label('unsigned', 'Unsigned', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('select_type', 'Select type', Session::get('locale')) !!}
                                {!! create_label('upload', 'Upload', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('file_name', 'File name', Session::get('locale')) !!}
                                {!! create_label('file_size', 'File size', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('download', 'Download', Session::get('locale')) !!}
                                {!! create_label('uploaded', 'Uploaded', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('project_media', 'Project media', Session::get('locale')) !!}
                                {!! create_label('task_media', 'Task media', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('media_storage', 'Media storage', Session::get('locale')) !!}
                                {!! create_label('select_storage_type', 'Select storage type', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('local_storage', 'Local storage', Session::get('locale')) !!}
                                {!! create_label('media_storage_settings', 'Media storage settings', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('create_customer', 'Create Customer', Session::get('locale')) !!}
                                {!! create_label('customers', 'Customers', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('register_customer', 'Register Customer', Session::get('locale')) !!}
                                {!! create_label('monthly_revenue', 'Total Revenue (Monthly)', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('percentageChange', 'Change from last month', Session::get('locale')) !!}
                                {!! create_label('monthly_customer', 'Total Customer (Monthly)', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('monthly_subscription', 'Active Subscriptions (Monthly)', Session::get('locale')) !!}
                                {!! create_label('totalPlans', 'Total Plans', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('total_customers', 'Total Customers', Session::get('locale')) !!}
                                {!! create_label('customer_counts', 'Total Count of Customers', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('total_revenue', 'Total Revenue', Session::get('locale')) !!}
                                {!! create_label('subscription_rate', 'Subscription Rate', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('plan_sales', 'Plan Sales', Session::get('locale')) !!}
                                {!! create_label('get_active_subscription_per_plan', 'Active Subscriptions Per Plans', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('recent_transactions', 'Recent Transactions', Session::get('locale')) !!}
                                {!! create_label('recently_added_transactions', 'Recently Added Transactions', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('top_customers', 'Top Customers', Session::get('locale')) !!}
                                {!! create_label('topCustomers', 'Top 5 Customers by Maximum Purchase', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('create_plan', 'Create Plan', Session::get('locale')) !!}
                                {!! create_label('plans', 'Plans', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('max_projects', 'Maximum Projects', Session::get('locale')) !!}
                                {!! create_label('max_clients', 'Maximum Clients', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('max_team_members', 'Maximum Team Members', Session::get('locale')) !!}
                                {!! create_label('max_workspaces', 'Maximum Workspaces', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('plan_tenure', 'Plan Tenure', Session::get('locale')) !!}
                                {!! create_label('paid', 'Paid', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('price', 'Price', Session::get('locale')) !!}
                                {!! create_label('discounted_price', 'Discounted Price', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('monthly', 'Monthly', Session::get('locale')) !!}
                                {!! create_label('yearly', 'Yearly', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('lifetime', 'Lifetime', Session::get('locale')) !!}
                                {!! create_label('module_selection', 'Module Selection', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('select_all', 'Select All', Session::get('locale')) !!}
                                {!! create_label('create_plan_button', 'Create Plan', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('plan_type', 'Plan Type', Session::get('locale')) !!}
                                {!! create_label('modules', 'Modules', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('monthly_price', 'Monthly Price', Session::get('locale')) !!}
                                {!! create_label('monthly_discounted_price', 'Monthly Discounted Price', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('yearly_price', 'Yearly Price', Session::get('locale')) !!}
                                {!! create_label('yearly_discounted_price', 'Yearly Discounted Price', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('lifetime_price', 'Lifetime Price', Session::get('locale')) !!}
                                {!! create_label('lifetime_discounted_price', 'Lifetime Discounted Price', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('edit_plan', 'Edit Plan', Session::get('locale')) !!}
                                {!! create_label('update_plan_button', 'Update Plan', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('create_subscription', 'Create Subscription', Session::get('locale')) !!}
                                {!! create_label('subscriptions', 'Subscriptions', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('select_plan', 'Select Plan', Session::get('locale')) !!}
                                {!! create_label('select_user', 'Select User', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('select_tenure', 'Select Tenure', Session::get('locale')) !!}
                                {!! create_label('charging_price', 'Charging Price', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('starts_at', 'Starts at', Session::get('locale')) !!}
                                {!! create_label('ends_at', 'Ends at', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('payment_method', 'Payment Method', Session::get('locale')) !!}
                                {!! create_label('offline', 'Offline', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('bank_transfer', 'Bank Transfer', Session::get('locale')) !!}
                                {!! create_label('payment_gateway', 'Payment Gateway', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('plan_features', 'Plan Features', Session::get('locale')) !!}
                                {!! create_label('user_name', 'User Name', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('plan_name', 'Plan Name', Session::get('locale')) !!}
                                {!! create_label('tenure', 'Tenure', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('features', 'Features', Session::get('locale')) !!}
                                {!! create_label('charging_currency', 'Charging Currency', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('upgrade_subscriptions', 'Upgrade Subscriptions', Session::get('locale')) !!}
                                {!! create_label('transactions', 'Transactions', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('subscription_id', 'Subscription Id', Session::get('locale')) !!}
                                {!! create_label('user_id', 'User Id', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('user_name', 'User Name', Session::get('locale')) !!}
                                {!! create_label('amount', 'Amount', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('transaction_id', 'Transaction ID', Session::get('locale')) !!}

                                {!! create_label('created_date', 'Created Date ', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('buy_plan', 'Buy Plan', Session::get('locale')) !!}
                                {!! create_label('subscription_plan', 'Subscription Plan', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('pricing_plans', 'Pricing Plans', Session::get('locale')) !!}
                                {!! create_label(
        'buy_plan_description1',
        'All plans include advanced tools and features to boost your productivity<br>Choose the best
                                        plan to fit your needs',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('proceed', 'Proceed', Session::get('locale')) !!}
                                {!! create_label('checkout', 'Checkout', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'checkoutDescription1',
        'All plans include advanced tools and features to boost your product.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('plan_details', 'Plan Details', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('payment_methods', 'Payment Methods', Session::get('locale')) !!}
                                {!! create_label('paypal', 'Paypal', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('phonepe', 'PhonePe', Session::get('locale')) !!}
                                {!! create_label('stripe', 'Stripe', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('paystack', 'Paystack', Session::get('locale')) !!}
                                {!! create_label('order_summary', 'Order Summary', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('change_plan', 'Change Plan', Session::get('locale')) !!}
                                {!! create_label(
        'order_accept',
        'By continuing, you accept to our Terms of Services and Privacy Policy. Please note that
                                        payments are non-refundable',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('proceed_with_payment', 'Proceed with Payment', Session::get('locale')) !!}
                                {!! create_label('remaining_days', 'Remaining Days', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('current_plan', 'Current Plan', Session::get('locale')) !!}
                                {!! create_label('my_subscription', 'My Subscription', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'mySubscriptionDesc1',
        'Here is a detail of your current subscriptions',
        Session::get('locale'),
    ) !!}
                                {!! create_label('started_on', 'Started On', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('end_date', 'End Date', Session::get('locale')) !!}
                                {!! create_label('my_transactions', 'My Transactions', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('myTrnxDesc1', 'Here is a detail of your all transactions', Session::get('locale')) !!}
                                {!! create_label(
        'subscription_support_closing',
        ' Were here to ensure a smooth onboarding experience for you.',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('payment_successfull', 'Payment Successfull !!!', Session::get('locale')) !!}
                                {!! create_label(
        'subscription_support',
        'If you haven\'t received your subscription after 30 minutes, please don\'t hesitate to
                                        contact our friendly support team at ',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'subscription_added',
        'Your subscription request has been received successfully. We\'re working on activating your
                                        account right now, and this process typically takes up to 30 minutes to complete.',

        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'subscription_closing',
        'Were thrilled to have you as a new subscriber and look forward to providing you with an
                                        exceptional [product/service name] experience. Thank you for choosing us!',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('payment_failed', 'Payment Failed !!!', Session::get('locale')) !!}
                                {!! create_label(
        'subscription_failed',
        'Your Subcription Is Not Successfully Added , Some Error Occured ',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'orderSummaryDecs',
        'It can help you manage and service orders before,<br> during and after fulfilment',
        Session::get('locale'),
    ) !!}
                                {!! create_label('create_estimate_invoice', 'Create Estimate / Invoice', Session::get('locale')) !!}

                            </div>

                            <div class="row">
                                {!! create_label('finance', 'Finance', Session::get('locale')) !!}
                                {!! create_label('taxes', 'Taxes', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('create_tax', 'Create tax', Session::get('locale')) !!}
                                {!! create_label('update_tax', 'Update tax', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('units', 'Units', Session::get('locale')) !!}
                                {!! create_label('create_unit', 'Create unit', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('update_unit', 'Update unit', Session::get('locale')) !!}
                                {!! create_label('items', 'Items', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('create_item', 'Create item', Session::get('locale')) !!}
                                {!! create_label('price', 'Price', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('please_enter_price', 'Please enter price', Session::get('locale')) !!}
                                {!! create_label('unit', 'Unit', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('unit_id', 'Unit ID', Session::get('locale')) !!}
                                {!! create_label('update_item', 'Update item', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('etimates_invoices', 'Estimates/Invoices', Session::get('locale')) !!}
                                {!! create_label('create_estimate_invoice', 'Create estimate/invoice', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('sent', 'Sent', Session::get('locale')) !!}
                                {!! create_label('accepted', 'Accepted', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('draft', 'Draft', Session::get('locale')) !!}
                                {!! create_label('declined', 'Declined', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('expired', 'Expired', Session::get('locale')) !!}
                                {!! create_label('estimate', 'Estimate', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('invoice', 'Invoice', Session::get('locale')) !!}
                                {!! create_label('billing_details', 'Billing details', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('update_billing_details', 'Update billing details', Session::get('locale')) !!}
                                {!! create_label('please_enter_name', 'Please enter name', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('contact', 'Contact', Session::get('locale')) !!}
                                {!! create_label('please_enter_contact', 'Please enter contact', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('apply', 'Apply', Session::get('locale')) !!}
                                {!! create_label(
        'billing_details_updated_successfully',
        'Billing details updated successfully.',
        Session::get('locale'),
    ) !!}
                            </div>

                            <div class="row">
                                {!! create_label('note', 'Note', Session::get('locale')) !!}
                                {!! create_label('from_date', 'From date', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('to_date', 'To date', Session::get('locale')) !!}
                                {!! create_label('personal_note', 'Personal note', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'please_enter_personal_note_if_any',
        'Please enter personal note if any',
        Session::get('locale'),
    ) !!}
                                {!! create_label('item', 'Item', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('manage_items', 'Manage items', Session::get('locale')) !!}
                                {!! create_label('product_service', 'Product/Service', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('quantity', 'Quantity', Session::get('locale')) !!}
                                {!! create_label('rate', 'Rate', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('tax', 'Tax', Session::get('locale')) !!}
                                {!! create_label('sub_total', 'Sub total', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('final_total', 'Final total', Session::get('locale')) !!}
                                {!! create_label('etimate_invoice', 'Estimate/Invoice', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('estimate_id_prefix', 'EST-', Session::get('locale')) !!}
                                {!! create_label('invoice_id_prefix', 'INV-', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('update_estimate', 'Update estimate', Session::get('locale')) !!}
                                {!! create_label('estimate_details', 'Estimate details', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('invoice_details', 'Invoice details', Session::get('locale')) !!}
                                {!! create_label('estimate_summary', 'Estimate summary', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('invoice_summary', 'Invoice summary', Session::get('locale')) !!}
                                {!! create_label('select_unit', 'Select unit', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('estimate_no', 'Estimate No.', Session::get('locale')) !!}
                                {!! create_label('invoice_no', 'Invoice No.', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('storage_type_set_as_aws_s3', 'Storage type is set as AWS S3 storage', Session::get('locale')) !!}
                                {!! create_label('storage_type_set_as_local', 'Storage type is set as local storage', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('click_here_to_change', 'Click here to change', Session::get('locale')) !!}
                                {!! create_label('expenses', 'Expenses', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('expenses_types', 'Expense types', Session::get('locale')) !!}
                                {!! create_label('create_expense', 'Create expense', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('update_expense_type', 'Update expense type', Session::get('locale')) !!}
                                {!! create_label('expenses', 'Expenses', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('create_expense', 'Create expense', Session::get('locale')) !!}
                                {!! create_label('expense_type', 'Expense type', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('expense_date', 'Expense date', Session::get('locale')) !!}
                                {!! create_label('update_expense', 'Update expense', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('payments', 'Payments', Session::get('locale')) !!}
                                {!! create_label('create_payment', 'Create payment', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('payment_id', 'Payment ID', Session::get('locale')) !!}
                                {!! create_label('user_id', 'User ID', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('invoice_id', 'Invoice ID', Session::get('locale')) !!}
                                {!! create_label('payment_method_id', 'Payment method ID', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('payment_date_between', 'Payment date between', Session::get('locale')) !!}
                                {!! create_label('update_payment', 'Update payment', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('select_invoice', 'Select invoice', Session::get('locale')) !!}
                                {!! create_label('select_payment_method', 'Select payment method', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('fully_paid', 'Fully paid', Session::get('locale')) !!}
                                {!! create_label('partially_paid', 'Partially paid', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('estimates', 'Estimates', Session::get('locale')) !!}
                                {!! create_label('invoices', 'Invoices', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('amount_left', 'Amount left', Session::get('locale')) !!}
                                {!! create_label('not_specified', 'Not specified', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('no_payments_found_invoice', 'No payments found for this invoice.', Session::get('locale')) !!}
                                {!! create_label('no_items_found', 'No items found', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('update_invoice', 'Update invoice', Session::get('locale')) !!}
                                {!! create_label('view_estimate', 'View estimate', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('view_invoice', 'View invoice', Session::get('locale')) !!}
                                {!! create_label('currency_symbol_position', 'Currency symbol position', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('before', 'Before', Session::get('locale')) !!}
                                {!! create_label('after', 'After', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('currency_formate', 'Currency formate', Session::get('locale')) !!}
                                {!! create_label('comma_separated', 'Comma separated', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('dot_separated', 'Dot separated', Session::get('locale')) !!}
                                {!! create_label('decimal_points_in_currency', 'Decimal points in currency', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('project_milestones', 'Project milestones', Session::get('locale')) !!}
                                {!! create_label('create_milestone', 'Create milestone', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('incomplete', 'Incomplete', Session::get('locale')) !!}
                                {!! create_label('complete', 'Complete', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('cost', 'Cost', Session::get('locale')) !!}
                                {!! create_label('please_enter_cost', 'Please enter cost', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('progress', 'Progress', Session::get('locale')) !!}
                                {!! create_label('update_milestone', 'Update milestone', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('learn_more', 'Learn more', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('monthly', 'Mothly', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('annual', 'Annual', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('lifetime', 'Lifetime', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('privacy_policy', 'Privacy Policy', Session::get('locale')) !!}
                                {!! create_label('save', 'Save', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('refund_policy', 'Refund Policy', Session::get('locale')) !!}
                                {!! create_label('terms_and_conditions', 'Terms and Conditons', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('paypal_client_id', 'PayPal Client Id', Session::get('locale')) !!}
                                {!! create_label('paypal_secret_key', 'PayPal Secret Key', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('payment_mode', 'Payment Mode', Session::get('locale')) !!}
                                {!! create_label('sandbox', 'Sandbox (Testing)', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('production', 'Production (Live)', Session::get('locale')) !!}
                                {!! create_label('paypal_business_email', 'PayPal Business Email ID', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label(
        'notification_url',
        'Notification Url (Set this as IPN notification URL in you PayPal account)',
        Session::get('locale'),
    ) !!}
                                {!! create_label('currency_code', 'Currency Code', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('merchant_id', 'Merchant Id', Session::get('locale')) !!}
                                {!! create_label('terms_and_conditions', 'Terms and Conditons', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('app_id', 'App Id', Session::get('locale')) !!}
                                {!! create_label('salt_index', 'Salt Index', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('salt_key', 'Salt Key', Session::get('locale')) !!}
                                {!! create_label('phonepe_mode', 'PhonePe Mode [ SANDBOX / UAT / PRODUCTION ]', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('sandbox', 'Sandbox (Testing)', Session::get('locale')) !!}
                                {!! create_label('production', 'Production (Live)', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('UAT', 'UAT', Session::get('locale')) !!}
                                {!! create_label('payment_endpoint_url', 'Payment Endpoint Url', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('stripe_publishable_key', 'Stripe Publishable Key', Session::get('locale')) !!}
                                {!! create_label('stripe_secret_key', 'Stripe Secret Key', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('stripe_webhook_secret_key', 'Stripe Webhook Secret Key', Session::get('locale')) !!}
                                {!! create_label('paystack_key_id', 'Paystack Key Id', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('payment_mode', 'Payment Mode [ SANDBOX / PRODUCTION ]', Session::get('locale')) !!}
                                {!! create_label('paystack_secret_key', 'Paystack Secret Key', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('create_customer', 'Create Customer', Session::get('locale')) !!}
                                {!! create_label('payment_endpoint_url', 'Payment Endpoint Url', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label(
        'homeDesc1',
        'Unleash peak productivity with this system, your one-stop cloud-based project management
                                        platform. Streamline workflows, boost team collaboration, and stay ahead of deadlines. This
                                        system empowers you to effortlessly create tasks, assign them to team members, and track
                                        progress in real-time.',

        Session::get('locale'),
    ) !!}
                                {!! create_label('get_started', 'Get Started', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('contact_us', 'Contact Us', Session::get('locale')) !!}
                                {!! create_label('streamline_projects', 'Streamline Your Projects with', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'streamlineProjectDesc',
        'Take control of your projects and boost team productivity with ' .
        $general_settings['company_title'] .
        ' ,the all-in-one project management and task management solution. Our cloud-based platform
                                            empowers you to effortlessly organize projects, collaborate with your team, and track
                                            progress – all in one place.',

        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'effortlessOrganizationDesc',
        'provides a centralized hub to create, manage, and track all your projects. Say goodbye to
                                        scattered tasks and missed deadlines – our intuitive interface keeps everything organized
                                        and accessible.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('effortless_organization', 'Effortless Organization', Session::get('locale')) !!}
                                {!! create_label('seamless_collaboration', 'Seamless Collaboration', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'seamlessCollaborationDesc',
        'Foster a collaborative work environment with ' .
        $general_settings['company_title'] .
        '. Assign tasks,share files, and communicate effectively with your team in real-time. Ensure
                                            everyone is on the same page and working towards a common goal.',

        Session::get('locale'),
    ) !!}
                                {!! create_label('visualize_project_health', 'Visualize Project Health', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'visualizeProjectDesc',
        'Get insightful dashboards and reports to monitor project performance and identify areas for
                                        improvement.',

        Session::get('locale'),
    ) !!}
                                {!! create_label('pricingDesc', 'Choose the Plan That Fits Your Needs', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('one_time_payment', 'one time payment', Session::get('locale')) !!}
                                {!! create_label('sign_in', 'Sign In', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('about_us', 'About Us', Session::get('locale')) !!}
                                {!! create_label('faqs', 'FAQs', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('quick_links', 'Quick Links', Session::get('locale')) !!}
                                {!! create_label('contact_info_here', 'Contact Information Here', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'about_us_desc1',
        'We are passionate about empowering teams to achieve peak productivity.',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'about_us_desc2',
        'was born from the frustration of juggling complex projects and scattered tasks. We
                                        envisioned a better way – a cloud-based platform that streamlines workflows, fosters
                                        seamless collaboration, and empowers teams to accomplish more.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'about_us_desc3',
        'is a powerful project management and task management solution trusted by businesses of all
                                        sizes. We are dedicated to continuous innovation, ensuring our platform remains at the
                                        forefront of project management technology.',

        Session::get('locale'),
    ) !!}
                                {!! create_label('our_mission', 'Our Mission', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'ourMissionDesc',
        'To simplify project management and empower teams to achieve remarkable results.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('our_values', 'Our Values', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'our_values_Desc1',
        'Innovation: We are constantly seeking new ways to improve',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'our_values_Desc2',
        'and push the boundaries of project management software.',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'our_values_Desc3',
        'Collaboration: We believe in the power of teamwork and strive to create a platform that
                                        fosters seamless communication and collaboration.',

        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'our_values_Desc4',
        'Customer Focus: We are dedicated to providing exceptional customer support and ensuring our
                                        users have the tools they need to succeed.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('why_choose', 'Why Choose', Session::get('locale')) !!}
                                {!! create_label(
        'whyChooseDesc',
        'In today fast-paced world, managing projects and teams effectively can be a challenge. Our
                                        system is here to help you streamline your workflow, boost productivity, and achieve your
                                        goals. Here why our system stands out from the crowd',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'simple_and_intuitive_project_management',
        'Simple and Intuitive Project Management',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'simpleIntuitiveDesc',
        'No more learning curves: Our user-friendly interface makes it easy for anyone to get
                                        started, regardless of technical expertise. Visualize your projects with intuitive
                                        dashboards and customizable views.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'effective_task_organization_with_workspaces_and_statuses',
        'Effective Task Organization with Workspaces and Statuses',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'effectiveTaskDesc',
        'Organize chaos: Break down complex projects into manageable tasks and subtasks using our
                                        flexible workspace system. Keep track of progress with customizable task statuses (e.g., "To
                                        Do," "In Progress," "Completed") and prioritize effectively by highlighting critical
                                        tasks.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'improved_team_collaboration_and_communication',
        'Improved Team Collaboration and Communication',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'improvedTeamDesc',
        'Break down silos: Foster seamless collaboration with built-in communication tools like
                                        comments, mentions, and discussions. Stay on the same page with real-time task updates and
                                        activity feeds, ensuring everyone is informed. Centralize all project-related information,
                                        documents, and files in one easily accessible location.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'increased_productivity_and_efficiency',
        'Increased Productivity and Efficiency',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'increasedProductivityDesc',
        'Automate repetitive tasks to free up valuable time. Minimize distractions and streamline
                                        your workflow with centralized task management. Meet deadlines with confidence with built-in
                                        time tracking, milestone management, and progress reporting.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'aboutUsCTA',
        'Ready to experience the ' .
        $general_settings['company_title'] .
        'difference? Sign up and see how we can help your team achieve more!',
        Session::get('locale'),
    ) !!}

                            </div>
                            <div class="row">
                                {!! create_label(
        'taskify_features_heading',
        $general_settings['company_title'] . ' Powerful Features for Efficient Project Management',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'taskify_features_subheading',
        'Streamline your team\'s workflow and boost productivity with ' .
        $general_settings['company_title'] .
        ' comprehensive set of features.',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('project_management', 'Project Management', Session::get('locale')) !!}
                                {!! create_label(
        'project_management_desc',
        'Create and manage multiple projects with ease, ensuring seamless collaboration and
                                        organization.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('task_tracking', 'Task Tracking', Session::get('locale')) !!}
                                {!! create_label(
        'task_tracking_desc',
        'Assign, prioritize, and track tasks efficiently, keeping your team on top of their
                                        workload.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('user_management', 'User Management', Session::get('locale')) !!}
                                {!! create_label(
        'user_management_desc',
        'Manage user roles, permissions, and access levels, ensuring secure collaboration and data
                                        privacy.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('client_management', 'Client Management', Session::get('locale')) !!}
                                {!! create_label(
        'client_management_desc',
        'Streamline communication and manage client relationships with dedicated client portals.',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('contract_management', 'Contract Management', Session::get('locale')) !!}
                                {!! create_label(
        'contract_management_desc',
        'Create, store, and manage contracts seamlessly, ensuring compliance and transparency.',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('reporting', 'Reporting and Analytics', Session::get('locale')) !!}
                                {!! create_label(
        'reporting_desc',
        'Gain insights into project performance with comprehensive reporting and analytics
                                        features.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('collaboration', 'Collaboration', Session::get('locale')) !!}
                                {!! create_label(
        'collaboration_desc',
        'Foster seamless communication and collaboration with built-in chat, file sharing, and
                                        documentation features.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('time_tracking', 'Time Tracking', Session::get('locale')) !!}
                                {!! create_label(
        'time_tracking_desc',
        'Monitor time spent on tasks and projects, enabling accurate billing and productivity
                                        analysis.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('integrations', 'Integrations', Session::get('locale')) !!}
                                {!! create_label(
        'integrations_desc',
        'Connect Taskify with your favorite tools and services for a seamless workflow experience.',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('payment_gateways', 'Payment Gateways', Session::get('locale')) !!}
                                {!! create_label(
        'payment_gateways_desc',
        'Accept payments securely through integrated gateways like Stripe, PayPal, Paystack, and
                                        PhonePe.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('security', 'Security and Compliance', Session::get('locale')) !!}
                                {!! create_label(
        'security_desc',
        'Enjoy peace of mind with robust security measures and compliance with industry standards.',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('customization', 'Customization', Session::get('locale')) !!}
                                {!! create_label(
        'customization_desc',
        'Tailor Taskify to your specific needs with our flexible customization options and
                                        integrations.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('frequently_asked_questions', 'Frequently Asked Questions', Session::get('locale')) !!}
                                {!! create_label(
        'faqSubheading',
        'Find answers to common questions about our Project & Task Management System.',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'what_is_a_project_management_system',
        'What is a project management system?',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'what_is_a_project_management_system_answer',
        'A project management system is a software tool designed to help teams plan, execute, and
                                        manage projects from initiation to completion. It facilitates collaboration, task
                                        allocation, scheduling, and tracking of project progress.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'key_features_of_project_management_system',
        'What are the key features of a project management system?',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'key_features_of_project_management_system_answer',
        'Key features typically include task management, team collaboration, project planning and
                                        scheduling, time tracking, file sharing, reporting and analytics, and integration with other
                                        tools.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'benefits_of_project_management_system',
        'How does a project management system benefit businesses?',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'benefits_of_project_management_system_answer',
        'Project management systems improve productivity and efficiency by streamlining workflows,
                                        enabling better communication and collaboration among team members, providing transparency
                                        into project progress, and facilitating effective resource allocation.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'task_management_in_project_management_system',
        'What is task management in the context of project management systems?',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'task_management_in_project_management_system_answer',
        'Task management involves creating, assigning, tracking, and organizing individual tasks
                                        within a project. It helps ensure that team members are aware of their responsibilities and
                                        deadlines, and allows for better coordination and prioritization of work.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'task_management_contribution_to_project_success',
        'How does task management contribute to project success?',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'task_management_contribution_to_project_success_answer',
        'Effective task management ensures that project activities are completed on time and within
                                        budget, minimizes delays and bottlenecks, identifies potential issues early on, and enables
                                        efficient resource utilization.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'multiple_projects_handling',
        'Can a project management system handle multiple projects simultaneously?',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'multiple_projects_handling_answer',
        'Yes, most project management systems are designed to support the management of multiple
                                        projects concurrently. They typically provide features for organizing projects into separate
                                        workspaces or folders, allowing teams to easily switch between projects.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'customization_of_project_management_system',
        'Is it possible to customize project management systems to fit specific project
                                        requirements?',

        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'customization_of_project_management_system_answer',
        'Many project management systems offer customization options such as creating custom task
                                        types, defining project-specific workflows, adding custom fields, and integrating with other
                                        tools to adapt to the unique needs of different projects or industries.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'security_of_project_management_system',
        'How secure are project management systems for storing sensitive project data?',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'security_of_project_management_system_answer',
        'Project management systems prioritize data security and typically employ measures such as
                                        encryption, user authentication, access control, and regular data backups to safeguard
                                        sensitive project information from unauthorized access, loss, or theft.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'integration_with_other_tools',
        'Can project management systems integrate with other tools and applications?',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'integration_with_other_tools_answer',
        'Yes, project management systems often offer integrations with popular productivity tools,
                                        communication platforms, file storage services, and software development tools to streamline
                                        workflows and enhance collaboration across teams.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'choosing_right_project_management_system',
        'How do I choose the right project management system for my team?',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'choosing_right_project_management_system_answer',
        'When selecting a project management system, consider factors such as your team "\"s size
                                        and requirements, the complexity of your projects, ease of use, scalability, customization
                                        options, pricing, customer support, and compatibility with existing tools and workflows.
                                        It\'s also helpful to try out different systems through free trials or demos to evaluate
                                        their suitability for your needs.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'contact_us_subheading',
        'Have questions or need support? Reach out to us!',
        Session::get('locale'),
    ) !!}
                                {!! create_label('your_name', 'Your Name', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('your_email', 'Your Email', Session::get('locale')) !!}
                                {!! create_label('enter_your_name', 'Enter your name', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('enter_your_email', 'Enter your email', Session::get('locale')) !!}
                                {!! create_label('your_message', 'Your Message', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('enter_your_message', 'Enter your message', Session::get('locale')) !!}
                                {!! create_label('submit', 'Submit', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('login', 'Login', Session::get('locale')) !!}
                                {!! create_label('register', 'Register ', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('forgot_password', 'Forgot Password?', Session::get('locale')) !!}
                                {!! create_label('login_register_heading', 'Login or Register', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'login_register_subheading',
        'Access your account or create a new one to start managing your projects.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('enter_your_password', 'Please enter your password', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('enter_your_phone_number', 'Please Enter YourPhone Number', Session::get('locale')) !!}
                                {!! create_label('activity_log', 'Activity Log', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('active_subscription', 'Active Subscription', Session::get('locale')) !!}
                                {!! create_label('renew_manage_plan', 'Renew or Manage Plan', Session::get('locale')) !!}
                                {!! create_label('subscription_history', 'Subscription History', Session::get('locale')) !!}
                                {!! create_label('explore_more_features', 'Explore More Features', Session::get('locale')) !!}
                                {!! create_label(
        'taskify_features_heading2',
        'Empower your workflow with features designed to streamline your day.',
        Session::get('locale'),
    ) !!}

                            </div>
                            <div class="row">
                                {!! create_label('create_new_plan', 'Create New Plan', Session::get('locale')) !!}
                                {!! create_label('plan_image', 'Plan Image', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('inactive', 'Inactive', Session::get('locale')) !!}
                                {!! create_label('read_more_about_us', 'Read More About Us', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('maximizing_efficiency', 'Maximizing Efficiency', Session::get('locale')) !!}
                                {!! create_label('pricing', 'Pricing', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('see_our_pricing', 'See our pricing', Session::get('locale')) !!}
                                {!! create_label(
        'seePricingDesc',
        'You have Free Unlimited Updates and Premium Support on each package.',
        Session::get('locale'),
    ) !!}
                            </div>

                            <div class="row">
                                {!! create_label(
        'maxEffiencyDesc',
        'Efficiency is paramount in project management. We streamline processes, foster teamwork,
                                        and minimize inefficiencies, ensuring smooth project execution and success.',

        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'enter_your_emailDesc',
        'Enter your email and we will send you password reset link',
        Session::get('locale'),
    ) !!}
                                {!! create_label('back_to_login', 'Back to login', Session::get('local')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('team_collaboration', 'Team Collaboration', Session::get('locale')) !!}
                                {!! create_label(
        'teamCollabDesc',
        'Enhance team productivity and communication with our intuitive collaboration
                                        platform,facilitating seamless coordination and information sharing.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label('login_as_superadmin', 'Login As SuperAdmin', Session::get('locale')) !!}
                                {!! create_label('login_as_admin', 'Login As Admin', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('login_as_team_member', 'Login As Team Member', Session::get('locale')) !!}
                                {!! create_label('login_as_client', 'Login As Client', Session::get('locale')) !!}
                                {!! create_label(
        'empowering_teamsDesc',
        'Empowering Teams: Your Path to Productivity and Success',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">

                                {!! create_label('unlimited', 'Unilimited', Session::get('locale')) !!}
                                {!! create_label(
        'faqsDesc',
        'A lot of people dont appreciate the moment until its passed. I am not trying my hardest,
                                        and I am not trying to do',

        Session::get('locale'),
    ) !!}

                            </div>
                            <div class="row">
                                {!! create_label('chat_messages', 'Chat Messages', Session::get('locale')) !!}
                                {!! create_label(
        'chat_messagesDesc',
        'Enable real-time communication among team members with built-in chat messaging.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('virtual_meetings', 'Virtual Meetings', Session::get('locale')) !!}
                                {!! create_label(
        'virtual_meetingsDesc',
        'Organize virtual meetings and video conferences to facilitate remote collaboration.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('payslips', 'Payslips', Session::get('locale')) !!}
                                {!! create_label(
        'payslipsDesc',
        'Generate and distribute payslips to employees securely,ensuring transparency in payroll
                                        management.',

        Session::get('locale'),
    ) !!}
                                {!! create_label('finance_management', 'Finance Management', Session::get('locale')) !!}
                                {!! create_label(
        'finance_managementDesc',
        'Track expenses, manage budgets, and calculate taxes to maintain financial stability and
                                        compliance.',

        Session::get('locale'),
    ) !!}
                                {!! create_label('team_engagement', 'Team Engagement', Session::get('locale')) !!}
                                {!! create_label(
        'team_engagementDesc',
        'Celebrate upcoming birthdays and work anniversaries, and stay updated on team members leave
                                        status to foster a positive work environment.',

        Session::get('locale'),
    ) !!}
                                {!! create_label('elegant_dashboard', 'Elegant Dashboard', Session::get('locale')) !!}
                                {!! create_label(
        'elegant_dashboardDesc',
        'Access a visually appealing and comprehensive dashboard that provides key insights and
                                        metrics about your projects and tasks.',

        Session::get('locale'),
    ) !!}
                                {!! create_label('multi_language_support', 'Multi-Language Support', Session::get('locale')) !!}
                                {!! create_label(
        'multi_language_supportDesc',
        'Enable users to switch between multiple languages to accommodate diverse teams and
                                        clients.',

        Session::get('locale'),
    ) !!}
                                {!! create_label('workspace_management', 'Workspace Management', Session::get('locale')) !!}
                                {!! create_label(
        'workspace_managementDesc',
        'Organize projects, tasks, and team members into separate workspaces for better organization
                                        and efficiency.',

        Session::get('locale'),
    ) !!}

                                {!! create_label('explore_more_plans', 'Explore More Plans', Session::get('locale')) !!}

                            </div>

                            <div class="row">

                                {!! create_label(
        'project_management_and_task_management_system',
        'Project Management and Task Management System',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'enhance_team_collaboration_and_productivity',
        'Enhance Team Collaboration and Productivity',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'streamlined_collaboration_for_productivity',
        'Streamlined collaboration for productivity',
        Session::get('locale'),
    ) !!}
                                {!! create_label('manage_projects_efficiently', 'Manage Projects Efficiently', Session::get('locale')) !!}
                                {!! create_label(
        'simplify_project_organization_for_focus',
        'Simplify project organization for focus.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('assign_and_monitor_tasks', 'Assign and Monitor Tasks', Session::get('locale')) !!}
                                {!! create_label('assign_and_monitor_tasks', 'Assign and Monitor Tasks', Session::get('locale')) !!}
                                {!! create_label(
        'assign_track_and_meet_deadlines',
        'Easily assign tasks to team members, set priorities, and track progress in real-time to ensure nothing falls through the cracks.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('enhance_collaboration', 'Enhance Collaboration', Session::get('locale')) !!}
                                {!! create_label(
        'seamless_collaboration_for_success',
        'Connect your team with real-time communication, shared tasks, and seamless file sharing to boost teamwork and productivity.',
        Session::get('locale'),
    ) !!}

                                {!! create_label('user_friendly_no_learning_curve', 'User-friendly, no learning curve.', Session::get('locale')) !!}
                                {!! create_label('non_tech_users_start_easily', 'Non-tech users start easily.', Session::get('locale')) !!}
                                {!! create_label('intuitive_project_dashboards', 'Intuitive project dashboards.', Session::get('locale')) !!}
                                {!! create_label('customizable_project_views', 'Customizable project views.', Session::get('locale')) !!}
                                {!! create_label('task_management', 'Task Management', Session::get('locale')) !!}

                                {!! create_label(
        'subdivide_tasks_for_organization',
        'Subdivide tasks for organization.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('customizable_progress_tracking', 'Customizable progress tracking.', Session::get('locale')) !!}
                                {!! create_label('flexible_project_workspace', 'Flexible project workspace.', Session::get('locale')) !!}
                                {!! create_label('highlight_critical_tasks', 'Highlight critical tasks.', Session::get('locale')) !!}
                                {!! create_label('seamless_collaboration_tools', 'Seamless collaboration tools.', Session::get('locale')) !!}
                                {!! create_label('real_time_task_updates', 'Real-time task updates.', Session::get('locale')) !!}
                                {!! create_label('centralized_project_data', 'Centralize project data', Session::get('locale')) !!}
                                {!! create_label('foster_teamwork_break_silos', 'Foster teamwork, break silos', Session::get('locale')) !!}
                                {!! create_label('centralized_focused_workflow', 'Centralized, focused workflow', Session::get('locale')) !!}
                                {!! create_label('confident_deadline_tracking', 'Confident deadline tracking', Session::get('locale')) !!}
                                {!! create_label('efficient_streamlined_processes', 'Efficient streamlined processes', Session::get('locale')) !!}
                                {!! create_label('increased_productivity', 'Increased Productivity', Session::get('locale')) !!}

                                {!! create_label(
        'progress_ensures_timely_completion',
        'Progress ensures timely completion.',
        Session::get('locale'),
    ) !!}
                                {!! create_label('system_overview', 'System Overview', Session::get('locale')) !!}
                                {!! create_label('discover_our_system', 'Discover Our System', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('buy_now', 'Buy now', Session::get('locale')) !!}
                                {!! create_label('subcription_rate_of_plans', 'Subscription Rate of Plans', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('total_customers', 'Total Customers', Session::get('locale')) !!}
                                {!! create_label('total_count_of_customers', 'Total Count of Customers', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('total_revrenue', 'Total Revenue', Session::get('locale')) !!}
                                {!! create_label('total_revenue_obtained', 'Total Revenue obtained', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('my_profile', 'My Profile', Session::get('locale')) !!}
                                {!! create_label('logout', 'Logout', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'manage_tasks_and_assignments_efficiently',
        'Manage tasks and assignments efficiently.',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'take_and_organize_notes_for_better_productivity',
        'Take and organize notes for better productivity',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'schedule_and_organize_meetings_with_team_members',
        'Schedule and organize meetings with team members',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'communicate_with_team_members_in_real_time',
        'Communicate with team members in real time',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'create_and_manage_to_do_lists_for_tasks_and_projects',
        'Create and manage to do lists for tasks and projects',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'manage_contracts_and_agreements_with_clients',
        'Manage contracts and agreements with clients',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'view_and_manage_payslips_for_employees',
        'View and manage payslips for employees',
        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'create_and_manange_expenses_payments_and_invoice_estimates',
        'Create and Manange Expenses Payments and Invoice Estimates',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'if_you_want_to_make_this_plan_free_turn_this_off',
        'If you want to make this plan free turn this off',
        Session::get('locale'),
    ) !!}
                                {!! create_label('upgrade', 'Upgrade', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('delete', 'Delete', Session::get('locale')) !!}
                                {!! create_label('transactions', 'Transactions', Session::get('locale')) !!}
                            </div>

                            <div class="row">
                                {!! create_label('favicon', 'Favicon', Session::get('locale')) !!}
                                {!! create_label('footer_logo', 'Footer Logo', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('general_settings', 'General settings', Session::get('locale')) !!}
                                {!! create_label('email_settings', 'E-mail settings', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('payment_method_settings', 'Payment method settings', Session::get('locale')) !!}
                                {!! create_label('support_email', 'Support Email', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'superadmin_has_all_permissions',
        'Super Admin has all the permissions',
        Session::get('locale'),
    ) !!}
                                {!! create_label('delete_selected', 'Deleted selected', Session::get('locale')) !!}
                            </div>
                            <div class="row">

                                {!! create_label('value_must_be_greater_then_0', 'Value must be greater then 0', Session::get('locale')) !!}
                                {!! create_label('not_greater_then_100', 'Not greater Then 100', Session::get('locale')) !!}
                            </div>
                            {{-- Update 1.0.3 Labels --}}
                            <div class="row">
                                {!! create_label('members_on_leave', 'Members on leave', Session::get('locale')) !!}
                                {!! create_label('clients', 'Clients', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('filter_by_status', 'Filter by status', Session::get('locale')) !!}
                                {!! create_label('color', 'COLOR', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('enter_title', 'Enter Title', Session::get('locale')) !!}
                                {!! create_label('not_found', 'Not Found', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'like_admin_selected_users_will_be_able_to_update_and_create_leaves_for_other_members',
        'Like admin, selected users will be able to update and create leavesfor other members',
        Session::get('locale'),
    ) !!}
                                {!! create_label('member', 'Member', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('start_date', 'Start date', Session::get('locale')) !!}
                                {!! create_label('file', 'File', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('notification_templates', 'Notification Templates', Session::get('locale')) !!}
                                {!! create_label('sms', 'SMS', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('whatsapp', 'WhatsApp', Session::get('locale')) !!}
                                {!! create_label('system', 'System', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('default_email_template_info', 'A Default Subject and Message Will Be Used if a Specific Email Notification Template Is Not Set', Session::get('locale')) !!}
                                {!! create_label('default_sms_template_info', 'A Default Message Will Be Used if a Specific SMS Notification Template Is Not Set.', Session::get('locale')) !!}

                            </div>
                            <div class="row">
                                {!! create_label('default_whatsapp_template_info', 'A Default Message Will Be Used if a Specific WhatsApp Notification Template Is Not Set.', Session::get('locale')) !!}
                                {!! create_label('default_system_template_info', 'A Default Title and Message Will Be Used if a Specific System Notification Template Is Not Set.', Session::get('locale')) !!}

                            </div>
                            <div class="row">
                                {!! create_label('workspace_assignment', 'Workspace assignment', Session::get('locale')) !!}
                                {!! create_label('meeting_assignment', 'Meeting assignment', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('assignment', 'Assignment', Session::get('locale')) !!}
                                {!! create_label('status_updation', 'Status Updation', Session::get('locale')) !!}
                            </div>
                            <div class="row">
                                {!! create_label('account_creation_email_info', 'This template will be used for the email notification sent to notify users/clients about the successful creation of their account.', Session::get('locale')) !!}

                                {!! create_label(
        'verify_user_client_email_info',
        'This template will be used for the email sent for verifying new user/client creation.',
        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'forgot_password_email_info',
        'This template will be used for the email notification sent to users/clients to reset their
                                        password if they have forgotten it.',

        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'project_assignment_email_info',
        'This template will be used for the email notification sent to users/clients when they are
                                        assigned a project.',

        Session::get('locale'),
    ) !!}
                            </div>
                            <div class="row">
                                {!! create_label(
        'task_assignment_email_info',
        'This template will be used for the email notification sent to users/clients when they are
                                        assigned a task.',

        Session::get('locale'),
    ) !!}
                                {!! create_label(
        'workspace_assignment_email_info',
        'This template will be used for the email notification sent to users/clients when they are
                                        added to a workspace.',

        Session::get('locale'),
    ) !!}
                            </div>
                        </div>
                        <div class="row">
                            {!! create_label(
        'meeting_assignment_email_info',
        'This template will be used for the email notification sent to users/clients when they are added
                                    to a meeting.',

        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'leave_request_creation_email_info',
        'This Template Will Be Used for the Email notification sent to the Admin and Leave Editors Upon
                                    the Creation of a Leave Request.',

        Session::get('locale'),
    ) !!}
                        </div>
                        <div class="row">
                            {!! create_label('creation', 'Creation', Session::get('locale')) !!}
                            {!! create_label('team_member_on_leave_alert', 'Team Member on Leave Alert', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label(
        'project_assignment_sms_info',
        'This template will be used for the SMS notification sent to users/clients when they are
                                    assigned a project.',

        Session::get('locale'),
    ) !!}
                            {!! create_label('possible_placeholders', 'Possible placeholders', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label(
        'project_assignment_sms_will_not_sent',
        'If Deactive, project assignment SMS won\'t be sent',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'account_creation_email_will_not_sent',
        'If Deactive, account creation email won\'t be sent',
        Session::get('locale'),
    ) !!}
                        </div>
                        <div class="row">
                            {!! create_label(
        'leave_request_creation_whatsapp_will_not_sent',
        'If Deactive, Leave Request Creation Whatsapp Notification Won\'t be Sent',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'project_assignment_email_will_not_sent',
        'If Deactive, project assignment email won\'t be sent',
        Session::get('locale'),
    ) !!}
                        </div>
                        <div class="row">
                            {!! create_label(
        'task_assignment_email_will_not_sent',
        'If Deactive, task assignment email won\'t be sent',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'workspace_assignment_email_will_not_sent',
        'If Deactive, workspace assignment email won\'t be sent',
        Session::get('locale'),
    ) !!}
                        </div>
                        <div class="row">
                            {!! create_label(
        'meeting_assignment_email_will_not_sent',
        'If Deactive, meeting assignment email won\'t be sent',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'leave_request_creation_email_will_not_sent',
        'If Deactive, Leave Request Creation Email Won\'t be Sent',
        Session::get('locale'),
    ) !!}
                        </div>
                        <div class="row">
                            {!! create_label(
        'project_assignment_sms_will_not_sent',
        'If Deactive, project assignment SMS won\'t be sent',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'task_assignment_sms_will_not_sent',
        'If Deactive, task assignment SMS won\'t be sent',
        Session::get('locale'),
    ) !!}
                        </div>
                        <div class="row">
                            {!! create_label(
        'leave_request_creation_whatsapp_will_not_sent',
        'If Deactive, Leave Request Creation Whatsapp Notification Won\'t be Sent',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'project_assignment_whatsapp_will_not_sent',
        'If Deactive, project assignment whatsapp notification won\'t be sent',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'task_assignment_whatsapp_will_not_sent',
        'If Deactive, task assignment whatsapp notification won\'t be sent',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'workspace_assignment_whatsapp_will_not_sent',
        'If Deactive, workspace assignment whatsapp notification won\'t be sent',
        Session::get('locale'),
    ) !!}
                        </div>
                        <div class="row">
                            {!! create_label(
        'meeting_assignment_whatsapp_will_not_sent',
        'If Deactive, meeting assignment whatsapp notification won\'t be sent',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'leave_request_creation_whatsapp_will_not_sent',
        'If Deactive, Leave Request Creation Whatsapp Notification Won\'t be Sent',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'project_status_updation_whatsapp_will_not_sent',
        'If Deactive, Project Status Updation Whatsapp Notification won\'t be Sent',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'team_member_on_leave_alert_email_will_not_sent',
        'If Deactive, Team Member on Leave Alert Email Won\'t be Sent',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'task_status_updation_sms_info',
        'This Template Will Be Used for the SMS notification sent to the Users/Clients Upon the Status
                                Updation of a Task.',
        Session::get('locale'),
    ) !!}
                            {!! create_label('all_available_placeholders', 'All available placeholders', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('available_placeholders', 'Available placeholders', Session::get('locale')) !!}
                            {!! create_label('account_creation', 'Account creation', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('email_verification', 'Email verification', Session::get('locale')) !!}
                            {!! create_label('subject', 'Subject', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'task_assignment_sms_info',
        'This template will be used for the SMS notification sent to users/clients when they are
                                assigned a task.',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'workspace_assignment_sms_info',
        'This template will be used for the SMS notification sent to users/clients when they are added
                                to a workspace.',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'meeting_assignment_sms_info',
        'This template will be used for the SMS notification sent to users/clients when they are added
                                to a meeting.',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'leave_request_creation_sms_info',
        'This Template Will Be Used for the SMS notification sent to the Admin and Leave Editors Upon
                                the Creation of a Leave Request.',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'leave_request_status_updation_sms_info',
        'This Template Will Be Used for the SMS notification sent to the Admin/Leave Editors/Requestee
                                Upon the Status Updation of a Leave Request.',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'team_member_on_leave_alert_sms_info',
        'This template will be used for the SMS notification sent to team members upon approval of a
                                leave request, informing them about the absence of the requestee.',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'project_assignment_whatsapp_info',
        'This template will be used for the whatsApp notification sent to users/clients when they are
                                assigned a project.',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'task_assignment_whatsapp_info',
        'This template will be used for the whatsapp notification sent to users/clients when they are
                                assigned a task.',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'workspace_assignment_whatsapp_info',
        'This template will be used for the whatsapp notification sent to users/clients when they are
                                added to a workspace.',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'meeting_assignment_whatsapp_info',
        'This template will be used for the whatsapp notification sent to users/clients when they are
                                added to a meeting.',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'leave_request_creation_whatsapp_info',
        'This Template Will Be Used for the Whatsapp notification sent to the Admin and Leave Editors
                                Upon the Creation of a Leave Request.',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'leave_request_status_updation_whatsapp_info',
        'This Template Will Be Used for the Whatsapp notification sent to the Admin/Leave
                                Editors/Requestee Upon the Status Updation of a Leave Request.',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'team_member_on_leave_alert_whatsapp_info',
        'This template will be used for the WhatsApp notification sent to team members upon approval of
                                a leave request, informing them about the absence of the requestee.',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'project_assignment_system_info',
        'This template will be used for the system notification sent to users/clients when they are
                                assigned a project.',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'task_assignment_system_info',
        'This template will be used for the system notification sent to users/clients when they are
                                assigned a task.',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'task_status_updation_system_info',
        'This Template Will Be Used for the System notification sent to the Users/Clients Upon the
                                Status Updation of a Task.',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'workspace_assignment_system_info',
        'This template will be used for the system notification sent to users/clients when they are
                                added to a workspace.',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'meeting_assignment_system_info',
        'This template will be used for the system notification sent to users/clients when they are
                                added to a meeting.',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'leave_request_creation_system_info',
        'This Template Will Be Used for the System notification sent to the Admin and Leave Editors Upon
                                the Creation of a Leave Request.',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'leave_request_status_updation_system_info',
        'This Template Will Be Used for the System notification sent to the Admin/Leave
                                Editors/Requestee Upon the Status Updation of a Leave Request.',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'team_member_on_leave_alert_system_info',
        'This template will be used for the system notification sent to team members upon approval of a
                                leave request, informing them about the absence of the requestee.',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'leave_request_status_updation_email_info',
        'This Template Will Be Used for the Email notification sent to the Admin/Leave Editors/Requestee
                                Upon the Status Updation of a Leave Request.',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label('team_member_on_leave_alert_email_info', 'This template will be used for the email notification sent to team members upon approval of a leave request, informing them about the absence of the requestee.', Session::get('locale')) !!}
                            {!! create_label('project_status_updation_email_info', 'This Template Will Be Used for the Email notification sent to the Users/Clients Upon the Status Updation of a Project.', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label(
        'task_status_updation_email_info',
        'This Template Will Be Used for the Email notification sent to the Users/Clients Upon the Status
                                Updation of a Task.',
        Session::get('locale'),
    ) !!}
                            {!! create_label(
        'team_member_on_leave_alert_email_info',
        'This template will be used for the email notification sent to team members upon approval of a
                                leave request, informing them about the absence of the requestee.',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label('leave_request_status_updation_email_info', 'This Template Will Be Used for the Email notification sent to the Admin/Leave Editors/Requestee Upon the Status Updation of a Leave Request.', Session::get('locale')) !!}
                            {!! create_label('project_status_updation_sms_info', 'This Template Will Be Used for the SMS notification sent to the Users/Clients Upon the Status Updation of a Project.', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('project_status_updation_whatsapp_info', 'This Template Will Be Used for the Whatsapp notification sent to the Users/Clients Upon the Status Updation of a Project.', Session::get('locale')) !!}
                            {!! create_label('task_status_updation_whatsapp_info', 'This Template Will Be Used for the Whatsapp notification sent to the Users/Clients Upon the Status Updation of a Task.', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('project_status_updation_system_info', 'This Template Will Be Used for the System notification sent to the Users/Clients Upon the Status Updation of a Project.', Session::get('locale')) !!}
                            {!! create_label('reset_to_default', 'Reset to default', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('sms_gateway_wa', 'SMS gateway/WhatsApp', Session::get('locale')) !!}
                            {!! create_label('sms_gateway_wa_settings', 'SMS gateway/WhatsApp settings', Session::get('locale')) !!}

                        </div>
                        <div class="row">

                            {!! create_label('preferences', 'Preferences', Session::get('locale')) !!}
                            {!! create_label('notification_preferences', 'Notification Preferences', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('project_assignment', 'Project assignment', Session::get('locale')) !!}
                            {!! create_label('task_assignment', 'Task assignment', Session::get('locale')) !!}

                        </div>
                        <div class="row">

                            {!! create_label('project_status_updation', 'Project Status Updation', Session::get('locale')) !!}
                            {!! create_label('task_status_updation', 'Task Status Updation', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('leave_request_creation', 'Leave Request Creation', Session::get('locale')) !!}
                            {!! create_label('leave_request_status_updation', 'Leave Request Status Updation', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('notification_preferences', 'Notification Preferences', Session::get('locale')) !!}
                            {!! create_label(
        'mark_all_notifications_as_read_alert',
        'Are you sure you want to mark all notifications as read?',
        Session::get('locale'),
    ) !!}

                        </div>
                        <div class="row">
                            {!! create_label('confirm', 'Confirm') !!}
                            {!! create_label('mark_all_notifications_as_read_alert', 'Are you sure you want to mark all notifications as read?', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('no_unread_notifications', 'No unread notifications', Session::get('locale')) !!}
                            {!! create_label('notifications', 'Notifications', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('update_notifications_status_alert', 'Are you sure you want to update notification status?', Session::get('locale')) !!}
                            {!! create_label('view_all', 'View all', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('calendar_view', 'Calendar View', Session::get('locale')) !!}
                            {!! create_label('set_as_default_view', 'Set as default view', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('kanban_view', 'Kanban View', Session::get('locale')) !!}
                            {!! create_label('set_default_view_alert', 'Are You Want to Set as Default View?', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('default_view', 'Default View', Session::get('locale')) !!}
                            {!! create_label('tasks_insights', 'Tasks Insights', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('reports', 'Reports', Session::get('locale')) !!}
                            {!! create_label('projects_report', 'Projects Report', Session::get('locale')) !!}
                            {!! create_label('total_team_members', 'Total Team Members', Session::get('locale')) !!}
                            {!! create_label('average_overdue_days_per_project', 'Avg. Overdue Days/Project', Session::get('locale')) !!}
                            {!! create_label('overdue_projects_percentage', 'Overdue Projects (%)', Session::get('locale')) !!}
                            {!! create_label('total_overdue_days', 'Total Overdue Days', Session::get('locale')) !!}
                            {!! create_label('export', 'Export', Session::get('locale')) !!}
                            {!! create_label('total_days', 'Total Days', Session::get('locale')) !!}
                            {!! create_label('days_elapsed', 'Days Elapsed', Session::get('locale')) !!}
                            {!! create_label('days_remaining', 'Days Remaining', Session::get('locale')) !!}
                            {!! create_label('due_tasks', 'Due Tasks', Session::get('locale')) !!}
                            {!! create_label('overdue_tasks', 'Overdue Tasks', Session::get('locale')) !!}
                            {!! create_label('overdue_days', 'Overdue Days', Session::get('locale')) !!}
                            {!! create_label('team_members', 'Team Members', Session::get('locale')) !!}
                            {!! create_label('team', 'Team', Session::get('locale')) !!}
                            {!! create_label('dates', 'Dates', Session::get('locale')) !!}
                            {!! create_label('select_date_range', 'Select Date Range', Session::get('locale')) !!}

                        </div>
                        <div class="row">
                            {!! create_label('tasks_report', 'Tasks Report', Session::get('locale')) !!}
                            {!! create_label('average_task_completion_time', 'Avg. Task Completion Time', Session::get('locale')) !!}
                            {!! create_label('urgent_tasks', 'Urgent Tasks', Session::get('locale')) !!}
                            {!! create_label('date_info', 'Date Info', Session::get('locale')) !!}
                            {!! create_label('due_date', 'Due Date', Session::get('locale')) !!}
                            {!! create_label('invoices_report', 'Invoices Report', Session::get('locale')) !!}
                            {!! create_label('total_invoices', 'Total Invoices', Session::get('locale')) !!}
                            {!! create_label('total_amount', 'Total Amount', Session::get('locale')) !!}
                            {!! create_label('total_tax', 'Total Tax', Session::get('locale')) !!}
                            {!! create_label('total_final', 'Total Final', Session::get('locale')) !!}
                            {!! create_label('average_invoice_value', 'Avg. Invoice Value', Session::get('locale')) !!}
                            {!! create_label('date_range', 'Date Range', Session::get('locale')) !!}
                            {!! create_label('timestamps', 'Timestamps', Session::get('locale')) !!}
                            {!! create_label('leaves_report', 'Leaves Report', Session::get('locale')) !!}
                            {!! create_label('total_leaves', 'Total Leaves', Session::get('locale')) !!}
                            {!! create_label('approved_leaves', 'Approved Leaves', Session::get('locale')) !!}
                            {!! create_label('pending_leaves', 'Pending Leaves', Session::get('locale')) !!}
                            {!! create_label('rejected_leaves', 'Rejected Leaves', Session::get('locale')) !!}
                            {!! create_label('approved_leaves', 'Approved Leaves', Session::get('locale')) !!}
                            {!! create_label('income_vs_expense_report', 'Income vs Expense Report', Session::get('locale')) !!}
                            {!! create_label('total_income', 'Total Income', Session::get('locale')) !!}
                            {!! create_label('total_expenses', 'Total Expenses', Session::get('locale')) !!}
                            {!! create_label('profit_or_loss', 'Profit or Loss', Session::get('locale')) !!}
                            {!! create_label('date', 'Date', Session::get('locale')) !!}
                            {!! create_label('calendar', 'Calendar', Session::get('locale')) !!}
                            {!! create_label('tasks_count', 'Tasks Count', Session::get('locale')) !!}
                            {!! create_label('save_column_visibility', 'Save Column Visibility', Session::get('locale')) !!}
                            {!! create_label('days_overdue', 'Days Overdue', Session::get('locale')) !!}
                            {!! create_label('days_left', 'Days Left', Session::get('locale')) !!}
                            {!! create_label('select_projects', 'Select Projects', Session::get('locale')) !!}
                            {!! create_label('select_statuses', 'Select Statuses', Session::get('locale')) !!}
                            {!! create_label('select_priorities', 'Select Priorities', Session::get('locale')) !!}
                            {!! create_label('clear_filters', 'Clear Filters', Session::get('locale')) !!}
                            {!! create_label('etimates_invoices', 'Estimates/Invoices', Session::get('locale')) !!}
                            {!! create_label('expense_type_id', 'Expense type ID', Session::get('locale')) !!}
                            {!! create_label('priorities', 'Priorities', Session::get('locale')) !!}
                            {!! create_label('primary_workspace', 'Primary Workspace', Session::get('locale')) !!}
                            {!! create_label('dob', 'Date of birth', Session::get('locale')) !!}
                            {!! create_label('doj', 'Date of joining', Session::get('locale')) !!}
                            {!! create_label('no', 'No', Session::get('locale')) !!}
                            {!! create_label('task_summary', 'Task Summary', Session::get('locale')) !!}
                            {!! create_label('discussions', 'Discussions', Session::get('locale')) !!}
                            {!! create_label('media', 'Media', Session::get('locale')) !!}
                            {!! create_label('milestones', 'Milestones', Session::get('locale')) !!}
                            {!! create_label('mind_map_view', 'Mind Map View', Session::get('locale')) !!}
                            {!! create_label('mind_map', 'Mind Map', Session::get('locale')) !!}
                            {!! create_label('export_mindmap', 'Export Mind Map', Session::get('locale')) !!}
                            {!! create_label('gantt_chart_view', 'Gantt Chart View', Session::get('locale')) !!}
                            {!! create_label('prev', 'Previous', Session::get('locale')) !!}
                            {!! create_label('next', 'Next', Session::get('locale')) !!}
                            {!! create_label('day', 'Day', Session::get('locale')) !!}
                            {!! create_label('week', 'Week', Session::get('locale')) !!}
                            {!! create_label('month', 'Month', Session::get('locale')) !!}
                            {!! create_label('admin_settings', 'Admin Settings', Session::get('locale')) !!}
                            {!! create_label('income_vs_expense', 'Income vs Expense', Session::get('locale')) !!}
                            {!! create_label('clear_system_cache', 'Clear System Cache', Session::get('locale')) !!}
                            {!! create_label('confirm_update_dates', 'Confirm Update Dates', Session::get('locale')) !!}
                            {!! create_label('manager_alert', 'As a Manager, user can access and manage Plans, Subscriptions, Transactions, and Customers And Support', Session::get('locale')) !!}
                            {!! create_label('notifications_settings', 'Notifications Settings', Session::get('locale')) !!}

                            {!! create_label('slack_webhook_url', 'Slack webhook URL', Session::get('locale')) !!}
                            {!! create_label('slack', 'Slack', Session::get('locale')) !!}
                            {!! create_label('managers', 'Managers', Session::get('locale')) !!}
                            {!! create_label('create_manager', 'Create Manager', Session::get('locale')) !!}
                            {!! create_label('register_manager', 'Register Manager', Session::get('locale')) !!}
                            {!! create_label('support', 'Support', Session::get('locale')) !!}
                            {!! create_label('create_new_ticket', 'Create New Ticket', Session::get('locale')) !!}
                            {!! create_label('create_ticket', 'Create Ticket', Session::get('locale')) !!}
                            {!! create_label('submit_ticket', 'Submit Ticket', Session::get('locale')) !!}
                            {!! create_label('country_code_and_phone_number', 'Country code and phone number', Session::get('locale')) !!}
                            {!! create_label('slack_bot_token', 'Slack Bot Token', Session::get('locale')) !!}
                            {!! create_label('whatsapp_access_token', 'WhatsApp access token', Session::get('locale')) !!}
                            {!! create_label('whatsapp_phone_number_id', 'WhatsApp phone number ID', Session::get('locale')) !!}
                            {!! create_label('base_url', 'Base URL', Session::get('locale')) !!}
                            {!! create_label('method', 'Method', Session::get('locale')) !!}
                            {!! create_label('create_authorization_token', 'Create authorization token', Session::get('locale')) !!}
                            {!! create_label('account_sid', 'Account SID', Session::get('locale')) !!}
                            {!! create_label('auth_token', 'Auth token', Session::get('locale')) !!}
                            {!! create_label('header', 'Header', Session::get('locale')) !!}
                            {!! create_label('body', 'Body', Session::get('locale')) !!}
                            {!! create_label('params', 'Params', Session::get('locale')) !!}
                            {!! create_label('add_header_data', 'Add header data', Session::get('locale')) !!}
                            {!! create_label('key', 'Key', Session::get('locale')) !!}
                            {!! create_label('action', 'Action', Session::get('locale')) !!}
                            {!! create_label('sms_gateway', 'SMS Gateway', Session::get('locale')) !!}
                            {!! create_label('security_settings', 'Security Settings', Session::get('locale')) !!}
                            {!! create_label('max_login_attempts', 'Max Login Attempts', Session::get('locale')) !!}
                            {!! create_label('max_login_attempts_info', 'Leave it blank if you do not want to lock the account', Session::get('locale')) !!}
                            {!! create_label('time_decay', 'Time Decay', Session::get('locale')) !!}
                            {!! create_label('time_decay_info', 'This will not apply if login attempts are not locked', Session::get('locale')) !!}
                            {!! create_label('bank_name', 'Bank Name', Session::get('locale')) !!}
                            {!! create_label('bank_code', 'Bank Code', Session::get('locale')) !!}
                            {!! create_label('account_name', 'Account Name', Session::get('locale')) !!}
                            {!! create_label('account_number', 'Account Number', Session::get('locale')) !!}
                            {!! create_label('swift_code', 'Swift Code', Session::get('locale')) !!}
                            {!! create_label('extra_notes', 'Extra Notes', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('task_reminder', 'Task Reminder', Session::get('locale')) !!}
                            {!! create_label('task_reminder_email_info', 'This Template Will Be Used for the Email notification sent to the Users Upon the Reminder of a Task.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('task_reminder_email_will_not_sent', 'If Deactive, Task Reminder Email wont be Sent', Session::get('locale')) !!}
                            {!! create_label('task_reminder_sms_info', 'This Template Will Be Used for the SMS notification sent to the Users Upon the Reminder of a Task.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('task_reminder_sms_will_not_sent', 'If Deactive, Task Reminder SMS wont be Sent', Session::get('locale')) !!}
                            {!! create_label('task_reminder_whatsapp_info', 'This Template Will Be Used for the Whatsapp notification sent to the Users Upon the Reminder of a Task.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('task_reminder_whatsapp_will_not_sent', 'If Deactive, Task Reminder Whatsapp Notification wont be Sent', Session::get('locale')) !!}
                            {!! create_label('task_reminder_system_info', 'This Template Will Be Used for the System notification sent to the Users Upon the Reminder of a Task.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('task_reminder_system_will_not_sent', 'If Deactive, Task Reminder system notification wont be sent', Session::get('locale')) !!}
                            {!! create_label('task_reminder_slack_info', 'This Template Will Be Used for the slack notification sent to the Users Upon the Reminder of a Task.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('task_reminder_slack_will_not_sent', 'If Deactive, Task Reminder slack notification wont be sent', Session::get('locale')) !!}
                            {!! create_label('enable_reminder', 'Enable Reminder', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('task_reminder_info', 'Enable this option to set reminders for tasks. You can configure reminder frequencies eg.daily, weekly, or monthly, specific times, and customize alerts to ensure you stay on track with task deadlines.', Session::get('locale')) !!}
                            {!! create_label('enable_task_reminder', 'Enable Task Reminder', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('frequency_type', 'Frequency Type', Session::get('locale')) !!}
                            {!! create_label('daily', 'Daily', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('weekly', 'Weekly', Session::get('locale')) !!}
                            {!! create_label('monthly', 'Monthly', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('day_of_the_week', 'Day of the Week', Session::get('locale')) !!}
                            {!! create_label('any_day', 'Any Day', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('monday', 'Monday', Session::get('locale')) !!}
                            {!! create_label('tuesday', 'Tuesday', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('wednesday', 'Wednesday', Session::get('locale')) !!}
                            {!! create_label('thursday', 'Thursday', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('friday', 'Friday', Session::get('locale')) !!}
                            {!! create_label('saturday', 'Saturday', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('sunday', 'Sunday', Session::get('locale')) !!}
                            {!! create_label('day_of_the_month', 'Day of the Month', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('time_of_day', 'Time of Day', Session::get('locale')) !!}
                            {!! create_label('enable_recurring_task', 'Enable Recurring Task', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('recurring_task', 'Recurring Task', Session::get('locale')) !!}
                            {!! create_label('recurring_task_email_info', 'This Template Will Be Used for the Email notification sent to the Users Upon the Reccurrence of a Task.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('recurring_task_email_will_not_sent', 'If Deactive, Reccurrence Email wont be Sent', Session::get('locale')) !!}
                            {!! create_label('recurring_task_sms_info', 'This Template Will Be Used for the SMS notification sent to the Users Upon the Reccurrence of a Task.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('recurring_task_sms_will_not_sent', 'If Deactive, Task Reccurrence SMS wont be Sent', Session::get('locale')) !!}
                            {!! create_label('recurring_task_whatsapp_info', 'This Template Will Be Used for the Whatsapp notification sent to the Users Upon the Reccurrence of a Task.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('recurring_task_whatsapp_will_not_sent', 'If Deactive, Task Recurrence Whatsapp Notification wont be Sent', Session::get('locale')) !!}
                            {!! create_label('recurring_task_system_info', 'This Template Will Be Used for the System notification sent to the Users Upon the Recurrence of a Task.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('recurring_task_system_will_not_sent', 'If Deactive, Task Reminder system notification wont be sent', Session::get('locale')) !!}
                            {!! create_label('recurring_task_slack_info', 'This Template Will Be Used for the slack notification sent to the Users Upon the Recurrence of a Task.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('recurring_task_slack_will_not_sent', 'If Deactive, Task Recurrence slack notification wont be sent', Session::get('locale')) !!}
                            {!! create_label('recurring_tasks', 'Recurring Tasks', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('recurring_tasks_info', 'This option enables the creation of recurring tasks. You can set the frequency (daily, weekly,monthly, yearly), specific days, and manage the recurrence schedule efficiently.', Session::get('locale')) !!}
                            {!! create_label('recurrence_frequency', 'Recurrence Frequency', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('month_of_the_year', 'Month of the Year', Session::get('locale')) !!}
                            {!! create_label('starts_from', 'Starts From', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('number_of_occurrences', 'Number of Occurrences', Session::get('locale')) !!}
                            {!! create_label('announcement', 'Announcement', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('announcement_email_info', 'This template will be used for the email notification sent to users when new announcement.', Session::get('locale')) !!}
                            {!! create_label('mark_all_announcements_as_read_alert', 'Are you sure you want to mark all announcements as read?', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('update_announcement_status_alert', 'Are you sure you want to update announcement status?', Session::get('locale')) !!}
                            {!! create_label('create_announcement', 'Create Announcement', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('announcement_details', 'Announcement Details', Session::get('locale')) !!}
                            {!! create_label('please_enter_content', 'Please Enter Content', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('notify_users', 'Notify Users', Session::get('locale')) !!}
                            {!! create_label('all_workspace_users', 'All Workspace Users', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('announcement_email_will_not_sent', 'If Deactive, new announcement email wont be sent', Session::get('locale')) !!}
                            {!! create_label('announcement_sms_info', 'This template will be used for the SMS notification sent to users on new announcement.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('announcement_sms_will_not_sent', 'If Deactive, Announcement SMS wont be sent', Session::get('locale')) !!}
                            {!! create_label('announcement_whatsapp_info', 'This template will be used for the whatsapp notification sent to users on new announcement.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('announcement_whatsapp_will_not_sent', 'If Deactive, announcement whatsapp notification wont be sent', Session::get('locale')) !!}
                            {!! create_label('announcement_system_info', 'This template will be used for the system notification sent to users on the new announcement.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('announcement_system_will_not_sent', 'If Deactive, announcement system notification wont be sent', Session::get('locale')) !!}
                            {!! create_label('announcement_slack_info', 'This template will be used for the slack notification sent to users on the new announcement.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('announcement_slack_will_not_sent', 'If Deactive, announcement slack notification wont be sent', Session::get('locale'))!!}
                            {!! create_label('issue_assignment', 'Issue Assignment', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('project_issue_assignment_email_info', 'This Template Will Be Used for the Email notification sent to the Users/Clients Upon the Issue Assignment under a Project.', Session::get('locale')) !!}
                            {!! create_label('project_issue_assignment_email_will_not_sent', 'If Deactive, Project Issue Assignment Email wont be Sent', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('project_issue_assignment_sms_info', 'This Template Will Be Used for the SMS notification sent to the Users Upon the Creation of the Issue.', Session::get('locale')) !!}
                            {!! create_label('project_issue_assignment_sms_will_not_sent', 'If Deactive, Project Issue Assignment SMS wont be Sent', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('project_issue_assignment_whatsapp_info', 'This Template Will Be Used for the Whatsapp notification sent to the Users Upon the Issue Assignment.', Session::get('locale')) !!}
                            {!! create_label('project_issue_assignment_whatsapp_will_not_sent', 'If Deactive, Project Issue Assignment Whatsapp Notification wont be Sent', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('project_issue_assignment_system_info', 'This Template Will Be Used for the System notification sent to the Users Upon the Issue Assignment.', Session::get('locale')) !!}
                            {!! create_label('project_issue_assignment_system_will_not_sent', 'If Deactive, Project Issue Assignment System Notification wont be sent', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('project_issue_assignment_slack_info', 'This Template Will Be Used for the slack notification sent to the Users Upon the Issue Assignment.', Session::get('locale')) !!}
                            {!! create_label('project_issue_assignment_slack_will_not_sent', 'If Deactive, Project Issue Assignment slack Notification wont be sent', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('create_issue', 'Create Issue', Session::get('locale')) !!}
                            {!! create_label('update_issue', 'Update Issue', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('tasks_time_entries', 'Tasks Time Entries', Session::get('locale')) !!}
                            {!! create_label('tasks_time_entries_info', 'To use Time Entries in tasks, you need to enable this option. It allows time tracking and entry management for tasks under this project.', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('enable', 'Enable', Session::get('locale')) !!}
                            {!! create_label('add_task_time_entries', 'Add Task Time Entries', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('add_task_time_entry', 'Add Task Time Entry', Session::get('locale')) !!}
                            {!! create_label('entry_date', 'Entry Date', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('entry_type', 'Entry Type', Session::get('locale')) !!}
                            {!! create_label('standard', 'Standard', Session::get('locale')) !!}
                        </div>
                        <div class="row">
                            {!! create_label('flexible', 'Flexible', Session::get('locale')) !!}
                            {!! create_label('standard_hours', 'Standard Hours', Session::get('locale')) !!}
                        </div>
                        <div class="row">

                            {!! create_label('start_time', 'Start Time', Session::get('locale')) !!}
                            {!! create_label('end_time', 'End Time', Session::get('locale')) !!}
                        </div>
                        <div class="row">

                            {!! create_label('billable', 'Billable', Session::get('locale')) !!}
                            {!! create_label('announcements', 'Announcements', Session::get('locale')) !!}
                        </div>
                        <div class="row">

                            {!! create_label('content', 'Content', Session::get('locale')) !!}
                            {!! create_label('billing_type', 'Billing Type', Session::get('locale')) !!}
                        </div>
                        <div class="row">

                            {!! create_label('billable', 'Billable', Session::get('locale')) !!}
                            {!! create_label('non_billable', 'Non Billable', Session::get('locale')) !!}
                        </div>
                        <div class="row">

                            {!! create_label('completion_percentage', 'Completion Percentage (%)', Session::get('locale')) !!}
                            {!! create_label('status_timeline', 'Status Timeline', Session::get('locale')) !!}

                        </div>

                        <div class="row">
                            {!! create_label('status_timeline', 'Status Timeline', Session::get('locale')) !!}
                            {!! create_label('no_status_change', 'No Status Change', Session::get('locale')) !!}
                            {!! create_label('status_changed_from', 'Status changed from', Session::get('locale')) !!}
                            {!! create_label('initial_status', 'Initial Status', Session::get('locale')) !!}
                            {!! create_label('total_duration', 'Total Duration', Session::get('locale')) !!}
                            {!! create_label('time_entries', 'Time Entries', Session::get('locale')) !!}
                            {!! create_label('reminder_details', 'Reminders Details', Session::get('locale')) !!}
                            {!! create_label('no_reminders_set', 'No reminders set for this task', Session::get('locale')) !!}
                            {!! create_label('recurrence_details', 'Recurrence Details', Session::get('locale')) !!}
                            {!! create_label('no_recurrence_set', 'No recurrence set for this task', Session::get('locale')) !!}
                            {!! create_label('mark_all_as_read', 'Mark all as read', Session::get('locale')) !!}
                            {!! create_label('no_comments', 'No Comments', Session::get('locale')) !!}
                            {!! create_label('work_hours_report', 'Work Hours Report', Session::get('locale')) !!}
                            {!! create_label('total_hours', 'Total Hours', Session::get('locale')) !!}
                            {!! create_label('billable_hours', 'Billable Hours', Session::get('locale')) !!}
                            {!! create_label('non_billable_hours', 'Non Billable Hours', Session::get('locale')) !!}
                            {!! create_label('is_billable', 'Is Billable', Session::get('locale')) !!}
                            {!! create_label('your_all_in_one', 'Your All-in-one solution for smarter', Session::get('locale')) !!}
                            {!! create_label('every', 'Every', Session::get('locale')) !!}
                            {!! create_label('feature', 'Feature', Session::get('locale')) !!}
                            {!! create_label('your_team_needs_to', 'Your Team Needs To', Session::get('locale')) !!}
                            {!! create_label('complete_work_faster', 'Complete Work Faster', Session::get('locale')) !!}
                            {!! create_label('many_more_features', 'Many more features...', Session::get('locale')) !!}
                            {!! create_label('you_have_free', 'You Have Free', Session::get('locale')) !!}
                            {!! create_label('unlimited_updates', 'Unlimited Updates And', Session::get('locale')) !!}
                            {!! create_label('premium_support', 'Premium Support', Session::get('locale')) !!}
                            {!! create_label('on_each_package', 'On Each Package.', Session::get('locale')) !!}
                            {!! create_label('simplifying', 'Simplifying', Session::get('locale')) !!}
                            {!! create_label('management', 'Management', Session::get('locale')) !!}
                            {!! create_label('simplify_workflow', 'Simplify your workflow with powerful tools to organize tasks, collaborate in real-time, and track progress — all in one place.', Session::get('locale')) !!}
                            {!! create_label('assign_track_and_team_members', 'Easily assign tasks to team members, set priorities, and track progress in real-time to ensure nothing falls through the cracks.', Session::get('locale')) !!}
                            {!! create_label('seamless_collaboration_for_team_success', 'Connect your team with real-time communication, shared tasks, and seamless file sharing to boost teamwork and productivity.', Session::get('locale')) !!}

                        </div>

                        <div class="row">

                            <!-- </div> -->
                            <div class="card-footer">
                                <div class="col-sm-12">
                                    <div class="mt-5">
                                        <button type="submit" class="btn btn-primary me-2" id="submit_btnn">
                                            {{get_label('update', 'Update')}}

                                        </button>

                                    </div>
                                </div>
                            </div>
                            <!-- </div> -->
                            <!-- </div> -->
                        </div>
                        </form>
                    </div>
                    <!--/ List group with Badges & Pills -->
                </div>
            </div>
        </div>
    </div>
@endsection