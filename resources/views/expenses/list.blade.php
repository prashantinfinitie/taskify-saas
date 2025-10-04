@extends('layout')
@section('title')
    <?= get_label('expenses', 'Expenses') ?>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('/home') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('expenses', 'Expenses') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_expense_modal"><button
                        type="button" class="btn btn-sm btn-primary action_create_expenses" data-bs-toggle="tooltip"
                        data-bs-placement="left"
                        data-bs-original-title=" <?= get_label('create_expense', 'Create expense') ?>"><i
                            class="bx bx-plus"></i></button></a>
                <a href="{{ route('expenses-type.index') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('expense_types', 'Expense types') ?>"><i
                            class='bx bx-list-ul'></i></button></a>
            </div>
        </div>
        @if ($expenses > 0)
            @php
                $visibleColumns = getUserPreferences('expenses');
            @endphp
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="input-group input-group-merge">
                                <input type="text" id="expense_from_date_between" class="form-control"
                                    placeholder="<?= get_label('date_between', 'Date between') ?>" autocomplete="off">
                            </div>
                        </div>
                        @if (isAdminOrHasAllDataAccess())
                            <div class="col-md-4 mb-3">
                                <select class="form-select" id="user_filter" aria-label="Default select example">

                                </select>
                            </div>
                        @endif
                        <div class="col-md-4 mb-3">
                            <select class="form-select" id="type_filter" aria-label="Default select example">
                                <option value=""><?= get_label('select_type', 'Select type') ?></option>
                                @foreach ($expense_types as $type)
                                    <option value="{{ $type->id }}">{{ $type->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <input type="hidden" id="expense_date_from">
                    <input type="hidden" id="expense_date_to">
                    <div class="table-responsive text-nowrap">
                        <input type="hidden" id="data_type" value="expenses">
                        <input type="hidden" id="save_column_visibility">
                        <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ route('expenses.list') }}" data-icons-prefix="bx" data-icons="icons" data-show-refresh="true"
                            data-total-field="total" data-trim-on-search="false" data-data-field="rows"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-side-pagination="server"
                            data-show-columns="true" data-pagination="true" data-sort-name="id" data-sort-order="desc"
                            data-mobile-responsive="true" data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th data-checkbox="true"></th>
                                    <th data-field="id"
                                        data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('id', 'ID') ?></th>
                                    <th data-field="title"
                                        data-visible="{{ in_array('title', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('title', 'Title') ?></th>
                                    <th data-field="expense_type_id"
                                        data-visible="{{ in_array('expense_type_id', $visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('expense_type_id', 'Expense type ID') ?></th>
                                    <th data-field="expense_type"
                                        data-visible="{{ in_array('expense_type', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('expense_type', 'Expense type') ?></th>
                                    <th data-field="user_id"
                                        data-visible="{{ in_array('user_id', $visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('user_id', 'User ID') ?></th>
                                    <th data-field="user"
                                        data-visible="{{ in_array('user', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('user', 'User') ?></th>
                                    <th data-field="amount"
                                        data-visible="{{ in_array('amount', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('amount', 'Amount') ?></th>
                                    <th data-field="expense_date"
                                        data-visible="{{ in_array('expense_date', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('expense_date', 'Expense date') ?></th>
                                    <th data-field="note"
                                        data-visible="{{ in_array('note', $visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('note', 'Note') ?></th>
                                    <th data-field="created_by"
                                        data-visible="{{ in_array('created_by', $visibleColumns) ? 'true' : 'false' }}"
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
            $type = 'Expenses'; ?>
            <x-empty-state-card :type="$type" />
        @endif
    </div>
    <script>
        var label_update = '<?= get_label('update', 'Update') ?>';
        var label_delete = '<?= get_label('delete', 'Delete') ?>';
        var label_duplicate = '<?= get_label('duplicate', 'Duplicate') ?>';
    </script>
    <script src="{{ asset('assets/js/pages/expenses.js') }}"></script>
@endsection
