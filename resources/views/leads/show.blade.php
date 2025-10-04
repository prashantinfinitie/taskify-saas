@extends('layout')
@section('title')
    {{ get_label('lead_information', 'Lead Information') }} - {{ $lead->id }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}">
                                {{ get_label('home', 'Home') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            {{ get_label('leads_management', 'Leads Management') }}
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('leads.index') }}">
                                {{ get_label('leads', 'Leads') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ ucwords($lead->first_name . ' ' . $lead->last_name) }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <div class="btn-group">
                    <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-edit-alt"></i> {{ get_label('update', 'Update') }}
                    </a>

                </div>
            </div>
        </div>
        <div class="row">
            <!-- Lead Profile Card -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column align-items-center text-center">
                            <div class="avatar avatar-xl mb-3">
                                <span class="avatar-initial rounded-circle bg-primary text-white">
                                    {{ substr($lead->first_name, 0, 1) . substr($lead->last_name, 0, 1) }}
                                </span>
                            </div>
                            <h4>{{ ucwords($lead->first_name . ' ' . $lead->last_name) }}</h4>
                            <p class="text-muted mb-1">{{ $lead->job_title }}</p>
                            <p class="text-muted">{{ $lead->company }}</p>
                            <div class="d-flex mb-3 gap-2">
                                @if ($lead->email)
                                    <a href="mailto:{{ $lead->email }}"
                                        class="btn btn-sm btn-outline-primary rounded-pill hover-action">
                                        <i class="bx bx-envelope"></i>
                                    </a>
                                @endif
                                @if ($lead->phone)
                                    <a href="tel:{{ $lead->phone }}"
                                        class="btn btn-sm btn-outline-success rounded-pill hover-action">
                                        <i class="bx bx-phone"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">{{ get_label('lead_stage','Lead Stage') }}</span>
                            <span class="badge bg-label-{{ $lead->stage->color }}">{{ $lead->stage->name }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">{{ get_label('lead_source','Lead Source') }}</span>
                            <span>{{ $lead->source->name ?? 'N/A' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">{{ get_label('industry','Industry') }}</span>
                            <span>{{ $lead->industry }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">{{ get_label('created','Created') }}</span>
                            <span>{{ format_date($lead->created_at, true) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">{{ get_label('assigned_to','Assigned To') }}</span>
                            <span>{!! formatUserHtml($lead->assigned_user) !!}</span>
                        </div>
                    </div>
                </div>
                <!-- Social Links Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ get_label('social_links','Social Links') }}</h5>
                    </div>
                    <div class="card-body">
                        @if ($lead->website)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <span class="avatar avatar-xs">
                                        <i class="bx bx-globe text-primary"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <a href="{{ $lead->website }}" target="_blank" class="text-body">{{ get_label('website','Website') }}</a>
                                </div>
                            </div>
                        @endif
                        @if ($lead->linkedin)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <span class="avatar avatar-xs">
                                        <i class="bx bxl-linkedin-square text-primary"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <a href="{{ $lead->linkedin }}" target="_blank" class="text-body">{{get_label('linkedin','LinkedIn')}}</a>
                                </div>
                            </div>
                        @endif
                        @if ($lead->facebook)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <span class="avatar avatar-xs">
                                        <i class="bx bxl-facebook-circle text-primary"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <a href="{{ $lead->facebook }}" target="_blank" class="text-body">{{ get_label('facebook','Facebook') }}</a>
                                </div>
                            </div>
                        @endif
                        @if ($lead->instagram)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <span class="avatar avatar-xs">
                                        <i class="bx bxl-instagram text-danger"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <a href="{{ $lead->instagram }}" target="_blank" class="text-body">{{ get_label('instagram','Instagram') }}</a>
                                </div>
                            </div>
                        @endif
                        @if ($lead->pinterest)
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="avatar avatar-xs">
                                        <i class="bx bxl-pinterest text-danger"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <a href="{{ $lead->pinterest }}" target="_blank" class="text-body">{{ get_label('pinterest','Pinterest') }}</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Lead Details Card -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ get_label('lead_details','Lead Details') }}</h5>
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info"
                                    role="tab"> <i class="bx bx-info-circle me-1"></i> {{ get_label('info','Info') }}</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#follow-ups"
                                    role="tab"> <i class="bx bx-calendar-event me-1"></i> {{ get_label('follow_ups','Follow Ups') }}</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Info Tab -->
                            <div class="tab-pane fade show active" id="info" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="card border">
                                            <div class="card-header bg-transparent">
                                                <h6 class="card-title mb-0">
                                                    <i class="bx bx-user me-2"></i>{{ get_label('contact_information','Contact Information') }}
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <small class="text-muted d-block">{{ get_label('name','Name') }}</small>
                                                    <span>{{ ucwords($lead->first_name . ' ' . $lead->last_name) }}</span>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted d-block">{{ get_label('email','Email') }}</small>
                                                    <span>
                                                        <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                                                    </span>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">{{ get_label('phone_number','Phone Number') }}</small>
                                                    <span>
                                                        <a href="tel:{{ $lead->country_code }}{{ $lead->phone }}">
                                                            <img src="https://flagcdn.com/16x12/{{ strtolower($lead->country_iso_code) }}.png"
                                                                alt="{{ $lead->country_iso_code }}" class="me-1 w-auto">
                                                            {{ $lead->country_code }} {{ $lead->phone }}
                                                        </a>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="card border">
                                            <div class="card-header bg-transparent">
                                                <h6 class="card-title mb-0">
                                                    <i class="bx bx-building me-2"></i>{{ get_label('company_info', 'Company Information') }}
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <small class="text-muted d-block">{{ get_label('company','Company') }}</small>
                                                    <span>{{ $lead->company }}</span>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted d-block">{{ get_label('job_title','Job Title') }}</small>
                                                    <span>{{ $lead->job_title }}</span>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">{{ get_label('industry','Industry') }}</small>
                                                    <span>{{ $lead->industry }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="card border">
                                            <div class="card-header bg-transparent">
                                                <h6 class="card-title mb-0">
                                                    <i class="bx bx-map me-2"></i>{{ get_label('address_information','Address Information') }}
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <small class="text-muted d-block">{{ get_label('city','City') }}</small>
                                                        <span>{{ $lead->city }}</span>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <small class="text-muted d-block">{{ get_label('state','State') }}</small>
                                                        <span>{{ $lead->state }}</span>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <small class="text-muted d-block">{{ get_label('zip_code','Zip Code') }}</small>
                                                        <span>{{ $lead->zip }}</span>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">{{ get_label('country','Country') }}</small>
                                                        <span>{{ $lead->country }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Follow Ups Tab -->
                            <div class="tab-pane fade" id="follow-ups" role="tabpanel">
                                <div class="mb-3 text-end">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#create_lead_follow_up_modal">
                                        <i class="bx bx-plus me-1"></i>
                                        {{ get_label('create_lead_follow_up', 'Create Follow Up') }}
                                    </button>
                                </div>
                                {{-- @dd($lead) --}}

                                @forelse($lead->follow_ups()->orderBy('follow_up_at', 'desc')->get() as $followUp)
                                <div
                                    class="card @if ($followUp->status === 'completed') border-success
                                        @elseif($followUp->status === 'rescheduled') border-info
                                        @else border-warning @endif border-bottom-0 border-end-0 border-top-0 mb-3 border-4">

                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-semibold text-primary text-uppercase mb-1">
                                                    {{ get_label('follow_up_on', 'Follow-up on') }} :
                                                    {{ format_date($followUp->follow_up_at, true) }}
                                                </h6>
                                                <div class="d-flex align-items-center small text-muted">
                                                    <i
                                                        class="bx @if ($followUp->type === 'call') bx-phone
                                                                    @elseif($followUp->type === 'email') bx-envelope
                                                                    @elseif($followUp->type === 'sms') bx-message
                                                                    @elseif($followUp->type === 'meeting') bx-calendar
                                                                    @else bx-clipboard @endif me-1">
                                                    </i>
                                                    {{ ucfirst($followUp->type) }}
                                                    <span
                                                        class="badge @if ($followUp->status === 'completed') bg-label-success
                                                                        @elseif($followUp->status === 'rescheduled') bg-label-info
                                                                        @else bg-label-warning @endif ms-2">
                                                        {{ ucfirst($followUp->status) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="align-items-center d-flex">
                                                <a href="javascript:void(0);" class="edit-lead-follow-up"
                                                    data-id="{{ $followUp->id }}"
                                                    title="{{ get_label('update', 'Update') }}"><i
                                                        class="bx bx-edit mx-1"></i></a>
                                                <button title="{{ get_label('delete', 'Delete') }}" type="button"
                                                    class="btn delete" data-id="{{ $followUp->id }}"
                                                    data-reload="true"
                                                    data-type="leads/follow-up" data-table="table"><i
                                                        class="bx bx-trash text-danger mx-1"></i></button>
                                            </div>
                                        </div>

                                        @if ($followUp->note)
                                            <div class="small text-body mt-2">
                                                {!! $followUp->note !!}
                                            </div>
                                        @endif

                                        <div class="d-flex small text-muted mt-3 flex-wrap gap-3">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-user me-1"></i>
                                                <span>{{ get_label('assigned_to', 'Assigned To') }}:
                                                    {{ ucwords($followUp->assignedTo->first_name . ' ' . $followUp->assignedTo->last_name) }}</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-calendar-check me-1"></i>
                                                <span>{{ get_label('created_at', 'Created At') }}:
                                                    {{ format_date($followUp->created_at, true) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="bg-light rounded p-5 text-center">
                                    <i class="bx bx-calendar-exclamation fs-1 text-secondary mb-3"></i>
                                    <h6 class="mb-2">{{ get_label('no_follow_ups_found','No follow-ups found') }}</h6>
                                    <p class="text-muted mb-3">
                                        {{ get_label('create_your_first_follow_up_to_track_interactions_with_this_lead','Create your first follow-up to track interactions with this lead.') }}
                                    </p>
                                    <button class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#create_lead_follow_up_modal">
                                        <i class="bx bx-plus me-1"></i> {{ get_label('create_follow_up','Create Follow-up') }}
                                    </button>
                                </div>
                            @endforelse
                            </div>

                        </div>
                    </div>
                </div>
                {{-- @dd($lead->custom_fields) --}}
                @if(!empty($lead->custom_fields))
                    @php
                        $customFields = json_decode($lead->custom_fields, true);
                    @endphp

                    <div class="card my-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Additional Info</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($customFields as $label => $field)
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-semibold d-block">
                                            {{ $label }}
                                            @if(!empty($field['required']))
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                    
                                        @switch($field['type'])
                                            @case('textarea')
                                                <div class="border rounded p-2 bg-light">{{ $field['value'] }}</div>
                                                @break
                                    
                                            @case('url')
                                                <a href="{{ $field['value'] }}" target="_blank">{{ $field['value'] }}</a>
                                                @break
                                    
                                            @case('date')
                                                <div>{{ \Carbon\Carbon::parse($field['value'])->format('d M Y') }}</div>
                                                @break
                                    
                                            @case('checkbox')
                                            @php
                                                $selected = is_array($field['value']) ? $field['value'] : [$field['value']];
                                                $options = $field['options'] ?? $selected;
                                            @endphp

                                            @foreach($options as $option)
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" disabled {{ in_array($option, $selected) ? 'checked' : '' }}>
                                                    <label class="form-check-label">{{ $option }}</label>
                                                </div>
                                            @endforeach
                                            @break
                                            
                                            
                                            @case('select')
                                                <div class="badge bg-primary">{{ $field['value'] }}</div>
                                                @break
                                            
                                            @case('number')
                                                <div>{{ number_format($field['value'], 0, '.', ',') }}</div>
                                                @break
                                            
                                            @default
                                                <div>{{ $field['value'] }}</div>
                                        @endswitch
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!--- Follow Ups Modal -->
    <div class="modal fade" id="create_lead_follow_up_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content form-submit-event" action="{{ route('lead_follow_up.store') }}" method="POST">
                {{-- <input type="hidden" name="dnr"> --}}
                <input type="hidden" name="lead_id" value="{{ $lead->id }}" />
                <div class="modal-header">
                    <h5 class="modal-title">{{ get_label('create_lead_follow_up', 'Create Lead Follow Up') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="assign_to" class="form-label">{{ get_label('assigned_to', 'Assign To') }} <span
                                    class="asterisk">*</span></label>
                            <select name="assigned_to" class="form-select" id="create_follow_up_assigned_to"
                                data-single-select="true" data-allow-clear="false" data-consider-workspace="true"
                                required>
                                <option value="">{{ get_label('assigned_to', 'Assigned To') }}</option>
                            </select>
                            @error('assigned_to')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="follow_up_at"
                                class="form-label">{{ get_label('follow_up_date', 'Follow Up Date') }} <span
                                    class="asterisk">*</span></label>
                            <input type="datetime-local" name="follow_up_at" class="form-control" required>
                            <small
                                class="text-muted">{{ get_label('follow_up_date_info', 'This date will help you record when the follow-up is taken.') }}</small>
                            @error('follow_up_at')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">{{ get_label('follow_up_type', 'Follow Up Type') }}
                                <span class="asterisk">*</span></label>
                            <select class="form-select" name="type">
                                <option value="call">{{ get_label('call', 'Call') }}</option>
                                <option value="email">{{ get_label('email', 'Email') }}</option>
                                <option value="meeting">{{ get_label('meeting', 'Meeting') }}</option>
                                <option value="sms">{{ get_label('sms', 'SMS') }}</option>
                                <option value="other">{{ get_label('other', 'Other') }}</option>
                            </select>
                            <small
                                class="text-muted">{{ get_label('follow_up_type_info', 'Categorize the follow-up, for example: call, email, etc.') }}</small>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="status" class="form-label">{{ get_label('status', 'Status') }}</label>
                            <select name="status" class="form-select">
                                <option value="pending">{{ get_label('pending', 'Pending') }}</option>
                                <option value="completed">{{ get_label('completed', 'Completed') }}</option>
                                <option value="rescheduled">{{ get_label('rescheduled', 'Rescheduled') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-lable" for="note">{{ get_label('note', 'Note') }}</label>
                            <textarea name="note" class="form-control" id="follow_up_note"></textarea>
                            <small
                                class="text-muted">{{ get_label('follow_up_note_info', 'Add any notes that you want to keep for this follow-up.') }}</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ get_label('close', 'Close') }}
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        {{ get_label('create', 'Create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="edit_lead_follow_up_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content form-submit-event" action="{{ route('lead_follow_up.update') }}" method="POST">
                {{-- <input type="hidden" name="dnr"> --}}
                <input type="hidden" name="id" />
                <div class="modal-header">
                    <h5 class="modal-title">{{ get_label('edit_lead_follow_up', 'Edit Lead Follow Up') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="assign_to" class="form-label">{{ get_label('assigned_to', 'Assign To') }} <span
                                    class="asterisk">*</span></label>
                            <select name="assigned_to" class="form-select" id="edit_follow_up_assigned_to"
                                data-single-select="true" data-allow-clear="false" data-consider-workspace="true"
                                required>
                                <option value="">{{ get_label('assigned_to', 'Assigned To') }}</option>
                            </select>
                            @error('assigned_to')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="follow_up_at"
                                class="form-label">{{ get_label('follow_up_date', 'Follow Up Date') }} <span
                                    class="asterisk">*</span></label>
                            <input type="datetime-local" name="follow_up_at" class="form-control" required>
                            <small
                                class="text-muted">{{ get_label('follow_up_date_info', 'This date will help you record when the follow-up is taken.') }}</small>
                            @error('follow_up_at')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">{{ get_label('follow_up_type', 'Follow Up Type') }}
                                <span class="asterisk">*</span></label>
                            <select class="form-select" name="type">
                                <option value="call">{{ get_label('call', 'Call') }}</option>
                                <option value="email">{{ get_label('email', 'Email') }}</option>
                                <option value="meeting">{{ get_label('meeting', 'Meeting') }}</option>
                                <option value="sms">{{ get_label('sms', 'SMS') }}</option>
                                <option value="other">{{ get_label('other', 'Other') }}</option>
                            </select>
                            <small
                                class="text-muted">{{ get_label('follow_up_type_info', 'Categorize the follow-up, for example: call, email, etc.') }}</small>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="status" class="form-label">{{ get_label('status', 'Status') }}</label>
                            <select name="status" class="form-select">
                                <option value="pending">{{ get_label('pending', 'Pending') }}</option>
                                <option value="completed">{{ get_label('completed', 'Completed') }}</option>
                                <option value="rescheduled">{{ get_label('rescheduled', 'Rescheduled') }}</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-lable" for="note">{{ get_label('note', 'Note') }}</label>
                            <textarea name="note" class="form-control" id="edit_follow_up_note"></textarea>
                            <small
                                class="text-muted">{{ get_label('follow_up_note_info', 'Add any notes that you want to keep for this follow-up.') }}</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ get_label('close', 'Close') }}
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        {{ get_label('create', 'Create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
