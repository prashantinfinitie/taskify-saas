@extends('layout')
@section('title')
    {{ get_label('lead_sources', 'Lead Sources') }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ url('home') }}"><?= get_label('home', 'Home') ?></a>
                    </li>
                    <li class="breadcrumb-item">
                        <?= get_label('leads_management', 'Leads Management') ?>
                    </li>
                    <li class="breadcrumb-item active">
                        <?= get_label('lead_sources', 'Lead Sources') ?>
                    </li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_lead_source_modal"><button
                    type="button" class="btn btn-sm btn-primary action_create_items" data-bs-toggle="tooltip"
                    data-bs-placement="right"
                     data-bs-original-title=" <?= get_label('create_lead_source', 'Create Lead Source') ?>"><i
                        class="bx bx-plus"></i></button></a>
        </div>
    </div>

    @if ($lead_sources->count() > 0)
    @php
        $visibleColumns = getUserPreferences('lead_sources');
    @endphp
    <div class="card">
        <div class="card-body">
            <div class="row">


            </div>
            <div class="table-responsive text-nowrap">
                <input type="hidden" id="data_type" value="lead-sources">
                <input type="hidden" id="save_column_visibility">
                <table id="table" data-toggle="table" data-loading-template="loadingTemplate"
                            data-url="{{ route('lead-sources.list') }}" data-icons-prefix="bx" data-icons="icons"
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
                                        data-sortable="true"><?= get_label('id', 'ID') ?></th>
                                    <th data-field="name"
                                        data-visible="{{ in_array('name', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-sortable="true"><?= get_label('name', 'Name') ?></th>
                                    <th data-field="actions"
                                        data-visible="{{ in_array('actions', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        <?= get_label('actions', 'Actions') ?></th>
                                </tr>
                            </thead>
                        </table>
            </div>
        </div>
    </div>
@else
    <?php
    $type = 'Lead sources'; ?>
    <x-empty-state-card :type="$type" />
@endif

</div>
<script src="{{ asset('assets/js/pages/lead_sources.js') }}"></script>

@endsection
