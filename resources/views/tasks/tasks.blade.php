@extends('layout')

@section('title')
    <?= get_label('tasks', 'Tasks') ?> - <?= get_label('list_view', 'List view') ?>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        @isset($project->id)
                            <li class="breadcrumb-item">
                                <a href="{{ getDefaultViewRoute('projects') }}"><?= get_label('projects', 'Projects') ?></a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('projects.info', ['id' => $project->id]) }}">{{ $project->title }}</a>
                            </li>
                        @endisset
                        <li class="breadcrumb-item active"><?= get_label('tasks', 'Tasks') ?></li>
                    </ol>
                </nav>
            </div>
            <div>
                @php
                    $taskDefaultView = getUserPreferences('tasks', 'default_view');
                    // dd($taskDefaultView);
                @endphp
                @if (!$taskDefaultView || $taskDefaultView === 'tasks')
                    <span class="badge bg-primary"><?= get_label('default_view', 'Default View') ?></span>
                @else
                    <a href="javascript:void(0);"><span class="badge bg-secondary" id="set-default-view" data-type="tasks"
                            data-view="list"><?= get_label('set_as_default_view', 'Set as Default View') ?></span></a>
                @endif
            </div>

            @include('partials.tasks-views-buttons')
        </div>
        <?php
        $id = isset($project->id) ? 'project_' . $project->id : '';
        ?>
        <x-tasks-card :tasks="$tasks" :id="$id" :users="$users" :clients="$clients" :projects="$projects"
            :project="$project" :customFields="$taskCustomFields" />
    </div>
    <!-- </div> -->
@endsection
