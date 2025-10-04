@extends('layout')

@section('title')
    {{ get_label('create_lead_form', 'Create Lead Form') }}
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
                            {{ get_label('leads_management', 'Leads Management') }}
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{route('lead-forms.index')}}">{{ get_label('lead_forms', 'Lead Forms') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('create', 'Create') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>

                <a href="{{route('lead-forms.index')}}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('list_view', 'List view') ?>"><i
                            class='bx bx-list-ul'></i></button></a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="leadFormCreate" action="{{ route('lead-forms.store') }}" method="POST" class="form-submit-event">
                    @csrf
                    <input type="hidden" name="redirect_url" value="{{ route('lead-forms.index') }}">
                    <div class="row">
                        <!-- Form Configuration -->
                        <div class="col-md-12">
                            <h5>{{ get_label('form_configuration', 'Form Configuration') }}</h5>
                            <hr>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">{{ get_label('title', 'Form Title') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required
                                placeholder="{{ get_label('enter_form_title', 'Enter a descriptive title for your form') }}"
                                value="{{ old('title') }}">
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="source_id" class="form-label">{{ get_label('source', 'Lead Source') }} <span
                                    class="text-danger">*</span></label>
                            <select class="form-select select2" id="select_lead_source" name="source_id"
                                data-single-select="true" data-allow-clear="false" data-consider-workspace="true" required>
                                <option value="">{{ get_label('select_source', 'Select Source') }}</option>
                                @foreach ($sources as $source)
                                    <option value="{{ $source->id }}">{{ $source->name }}</option>
                                @endforeach
                            </select>
                            @error('source_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="stage_id" class="form-label">{{ get_label('stage', 'Initial Stage') }} <span
                                    class="text-danger">*</span></label>
                            <select class="form-select select2" id="selected_stages" name="stage_id"
                                data-single-select="true" data-allow-clear="false" data-consider-workspace="true" required>
                                <option value="">{{ get_label('select_stage', 'Select Stage') }}</option>
                                @foreach ($stages as $stage)
                                    <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                @endforeach
                            </select>
                            @error('stage_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="assigned_to" class="form-label">{{ get_label('assigned_to', 'Assign To') }} <span
                                    class="text-danger">*</span></label>
                            <select class="form-select select2" id="select_lead_assignee" name="assigned_to"
                                data-single-select="true" data-allow-clear="false" data-consider-workspace="true" required>
                                <option value="">{{ get_label('select_user', 'Select User') }}</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="description"
                                class="form-label">{{ get_label('description', 'Description') }}</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                placeholder="{{ get_label('enter_description', 'Provide a brief description of the form\'s purpose (optional)') }}">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Form Fields Configuration -->
                        <div class="col-md-12 mt-4">
                            <h5>{{ get_label('form_fields', 'Form Fields') }}</h5>
                            <hr>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="alert alert-info">
                                <strong>{{ get_label('note', 'Note') }}:</strong>
                                {{ get_label('mandatory_fields', 'The following fields are mandatory and automatically included:') }}
                                {{ implode(',   ', array_map(fn($field) => \App\Models\LeadFormField::MAPPABLE_FIELDS[$field], \App\Models\LeadFormField::REQUIRED_FIELDS)) }}
                            </div>


                        </div>

                        <div class="col-md-12">
                            <div id="mandatoryFields">
                                @foreach (\App\Models\LeadFormField::REQUIRED_FIELDS as $index => $field)
                                    <div class="field-row bg-light mb-3 rounded border p-3" data-id="{{ $index }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <strong>{{ \App\Models\LeadFormField::MAPPABLE_FIELDS[$field] }}</strong>
                                                <input type="hidden" name="fields[{{ $index }}][label]"
                                                    value="{{ \App\Models\LeadFormField::MAPPABLE_FIELDS[$field] }}">
                                                <input type="hidden" name="fields[{{ $index }}][name]" value="{{ $field }}">
                                                <input type="hidden" name="fields[{{ $index }}][type]"
                                                    value="{{ $field == 'email' ? 'email' : ($field == 'phone' ? 'tel' : 'text') }}">
                                                <input type="hidden" name="fields[{{ $index }}][is_required]" value="1">
                                                <input type="hidden" name="fields[{{ $index }}][is_mapped]" value="1">
                                                <input type="hidden" name="fields[{{ $index }}][options][]" value="">
                                            </div>
                                            <div class="col-md-8">
                                                <small
                                                    class="text-muted">{{ get_label('required_field', 'Required field (automatically included)') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="alert alert-primary d-flex align-items-center">
                            <i class="bx bx-move fs-4 me-2"></i>
                            <span class="fw-semibold">{{ get_label(
        'custom_form_fields_reorder_info',
        'Drag and drop the rows below to change the order of your form fields.'
    ) }}</span>
                        </div>

                        <div class="col-md-12">
                            <div id="fieldsContainer" class="sortable"></div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <button type="button" class="btn btn-outline-primary" onclick="addField()">
                                <i class="bx bx-plus"></i> {{ get_label('add_field', 'Add Field') }}
                            </button>
                        </div>

                        <div class="mt-4 text-start">
                            <button type="submit" class="btn btn-primary me-2"
                                id="submit_btn">{{ get_label('create', 'Create') }}</button>
                            <button type="reset"
                                class="btn btn-outline-secondary">{{ get_label('cancel', 'Cancel') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Include external scripts -->
    <script src="https://cdn.jsdelivr.net/npm/axios@1.4.0/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        window.appConfig = {
            fieldIndex: {{ count(\App\Models\LeadFormField::REQUIRED_FIELDS) }},
            labels: {
                field_label: "{{ get_label('label', 'Field Label') }}",
                enter_field_label: "{{ get_label('enter_field_label', 'Enter a clear label for this field') }}",
                field_type: "{{ get_label('type', 'Field Type') }}",
                select_type: "{{ get_label('select_type', 'Select Type') }}",
                map_to: "{{ get_label('map_to', 'Map to Lead Field') }}",
                custom_field: "{{ get_label('custom_field', 'Custom Field') }}",
                required: "{{ get_label('required', 'Required') }}",
                no: "{{ get_label('no', 'No') }}",
                yes: "{{ get_label('yes', 'Yes') }}",
                options: "{{ get_label('options', 'Options') }}",
                add_option: "{{ get_label('add_option', 'Add option') }}",
                add: "{{ get_label('add', 'Add') }}"
            },
            fieldTypes: [
                @foreach (\App\Models\LeadFormField::FIELD_TYPES as $value => $label)
                            {
                        value: "{{ $value }}",
                        label: "{{ $label }}"
                    },
                @endforeach
                ],
            mappableFields: [
                @foreach (\App\Models\LeadFormField::MAPPABLE_FIELDS as $value => $label)
                    @if (!in_array($value, \App\Models\LeadFormField::REQUIRED_FIELDS))
                                    {
                            value: "{{ $value }}",
                            label: "{{ $label }}"
                        },
                    @endif
                @endforeach
                ]
        };
    </script>
    <script src="{{ asset('assets/js/pages/lead-form.js') }}"></script>
@endsection