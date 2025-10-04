@extends('layout')
@section('title')
    <?= get_label('tasks', 'Tasks') ?> - <?= get_label('draggable', 'Draggable') ?>
@endsection
@section('content')
    @php
        $routePrefix = Route::getCurrentRoute()->getPrefix();
    @endphp
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        @if (isset($project->id))

                            <li class="breadcrumb-item">
                                <a href="{{ getDefaultViewRoute('projects') }}"><?= get_label('projects', 'Projects') ?></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('projects.info', ['id' => $project->id]) }}">{{ $project->title }}</a>
                            </li>
                        @endisset
                        <li class="breadcrumb-item active">
                            <?= get_label('tasks', 'Tasks') ?>
                        </li>
                </ol>
            </nav>
        </div>
        <div>
            @php
                $taskDefaultView = getUserPreferences('tasks', 'default_view');
            @endphp
            @if ($taskDefaultView === 'tasks/draggable')
                <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
            @else
                <a href="javascript:void(0);"><span class="badge bg-secondary" id="set-default-view" data-type="tasks"
                        data-view="draggable"><?= get_label('set_as_default_view', 'Set as Default View') ?></span></a>
            @endif
        </div>
        <div>
            @include('partials.tasks-views-buttons')
        </div>
    </div>
    @if ($total_tasks > 0)
        <div class="alert alert-primary alert-dismissible" role="alert">
            <?= get_label('drag_drop_update_task_status', 'Drag and drop to update task status') . ' !' ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div class="d-flex card flex-row overflow-x-y-hidden">
            @foreach ($statuses as $status)
                @if ($status->admin_id == getAdminIdByUserRole() || $status->admin_id === null)
                    <div class="my-4 status-row">
                        <h4 class="fw-bold mx-4 my-2">{{ $status->title }}</h4>
                        <div class="row m-2 d-flex flex-column h-100" id="{{ $status->slug }}"
                            data-status="{{ $status->id }}">
                            @foreach ($tasks as $task)
                                @if ($task->status_id == $status->id)
                                    <x-kanban :task="$task" />
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <?php
        $type = 'Tasks';
        ?>
        <x-empty-state-card :type="$type" />
    @endif
</div>


<script>
    var statusArray = '<?php echo json_encode($statuses); ?>';
    var routePrefix = '{{ $routePrefix }}';
</script>
<script src="{{ asset('assets/js/pages/task-board.js') }}"></script>
@endsection
