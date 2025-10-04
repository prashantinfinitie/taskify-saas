@extends('layout')
@section('title')
    <?= get_label('subscriptions', 'Subscriptions') ?>
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
                            <?= get_label('subscriptions', 'Subscriptions') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('subscriptions.create') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title=" <?= get_label('create_subscription', 'Create Subscription') ?>"><i
                            class="bx bx-plus"></i></button></a>

            </div>
        </div>
        @if (is_countable($subscriptions) && count($subscriptions) > 0)
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ get_label('subscriptions', 'Subscriptions') }}</h4>
                    <input type="hidden" id="data_type" value="subscriptions">
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">{{ get_label('filter_by_plans', 'Filter by plans') }}</label>
                            <select class="form-select" name="filter_plans" id="filter_plans">
                                <option value="">{{ get_label('select_plans', 'Select Plans') }}</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ ucfirst($plan->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ get_label('filter_by_status', 'Filter by status') }}</label>
                            <select class="form-select" id="status">
                                <option value="">{{ get_label('select_status', 'Select Status') }}</option>
                                <option value="active">{{ get_label('active', 'Active') }}</option>
                                <option value="inactive">{{ get_label('inactive', 'Inactive') }}</option>
                                <option value="pending">{{ get_label('pending', 'Pending') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ route('subscriptions.list') }}" data-icons-prefix="bx" data-icons="icons"
                            data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                            data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                            data-side-pagination="server" data-show-columns="true" data-pagination="true"
                            data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                            data-query-params="queryParams">
                            <thead>
                                <tr>

                                    <th data-visible="false" data-sortable="true" data-field="id">
                                        {{ get_label('id', 'ID') }}</th>
                                    <th data-field="user_name">{{ get_label('user_name', 'User Name') }}</th>
                                    <th data-field="plan_name">{{ get_label('plan_name', 'Plan Name') }}</th>
                                    <th data-field="tenure">{{ get_label('tenure', 'Tenure') }}</th>
                                    <th data-field="start_date">{{ get_label('starts_at', 'Start Date') }}</th>
                                    <th data-field="end_date">{{ get_label('end_date', 'End Date') }}</th>
                                    <th data-field="payment_method">{{ get_label('payment_method', 'Payment Method') }}
                                    </th>
                                    <th data-field="features">{{ get_label('features', 'Features') }}</th>
                                    <th data-sortable="true" data-field="charging_price">
                                        {{ get_label('charging_price', 'Charging Price') }}</th>
                                    <th data-visible="false" data-field="charging_currency">
                                        {{ get_label('charging_currency', 'Charging Currency') }}</th>
                                    <th data-field="status">{{ get_label('status', 'Status') }}</th>
                                    <th data-formatter="actionFormatter">{{ get_label('actions', 'Actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Bootstrap Modal -->
            <div class="modal fade" id="subscriptionModal" tabindex="-1"  data-bs-keyboard="false"
                role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                <div class="modal-dialog  modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-body">
                            <h5 class="modal-title text-capitalize" id="modalTitleId">
                                <i
                                    class="bx bx-info-circle me-2"></i>{{ get_label('subscription_detail', 'Subscription Detail') }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-body text-capitalize">
                            <div class="row g-4">
                                <div class="col-lg-8">
                                    <div class="card mb-4 shadow-lg">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-sm-6 mb-3">
                                                    <h6 class="text-muted">
                                                        {{ get_label('basic_info', 'Basic Information') }}</h6>
                                                    <p><strong>{{ get_label('id', 'ID') }}:</strong> <span
                                                            id="subscriptionId" class="badge bg-label-primary"></span></p>
                                                    <p><strong>{{ get_label('user', 'User') }}:</strong> <span
                                                            id="subscriptionUser"></span></p>
                                                    <p><strong>{{ get_label('plan', 'Plan') }}:</strong> <span
                                                            id="subscriptionPlan" class="badge bg-label-info"></span></p>
                                                    <p><strong>{{ get_label('status', 'Status') }}:</strong> <span
                                                            id="subscriptionStatus" class=""></span></p>
                                                </div>
                                                <div class="col-sm-6 mb-3">
                                                    <h6 class="text-muted">
                                                        {{ get_label('payment_info', 'Payment Information') }}</h6>
                                                    <p><strong>{{ get_label('payment_method', 'Payment Method') }}:</strong>
                                                        <span id="subscriptionPaymentMethod"></span></p>
                                                    <p><strong>{{ get_label('tenure', 'Tenure') }}:</strong> <span
                                                            id="subscriptionTenure"></span></p>
                                                    <p><strong>{{ get_label('price', 'Price') }}:</strong> <span
                                                            id="subscriptionPrice" class="text-primary fw-bold"></span>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <h6 class="text-muted">
                                                        {{ get_label('subscription_period', 'Subscription Period') }}</h6>
                                                    <p><strong>{{ get_label('starts_at', 'Starts At') }}:</strong> <span
                                                            id="subscriptionStartsAt"></span></p>
                                                    <p><strong>{{ get_label('ends_at', 'Ends At') }}:</strong> <span
                                                            id="subscriptionEndsAt"></span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card shadow-lg">
                                        <div class="card-body">
                                            <h6 class="card-subtitle text-muted mb-3">
                                                {{ get_label('transactions', 'Transactions') }}</h6>
                                            <div class="table-responsive">
                                                <table class="table-bordered table-striped table">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>{{ get_label('id', 'ID') }}</th>
                                                            <th>{{ get_label('amount', 'Amount') }}</th>
                                                            <th>{{ get_label('status', 'Status') }}</th>
                                                            <th>{{ get_label('payment_method', 'Payment Method') }}</th>
                                                            <th>{{ get_label('transaction_id', 'Transaction ID') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="subscriptionTransactions"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="card mb-4 shadow-lg">
                                        <div class="card-body">
                                            <h6 class="card-subtitle text-muted mb-3">
                                                {{ get_label('features', 'Features') }}</h6>
                                            <ul id="subscriptionFeatures" class="list-group list-group-flush"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

@else
    <?php
    $type = 'Subscriptions'; ?>
    <x-empty-state-card :type="$type" />
    @endif
    </div>
    @php
        $routePrefix = Route::getCurrentRoute()->getPrefix();
    @endphp
    <script>
        var label_update = '<?= get_label('upgrade ', 'Upgrade') ?>';
        var label_delete = '<?= get_label('delete', 'Delete') ?>';
        var label_view = '<?= get_label('view ', 'View ') ?>';
        var routePrefix = '{{ $routePrefix }}';
    </script>
    <script src="{{ asset('assets/js/pages/subscriptions.js') }}"></script>
@endsection
