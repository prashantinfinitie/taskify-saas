@extends('layout')

@section('title')
    <?= get_label('payslips', 'Payslips') ?>
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
                        <li class="breadcrumb-item active">
                            <?= get_label('payslips', 'Payslips') ?>
                        </li>

                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('payslips.create') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title=" <?= get_label('create_payslip', 'Create payslip') ?>"><i
                            class="bx bx-plus"></i></button></a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                @if ($payslips > 0)
                    @php
                        $visibleColumns = getUserPreferences('payslips');
                    @endphp
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input class="form-control" type="month" id="filter_payslip_month" name="month">

                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="user_filter" aria-label="Default select example">

                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="created_by_filter" aria-label="Default select example">

                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="status_filter" aria-label="Default select example">
                                <option value="">
                                    <?= get_label('select_payment_status', 'Select payment status') ?>
                                </option>
                                <option value="1"><?= get_label('paid', 'Paid') ?></option>
                                <option value="0"><?= get_label('unpaid', 'Unpaid') ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <input type="hidden" id="data_type" value="payslips">
                        <input type="hidden" id="data_table" value="payslips_table">
                        <input type="hidden" id="save_column_visibility">
                        <table id="payslips_table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ route('payslips.list') }}" data-icons-prefix="bx" data-icons="icons"
                            data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                            data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                            data-side-pagination="server" data-show-columns="true" data-pagination="true"
                            data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                            data-query-params="queryParams" data-route-prefix="{{ Route::getCurrentRoute()->getPrefix() }}">

                            <thead>
                                <tr>
                                    <th data-checkbox="true"></th>
                                    <th data-field="id"
                                        data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true" data-formatter="idFormatter"><?= get_label('id', 'ID') ?></th>
                                    <th data-field="user"
                                        data-visible="{{ in_array('user', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="false"><?= get_label('member', 'Member') ?></th>
                                    <th data-field="month"
                                        data-visible="{{ in_array('month', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('month', 'Month') ?></th>
                                    <th data-field="basic_salary"
                                        data-visible="{{ in_array('basic_salary', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('basic_salary', 'Basic salary') ?></th>
                                    <th data-field="working_days" data-sortable="true"
                                        data-visible="{{ in_array('working_days', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('working_days', 'Working days') ?></th>
                                    <th data-field="lop_days" data-sortable="true"
                                        data-visible="{{ in_array('lop_days', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('lop_days', 'Loss of pay days') ?></th>
                                    <th data-field="paid_days" data-sortable="true"
                                        data-visible="{{ in_array('paid_days', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('paid_days', 'Paid days') ?></th>
                                    <th data-field="leave_deduction" data-sortable="true"
                                        data-visible="{{ in_array('leave_deduction', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('leave_deduction', 'Leave deduction') ?></th>
                                    <th data-field="ot_hours" data-sortable="true"
                                        data-visible="{{ in_array('ot_hours', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('over_time_hours', 'Over time hours') ?></th>
                                    <th data-field="ot_rate" data-sortable="true"
                                        data-visible="{{ in_array('ot_rate', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('over_time_rate', 'Over time rate') ?></th>
                                    <th data-field="ot_payment" data-sortable="true"
                                        data-visible="{{ in_array('ot_payment', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('over_time_payment', 'Over time payment') ?></th>
                                    <th data-field="incentives" data-sortable="true"
                                        data-visible="{{ in_array('incentives', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('incentives', 'Incentives') ?></th>
                                    <th data-field="bonus" data-sortable="true"
                                        data-visible="{{ in_array('bonus', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('bonus', 'Bonus') ?></th>
                                    <th data-field="total_allowance" data-sortable="true"
                                        data-visible="{{ in_array('total_allowance', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('total_allowance', 'Total allowance') ?></th>
                                    <th data-field="total_deductions" data-sortable="true"
                                        data-visible="{{ in_array('total_deductions', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('total_deductions', 'Total deductions') ?></th>
                                    <th data-field="net_pay"
                                        data-visible="{{ in_array('net_pay', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('net_pay', 'Net pay') ?></th>
                                    <th data-field="payment_method" data-sortable="true"
                                        data-visible="{{ in_array('payment_method', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('payment_method', 'Payment method') ?></th>
                                    <th data-field="payment_date" data-sortable="true"
                                        data-visible="{{ in_array('payment_date', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('payment_date', 'Payment date') ?></th>
                                    <th data-field="status"
                                        data-visible="{{ in_array('status', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('status', 'Status') ?></th>
                                    <th data-field="note" data-sortable="true"
                                        data-visible="{{ in_array('note', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('note', 'Note') ?></th>
                                    <th data-field="created_by" data-sortable="false"
                                        data-visible="{{ in_array('created_by', $visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('created_by', 'Created by') }}</th>
                                    <th data-field="created_at" data-sortable="true"
                                        data-visible="{{ in_array('created_at', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('created_at', 'Created at') ?></th>
                                    <th data-field="updated_at" data-sortable="true"
                                        data-visible="{{ in_array('updated_at', $visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('updated_at', 'Updated at') ?></th>
                                    <th data-field="actions"
                                        data-visible="{{ in_array('actions', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('actions', 'Actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @else
                    <?php
                    $type = 'Payslips'; ?>
                    <x-empty-state-card :type="$type" />
                @endif
            </div>
        </div>
    </div>


    <script>
        var label_update = '<?= get_label('update ', 'Update ') ?>';
        var label_delete = '<?= get_label('delete ', 'Delete ') ?>';
        var label_duplicate = '<?= get_label('duplicate ', 'Duplicate ') ?>';
        var label_payslip_id_prefix = '<?= get_label('payslip_id_prefix ', 'PSL - ') ?>';
        var label_select_member = '<?= get_label('select_member', 'Select Member') ?>';
        var label_select_created_by ="<?= get_label('select_created_by', 'Select Created By') ?>";
    </script>
    <script src="{{ asset('assets/js/pages/payslips.js') }}"></script>
@endsection
