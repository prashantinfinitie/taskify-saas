@extends('layout')
@section('title')
    <?= get_label('sms_gateway_wa_settings', 'SMS gateway/WhatsApp settings') ?>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('superadmin.panel') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <?= get_label('settings', 'Settings') ?>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('notifications_settings', 'Notifications Settings') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        @php
            $sms_gateway_settings = get_settings('sms_gateway_settings');
        @endphp
        <div class="card">
            <div class="card-body">
                <div class="list-group list-group-horizontal-md text-md-center">
                    <a class="list-group-item list-group-item-action active" data-bs-toggle="list"
                        href="#sms-gateway-settings"><?= get_label('sms_gateway', 'SMS Gateway') ?></a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list"
                        href="#whatsapp-settings"><?= get_label('whatsapp', 'WhatsApp') ?></a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list"
                        href="#slack-settings"><?= get_label('slack', 'Slack') ?></a>
                </div>
                <div class="tab-content px-0">
                    <div class="tab-pane fade show active" id="sms-gateway-settings">
                        <div class="alert alert-primary" role="alert">
                            <?= get_label('important_settings_for_SMS_feature_to_be_work', 'Important settings for SMS feature to be work') ?>,
                            <a href="javascript:void(0)" data-bs-toggle="modal"
                                data-bs-target="#sms_instuction_modal"><?= get_label('click_for_sms_gateway_settings_help', 'Click Here for Help with SMS Gateway Settings.') ?></a>
                        </div>
                        <form action="{{ route('sms_gateway.store') }}" class="form-submit-event" method="POST"
                            enctype="multipart/form-data">
                            <input type="hidden" name="dnr">
                            @csrf
                            @method('PUT')
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="base_url" class="form-label">{{ get_label('base_url', 'Base URL') }} <span
                                            class="asterisk">*</span></label>
                                    <input type="text" class="form-control" name="base_url"
                                        value="{{ $sms_gateway_settings['base_url'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="sms_gateway_method" class="form-label">{{ get_label('method', 'Method') }}
                                        <span class="asterisk">*</span></label>
                                    <select class="form-select" name="sms_gateway_method">
                                        <option value="POST"
                                            {{ $sms_gateway_settings && isset($sms_gateway_settings['sms_gateway_method']) && $sms_gateway_settings['sms_gateway_method'] == 'POST' ? 'selected' : '' }}>
                                            POST</option>
                                        <option value="GET"
                                            {{ $sms_gateway_settings && isset($sms_gateway_settings['sms_gateway_method']) && $sms_gateway_settings['sms_gateway_method'] == 'GET' ? 'selected' : '' }}>
                                            GET</option>
                                    </select>
                                </div>
                            </div>
                            <h4 class="mb-3 mt-4">
                                {{ get_label('create_authorization_token', 'Create authorization token') }}
                            </h4>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="base_url"
                                        class="form-label">{{ get_label('account_sid', 'Account SID') }}</label>
                                    <input type="text" class="form-control" id="converterInputAccountSID">
                                </div>
                                <div class="col-md-6">
                                    <label for="base_url"
                                        class="form-label">{{ get_label('auth_token', 'Auth token') }}</label>
                                    <input type="text" class="form-control" id="converterInputAuthToken">
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary mb-3 me-2"
                                id="createBasicToken"><?= get_label('create', 'Create') ?></button>
                            <h4 class="mb-4" id="basicToken"></h4>
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="nav-align-top mb-4">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li class="nav-item">
                                                <button type="button" class="nav-link active" role="tab"
                                                    data-bs-toggle="tab" data-bs-target="#navs-top-header"
                                                    aria-controls="navs-top-header" aria-selected="true">
                                                    {{ get_label('header', 'Header') }}
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                                    data-bs-target="#navs-top-body" aria-controls="navs-top-body"
                                                    aria-selected="false">
                                                    {{ get_label('body', 'Body') }}
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                                    data-bs-target="#navs-top-params" aria-controls="navs-top-params"
                                                    aria-selected="false">
                                                    {{ get_label('params', 'Params') }}
                                                </button>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane fade show active" id="navs-top-header" role="tabpanel">
                                                <h6 class="text-muted">
                                                    {{ get_label('add_header_data', 'Add header data') }}</h6>
                                                <div class="row">
                                                    <div class="col-md-12" id="header-rows">
                                                        <div class="d-flex">
                                                            <div class="col-md-5 mx-1 mb-3">
                                                                <label class="form-label text-muted"
                                                                    for="">{{ get_label('key', 'Key') }}</label>
                                                                <input type="text" id="header_key"
                                                                    class="form-control">
                                                            </div>
                                                            <div class="col-md-5 mx-1 mb-3">
                                                                <label class="form-label text-muted"
                                                                    for="">{{ get_label('value', 'Value') }}</label>
                                                                <input type="text" id="header_value"
                                                                    class="form-control">
                                                            </div>
                                                            <div class="col-md-1 mx-3 mb-3">
                                                                <label class="form-label text-muted"
                                                                    for=""><?= get_label('action', 'Action') ?></label>
                                                                <button type="button" class="btn btn-sm btn-success"
                                                                    id="add-header"><i class="bx bx-check"></i></button>
                                                            </div>
                                                        </div>
                                                        @foreach ($sms_gateway_settings['header_data'] ?? [] as $key => $value)
                                                            <div class="d-flex header-row">
                                                                <div class="col-md-5 mx-1 mb-3">
                                                                    <input type="text" class="form-control"
                                                                        name="header_key[]" value="{{ $key }}">
                                                                </div>
                                                                <div class="col-md-5 mx-1 mb-3">
                                                                    <input type="text" class="form-control"
                                                                        name="header_value[]"
                                                                        value="{{ $value }}">
                                                                </div>
                                                                <div class="col-md-1 mx-3 mb-3">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger remove-header"><i
                                                                            class="bx bx-trash"></i></button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="navs-top-body" role="tabpanel">
                                                <ul class="nav nav-tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <button type="button" class="nav-link active" role="tab"
                                                            data-bs-toggle="tab" data-bs-target="#text-json-tab"
                                                            aria-controls="text-json-tab" aria-selected="true">
                                                            text/JSON
                                                        </button>
                                                    </li>
                                                    <li class="nav-item">
                                                        <button type="button" class="nav-link" role="tab"
                                                            data-bs-toggle="tab" data-bs-target="#formdata-tab"
                                                            aria-controls="formdata-tab" aria-selected="false">
                                                            FormData
                                                        </button>
                                                    </li>
                                                </ul>
                                                <div class="tab-content">
                                                    <div class="tab-pane fade show active" id="text-json-tab"
                                                        role="tabpanel">
                                                        <div class="col-md-12">
                                                            <textarea name="text_format_data" class="text_format_data">{{ $sms_gateway_settings['text_format_data'] ?? '' }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane fade" id="formdata-tab" role="tabpanel">
                                                        <h6 class="text-muted">
                                                            {{ get_label('add_body_data_parameters_and_values', 'Add body data parameter and values') }}
                                                        </h6>
                                                        <div class="col-md-12" id="body-formdata-rows">
                                                            <div class="d-flex">
                                                                <div class="col-md-5 mx-1 mb-3">
                                                                    <label class="form-label text-muted"
                                                                        for="">{{ get_label('key', 'Key') }}</label>
                                                                    <input type="text" id="body_formdata_key"
                                                                        class="form-control">
                                                                </div>
                                                                <div class="col-md-5 mx-1 mb-3">
                                                                    <label class="form-label text-muted"
                                                                        for="">{{ get_label('value', 'Value') }}</label>
                                                                    <input type="text" id="body_formdata_value"
                                                                        class="form-control">
                                                                </div>
                                                                <div class="col-md-1 mx-3 mb-3">
                                                                    <label class="form-label text-muted"
                                                                        for=""><?= get_label('action', 'Action') ?></label>
                                                                    <button type="button" class="btn btn-sm btn-success"
                                                                        id="add-body-formdata"><i
                                                                            class="bx bx-check"></i></button>
                                                                </div>
                                                            </div>
                                                            @foreach ($sms_gateway_settings['body_formdata'] ?? [] as $key => $value)
                                                                <div class="d-flex body-formdata-row">
                                                                    <div class="col-md-5 mx-1 mb-3">
                                                                        <input type="text" class="form-control"
                                                                            name="body_key[]"
                                                                            value="{{ $key }}">
                                                                    </div>
                                                                    <div class="col-md-5 mx-1 mb-3">
                                                                        <input type="text" class="form-control"
                                                                            name="body_value[]"
                                                                            value="{{ $value }}">
                                                                    </div>
                                                                    <div class="col-md-1 mx-3 mb-3">
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-danger remove-body-formdata"><i
                                                                                class="bx bx-trash"></i></button>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="navs-top-params" role="tabpanel">
                                                <h6 class="text-muted">{{ get_label('add_params', 'Add params') }}</h6>
                                                <div class="row">
                                                    <div class="col-md-12" id="params-rows">
                                                        <div class="d-flex">
                                                            <div class="col-md-5 mx-1 mb-3">
                                                                <label class="form-label text-muted"
                                                                    for="">{{ get_label('key', 'Key') }}</label>
                                                                <input type="text" id="params_key"
                                                                    class="form-control">
                                                            </div>
                                                            <div class="col-md-5 mx-1 mb-3">
                                                                <label class="form-label text-muted"
                                                                    for="">{{ get_label('value', 'Value') }}</label>
                                                                <input type="text" id="params_value"
                                                                    class="form-control">
                                                            </div>
                                                            <div class="col-md-1 mx-3 mb-3">
                                                                <label class="form-label text-muted"
                                                                    for=""><?= get_label('action', 'Action') ?></label>
                                                                <button type="button" class="btn btn-sm btn-success"
                                                                    id="add-params"><i class="bx bx-check"></i></button>
                                                            </div>
                                                        </div>
                                                        @foreach ($sms_gateway_settings['params_data'] ?? [] as $key => $value)
                                                            <div class="d-flex params-row">
                                                                <div class="col-md-5 mx-1 mb-3">
                                                                    <input type="text" class="form-control"
                                                                        name="params_key[]" value="{{ $key }}">
                                                                </div>
                                                                <div class="col-md-5 mx-1 mb-3">
                                                                    <input type="text" class="form-control"
                                                                        name="params_value[]"
                                                                        value="{{ $value }}">
                                                                </div>
                                                                <div class="col-md-1 mx-3 mb-3">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger remove-params"><i
                                                                            class="bx bx-trash"></i></button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <h5 class="mt-5">
                                                    {{ get_label('available_placeholders', 'Available placeholders') }}
                                                </h5>
                                                <table class="table-bordered table">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ get_label('placeholder', 'Placeholder') }}</th>
                                                            <th>{{ get_label('action', 'Action') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td class="copyText">{only_mobile_number}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(0)"
                                                                    title="{{ get_label('copy_to_clipboard', 'Copy to clipboard') }}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <!-- <tr>
                                                                                <td class="copyText">{mobile_number_with_country_code}</td>
                                                                                <td>
                                                                                    <a href="javascript:void(0);" onclick="copyToClipboard(1)" title="{{ get_label('copy_to_clipboard', 'Copy to clipboard') }}">
                                                                                        <i class="bx bx-copy text-warning mx-2"></i>
                                                                                    </a>
                                                                                </td>
                                                                            </tr> -->
                                                        <tr>
                                                            <td class="copyText">{country_code}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(1)"
                                                                    title="{{ get_label('copy_to_clipboard', 'Copy to clipboard') }}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="copyText">{message}</td>
                                                            <td>
                                                                <a href="javascript:void(0);" onclick="copyToClipboard(2)"
                                                                    title="{{ get_label('copy_to_clipboard', 'Copy to clipboard') }}">
                                                                    <i class="bx bx-copy text-warning mx-2"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary me-2"
                                    id="submit_btn"><?= get_label('update', 'Update') ?></button>
                                <button type="reset"
                                    class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                            </div>
                        </form>
                    </div>
                    @php
                        $whatsapp_settings = get_settings('whatsapp_settings');
                    @endphp
                    <div class="tab-pane fade show" id="whatsapp-settings">
                        <div class="alert alert-primary" role="alert">
                            <?= get_label('important_settings_for_whatsapp_notification_feature_to_be_work', 'Important settings for WhatsApp notification feature to be work.') ?>
                            <a href="javascript:void(0)" data-bs-toggle="modal"
                                data-bs-target="#whatsapp_instuction_modal"><?= get_label('click_for_help', 'Click here for help.') ?></a>
                        </div>
                        <form action="{{ route('whatsapp_settings.store') }}" class="form-submit-event" method="POST">
                            <input type="hidden" name="dnr">
                            @csrf
                            @method('PUT')
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="whatsapp_access_token"
                                        class="form-label">{{ get_label('whatsapp_access_token', 'WhatsApp access token') }}
                                        <span class="asterisk">*</span></label>
                                    <input type="text" class="form-control" name="whatsapp_access_token"
                                        value="{{ $whatsapp_settings['whatsapp_access_token'] ?? '' }}"
                                        placeholder="{{ get_label('whatsapp_access_token', 'WhatsApp access token') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="whatsapp_phone_number_id"
                                        class="form-label">{{ get_label('whatsapp_phone_number_id', 'WhatsApp phone number ID') }}
                                        <span class="asterisk">*</span></label>
                                    <input type="text" class="form-control" name="whatsapp_phone_number_id"
                                        value="{{ $whatsapp_settings['whatsapp_phone_number_id'] ?? '' }}"
                                        placeholder="{{ get_label('whatsapp_phone_number_id', 'WhatsApp phone number ID') }}">
                                </div>

                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary me-2"
                                    id="submit_btn"><?= get_label('update', 'Update') ?></button>
                                <button type="reset"
                                    class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                            </div>
                        </form>
                    </div>
                    @php
                        $slack_settings = get_settings('slack_settings');
                    @endphp
                    <div class="tab-pane fade show" id="slack-settings">
                        <div class="alert alert-primary" role="alert">
                            <?= get_label('important_settings_for_slack_notification_feature_to_be_work', 'Important settings for Slack notification feature to be work.') ?>
                            <a href="javascript:void(0)" data-bs-toggle="modal"
                                data-bs-target="#slack_instruction_modal"><?= get_label('click_for_help', 'Click here for help.') ?></a>
                        </div>
                        <form action="{{ route('slack_settings.store') }}" class="form-submit-event" method="POST">
                            <input type="hidden" name="dnr">
                            @csrf
                            @method('PUT')
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="slack_bot_token"
                                        class="form-label">{{ get_label('slack_bot_token', 'Slack bot token') }}
                                        <span class="asterisk">*</span></label>
                                    <input type="text" class="form-control" name="slack_bot_token"
                                        value="{{ $slack_settings['slack_bot_token'] ?? '' }}"
                                        placeholder="{{ get_label('slack_bot_token', 'Slack bot token') }}">
                                </div>


                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary me-2"
                                    id="submit_btn"><?= get_label('update', 'Update') ?></button>
                                <button type="reset"
                                    class="btn btn-outline-secondary"><?= get_label('cancel', 'Cancel') ?></button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="whatsapp_instuction_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('whatsapp_configuration', 'WhatsApp Configuration') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>{{ get_label('steps_for_whatsapp_cloud_api_setup', 'Steps for WhatsApp Cloud API Setup') }}:</h6>
                    <ol>
                        <li>
                            <strong>Access Facebook Developers Dashboard:</strong>
                            <ul>
                                <li>Go to <a href="https://developers.facebook.com/apps/" target="_blank">Facebook for
                                        Developers</a></li>
                                <li>Log in or create a developer account if you haven't already</li>
                            </ul>
                            <img src="{{ asset('/storage/images/fb_developer_dashboard.png') }}"
                                alt="Facebook Developer Dashboard" class="img-fluid mb-3 mt-2">
                        </li>
                        <li>
                            <strong>Create or Select an App:</strong>
                            <ul>
                                <li>Click "Create App" or select your existing app</li>
                                <li>Choose "Business" as the app type if creating new</li>
                            </ul>
                            <img src="{{ asset('/storage/images/create_app_image.png') }}" alt="Create App Process"
                                class="img-fluid mb-3 mt-2">
                        </li>
                        <li>
                            <strong>Set up WhatsApp:</strong>
                            <ul>
                                <li>In the app dashboard, find and add the "WhatsApp" product</li>
                                <li>Follow the setup process, including business verification if required</li>
                            </ul>
                            <img src="{{ asset('/storage/images/whatsapp_setup_image.png') }}"
                                alt="WhatsApp Setup in Developer Dashboard" class="img-fluid mb-3 mt-2">
                        </li>
                        <li>
                            <strong>Get Access Token and Phone Number ID:</strong>
                            <ul>
                                <li>In the WhatsApp section, find "Getting Started"</li>
                                <li>Locate your Temporary Access Token and Phone Number ID</li>
                            </ul>
                            <img src="{{ asset('/storage/images/access_token_phone_id_image.png') }}"
                                alt="Access Token and Phone Number ID Location" class="img-fluid mb-3 mt-2">
                        </li>
                        <li>
                            <strong>Create Message Template (Important):</strong>
                            <ul>
                                <li>In the WhatsApp section, go to "Message Templates"</li>
                                <li>Click "Create Template"</li>
                                <li>Name your template "taskify_saas_notification"</li>
                                <li>Set language to English</li>
                                <li>In the Body section, enter exactly:
                                    <pre>@{{ 1 }}

Please take necessary actions if required.

Thank you,
@{{ 2 }}</pre>
                                </li>
                                <li>Provide sample content for the @{{ 1 }} , @{{ 2 }} variable
                                </li>
                                <li>Submit the template for review</li>
                            </ul>
                            <img src="{{ asset('/storage/images/template_creation_image.png') }}"
                                alt="Message Template Creation" class="img-fluid mb-3 mt-2">
                        </li>
                    </ol>
                    <p><strong>Note:</strong> It's crucial to create the template exactly as shown for the integration to
                        work correctly. The @{{ 1 }} , @{{ 2 }} represents a variable in the
                        WhatsApp template, not a Blade variable.</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

   <div class="modal fade" id="slack_instruction_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('slack_bot_configuration', 'Slack Bot Configuration') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>{{ get_label('steps_for_slack_bot_token_setup', 'Steps for Slack Bot Token Setup') }}:</h6>
                <ol>
                    <li>
                        <strong>Create a Slack App:</strong>
                        <ol type="a">
                            <li>Go to <a href="https://api.slack.com/apps" target="_blank">https://api.slack.com/apps</a></li>
                            <li>Click "Create New App"</li>
                            <li>Choose "From scratch"</li>
                            <li>Name your app (e.g., "Taskify Notifier") and select your workspace</li>
                            <li>Click "Create App"</li>
                        </ol>
                        <img src="{{ asset('/storage/images/create-slack-app.png') }}" alt="Create Slack App" class="img-fluid mb-3 mt-2">
                    </li>
                    <li>
                        <strong>Set up Bot Token Scopes:</strong>
                        <ol type="a">
                            <li>In the left sidebar, click on "OAuth & Permissions"</li>
                            <li>Scroll down to "Scopes"</li>
                            <li>Under "Bot Token Scopes", click "Add an OAuth Scope"</li>
                            <li>Add these scopes:
                                <ul>
                                    <li>chat:write</li>
                                    <li>users:read</li>
                                    <li>users:read.email</li>
                                </ul>
                            </li>
                        </ol>
                        <img src="{{ asset('/storage/images/bot-token-scopes.png') }}" alt="Bot Token Scopes" class="img-fluid mb-3 mt-2">
                    </li>
                    <li>
                        <strong>Install the app to your workspace:</strong>
                        <ol type="a">
                            <li>Scroll up to the top of the "OAuth & Permissions" page</li>
                            <li>Click "Install to Workspace"</li>
                            <li>Review the permissions and click "Allow"</li>
                        </ol>
                        <img src="{{ asset('/storage/images/slack-app.png') }}" alt="Install to Workspace" class="img-fluid mb-3 mt-2">
                    </li>
                    <li>
                        <strong>Get your Bot Token:</strong>
                        <ol type="a">
                            <li>After installation, you'll be back on the "OAuth & Permissions" page</li>
                            <li>Look for "Bot User OAuth Token" under "OAuth Tokens for Your Workspace"</li>
                            <li>Click "Copy" to copy this token</li>
                            <li>Store this token securely (we'll use it in the code later)</li>
                        </ol>
                        <img src="{{ asset('/storage/images/bot-token.png') }}" alt="Get Bot Token" class="img-fluid mb-3 mt-2">
                    </li>
                    <li>
                        <strong>Enable Socket Mode (optional, but recommended for enhanced security):</strong>
                        <ol type="a">
                            <li>In the left sidebar, click on "Socket Mode"</li>
                            <li>Toggle "Enable Socket Mode" to On</li>
                            <li>If prompted, generate an app-level token and store it securely</li>
                        </ol>
                        <img src="{{ asset('/storage/images/slack-soket-mode.png') }}" alt="Enable Socket Mode" class="img-fluid mb-3 mt-2">
                    </li>
                </ol>
                <div class="alert alert-warning" role="alert">
                    <strong>Important:</strong> Keep your Bot Token and app-level token (if generated) confidential. Do not share them publicly or commit them to version control systems.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
            </div>
        </div>
    </div>
</div>

    <script src="{{ asset('assets/js/pages/sms-gateway-settings.js') }}"></script>
@endsection
