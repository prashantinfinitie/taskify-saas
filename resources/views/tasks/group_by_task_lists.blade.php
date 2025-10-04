@extends('layout')
@section('title')
    {{ get_label('tasks', 'Tasks') }} - {{ get_label('group_by_task_lists', 'Group by task lists') }}
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}">
                                <?= get_label('home', 'Home') ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('tasks', 'Tasks') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <!-- Default View Badge or Option -->
            <div>
                @php
                    $taskDefaultView = getUserPreferences('tasks', 'default_view');
                    // dd($taskDefaultView);
                @endphp
                @if ($taskDefaultView === 'tasks/group-by-task-list')
                    <span class="badge bg-primary">
                        <?= get_label('default_view', 'Default View') ?>
                    </span>
                @else
                    <a href="javascript:void(0);">
                        <span class="badge bg-secondary" id="set-default-view" data-type="tasks"
                            data-view="group-by-task-list">
                            <?= get_label('set_as_default_view', 'Set as Default View') ?>
                        </span>
                    </a>
                @endif
            </div>
            @include('partials.tasks-views-buttons')


        </div>
        <!-- Task Table -->
        <div class="card">
            <div class="card-header border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center row">
                    <div class="col-md-6">
                        <h5 class="mb-0">{{ get_label('grouped_by_task_lists', 'Grouped by Task lists') }}</h5>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="task-lists" id="taskListsContainer">
                    <x-group-task-list :taskLists="$taskLists" />
                </div>
                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="d-none">
                    <div class="d-flex justify-content-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/pages/group-by-task-lists.js') }}"></script>

@endsection
