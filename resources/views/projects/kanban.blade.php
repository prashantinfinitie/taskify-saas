@extends('layout')
@section('title')
    <?= get_label('kanban_view', 'Kanban View') ?>
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
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        @if (!($is_favorite == 1))
                            <li class="breadcrumb-item"><a
                                    href="{{ getDefaultViewRoute('projects') }}"><?= get_label('projects', 'Projects') ?></a>
                            </li>
                        @else
                            <li class="breadcrumb-item active"><a
                                    href="{{ route('projects.index', ['type' => 'favorite']) }}"><?= get_label('favorite', 'Favorite') ?></a>
                            </li>
                        @endif
                        <li class="breadcrumb-item active"><?= get_label('kanban_view', 'Kanban View') ?></li>
                    </ol>
                </nav>
            </div>
            <div>
                @php
                    $projectDefaultView = getUserPreferences('projects', 'default_view');
                @endphp
                @if (!$projectDefaultView || $projectDefaultView === 'kanban_view')
                    <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
                @else
                    <a href="javascript:void(0);"><span class="badge bg-secondary" id="set-default-view"
                            data-type="projects"
                            data-view="kanban_view"><?= get_label('set_as_default_view', 'Set as Default View') ?></span></a>
                @endif
            </div>
            <div>
                @php
                    $url =
                        $is_favorite == 1
                            ? url('/master-panel/projects/list/favorite')
                            : url('/master-panel/projects/list');
                    $additionalParams = request()->has('status')
                        ? '/master-panel/projects/list?status=' . request()->status
                        : '';
                    $finalUrl = url($additionalParams ?: $url);
                    $currentPath = request()->path();
                    $showCreateButton = !in_array($currentPath, ['projects/list/favorite', 'projects/favorite']);
                @endphp
                @if ($showCreateButton)
                    <a href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#create_project_offcanvas"><button
                            type="button" class="btn btn-sm btn-primary action_create_projects" data-bs-toggle="tooltip"
                            data-bs-placement="left"
                            data-bs-original-title="<?= get_label('create_project', 'Create project') ?>"><i
                                class='bx bx-plus'></i></button></a>
                @endif
                <a href="{{ $finalUrl }}"><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                        data-bs-placement="left" data-bs-original-title="<?= get_label('list_view', 'List view') ?>"><i
                            class='bx bx-list-ul'></i></button></a>
                <a
                    href="{{ url(request()->has('status') ? route('projects.index', ['status' => request()->status]) : route('projects.index')) }}">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('grid_view', 'Grid view') ?>">
                        <i class='bx bxs-grid-alt'></i>
                    </button>
                </a>
                <a href="{{ route('projects.gantt_chart') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('gantt_chart_view', 'Gantt Chart View') ?>"><i
                            class='bx bxs-collection'></i></button></a>
                            <a href="{{ route('projects.calendar_view') }}"><button type="button" class="btn btn-sm btn-primary"
                            data-bs-toggle="tooltip" data-bs-placement="left"
                            data-bs-original-title="{{ get_label('calendar_view', 'Calendar log') }}"><i
                                class='bx bx-calendar'></i></button></a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-3">
                <select class="form-select" id="status_filter" aria-label="Default select example">
                    <option value=""><?= get_label('filter_by_status', 'Filter by status') ?></option>
                    @foreach ($statuses as $status)
                        <?php $selected = isset($_REQUEST['status']) && $_REQUEST['status'] !== '' && $_REQUEST['status'] == $status->id ? 'selected' : '';
                        ?>
                        <option value="{{ $status->id }}" {{ $selected }}>{{ $status->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <select class="form-select" id="sort" aria-label="Default select example">
                    <option value=""><?= get_label('sort_by', 'Sort by') ?></option>
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
            <div class="col-md-5 mb-3">
                <select id="selected_tags" class="form-control js-example-basic-multiple" name="tag[]" multiple="multiple"
                    data-placeholder="<?= get_label('filter_by_tags', 'Filter by tags') ?>">
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}" @if (in_array($tag->id, $selectedTags)) selected @endif>
                            {{ $tag->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <div>
                    <button type="button" id="tags_filter" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                        data-bs-placement="left" data-bs-original-title="<?= get_label('filter', 'Filter') ?>"><i
                            class='bx bx-filter-alt'></i></button>
                </div>
            </div>
        </div>
        @if (is_countable($projects) && count($projects) > 0)
            @php
                $showSettings =
                    $user->can('edit_projects') || $user->can('delete_projects') || $user->can('create_projects');
                $canEditProjects = $user->can('edit_projects');
                $canDeleteProjects = $user->can('delete_projects');
                $canDuplicateProjects = $user->can('create_projects');
            @endphp
            <x-project-card :projects="$projects" :statuses="$statuses" :showSettings="$showSettings" :canEditProjects="$canEditProjects" :canDeleteProjects="$canDeleteProjects"
                :canDuplicateProjects="$canDuplicateProjects" />
        @else
            <?php $type = 'projects'; ?>
            <x-empty-state-card :type="$type" />
        @endif
    </div>
    <script>
        var add_favorite = '<?= get_label('add_favorite', 'Click to mark as favorite') ?>';
        var remove_favorite = '<?= get_label('remove_favorite', 'Click to remove from favorite') ?>';
    </script>
    <script src="{{ asset('assets/js/pages/project-grid.js') }}"></script>
@endsection
