@extends('layout')
@section('title')
    {{ get_label('candidates', 'Candidates') }} - {{ get_label('kanban_view', 'Kanban View') }}11
@endsection
@php
    $user = getAuthenticatedUser();
@endphp
@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{route('home.index')}}">{{ get_label('home', 'Home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span>{{ get_label('candidate', 'Candidate') }}</span>
                        </li>
                        <li class="breadcrumb-item active">{{ get_label('kanban', 'Kanban') }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                @php
                    $candidatesDefaultView = getUserPreferences('candidates', 'default_view');
                @endphp
                @if ($candidatesDefaultView === 'kanban')
                    <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
                @else
                    <a href="javascript:void(0);"><span class="badge bg-secondary" id="set-default-view" data-type="candidates"
                            data-view="kanban"><?= get_label('set_as_default_view', 'Set as Default View') ?></span></a>
                @endif
            </div>
            <div>
                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#candidateModal">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="{{ get_label('create_candidate', 'Create candidate') }}">
                        <i class='bx bx-plus'></i>
                    </button>
                </a>
                <a href="{{ route('candidate.index') }}">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="{{ get_label('list_view', 'List view') }}">
                        <i class='bx bx-list-ul'></i>
                    </button>
                </a>

            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="input-group input-group-merge">
                    <input type="text" class="form-control" id="candidate_kanban_date_between"
                        placeholder="<?= get_label('date_between', 'Date Between') ?>" autocomplete="off">

                </div>
                <input type="hidden" name="start_date" id="candidate_kanban_start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" id="candidate_kanban_end_date" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3 mb-3">
                <select class="form-select js-example-basic-multiple" id="sort" name="sort"
                    aria-label="Default select example"
                    data-placeholder="<?= get_label('select_sort_by', 'Select Sort By') ?>" data-allow-clear="true">
                    <option></option>
                    <option value="newest" <?= request()->sort && request()->sort == 'newest' ? 'selected' : '' ?>>
                        <?= get_label('newest', 'Newest') ?>
                    </option>
                    <option value="oldest" <?= request()->sort && request()->sort == 'oldest' ? 'selected' : '' ?>>
                        <?= get_label('oldest', 'Oldest') ?>
                    </option>
                    <option value="recently-updated" <?= request()->sort && request()->sort == 'recently-updated' ? 'selected' : '' ?>>
                        <?= get_label('most_recently_updated', 'Most recently updated') ?>
                    </option>
                    <option value="earliest-updated" <?= request()->sort && request()->sort == 'earliest-updated' ? 'selected' : '' ?>>
                        <?= get_label('least_recently_updated', 'Least recently updated') ?>
                    </option>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <select class="form-select" id="select_candidate_statuses" name="statuses[]"
                    aria-label="Default select example"
                    data-placeholder="<?= get_label('filter_by_statuses', 'Filter by statuses') ?>" data-allow-clear="true"
                    multiple>
                </select>
            </div>

            <div class="col-md-1">
                <div>
                    <button type="button" id="candidates-kanban-filter" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('filter', 'Filter') ?>"><i
                            class='bx bx-filter-alt'></i></button>
                </div>
            </div>
        </div>
        @if (is_countable($candidates) && count($candidates) > 0)
            @php
                $showSettings = $user->can('edit_candidate') || $user->can('delete_candidate') || $user->can('create_candidate');
                $canEditCandidates = $user->can('edit_candidate');
                $canDeleteCandidates = $user->can('delete_candidate');
                $canCreateCandidates = $user->can('create_candidate');
            @endphp
            {{-- @foreach ($statuses as $status) --}}
            {{-- @dd($status->id); --}}
            {{-- @endforeach --}}
            <x-candidates-kanban-card :candidates="$candidates" :candidateStatuses="$candidate_statuses"
                :showSettings="$showSettings" :canEditCandidates="$canEditCandidates"
                :canDeleteCandidates="$canDeleteCandidates" :canCreateCandidates="$canCreateCandidates" />
        @else
            <?php    $type = 'candidates'; ?>
            <x-empty-state-card :type="$type" />
        @endif
    </div>

    <script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
    <script src="{{ asset('assets/js/pages/candidate.js') }}"></script>
    <script src="{{ asset('assets/js/pages/candidate-kanban.js') }}"></script>

@endsection
