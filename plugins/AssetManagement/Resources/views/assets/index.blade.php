@extends('layout')

@section('title')
    {{ get_label('assets', 'Assets') }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('home') }}">{{ get_label('home', 'Home') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('assets', 'Assets') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                @if (isAdminOrHasAllDataAccess())
                    <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#createAssetOffcanvas">
                        <button type="button" id="createAssetModalBtn"
                            class="btn btn-sm btn-primary action_create_template" data-bs-toggle="tooltip"
                            data-bs-placement="left"
                            data-bs-original-title="{{ get_label('create_asset', 'Create Asset') }}">
                            <i class='bx bx-plus'></i>
                        </button>
                    </a>
                    <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#bulkAssignOffcanvas">
                        <button type="button" id="bulkAssignModalBtn" class="btn btn-sm btn-primary action_create_template"
                            data-bs-toggle="tooltip" data-bs-placement="left"
                            data-bs-original-title="{{ get_label('bulk_assign', 'Bulk assign') }}">
                            <i class='bx bx-group'></i>
                        </button>
                    </a>
                    <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#bulkAssetsUploadOffcanvas">
                        <button type="button" id="bulkAssetsUploadModalBtn"
                            class="btn btn-sm btn-primary action_create_template" data-bs-toggle="tooltip"
                            data-bs-placement="left" data-bs-original-title="{{ get_label('bulk_upload', 'Bulk Upload') }}">
                            <i class='bx bx-upload'></i>
                        </button>
                    </a>
                    <a href="{{ route('assets.global-analytics') }}">
                        <button type="button" class="btn btn-sm btn-primary action_create_template"
                            data-bs-toggle="tooltip" data-bs-placement="left"
                            data-bs-original-title="{{ get_label('analytics', 'Analytics') }}">
                            <i class='bx bx-chart'></i>
                        </button>
                    </a>
                    <a href="{{ route('assets.export') }}">
                        <button type="button" class="btn btn-sm btn-primary action_create_template"
                            data-bs-toggle="tooltip" data-bs-placement="left"
                            data-bs-original-title="{{ get_label('export_assets', 'Export All Assets') }}">
                            <i class='bx bx-export'></i>
                        </button>
                    </a>
                @endif
            </div>
        </div>

        @if ($assets->count() > 0)
            @php
                $visibleColumns = getUserPreferences('assets');
            @endphp
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <select class="form-select select-asset-category_in_filter" id="select_categories"
                                name="categories[]" aria-label="Default select example"
                                data-placeholder="{{ get_label('filter_by_categorie', 'Filter by Categories') }}"
                                data-allow-clear="true" multiple></select>
                        </div>
                        @if (isAdminOrHasAllDataAccess())
                            <div class="col-md-4 mb-3">
                                <select class="form-select select-asset-assigned_to_in_filter" id="select_assigned_to"
                                    name="users[]" aria-label="Default select example"
                                    data-placeholder="{{ get_label('filter_by_assigned_users', 'Filter by Assigned Users ') }}"
                                    data-allow-clear="true" multiple></select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <select class="form-select asset_status" id="asset_status" name="asset_status"
                                    aria-label="Default select example"
                                    data-placeholder="{{ get_label('filter_by_statuses', 'Filter by statuses') }}"
                                    data-allow-clear="true" multiple>
                                    <option value=""></option>
                                    <option value="available">{{ get_label('available', 'Available') }}</option>
                                    <option value="non-functional">{{ get_label('non_functional', 'Non-Functional') }}
                                    </option>
                                    <option value="lost">{{ get_label('lost', 'Lost') }}</option>
                                    <option value="damaged">{{ get_label('damaged', 'Damaged') }}</option>
                                    <option value="lent">{{ get_label('lent', 'Lent') }}</option>
                                    <option value="under-maintenance">
                                        {{ get_label('under_maintenance', 'Under Maintenance') }}</option>
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="table-responsive text-nowrap">
                        <input type="hidden" id="data_type" value="assets">
                        <input type="hidden" id="save_column_visibility">
                        <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ route('assets.list') }}" data-icons-prefix="bx" data-icons="icons"
                            data-show-refresh="true" data-total-field="total" data-trim-on-search="false"
                            data-data-field="rows" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                            data-side-pagination="server" data-show-columns="true" data-pagination="true"
                            data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                            data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th data-checkbox="true"></th>
                                    <th data-field="id"
                                        data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('id', 'ID') }}</th>
                                    <th data-field="name"
                                        data-visible="{{ in_array('name', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('name', 'Name') }}</th>
                                    <th data-field="lent_to"
                                        data-visible="{{ in_array('lent_to', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('lent_to', 'Lent To') }}</th>
                                    <th data-field="category"
                                        data-visible="{{ in_array('category', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('category', 'Category') }}</th>
                                    <th data-field="description"
                                        data-visible="{{ in_array('description', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('description', 'Description') }}</th>
                                    <th data-field="status"
                                        data-visible="{{ in_array('status', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('status', 'Status') }}</th>
                                    <th data-field="asset_tag"
                                        data-visible="{{ in_array('asset_tag', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('asset_tag', 'Asset Tag') }}</th>
                                    <th data-field="purchase_cost"
                                        data-visible="{{ in_array('purchase_cost', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('purchase_cost', 'Purchase Cost') }}</th>
                                    <th data-field="purchase_date"
                                        data-visible="{{ in_array('purchase_date', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('purchase_date', 'Purchase Date') }}</th>
                                    <th data-field="created_at"
                                        data-visible="{{ in_array('created_at', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('created_at', 'Created At') }}</th>
                                    <th data-field="updated_at"
                                        data-visible="{{ in_array('updated_at', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('updated_at', 'Updated At') }}</th>
                                    <th data-field="actions"
                                        data-visible="{{ in_array('actions', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('actions', 'Actions') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        @else
            @if (isAdminOrHasAllDataAccess())
                <div class="card empty-state text-center">
                    <div class="card-body">
                        <div class="misc-wrapper">
                            <h2 class="mx-2 mb-2">
                                <span>{{ get_label('assets_not_found', 'Assets Not Found') }}</span>
                            </h2>
                            <p class="mx-2 mb-4">{{ get_label('no_data_available', 'Oops! No data available yet.') }}</p>
                            <a href="javascript:void(0);" class="btn btn-md btn-primary action_create_template m-1"
                                id="createAssetModalBtn" data-bs-toggle="offcanvas"
                                data-bs-target="#createAssetOffcanvas"
                                title="{{ get_label('create_asset', 'Create Asset') }}">
                                {{ get_label('create_now', 'Create now') }}
                            </a>
                            <div class="mt-3">
                                <img src="{{ asset('/storage/no-result.png') }}" alt="No result" width="500"
                                    class="img-fluid" />
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card empty-state text-center">
                    <div class="card-body">
                        <div class="misc-wrapper">
                            <h2 class="mx-2 mb-2">
                                <span>{{ get_label('you_dont_have_any_assets_assigned_to_you', 'You dont Have Any Assets Assigned To You.') }}</span>
                            </h2>
                            <p class="mx-2 mb-4">
                                {{ get_label('contact_admin', 'Contact admin if you think this is an error') }}
                            </p>
                            <div class="mt-3">
                                <img src="{{ asset('/storage/no-result.png') }}" alt="No result" width="500"
                                    class="img-fluid" />
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    @include('assets::assets.offcanvas')

    <script src="{{ asset('assets/js/asset-plugin/assets.js') }}"></script>
@endsection
