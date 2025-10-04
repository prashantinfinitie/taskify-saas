@extends('layout')

@section('title')
    <?= get_label('expense_types', 'Expense types') ?>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('expenses.index') }}"><?= get_label('expenses', 'Expenses') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('expenses_types', 'Expense types') ?>
                        </li>

                    </ol>
                </nav>
            </div>
            <div>
                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_expense_type_modal"><button
                        type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title=" <?= get_label('create_expense_type', 'Create expense type') ?>"><i
                            class="bx bx-plus"></i></button></a>
                <a href="{{ route('expenses.index') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('expenses', 'Expenses') ?>"><i
                            class='bx bx-list-ul'></i></button></a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    @if ($expense_types > 0)
                        <input type="hidden" id="data_type" value="expense-type">

                        <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ route('expenses-type.list') }}" data-icons-prefix="bx" data-icons="icons"
                            data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                            data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                            data-side-pagination="server" data-show-columns="true" data-pagination="true"
                            data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                            data-query-params="queryParamsExpenseTypes">
                            <thead>
                                <tr>
                                    <th data-checkbox="true"></th>
                                    <th data-sortable="true" data-field="id"><?= get_label('id', 'ID') ?></th>
                                    <th data-sortable="true" data-field="title"><?= get_label('title', 'Title') ?></th>
                                    <th data-sortable="true" data-field="description" data-visible="false">
                                        <?= get_label('description', 'Description') ?></th>
                                    <th data-sortable="true" data-field="created_at" data-visible="false">
                                        <?= get_label('created_at', 'Created at') ?></th>
                                    <th data-sortable="true" data-field="updated_at" data-visible="false">
                                        <?= get_label('updated_at', 'Updated at') ?></th>
                                    <th data-formatter="actionsFormatter"><?= get_label('actions', 'Actions') ?></th>
                                </tr>
                            </thead>
                        </table>
                    @else
                        <?php
                        $type = 'Expense types'; ?>
                        <x-empty-state-card :type="$type" />
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script>
        var label_update = '<?= get_label('update', 'Update') ?>';
        var label_delete = '<?= get_label('delete', 'Delete') ?>';
    </script>
    <script src="{{ asset('assets/js/pages/expenses.js') }}"></script>
@endsection
