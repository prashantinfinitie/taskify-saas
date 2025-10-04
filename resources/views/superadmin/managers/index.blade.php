@extends('layout')
@section('title')
    {{ get_label('managers', 'Managers') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between flex-wraps mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('superadmin.panel') }}"><?= get_label('home', 'Home') ?></a>
                        </li>

                        <li class="breadcrumb-item active">
                            <?= get_label('managers', 'Managers') ?>
                        </li>

                    </ol>
                </nav>
            </div>

            <div>
                <a href="{{ route('managers.create') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title=" <?= get_label('create_manager', 'Create Manager') ?>"><i
                            class="bx bx-plus"></i></button></a>
            </div>
        </div>
        @if (is_countable($managers) && count($managers) > 0)
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ get_label('managers', 'Managers') }}</h4>
                    <input type="hidden" id="data_type" value="managers">
                </div>
                <div class="card-body">
                    <div class="alert alert-primary mt-3" role="alert">
                        {{ get_label('manager_alert', 'As a Manager, user can access and manage Plans, Subscriptions, Transactions, and Customers And Support') }}
                    </div>
                    <div class="table-responsive text-nowrap">

                        <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ route('managers.list') }}" data-icons-prefix="bx" data-icons="icons"
                            data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                            data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                            data-side-pagination="server" data-show-columns="true" data-pagination="true"
                            data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                            data-query-params="queryParams">
                            <thead>
                                <tr>

                                    <th data-visible="false" data-sortable="true" data-field="id">
                                        {{ get_label('id', 'ID') }}</th>
                                    <th data-field="first_name" data-sortable="true">
                                        {{ get_label('first_name', 'First Name') }}</th>
                                    <th data-field="last_name" data-sortable="true">
                                        {{ get_label('last_name', 'Last Name') }}</th>
                                    <th data-field="phone">{{ get_label('phone_number', 'Phone Number') }}</th>
                                    <th data-field="email">{{ get_label('email', 'Email') }}</th>
                                    <th data-field="status">{{ get_label('status', 'Status') }}</th>
                                    <th data-formatter="actionFormatter">{{ get_label('actions', 'Actions') }}</th>
                                </tr>
                            </thead>
                        </table>

                    </div>
                </div>
            </div>
        @else
            <?php
            $type = 'Managers'; ?>
            <x-empty-state-card :type="$type" />
        @endif

    </div>
    @php
        $routePrefix = Route::getCurrentRoute()->getPrefix();
    @endphp

    <script>
        var label_update = '<?= get_label('update ', 'Update ') ?>';
        var label_delete = '<?= get_label('delete ', 'Delete ') ?>';
        var routePrefix = '{{ $routePrefix }}';
    </script>

    <script src="{{ asset('assets/js/pages/managers.js') }}"></script>
@endsection
