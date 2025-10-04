@extends('layout')
@section('title')
    {{ get_label('leads', 'Leads') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('home') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <?= get_label('leads_management', 'Leads Management') ?>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('leads', 'Leads') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                @php
                    $leadsDefaultView = getUserPreferences('leads', 'default_view');
                @endphp
                @if ($leadsDefaultView === 'list')
                    <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
                @else
                    <a href="javascript:void(0);"><span class="badge bg-secondary" id="set-default-view" data-type="leads"
                            data-view="list"><?= get_label('set_as_default_view', 'Set as Default View') ?></span></a>
                @endif
            </div>
            <div>
                <a href="{{ route('leads.create') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('create_lead', 'Create Lead') ?>"><i
                            class="bx bx-plus"></i></button></a>
                <a href="{{ route('leads.kanban_view') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('kanban_view', 'Kanban View') ?>"><i
                            class="bx bx-layout"></i></button></a>
                <a href="{{ route('leads.upload') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('bulk_upload', 'Bulk Upload') ?>"><i
                            class="bx bx-upload"></i></button></a>
            </div>
        </div>
        {{-- @dd($leads) --}}
        @if ($leads->count() > 0)
            @php
                $visibleColumns = getUserPreferences('leads');
            @endphp
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <select class="form-select js-example-basic-multiple" id="sort" aria-label="Default select example"
                                data-placeholder="<?= get_label('select_sort_by', 'Select Sort By') ?>" data-allow-clear="true">
                                <option></option>
                                <option value="newest"> {{ get_label('newest', 'Newest') }}</option>
                                <option value="oldest">{{ get_label('oldest', 'Oldest') }}</option>
                                <option value="recently-updated">
                                    {{ get_label('most_recently_updated', 'Most recently updated') }}
                                </option>
                                <option value="earliest-updated">
                                    {{ get_label('least_recently_updated', 'Least recently updated') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <select class="form-select" id="selected_sources" name="sources[]"
                                aria-label="Default select example"
                                data-placeholder="{{ get_label('filter_by_sources', 'Filter by sources') }}"
                                data-allow-clear="true" multiple>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <input type="text" name="date_range" id="filter_date_range" class="form-control"
                                placeholder="{{ get_label('filter_by_date_range', 'Filter by date range') }}" value=""
                                autocomplete="off">
                            <input type="hidden" name="start_date" id="lead_start_date" value="">
                            <input type="hidden" name="end_date" id="lead_end_date" value="">
                        </div>
                        <div class="col-md-3 mb-3">
                            <select class="form-select" id="selected_stages" name="stages[]" aria-label="Default select example"
                                data-placeholder="{{ get_label('filter_by_stages', 'Filter by stages') }}"
                                data-allow-clear="true" multiple>
                            </select>
                        </div>

                    </div>
                    <div class="table-responsive text-nowrap">
                        <input type="hidden" id="data_type" value="leads">
                        <input type="hidden" id="save_column_visibility">
                        <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ route('leads.list') }}" data-icons-prefix="bx" data-icons="icons"
                            data-show-refresh="true" data-total-field="total" data-trim-on-search="false" data-data-field="rows"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-side-pagination="server"
                            data-show-columns="true" data-pagination="true" data-sort-name="order" data-sort-order="asc"
                            data-mobile-responsive="true" data-query-params="queryParamsLead">
                            <thead>
                                <tr>
                                    <th data-checkbox="true"></th>
                                    <th data-field="id"
                                        data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('id', 'ID') }}</th>
                                    <th data-field="name"
                                        data-visible="{{ in_array('name', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('name', 'Name') }}</th>
                                    <th data-field="company"
                                        data-visible="{{ in_array('company', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('company', 'Company') }}</th>
                                    <th data-field="website"
                                        data-visible="{{ in_array('website', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('website', 'Website') }}</th>
                                    <th data-field="job_title"
                                        data-visible="{{ in_array('job_title', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('job_title', 'Job Title') }}</th>
                                    <th data-field="stage"
                                        data-visible="{{ in_array('stage', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="false">{{ get_label('stage', 'Stage') }}</th>
                                    <th data-field="source"
                                        data-visible="{{ in_array('source', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="false">{{ get_label('source', 'Source') }}</th>
                                    <th data-field="assigned_to"
                                        data-visible="{{ in_array('assigned_to', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="false">{{ get_label('assigned_to', 'Assigned To') }}</th>
                                    <th data-field="created_at"
                                        data-visible="{{ in_array('created_at', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('created_at', 'Created At') }}</th>
                                    <th data-field="updated_at"
                                        data-visible="{{ in_array('updated_at', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('updated_at', 'Updated At') }}</th>
                                    <th data-field="actions"
                                        data-visible="{{ in_array('actions', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('actions', 'Actions') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <?php
            $type = 'Leads'; ?>
            <x-empty-state-card :type="$type" />
        @endif
    </div>
    <script src="{{ asset('assets/js/pages/lead.js') }}"></script>

@endsection
