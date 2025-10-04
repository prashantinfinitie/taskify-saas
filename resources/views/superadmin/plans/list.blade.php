@extends('layout')

@section('title')
    <?= get_label('plans', 'Plans') ?>
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
                            <?= get_label('plans', 'Plans') ?>
                        </li>
                    </ol>
                </nav>
            </div>

            <div>
                <a href="{{ route('plans.create') }}">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title=" <?= get_label('create_plan', 'Create Plan') ?>">
                        <i class="bx bx-plus"></i>
                    </button>
                </a>

            </div>
        </div>
        @if (is_countable($plans) && count($plans) > 0)
            <div class="card">
                <div class="card-header  d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ get_label('plans', 'Plans') }}</h4>
                    <input type="hidden" id="data_type" value="plans">
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">{{ get_label('filter_by_status' , 'Filter by status') }}</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">{{ get_label('select_status' , 'Select Status') }}</option>
                                <option value="active">{{ get_label('active' , 'Active') }}</option>
                                <option value="inactive">{{ get_label('inactive' , 'Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ get_label('filter_by_type' , 'Filter By Type') }}</label>
                            <select id="filter_by_type" class="form-select">
                                <option value="">{{ get_label('select_type' ,'Select Type') }}</option>
                                <option value="free">{{ get_label('free' , 'Free') }}</option>
                                <option value="paid">{{ get_label('paid' , 'Paid') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive text-nowrap">

                        <table id="table" class="table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ route('plans.list') }}" data-icons-prefix="bx" data-icons="icons"
                            data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                            data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                            data-side-pagination="server" data-show-columns="true" data-pagination="true"
                            data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                            data-query-params="queryParams">
                            <thead>
                                <tr>

                                    <th data-visible="false" data-sortable="true" data-field="id">
                                        {{ get_label('id', 'ID') }}</th>
                                    <th data-field="name">{{ get_label('name', 'Name') }}</th>
                                    <th data-visible="false" data-field="description" data-sortable="true">
                                        {{ get_label('description', 'Description') }}</th>
                                    <th data-field="plan_type">{{ get_label('plan_type', 'Plan Type') }}</th>
                                    <th data-visible="false" data-field="max_projects">
                                        {{ get_label('max_projects', 'Maximum Projects') }}</th>
                                    <th data-visible="false" data-field="max_clients">
                                        {{ get_label('max_clients', 'Maximum Clients') }}</th>
                                    <th data-visible="false" data-field="max_team_members">
                                        {{ get_label('max_team_members', 'Maximum Team Members') }}</th>
                                    <th data-visible="false" data-field="max_workspaces">
                                        {{ get_label('max_workspaces', 'Maximum Workshops') }}</th>
                                    <th data-field="modules">{{ get_label('modules', 'Modules') }}</th>
                                    <th data-field="monthly_price">{{ get_label('monthly_price', 'Monthly Price') }}
                                    </th>
                                    <th data-visible="false" data-field="monthly_discounted_price">
                                        {{ get_label('monthly_discounted_price', 'Monthly Discounted Price') }}</th>
                                    <th data-field="yearly_price">{{ get_label('yearly_price', 'Yearly Price') }}</th>
                                    <th data-visible="false" data-field="yearly_discounted_price">
                                        {{ get_label('yearly_discounted_price', 'Yearly Discounted Price') }}</th>
                                    <th data-field="lifetime_price">{{ get_label('lifetime_price', 'Lifetime Price') }}
                                    </th>
                                    <th data-visible="false" data-field="lifetime_discounted_price">
                                        {{ get_label('lifetime_discounted_price', 'Lifetime Discounted Price') }}</th>
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
            $type = 'Plans';
            ?>
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

    <script src="{{ asset('assets/js/pages/plans.js') }}"></script>
@endsection
