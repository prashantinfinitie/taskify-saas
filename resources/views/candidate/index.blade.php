@extends('layout')

@section('title')
    {{ get_label('candidates', 'Candidates') }}
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
                            <span>{{ get_label('candidates', 'Candidates') }}</span>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('list', 'List') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                @php
                    $candidateDefaultView = getUserPreferences('candidates', 'default_view');
                @endphp
                @if ($candidateDefaultView === 'list')
                    <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
                @else
                    <a href="javascript:void(0);"><span class="badge bg-secondary" id="set-default-view" data-type="candidates"
                            data-view="list"><?= get_label('set_as_default_view', 'Set as Default View') ?></span></a>
                @endif
            </div>
            <div>

                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#candidateModal">
                    <button type="button" class="btn btn-sm btn-primary action_create_template" data-bs-toggle="tooltip"
                        data-bs-placement="left"
                        data-bs-original-title="{{ get_label('create_candidate', 'Create Candidate') }}">
                        <i class='bx bx-plus'></i>
                    </button>
                </a>

                <a href="{{ route('candidate.kanban_view') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('kanban_view', 'Kanban View') ?>"><i
                            class="bx bx-layout"></i></button></a>
            </div>
        </div>
        @if ($candidates->count() > 0)
        @php
            $visibleColumns = getUserPreferences('candidate');
        @endphp
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" id="candidate_filter_date_range"
                            placeholder="<?= get_label('date_between', 'Date Between') ?>" autocomplete="off">

                        <input type="hidden" id="candidate_start_date" name="start_date" value="">
                        <input type="hidden" id="candidate_end_date" name="end_date" value="">

                    </div>

                    <div class="col-md-3 mb-3">
                        <select class="form-select js-example-basic-multiple" id="sort" name="sort"
                            aria-label="Default select example"
                            data-placeholder="<?= get_label('select_sort_by', 'Select Sort By') ?>" data-allow-clear="true">
                            <option></option>
                            <option value="newest" <?= request()->sort && request()->sort == 'newest' ? "selected" : "" ?>>
                                <?= get_label('newest', 'Newest') ?>
                            </option>
                            <option value="oldest" <?= request()->sort && request()->sort == 'oldest' ? "selected" : "" ?>>
                                <?= get_label('oldest', 'Oldest') ?>
                            </option>
                            <option value="recently-updated" <?= request()->sort && request()->sort == 'recently-updated' ? "selected" : "" ?>>
                                <?= get_label('most_recently_updated', 'Most recently updated') ?>
                            </option>
                            <option value="earliest-updated" <?= request()->sort && request()->sort == 'earliest-updated' ? "selected" : "" ?>>
                                <?= get_label('least_recently_updated', 'Least recently updated') ?>
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <select class="form-select" id="select_candidate_statuses" name="statuses[]"
                            aria-label="Default select example"
                            data-placeholder="<?= get_label('filter_by_statuses', 'Filter by statuses') ?>"
                            data-allow-clear="true" multiple></select>
                    </div>
                </div>
                <div class="table-responsive text-nowrap">
                    <input type="hidden" id="data_type" value="candidate">
                    <input type="hidden" id="save_column_visibility">
                    <table id="table" data-toggle="table" data-url="{{ route('candidate.list') }}"
                        data-loading-template="loadingTemplate" data-icons-prefix="bx" data-icons="icons"
                        data-show-refresh="true" data-total-field="total" data-trim-on-search="false" data-data-field="rows"
                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-side-pagination="server"
                        data-show-columns="true" data-pagination="true" data-sort-name="id" data-sort-order="desc"
                        data-mobile-responsive="true" data-query-params="queryParams">
                        <thead>
                            <tr>
                                <th data-checkbox="true"></th>
                                <th data-field="id" data-sortable="true"
                                    data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                    {{ get_label('id', 'ID') }}
                                </th>
                                <th data-field="name" data-sortable="true"
                                    data-visible="{{ in_array('name', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                    {{ get_label('name', 'Name') }}
                                </th>
                                <th data-field="email"
                                    data-visible="{{ in_array('email', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                    {{ get_label('email', 'Email') }}
                                </th>
                                <th data-field="phone" data-escap="false"
                                    data-visible="{{ in_array('phone', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                    {{ get_label('phone_number', 'Phone Number') }}
                                </th>
                                <th data-field="position" data-sortable="true"
                                    data-visible="{{ in_array('position', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                    {{ get_label('position', 'Position') }}
                                </th>
                                <th data-field="status" data-sortable="true"
                                    data-visible="{{ in_array('status', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                    {{ get_label('status', 'Status') }}
                                </th>
                                <th data-field="source" data-sortable="true"
                                    data-visible="{{ in_array('source', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                    {{ get_label('source', 'Source') }}
                                </th>
                                <th data-field="interviews" data-sortable="false">
                                    {{ get_label('interviews', 'Interviews') }}
                                </th>

                                <th data-field="created_at" data-sortable="true"
                                    data-visible="{{ in_array('created_at', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                    {{ get_label('created_at', 'Created at') }}
                                </th>
                                <th data-field="updated_at" data-sortable="true"
                                    data-visible="{{ in_array('updated_at', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                    {{ get_label('updated_at', 'Updated at') }}
                                </th>
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
            $type = 'Candidates'; ?>
            <x-empty-state-card :type="$type" />
        @endif
        <script src="{{ asset('assets/js/pages/candidate.js') }}"></script>
    </div>
@endsection
