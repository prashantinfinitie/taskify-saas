@php
    $currentRoute = Route::currentRouteName();
    $projectId = $project->id ?? request('project', '');
    $status = request('status');
    $baseUrl = $projectId ? route('projects.tasks.index', ['id' => $projectId]) : route('tasks.index');
    $url = $status ? "{$baseUrl}?status={$status}" : $baseUrl;


    $buttons = [
        [
            'icon' => 'bx bx-plus',
            'title' => get_label('create_task', 'Create task'),
            'link' => 'javascript:void(0);',
            'attributes' => 'data-bs-toggle="offcanvas" data-bs-target="#create_task_offcanvas"',
            'route' => '',
            'toggle'=>'tooltip',
        ],
        [
            'icon' => 'bx bx-list-ul',
            'title' => get_label('list_view', 'List view'),
            'link' => $url,
            'route' => 'tasks.index',
            'toggle'=>'tooltip',
        ],
        [
            'icon' => 'bx bxs-dashboard',
            'title' => get_label('draggable', 'Draggable'),
            'link' => $projectId ? route('projects.tasks.draggable', ['id' => $projectId]) : route('tasks.draggable'),
            'route' => 'tasks.draggable',
            'toggle'=>'tooltip',
        ],
        [
            'icon' => 'bx bxs-calendar',
            'title' => get_label('calendar_view', 'Calendar View'),
            'link' => route('tasks.calendar_view'),
            'route' => 'tasks.calendar_view',
            'toggle'=>'tooltip',
        ],
        // New button for Group by Task List
        [

            'icon' => 'bx bx-align-middle',
            'title' => get_label('group_by_task_list', 'Group by Task List'),
            'link' => route('tasks.groupByTaskList'),
            'route' => 'tasks.groupByTaskList',
            'toggle'=>'tooltip',
        ],
    ];
@endphp

<div>
    @foreach ($buttons as $button)
        @if ($button['route'] !== $currentRoute)
            <a href="{{ $button['link'] }}" {!! $button['attributes'] ?? '' !!}>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="{{ $button['toggle'] }}" data-bs-placement="left" data-bs-original-title="{{ $button['title'] }}">
                    <i class="{{ $button['icon'] }}"></i>
                </button>
            </a>
        @endif
    @endforeach
</div>
