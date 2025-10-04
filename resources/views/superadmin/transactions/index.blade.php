@extends('layout')

@section('title')
    <?= get_label('transactions', 'Transactions') ?>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('superadmin.panel') }}"><?= get_label('home', 'Home') ?></a>
                        </li>

                        <li class="breadcrumb-item active">
                            <?= get_label('transactions', 'Transactions') ?>
                        </li>

                    </ol>
                </nav>
            </div>


        </div>

        <div class="card">
            <div class="card-header  d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">{{ get_label('transactions', 'Transactions') }}</h4>
                <input type="hidden" id="data_type" value="transactions">
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">{{ get_label('filter_by_users' , 'Filter by users' ) }}</label>
                        <select class="form-select" name="filter_by_users" id="filter_by_users">
                        <option value="">{{ get_label('select_user' , 'Select User') }}</option>
                        @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ ucfirst($user->first_name) }} {{ ucfirst($user->last_name) }}</option>
                        @endforeach
                        </select>
                    </div>
                </div>
                <div class="table-responsive text-nowrap">

                        <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ route('transactions.list') }}" data-icons-prefix="bx" data-icons="icons"
                            data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                            data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                            data-side-pagination="server" data-show-columns="true" data-pagination="true"
                            data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                            data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th data-visible="false" data-sortable="true" data-field="id">
                                        {{ get_label('id', 'ID') }}</th>
                                    <th data-visible="false" data-sortable="true" data-field="subscription_id">
                                        {{ get_label('subscription_id', 'Subscription Id') }}</th>
                                    <th data-visible="false" data-sortable="true" data-field="user_id">
                                        {{ get_label('user_id', 'User Id') }}</th>
                                    <th data-field="user_name">{{ get_label('user_name', 'User Name') }}</th>
                                    <th data-field="payment_method">{{ get_label('payment_method', 'Payment Method') }}</th>
                                    <th data-sortable="true" data-field="amount">{{ get_label('amount', 'amount') }}</th>
                                    <th data-visible="true" data-field="currency">
                                        {{ get_label('charging_currency', 'Charging Currency') }}</th>
                                    <th data-visible="true" data-field="transaction_id">
                                        {{ get_label('transaction_id', 'Transaction ID') }}</th>
                                    <th data-field="status">{{ get_label('status', 'Status') }}</th>
                                    <th data-visible="true" data-field="created_at">
                                        {{ get_label('created_date', 'Created Date ') }}</th>
                                </tr>
                            </thead>
                        </table>

                </div>
            </div>
        </div>

    </div>
    <script src="{{ asset('assets/js/pages/transactions.js') }}"></script>
@endsection
