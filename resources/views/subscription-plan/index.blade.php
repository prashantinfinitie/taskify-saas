@extends('layout')

@section('title')
    <?= get_label('subscription_plan', 'Subscription Plan') ?>
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
                            <?= get_label('subscription_plan', 'Subscription Plan') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>


        @if ($activeSubscription)
            @php
                switch ($activeSubscription->tenure) {
                    case 'monthly':
                        $tenure = 30;
                        break;
                    case 'yearly':
                        $tenure = 365;
                        break;
                    case 'lifetime':
                        $tenure = 1;
                        break;
                }
                $remainingDays = abs(floor($remainingDays));
                $badgeClass = '';
                if ($remainingDays > 15) {
                    $badgeClass = 'bg-success';
                } elseif ($remainingDays > 10) {
                    $badgeClass = 'bg-warning';
                } else {
                    $badgeClass = 'bg-danger';
                }

            @endphp
            <div class="row">
                <div class="col-lg-8 order-0 mb-4">
                    <div class="card">
                        <div class="d-flex align-items-end row">
                            <div class="col-sm-7">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">Congratulations
                                        {{ ucfirst(auth()->user()->first_name) }} {{ ucfirst(auth()->user()->last_name) }}!
                                        🎉</h5>
                                    <h6 class="card-title text-muted">Here's an overview of your active subscription:</h6>
                                    <p class="mb-4">Your subscription plan,
                                        <strong>{{ $activeSubscription->plan->name }}</strong> will expire on <span
                                            class="fw-medium">{{ $activeSubscription->ends_at }}</span>. Don't miss out on
                                        our exclusive features and benefits. Renew or upgrade your plan today!
                                    </p>
                                    <a href="{{ route('subscription-plan.buy-plan') }}"
                                        class="btn btn-sm btn-outline-primary">{{ get_label('renew_manage_plan', 'Renew or Manage Plan') }}</a>
                                </div>
                            </div>
                            <div class="col-sm-5 text-sm-left text-center">
                                <div class="card-body px-md-4 px-0 pb-0">
                                    <img src="../assets/img/illustrations/man-with-laptop-light.png" height="140"
                                        alt="View Badge User" data-app-dark-img="illustrations/man-with-laptop-dark.png"
                                        data-app-light-img="illustrations/man-with-laptop-light.png">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 order-0 mb-4">
                    <div class="card">

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title text-primary mb-3">
                                        {{ get_label('active_subscription', 'Active Subscription') }}</h5>
                                    <div class="d-flex flex-column">
                                        <div class="mb-2">
                                            <span>{{ get_label('plan_name', 'Plan Name') }} : </span>
                                            <span class="text-dark">
                                                {{ $activeSubscription->plan->name }}
                                            </span>
                                        </div>
                                        <div class="mb-2">
                                            <span>{{ get_label('tenure', 'Tenure') }} : </span>
                                            <span class="text-dark">
                                                {{ ucfirst($activeSubscription->tenure) }}
                                            </span>
                                        </div>
                                        <div class="mb-2">
                                            <span>{{ get_label('remaining_days', 'Remaining Days') }} : </span>

                                            <span class="badge {{ $badgeClass }}  text-dark ms-2">
                                                {{ abs(floor($remainingDays)) }} {{ get_label('days', 'Days') }}
                                            </span>
                                            <span class="text-muted ms-2">
                                                ({{ $activeSubscription->ends_at }})
                                            </span>

                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="mt-4">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $badgeClass }}"
                                        role="progressbar" style="width: {{ ($remainingDays / $tenure) * 100 }}%;"
                                        aria-valuenow="{{ $remainingDays }}" aria-valuemin="0"
                                        aria-valuemax="{{ $tenure }}">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="alert alert-primary text-center" role="alert">
                        <p class="fs-5 text-dark mb-0">No active subscription found.</p>
                        <a href="{{ route('subscription-plan.buy-plan') }}"
                            class="btn btn-primary mt-3">{{ get_label('buy_plan', 'Buy Plan') }}</a>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-md-12">

            <div class="nav-align-top mb-4">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                            data-bs-target="#subscription_history" aria-controls="subscription_history"
                            aria-selected="true"><i class="bx bx-task"></i>
                            {{ get_label('subscription_history', 'Subscription History') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#transactions" aria-controls="transactions" aria-selected="false"
                            tabindex="-1"><i class="bx bx-money"></i>
                            {{ get_label('transactions', 'Transactions') }}</button>
                    </li>

                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="subscription_history" role="tabpanel">
                        <div class="table-responsive text-nowrap">

                            <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                                data-url="{{ route('subscription-plan.subscriptionHistory') }}" data-icons-prefix="bx"
                                data-icons="icons" data-show-refresh="true" data-total-field="total"
                                data-trim-on-search="false" data-data-field="rows"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-search-highlight="true"
                                data-side-pagination="server" data-show-columns="true" data-pagination="true"
                                data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                data-query-params="SubscriptionHistory">
                                <thead>
                                    <tr>
                                        <th data-visible='true' data-sortable="true" data-field="id">
                                            {{ get_label('id', 'ID') }}</th>
                                        <th data-field="user_name">{{ get_label('user_name', 'User Name') }}</th>
                                        <th data-field="plan_name">{{ get_label('plan_name', 'Plan Name') }}</th>
                                        <th data-field="payment_method">
                                            {{ get_label('payment_method', 'Payment Method') }}</th>
                                        <th data-sortable="true" data-field="status">
                                            {{ get_label('status', 'Status') }}</th>
                                        <th data-visible='true' data-field="charging_price">
                                            {{ get_label('charging_price', 'Charging Price') }}</th>
                                        <th data-visible='true' data-field="features">
                                            {{ get_label('features', 'Features') }}</th>
                                        <th data-visible='true' data-field="tenure">
                                            {{ get_label('tenure', 'Tenure') }}</th>
                                        <th data-visible='true' data-field="start_date">
                                            {{ get_label('started_at', 'Start Date ') }}</th>
                                        <th data-visible='true' data-field="end_date">
                                            {{ get_label('end_date', 'End Date ') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="transactions" role="tabpanel">
                        <div class="table-responsive text-nowrap">
                            <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                                data-url="{{ route('subscription-plan.transactionsList') }}" data-icons-prefix="bx"
                                data-icons="icons" data-show-refresh="true" data-total-field="total"
                                data-trim-on-search="false" data-data-field="rows"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                data-side-pagination="server" data-show-columns="true" data-pagination="true"
                                data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        <th data-visible='true' data-sortable="true" data-field="id">
                                            {{ get_label('id', 'ID') }}</th>
                                        <th data-visible='false' data-sortable="true" data-field="subscription_id">
                                            {{ get_label('subscription_id', 'Subscription Id') }}</th>
                                        <th data-visible='false' data-sortable="true" data-field="user_id">
                                            {{ get_label('user_id', 'User Id') }}</th>
                                        <th data-field="user_name">{{ get_label('user_name', 'User Name') }}</th>
                                        <th data-field="plan_name">{{ get_label('plan_name', 'Plan Name') }}</th>
                                        <th data-field="payment_method">
                                            {{ get_label('payment_method', 'Payment Method') }}</th>
                                        <th data-sortable="true" data-field="status">
                                            {{ get_label('status', 'Status') }}</th>
                                        <th data-sortable="true" data-field="amount">
                                            {{ get_label('amount', 'amount') }}</th>
                                        <th data-visible='true' data-field="currency">
                                            {{ get_label('charging_currency', 'Charging Currency') }}</th>
                                        <th data-visible='true' data-field="transaction_id">
                                            {{ get_label('transaction_id', 'Transaction ID') }}</th>
                                        <th data-visible='true' data-field="created_at">
                                            {{ get_label('created_date', 'Created  Date ') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>








    </div>


    <script src="{{ asset('assets/js/pages/subscription-plan.js') }}"></script>
@endsection
