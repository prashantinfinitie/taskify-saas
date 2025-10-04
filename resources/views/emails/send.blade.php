@extends('layout')

@section('title')
    <?= get_label('send_email', 'Send Email') ?>
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
                        <li class="breadcrumb-item active">
                            {{ get_label('send_email', 'Send Email') }}
                        </li>
                    </ol>
                </nav>
            </div>

            <div>

                <a href="{{ route('emails.index') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('list_view', 'List View') ?>"><i
                            class="bx bx-list-ul"></i></button></a>

            </div>
        </div>

        <div class="nav-align-top nav-tabs-shadow mb-6">
            <ul class="nav nav-tabs nav-fill" role="tablist">
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-template-email" aria-controls="navs-template-email" aria-selected="true">
                        <span class="d-none d-sm-block"><i class="tf-icons bx bx-layout align-text-bottom"></i>
                            {{ get_label('template_email', 'Template Email') }}</span>
                        <i class="bx bx-layout d-sm-none"></i>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-custom-email" aria-controls="navs-custom-email" aria-selected="false">
                        <span class="d-none d-sm-block"><i class="tf-icons bx bx-edit-alt me-1_5 align-text-bottom"></i>
                            {{ get_label('custom_email', 'Custom Email') }}</span>
                        <i class="bx bx-edit-alt d-sm-none"></i>
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Template Email Tab -->
                <div class="tab-pane fade show active" id="navs-template-email" role="tabpanel">
                    <div class=" d-flex align-items-center justify-content-between ">
                        <h5 class="mb-0">{{ get_label('select_template', 'Select Template') }}</h5>
                    </div>
                    <div class="mt-2">
                        <div class="row">
                            <div class="col-md-12">
                                <select name="template_id" id="templateSelector" class="form-select">
                                    <option value="">-- {{ get_label('select_template', 'Select Template') }} --
                                    </option>
                                    @foreach ($templates as $template)
                                                                <option value="{{ $template->id }}" {{ request('template_id') == $template->id ?
                                        'selected' : '' }}>
                                                                    {{ $template->name }}
                                                                </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Email Composition Section -->
                    <div id="emailComposition" class="d-none">
                        <form method="POST" action="{{  route('emails.store') }}" id="emailForm"
                            enctype="multipart/form-data" class="form-submit-event">
                            @csrf
                            <input type="hidden" name="redirect_url" value="" />
                            <input type="hidden" name="email_template_id" id="templateIdInput">
                            <input type="hidden" name="content" id="templateBodyInput">

                            <div class=" d-flex align-items-center justify-content-between mt-3 ">
                                <h5 class="mb-0">{{ get_label('compose_email', 'Compose Email') }}</h5>
                                <div class="d-flex gap-2">
                                    <button type="button" id="previewBtn" class="btn btn-label-secondary hover-shadow"
                                        data-company-title="{{ $general_settings['company_title'] ?? 'Company Title' }}"
                                        data-label-preview="{{ get_label('preview', 'Preview') }}">
                                        <i class="bx bx-show me-1"></i> {{ get_label('preview', 'Preview') }}
                                    </button>
                                    <button type="submit" id="submit_btn" class="btn btn-primary hover-shadow"
                                        data-label-send="{{ get_label('send_now', 'Send Now') }}"
                                        data-label-schedule="{{ get_label('schedule_email', 'Schedule Email') }}">
                                        <i class="bx bx-send me-1"></i> {{ get_label('send_now', 'Send Now') }}
                                    </button>
                                </div>
                            </div>

                            <div class="">
                                <!-- Subject & Recipient -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ get_label('subject', 'Subject') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-text"></i></span>
                                            <input type="text" name="subject" id="emailSubject" class="form-control"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ get_label('to_email', 'Recipient Email') }}</label>
                                        <select name="emails[]" class="form-control to_emails" multiple="multiple" required>
                                        </select>
                                        <small
                                            class="text-muted">{{ get_label('email_add_note', 'You can type and add multiple emails') }}</small>
                                    </div>
                                </div>

                                <!-- Placeholders -->
                                <div id="placeholderFields" class="row">
                                    <!-- Populated by JavaScript -->
                                </div>

                                <!-- Attachments and Scheduling -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-header bg-label-primary">
                                                <h6 class="mb-0">{{ get_label('attachments', 'Attachments') }}</h6>
                                            </div>
                                            <div class="card-body">
                                                <label for="attachments"
                                                    class="form-label">{{ get_label('choose_files', 'Choose files to upload') }}</label>
                                                <input type="file" name="attachments[]" id="attachments"
                                                    class="form-control" multiple>
                                                <div id="file-list" class="d-none mt-3">
                                                    <h6 class="small">
                                                        {{ get_label('selected_files', 'Selected Files:') }}
                                                    </h6>
                                                    <ul class="list-unstyled mb-0" id="file-names"></ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-label-primary">
                                                <h6 class="mb-0">{{ get_label('delivery_options', 'Delivery Options') }}
                                                </h6>
                                            </div>
                                            <div class="card-body mt-3">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" id="scheduleToggle"
                                                        name="schedule_toggle"
                                                        data-select-time-error="{{ get_label('select_time_error', 'Please select a time to schedule the email') }}">
                                                    <label class="form-check-label" for="scheduleToggle">
                                                        {{ get_label('schedule_email', 'Schedule Email') }}
                                                    </label>
                                                </div>

                                                <div id="scheduleField" class="d-none">
                                                    <label
                                                        class="form-label">{{ get_label('schedule_at', 'Schedule Date & Time') }}</label>
                                                    <input type="datetime-local" name="scheduled_at" class="form-control"
                                                        min="{{ now()->format('Y-m-d\TH:i') }}">
                                                    <small class="text-muted">
                                                        {{ get_label('timezone_note', 'Timezone:') }}
                                                        {{ config('app.timezone') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Custom Email Tab -->
                <div class="tab-pane fade" id="navs-custom-email" role="tabpanel">
                    <div class="card-header d-flex align-items-center justify-content-between py-3">
                        <h5 class="mb-0">{{ get_label('compose_custom_email', 'Compose Custom Email') }}</h5>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{  route('emails.store') }}" id="customEmailForm"
                            enctype="multipart/form-data" class="form-submit-event">
                            @csrf
                            <input type="hidden" name="redirect_url" value="" />

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">{{ get_label('subject', 'Subject') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-text"></i></span>
                                        <input type="text" name="subject" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">{{ get_label('recipients', 'Recipients') }}</label>
                                    <select name="emails[]" class="form-control to_emails" multiple="multiple" required>
                                    </select>
                                    <small
                                        class="text-muted">{{ get_label('email_add_note', 'You can type and add multiple emails') }}</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="mb-3">
                                    <label for="message" class="form-label">
                                        <?= get_label('message', 'Message') ?>
                                    </label>
                                    <textarea class="form-control description" rows="5" name="body"
                                        placeholder="<?= get_label('please_enter_message', 'Please enter message') ?>">{{ old('body') }}</textarea>

                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header bg-label-primary">
                                            <h6 class="mb-0">{{ get_label('attachments', 'Attachments') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <label for="custom_attachments"
                                                class="form-label">{{ get_label('choose_files', 'Choose files to upload') }}</label>
                                            <input type="file" name="attachments[]" id="custom_attachments"
                                                class="form-control" multiple>
                                            <div id="custom-file-list" class="d-none mt-3">
                                                <h6 class="small">{{ get_label('selected_files', 'Selected Files:') }}
                                                </h6>
                                                <ul class="list-unstyled mb-0" id="custom-file-names"></ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header bg-label-primary">
                                            <h6 class="mb-0">{{ get_label('delivery_options', 'Delivery Options') }}
                                            </h6>
                                        </div>
                                        <div class="card-body mt-3">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="customScheduleToggle"
                                                    name="schedule_toggle"
                                                    data-select-time-error="{{ get_label('select_time_error', 'Please select a time to schedule the email') }}">
                                                <label class="form-check-label" for="customScheduleToggle">
                                                    {{ get_label('schedule_email', 'Schedule Email') }}
                                                </label>
                                            </div>

                                            <div id="customScheduleField" class="d-none">
                                                <label
                                                    class="form-label">{{ get_label('schedule_at', 'Schedule Date & Time') }}</label>
                                                <input type="datetime-local" name="scheduled_at" class="form-control"
                                                    min="{{ now()->format('Y-m-d\TH:i') }}">
                                                <small class="text-muted">
                                                    {{ get_label('timezone_note', 'Timezone:') }}
                                                    {{ config('app.timezone') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-start mt-3">
                                <button type="submit" id="submit_btn" class="custom_submit_btn btn btn-primary hover-shadow"
                                    data-label-send="{{ get_label('send_now', 'Send Now') }}"
                                    data-label-schedule="{{ get_label('schedule_email', 'Schedule Email') }}">
                                    <i class="bx bx-send me-1"></i> {{ get_label('send_now', 'Send Now') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>





    <script>
        var logo_url = "{{ asset($general_settings['full_logo']) }}";
    </script>

    <script src="{{ asset('assets/js/pages/send-email.js') }}"></script>
@endsection