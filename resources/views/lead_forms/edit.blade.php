@extends('layout')

@section('title')
    {{ get_label('edit_lead_form', 'Edit Lead Form') }}
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
                            {{ get_label('edit', 'Edit') }}
                        </li>
                    </ol>
                </nav>
                
            </div>
            <div>
               
               
                <a href="{{route('lead-forms.index')}}"><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                        data-bs-placement="left" data-bs-original-title="<?= get_label('list_view', 'List view') ?>"><i
                            class='bx bx-list-ul'></i></button></a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="leadFormEdit" action="{{ route('lead-forms.update', $leadForm->id) }}" method="POST"
                    class="form-submit-event">
                    @csrf
                    {{-- @method('PUT') --}}
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
                                value="{{ old('title', $leadForm->title) }}">
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
                                    <option value="{{ $source->id }}"
                                        {{ old('source_id', $leadForm->source_id) == $source->id ? 'selected' : '' }}>
                                        {{ $source->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('source_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="stage_id" class="form-label">{{ get_label('stage', 'Initial Stage') }} <span
                                    class="text-danger">*</span></label>
                            <select class="form-select select2" id="select_lead_stage" name="stage_id"
                                data-single-select="true" data-allow-clear="false" data-consider-workspace="true" required>
                                <option value="">{{ get_label('select_stage', 'Select Stage') }}</option>
                                @foreach ($stages as $stage)
                                    <option value="{{ $stage->id }}"
                                        {{ old('stage_id', $leadForm->stage_id) == $stage->id ? 'selected' : '' }}>
                                        {{ $stage->name }}
                                    </option>
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
                                    <option value="{{ $user->id }}"
                                        {{ old('assigned_to', $leadForm->assigned_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}
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
                                placeholder="{{ get_label('enter_description', 'Provide a brief description of the form\'s purpose (optional)') }}">{{ old('description', $leadForm->description) }}</textarea>
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
                                {{ get_label('mandatory_fields', 'The following fields are mandatory and automatically included: First Name, Last Name, Email, Phone, Company') }}
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div id="mandatoryFields">
                                @php
                                    $requiredFieldIndex = 0;
                                @endphp
                                @foreach (\App\Models\LeadFormField::REQUIRED_FIELDS as $field)
                                    <div class="field-row bg-light mb-3 rounded border p-3" data-id="{{ $requiredFieldIndex }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <strong>{{ \App\Models\LeadFormField::MAPPABLE_FIELDS[$field] }}</strong>
                                                <input type="hidden" name="fields[{{ $requiredFieldIndex }}][label]"
                                                    value="{{ \App\Models\LeadFormField::MAPPABLE_FIELDS[$field] }}">
                                                <input type="hidden" name="fields[{{ $requiredFieldIndex }}][name]"
                                                    value="{{ $field }}">
                                                <input type="hidden" name="fields[{{ $requiredFieldIndex }}][type]"
                                                    value="{{ $field == 'email' ? 'email' : ($field == 'phone' ? 'tel' : 'text') }}">
                                                <input type="hidden" name="fields[{{ $requiredFieldIndex }}][is_required]"
                                                    value="1">
                                                <input type="hidden" name="fields[{{ $requiredFieldIndex }}][is_mapped]"
                                                    value="1">
                                                <input type="hidden" name="fields[{{ $requiredFieldIndex }}][options][]"
                                                    value="">
                                            </div>
                                            <div class="col-md-8">
                                                <small
                                                    class="text-muted">{{ get_label('required_field', 'Required field (automatically included)') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $requiredFieldIndex++;
                                    @endphp
                                @endforeach
                            </div>
                        </div>
   <div class="alert alert-primary d-flex align-items-center">
            <i class="bx bx-move fs-4 me-2"></i>
            <span
                class="fw-semibold">{{ get_label('custom_form_fields_reorder_info','Drag and drop the rows below to change the order of your form fields.'
                ) }}</span>
        </div>
                        <div class="col-md-12">
                            <div id="fieldsContainer" class="sortable">
                                @php
                                    $fieldIndex = $requiredFieldIndex;
                                @endphp
                                @foreach ($leadForm->leadFormFields as $field)
                                    @if (!in_array($field->name, \App\Models\LeadFormField::REQUIRED_FIELDS))
                                        <div class="field-row mb-3 rounded border bg-white p-3"
                                            id="field-{{ $fieldIndex }}" data-id="{{ $fieldIndex }}">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">{{ get_label('label', 'Field Label') }}
                                                        <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control"
                                                        name="fields[{{ $fieldIndex }}][label]" required
                                                        placeholder="{{ get_label('enter_field_label', 'Enter a clear label for this field') }}"
                                                        value="{{ old('fields.' . $fieldIndex . '.label', $field->label) }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">{{ get_label('type', 'Field Type') }} <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-select" name="fields[{{ $fieldIndex }}][type]"
                                                        onchange="toggleOptions({{ $fieldIndex }})" required>
                                                        <option value="">
                                                            {{ get_label('select_type', 'Select Type') }}</option>
                                                        @foreach (\App\Models\LeadFormField::FIELD_TYPES as $value => $label)
                                                            <option value="{{ $value }}"
                                                                {{ old('fields.' . $fieldIndex . '.type', $field->type) == $value ? 'selected' : '' }}>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label
                                                        class="form-label">{{ get_label('map_to', 'Map to Lead Field') }}</label>
                                                    <select class="form-select" name="fields[{{ $fieldIndex }}][name]"
                                                        onchange="toggleMapping({{ $fieldIndex }})">
                                                        <option value="">
                                                            {{ get_label('custom_field', 'Custom Field') }}</option>
                                                        @foreach (\App\Models\LeadFormField::MAPPABLE_FIELDS as $value => $label)
                                                            @if (!in_array($value, \App\Models\LeadFormField::REQUIRED_FIELDS))
                                                                <option value="{{ $value }}"
                                                                    {{ old('fields.' . $fieldIndex . '.name', $field->name) == $value ? 'selected' : '' }}>
                                                                    {{ $label }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label
                                                        class="form-label">{{ get_label('required', 'Required') }}</label>
                                                    <select class="form-select"
                                                        name="fields[{{ $fieldIndex }}][is_required]">
                                                        <option value="0"
                                                            {{ old('fields.' . $fieldIndex . '.is_required', $field->is_required) == 0 ? 'selected' : '' }}>
                                                            {{ get_label('no', 'No') }}</option>
                                                        <option value="1"
                                                            {{ old('fields.' . $fieldIndex . '.is_required', $field->is_required) == 1 ? 'selected' : '' }}>
                                                            {{ get_label('yes', 'Yes') }}</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 text-end">
                                                    <label class="form-label"> </label>
                                                    <div>
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            onclick="removeField({{ $fieldIndex }})">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Options Container -->
                                            <div class="options-container mt-3"
                                                id="options_container_{{ $fieldIndex }}"
                                                style="display:{{ in_array($field->type, ['select', 'radio', 'checkbox']) ? 'block' : 'none' }};">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label
                                                            class="form-label">{{ get_label('options', 'Options') }}</label>
                                                        <div class="options-list mb-2"
                                                            id="options_list_{{ $fieldIndex }}">
                                                            @if ($field->options)
                                                                @foreach (json_decode($field->options, true) as $option)
                                                                    <span class="badge bg-light text-dark mb-2 me-2"
                                                                        data-value="{{ $option }}">
                                                                        {{ $option }}
                                                                        <button type="button"
                                                                            class="btn-close btn-sm ms-1"
                                                                            onclick="removeOption({{ $fieldIndex }}, '{{ $option }}')"
                                                                            aria-label="Remove option"></button>
                                                                    </span>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control"
                                                                id="option_input_{{ $fieldIndex }}"
                                                                placeholder="{{ get_label('add_option', 'Add option') }}">
                                                            <button class="btn btn-outline-primary" type="button"
                                                                onclick="addOption({{ $fieldIndex }})">
                                                                {{ get_label('add', 'Add') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <input type="hidden" name="fields[{{ $fieldIndex }}][is_mapped]"
                                                value="{{ old('fields.' . $fieldIndex . '.is_mapped', $field->is_mapped ? '1' : '0') }}"
                                                id="is_mapped_{{ $fieldIndex }}">
                                            <div class="options-hidden" id="options_hidden_{{ $fieldIndex }}">
                                                @if ($field->options)
                                                    @foreach (json_decode($field->options, true) as $option)
                                                        <input type="hidden"
                                                            name="fields[{{ $fieldIndex }}][options][]"
                                                            value="{{ $option }}">
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                        @php
                                            $fieldIndex++;
                                        @endphp
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <button type="button" class="btn btn-outline-primary" onclick="addField()">
                                <i class="bx bx-plus"></i> {{ get_label('add_field', 'Add Field') }}
                            </button>
                        </div>

                        <div class="mt-4 text-start">
                            <button type="submit"
                                class="btn btn-primary me-2">{{ get_label('update', 'Update') }}</button>
                            <a href=""
                                class="btn btn-outline-secondary">{{ get_label('cancel', 'Cancel') }}</a>
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
            fieldIndex: {{ $fieldIndex }},
            formId: 'leadFormEdit',
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
