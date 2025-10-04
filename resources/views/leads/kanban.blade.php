@extends('layout')
@section('title')
    {{ get_label('kanban_view', 'Kanban View') }} - {{ get_label('leads', 'Leads') }}
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
                        <li class="breadcrumb-item">
                            <a href="{{ route('leads.index') }}"><?= get_label('leads', 'Leads') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('kanban_view', 'Kanban View') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                @php
                    $leadsDefaultView = getUserPreferences('leads', 'default_view');
                @endphp
                @if ($leadsDefaultView === 'kanban')
                    <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
                @else
                    <a href="javascript:void(0);"><span class="badge bg-secondary" id="set-default-view" data-type="leads"
                            data-view="kanban"><?= get_label('set_as_default_view', 'Set as Default View') ?></span></a>
                @endif
            </div>
            <div>
                <a href="{{ route('leads.create') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('create_lead', 'Create Lead') ?>"><i
                            class="bx bx-plus"></i></button></a>
                <a href="{{ route('leads.index') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('list_view', 'List View') ?>"><i
                            class="bx bx-list-ul"></i></button></a>
                             <a href="{{ route('leads.upload') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('bulk_upload', 'Bulk Upload') ?>"><i
                            class="bx bx-upload"></i></button></a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <select class="form-select js-example-basic-multiple" id="sort" aria-label="Default select example"
                    data-placeholder="<?= get_label('select_sort_by', 'Select Sort By') ?>" data-allow-clear="true">
                    <option></option>
                    <option value="newest" <?= request()->sort && request()->sort == 'newest' ? 'selected' : '' ?>>
                        <?= get_label('newest', 'Newest') ?></option>
                    <option value="oldest" <?= request()->sort && request()->sort == 'oldest' ? 'selected' : '' ?>>
                        <?= get_label('oldest', 'Oldest') ?></option>
                    <option value="recently-updated"
                        <?= request()->sort && request()->sort == 'recently-updated' ? 'selected' : '' ?>>
                        <?= get_label('most_recently_updated', 'Most recently updated') ?></option>
                    <option value="earliest-updated"
                        <?= request()->sort && request()->sort == 'earliest-updated' ? 'selected' : '' ?>>
                        <?= get_label('least_recently_updated', 'Least recently updated') ?></option>
                </select>
            </div>
            @php
                // Get selected statuses and tags from the request
                $selectedSources = request()->input('sources', []);

                $filterSources = \App\Models\LeadSource::whereIn('id', $selectedSources)->get();
            @endphp
            <div class="col-md-3 mb-3">
                <select class="form-select" id="selected_sources" name="sources[]"
                    aria-label="Default select example"
                    data-placeholder="{{ get_label('filter_by_sources', 'Filter by sources') }}"
                    data-allow-clear="true" multiple>
                </select>
            </div>
            <div class="col-md-4 mb-3">
               <input type="text" name="date_range" id="filter_date_range" class="form-control"
                         placeholder="<?= get_label('filter_by_date_range', 'Filter by date range') ?>"
                       value="@if (request('start_date') && request('end_date')){{ format_date(request('start_date')) }} To {{ format_date(request('end_date')) }}@endif">

                <input type="hidden" name="start_date" id="lead_kanban_start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" id="lead_kanban_end_date" value="{{ request('end_date') }}">
            </div>


            <div class="col-md-1">
                <div>
                    <button type="button" id="leads-kanban-filter" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                        data-bs-placement="left" data-bs-original-title="<?= get_label('filter', 'Filter') ?>"><i
                            class='bx bx-filter-alt'></i></button>
                </div>
            </div>
        </div>
        @if (is_countable($leads) && count($leads) > 0)
            @php
                $showSettings = true;
                $canEditLeads = true;
                $canDeleteLeads = true;
                $webGuard = Auth::guard('web')->check();
            @endphp
           <x-leads-kanban-card :leads="$leads" :stages="$lead_stages" :showSettings="$showSettings" :canEditLeads="$canEditLeads" :canDeleteLeads="$canDeleteLeads"
           :webGuard="$webGuard" />
        @else
            <?php $type = 'leads'; ?>
            <x-empty-state-card :type="$type" />
        @endif
    </div>

    <script src="{{ asset('assets/js/pages/leads-kanban.js') }}"></script>
@endsection
