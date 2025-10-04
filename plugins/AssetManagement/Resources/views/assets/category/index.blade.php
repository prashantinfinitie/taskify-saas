@extends('layout')

@section('title')
    {{ get_label('asset_category', 'Asset Category') }}
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
                        <li class="breadcrumb-item">
                            <a href="{{ route('assets.index') }}">{{ get_label('assets', 'Assets') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('asset_category', 'Asset Category') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary action_create_template" data-bs-toggle="offcanvas"
                    data-bs-target="#createCategoryOffcanvas" data-bs-toggle="tooltip" data-bs-placement="left"
                    data-bs-original-title="{{ get_label('create_asset_category', 'Create Asset Category') }}">
                    <i class='bx bx-plus'></i>
                </button>
            </div>
        </div>

        @if ($categories->count() > 0)
            @php
                $visibleColumns = getUserPreferences('asset_category');
            @endphp
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive text-nowrap">
                        <input type="hidden" id="data_type" value="assets/category">
                        <input type="hidden" id="save_column_visibility">
                        <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ route('assets.category.list') }}" data-icons-prefix="bx" data-icons="icons"
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
                                    <th data-field="color"
                                        data-visible="{{ in_array('color', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('color', 'Color') }}</th>
                                    <th data-field="description"
                                        data-visible="{{ in_array('description', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('description', 'Description') }}</th>
                                    <th data-field="created_at"
                                        data-visible="{{ in_array('created_at', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('created_at', 'Created At') }}</th>
                                    <th data-field="updated_at"
                                        data-visible="{{ in_array('updated_at', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true">{{ get_label('updated_at', 'Updated At') }}</th>
                                    <th data-field="actions"
                                        data-visible="{{ in_array('actions', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('actions', 'Actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="card empty-state text-center">
                <div class="card-body">
                    <div class="misc-wrapper">
                        <h2 class="mx-2 mb-2">
                            <span>{{ get_label('assets_categories_not_found', 'Assets Categories Not Found') }}</span>
                        </h2>
                        <p class="mx-2 mb-4">
                            {{ get_label('no_data_available', 'Oops! No data available yet.') }}
                        </p>

                        <button type="button" class="btn btn-md btn-primary action_create_template m-1"
                            data-bs-toggle="offcanvas" data-bs-target="#createCategoryOffcanvas" data-bs-toggle="tooltip"
                            data-bs-placement="left"
                            data-bs-original-title="{{ get_label('create_asset_category', 'Create Asset Category') }}">
                            {{ get_label('create_now', 'Create now') }}
                        </button>

                        <div class="mt-3">
                            <img src="{{ asset('/storage/no-result.png') }}" alt="No result" width="500"
                                class="img-fluid" />
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('assets::assets.offcanvas')

    <script src="{{ asset('assets/js/asset-plugin/assets.js') }}"></script>
@endsection

