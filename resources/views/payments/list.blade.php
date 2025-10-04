@extends('layout')
@section('title')
<?= get_label('payments', 'Payments') ?>
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{route('home.index')}}"><?= get_label('home', 'Home') ?></a>
                    </li>
                    <li class="breadcrumb-item active">
                        <?= get_label('payments', 'Payments') ?>
                    </li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_payment_modal"><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title=" <?= get_label('create_payment', 'Create payment') ?>"><i class="bx bx-plus"></i></button></a>
            <a href="{{ route('payment_method.index') }}"><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title="<?= get_label('payment_methods', 'Payment methods') ?>"><i class='bx bx-list-ul'></i></button></a>
        </div>
    </div>
    @if ($payments > 0)
    @php
    $visibleColumns = getUserPreferences('payments');
    @endphp
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="mb-3 col-md-4">
                    <div class="input-group input-group-merge">
                        <input type="text" id="payment_date_between" class="form-control" placeholder="<?= get_label('payment_date_between', 'Payment date between') ?>" autocomplete="off">
                    </div>
                </div>
                @if(isAdminOrHasAllDataAccess())
                <div class="col-md-4 mb-3">
                    <select class="form-select" id="user_filter" aria-label="Default select example">

                    </select>
                </div>
                @endif
                <div class="col-md-4 mb-3">
                    <select class="form-select" id="invoice_filter" aria-label="Default select example">

                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <select class="form-select" id="payment_method_filter" aria-label="Default select example">
                        <option value=""><?= get_label('select_payment_method', 'Select payment method') ?></option>
                        @foreach ($payment_methods as $pm)
                        <option value="{{$pm->id}}">{{$pm->title}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <input type="hidden" id="payment_date_from">
            <input type="hidden" id="payment_date_to">
            <div class="table-responsive text-nowrap">
                <input type="hidden" id="data_type" value="payments">
                <input type="hidden" id="save_column_visibility">
                <table id="table" data-toggle="table" data-loading-template="loadingTemplate" data-url="{{ route('payments.list') }}" data-icons-prefix="bx" data-icons="icons" data-show-refresh="true" data-total-field="total" data-trim-on-search="false" data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-side-pagination="server" data-show-columns="true" data-pagination="true" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-query-params="queryParams">
                    <thead>
                        <tr>
                            <th data-checkbox="true"></th>
                            <th data-field="id" data-sortable="true" data-visible="{{ (in_array('id', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}"><?= get_label('id', 'ID') ?></th>
                            <th data-field="user_id" data-sortable="true" data-visible="{{ (in_array('user_id', $visibleColumns)) ? 'true' : 'false' }}"><?= get_label('user_id', 'User ID') ?></th>
                            <th data-field="user" data-sortable="true" data-visible="{{ (in_array('user', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}"><?= get_label('user', 'User') ?></th>
                            <th data-field="invoice_id" data-sortable="true" data-visible="{{ (in_array('invoice_id', $visibleColumns)) ? 'true' : 'false' }}"><?= get_label('invoice_id', 'Invoice ID') ?></th>
                            <th data-field="invoice" data-sortable="true" data-visible="{{ (in_array('invoice', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}"><?= get_label('invoice', 'Invoice') ?></th>
                            <th data-field="payment_method_id" data-sortable="true" data-visible="{{ (in_array('payment_method_id', $visibleColumns)) ? 'true' : 'false' }}"><?= get_label('payment_method_id', 'Payment method ID') ?></th>
                            <th data-field="payment_method" data-sortable="true" data-visible="{{ (in_array('payment_method', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}"><?= get_label('payment_method', 'Payment method') ?></th>
                            <th data-field="amount" data-sortable="true" data-visible="{{ (in_array('amount', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}"><?= get_label('amount', 'Amount') ?></th>
                            <th data-field="payment_date" data-sortable="true" data-visible="{{ (in_array('payment_date', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}"><?= get_label('payment_date', 'Payment date') ?></th>
                            <th data-field="note" data-sortable="true" data-visible="{{ (in_array('note', $visibleColumns)) ? 'true' : 'false' }}"><?= get_label('note', 'Note') ?></th>
                            <th data-field="created_by" data-sortable="false" data-visible="{{ (in_array('created_by', $visibleColumns)) ? 'true' : 'false' }}"><?= get_label('created_by', 'Created by') ?></th>
                            <th data-field="created_at" data-sortable="true" data-visible="{{ (in_array('created_at', $visibleColumns)) ? 'true' : 'false' }}"><?= get_label('created_at', 'Created at') ?></th>
                            <th data-field="updated_at" data-sortable="true" data-visible="{{ (in_array('updated_at', $visibleColumns)) ? 'true' : 'false' }}"><?= get_label('updated_at', 'Updated at') ?></th>
                            <th data-field="actions" data-formatter="actionsFormatter" data-visible="{{ (in_array('actions', $visibleColumns) || empty($visibleColumns)) ? 'true' : 'false' }}"><?= get_label('actions', 'Actions') ?></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    @else
    <?php
    $type = 'Payments'; ?>
    <x-empty-state-card :type="$type" />
    @endif
</div>
<script>
    var label_update = '<?= get_label('update', 'Update') ?>';
    var label_delete = '<?= get_label('delete', 'Delete') ?>';
    var label_duplicate = '<?= get_label('duplicate', 'Duplicate') ?>';
    var label_select_invoice = "<?= get_label('select_invoice', 'Select Invoice') ?>";
</script>
<script src="{{asset('assets/js/pages/payments.js')}}"></script>
@endsection
