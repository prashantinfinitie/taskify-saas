@extends('layout')
@section('title')
    <?= get_label('contracts', 'Contracts') ?>
@endsection
@php
    $visibleColumns = getUserPreferences('contracts');
@endphp
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('contracts', 'Contracts') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_contract_modal"><button
                        type="button" class="btn btn-sm btn-primary action_create_contracts" data-bs-toggle="tooltip"
                        data-bs-placement="left"
                        data-bs-original-title=" <?= get_label('create_contract', 'Create contract') ?>"><i
                            class="bx bx-plus"></i></button></a>
                <a href="{{ route('contracts.contract_types') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('contract_types', 'Contract types') ?>"><i
                            class='bx bx-list-ul'></i></button></a>
            </div>
        </div>
        @if ($contracts > 0)
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="input-group input-group-merge">
                                <input type="text" id="contract_start_date_between" class="form-control"
                                    placeholder="<?= get_label('from_date_between', 'From date between') ?>"
                                    autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="input-group input-group-merge">
                                <input type="text" id="contract_end_date_between" class="form-control"
                                    placeholder="<?= get_label('to_date_between', 'To date between') ?>" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <select class="form-select" id="project_filter" aria-label="Default select example">

                            </select>
                        </div>
                        @if (!isClient())
                            <div class="col-md-4 mb-3">
                                <select class="form-select" id="client_filter" aria-label="Default select example">

                                </select>
                            </div>
                        @endif
                        <div class="col-md-4 mb-3">
                            <select class="form-select" id="type_filter" aria-label="Default select example">
                                <option value=""><?= get_label('select_type', 'Select type') ?></option>
                                @foreach ($contract_types as $type)
                                    <option value="{{ $type->id }}">{{ $type->type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <select class="form-select" id="status_filter" aria-label="Default select example">
                                <option value=""><?= get_label('select_status', 'Select status') ?></option>
                                <option value="signed"><?= get_label('signed', 'Signed') ?></option>
                                <option value="not_signed"><?= get_label('not_signed', 'Not signed') ?></option>
                                <option value="partially_signed"><?= get_label('partially_signed', 'Partially signed') ?>
                                </option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="start_date_from" id="contract_start_date_from">
                    <input type="hidden" name="start_date_to" id="contract_start_date_to">
                    <input type="hidden" name="end_date_from" id="contract_end_date_from">
                    <input type="hidden" name="end_date_to" id="contract_end_date_to">
                    <div class="table-responsive text-nowrap">
                        <input type="hidden" id="data_type" value="contracts">
                        <input type="hidden" id="data_table" value="contracts_table">
                        <input type="hidden" id="save_column_visibility">
                        <table id="contracts_table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ route('contracts.list') }}" data-icons-prefix="bx" data-icons="icons" data-show-refresh="true"
                            data-total-field="total" data-trim-on-search="false" data-data-field="rows"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-side-pagination="server"
                            data-show-columns="true" data-pagination="true" data-sort-name="id" data-sort-order="desc"
                            data-mobile-responsive="true" data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th data-checkbox="true"></th>
                                    <th data-field="id"
                                        data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true" data-formatter="idFormatter"><?= get_label('id', 'ID') ?>
                                    </th>
                                    <th data-field="title"
                                        data-visible="{{ in_array('title', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('title', 'Title') ?></th>
                                    <th data-field="client"
                                        data-visible="{{ in_array('client', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="false"><?= get_label('client', 'Client') ?></th>
                                    <th data-field="project"
                                        data-visible="{{ in_array('project', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="false"><?= get_label('project', 'Project') ?></th>
                                    <th data-field="contract_type"
                                        data-visible="{{ in_array('contract_type', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="false"><?= get_label('type', 'Type') ?></th>
                                    <th data-field="start_date"
                                        data-visible="{{ in_array('start_date', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('starts_at', 'Starts at') ?></th>
                                    <th data-field="end_date"
                                        data-visible="{{ in_array('end_date', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('ends_at', 'Ends at') ?></th>
                                    <th data-field="duration"
                                        data-visible="{{ in_array('duration', $visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="false"><?= get_label('duration', 'Duration') ?></th>
                                    <th data-field="value"
                                        data-visible="{{ in_array('value', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('value', 'Value') ?></th>
                                    <th data-field="promisor_sign"
                                        data-visible="{{ in_array('promisor_sign', $visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">
                                        <?= get_label('promisor_sign_status', 'Promisor sign status') ?></th>
                                    <th data-field="promisee_sign"
                                        data-visible="{{ in_array('promisee_sign', $visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">
                                        <?= get_label('promisee_sign_status', 'Promisee sign status') ?></th>
                                    <th data-field="status"
                                        data-visible="{{ in_array('status', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('status', 'Status') ?></th>
                                    <th data-field="description"
                                        data-visible="{{ in_array('description', $visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('description', 'Description') ?></th>
                                    <th data-field="created_by"
                                        data-visible="{{ in_array('created_by', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="false"><?= get_label('created_by', 'Created by') ?></th>
                                    <th data-field="created_at"
                                        data-visible="{{ in_array('created_at', $visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('created_at', 'Created at') ?></th>
                                    <th data-field="updated_at"
                                        data-visible="{{ in_array('updated_at', $visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('updated_at', 'Updated at') ?></th>
                                    <th data-field="actions"
                                        data-visible="{{ in_array('actions', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('actions', 'Actions') ?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <?php
            $type = 'Contracts'; ?>
            <x-empty-state-card :type="$type" />
        @endif
    </div>
    <script>
        var label_update = '<?= get_label('update', 'Update') ?>';
        var label_delete = '<?= get_label('delete', 'Delete') ?>';
        var label_duplicate = '<?= get_label('duplicate', 'Duplicate') ?>';
        var label_contract_id_prefix = '<?= get_label('contract_id_prefix', 'CTR-') ?>';
    </script>
    <script src="{{ asset('assets/js/pages/contracts.js') }}"></script>
@endsection
