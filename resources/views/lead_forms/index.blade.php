@extends('layout')

@section('title')
    {{ get_label('lead_forms', 'Lead Forms') }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home.index') }}">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        {{ get_label('leads_management', 'Leads Management') }}
                    </li>
                    <li class="breadcrumb-item active">
                        {{ get_label('lead_forms', 'Lead Forms') }}
                    </li>
                </ol>
            </nav>
            <a href="{{route('lead-forms.create')}}">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                    title="{{ get_label('create_lead_form', 'Create Lead Form') }}">
                    <i class="bx bx-plus"></i>
                </button>
            </a>
        </div>
            {{-- @dd($forms) --}}
        @if ($forms->count() > 0)
            @php $visibleColumns = getUserPreferences('lead_forms'); @endphp
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <input type="hidden" id="data_type" value="lead-forms">
                        <table id="table" data-toggle="table" data-loading-template="loadingTemplate" data-url="{{ route('lead-forms.list') }}"
                            data-icons-prefix="bx" data-icons="icons" data-show-refresh="true" data-total-field="total"
                            data-trim-on-search="false" data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-search="true" data-side-pagination="server" data-show-columns="true" data-pagination="true"
                            data-sort-name="created_at" data-sort-order="desc" data-mobile-responsive="true"
                            data-query-params="queryParamsLeadForms">
                            <thead>
                                <tr>
                                    <th data-checkbox="true"></th>
                                    <th data-field="id"
                                        data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('id', 'ID') }}</th>
                                    <th data-field="title"
                                        data-visible="{{ in_array('title', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('title', 'Title') }}</th>
                                    <th data-field="description"
                                        data-visible="{{ in_array('description', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('description', 'Description') }}</th>
                                    <th data-field="source"
                                        data-visible="{{ in_array('source', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('source', 'Source') }}</th>
                                    <th data-field="stage"
                                        data-visible="{{ in_array('stage', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('stage', 'Stage') }}</th>
                                    <th data-field="assigned_to"
                                        data-visible="{{ in_array('assigned_to', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('assigned_to', 'Assigned To') }}</th>
                                    <th data-field="public_url"
                                        data-visible="{{ in_array('public_url', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('public_url', 'Public URL') }}</th>
                                    <th data-field="responses"
                                        data-visible="{{ in_array('responses', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('responses', 'Responses') }}</th>
                                    <th data-field="created_at"
                                        data-visible="{{ in_array('created_at', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('created_at', 'Created At') }}</th>
                                    <th data-field="updated_at"
                                        data-visible="{{ in_array('updated_at', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('updated_at', 'Updated At') }}</th>
                                    <th data-field="actions"
                                        data-visible="{{ in_array('actions', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('actions', 'Actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        @else
            @php $type = 'Lead Forms'; @endphp
            <x-empty-state-card :type="$type" />
        @endif

        <!-- Embed Form Iframe Container -->
        <div id="embedFormContainer" class="d-none position-fixed bottom-0 end-0 m-3 shadow-lg"
            style="width: 450px; height: 600px; z-index: 1050;">
            <div class="card h-100 border-0">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" id="embedFormTitle">{{ get_label('form_preview', 'Form Preview') }}</h5>
                    <button type="button" class="btn-close" onclick="toggleEmbedForm()"></button>
                </div>
                <div class="card-body d-flex flex-column p-0">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <button class="nav-link active" onclick="showEmbedTab('preview')">
                                <i class="bx bx-show"></i> {{ get_label('preview', 'Preview') }}
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" onclick="showEmbedTab('code')">
                                <i class="bx bx-code"></i> {{ get_label('code', 'Code') }}
                            </button>
                        </li>
                    </ul>
                    <div id="embedPreviewTab" class="tab-content flex-grow-1 d-block p-3">
                        <div id="embedFormPreview" class="h-100 overflow-auto rounded border bg-white"></div>
                    </div>
                    <div id="embedCodeTab" class="tab-content flex-grow-1 d-none p-3">
                        <div class="mb-2">
                            <button class="btn btn-sm btn-primary" onclick="copyCurrentEmbedCode()">
                                <i class="bx bx-copy"></i> {{ get_label('copy', 'Copy') }}
                            </button>
                        </div>
                        <textarea id="embedFormCode" class="form-control h-100" rows="8" readonly></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var label_update = '{{ get_label('update', 'Update') }}';
        var label_delete = '{{ get_label('delete', 'Delete') }}';
    </script>

    <script>
        window.appConfig = {
            labels: {
                copied: "{{ get_label('copied', 'Copied') }}"
            }
        }
    </script>

    <script src="{{ asset('assets/js/pages/lead-form.js') }}"></script>
@endsection
