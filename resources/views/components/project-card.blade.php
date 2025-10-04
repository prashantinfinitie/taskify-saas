<div class="kanban-board d-flex bg-body gap-3 overflow-auto p-3">
    @foreach ($statuses as $status)
        <div class="kanban-column card" data-status-id="{{ $status->id }}">
            <div
                class="kanban-column-header card-header bg-label-{{ $status->color }} d-flex justify-content-between align-items-center p-3">
                <div class="fw-semibold">
                    {{ $status->title }}
                </div>
                <div class="column-count badge text-{{ $status->color }} bg-white">
                    {{ $projects->where('status_id', $status->id)->count() }}/{{ $projects->count() }}
                </div>
            </div>
            <div class="kanban-column-body card-body bg-body p-3">
                @foreach ($projects->where('status_id', $status->id) as $project)
                    <div class="kanban-card card mb-3" data-card-id="{{ $project->id }}">
                        <div class="card-body">
                            <div class="card-tags mb-2">
                                @foreach ($project->tags as $tag)
                                    <span
                                        class="tag-border fs-small text-uppercase text-{{ $tag->color }} tag-color-{{ $tag->color }} me-1">{{ ucfirst($tag->title) }}</span>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title mb-0">
                                    <a href="{{ url('/master-panel/projects/information/' . $project->id) }}"
                                        class="text-body" target="_blank">
                                        {{ ucfirst(Str::limit($project->title, 20)) }}
                                    </a>
                                </h5>
                                <span class="badge bg-label-{{ $project->priority ? $project->priority->color : '' }}">
                                    {{ $project->priority ? $project->priority->title : '' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-light">
                                    <i class='bx bx-calendar me-1'></i>{{ get_label('start_date', 'Start Date') }}:
                                    {{ format_date($project->start_date) }}
                                </small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-light">
                                    <i class='bx bx-calendar me-1'></i>{{ get_label('end_date', 'End Date') }}:
                                    {{ format_date($project->end_date) }}
                                </small>
                            </div>
                            <div class="align-items-center card-actions d-flex justify-content-evenly mt-2">
                                <a href="javascript:void(0);" class="quick-view" data-id="{{ $project->id }}"
                                    data-type="project">
                                    <i class='bx bx bx-info-circle text-info' data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title="{{ get_label('quick_view', 'Quick View') }}"></i>
                                </a>
                                <a href="javascript:void(0);" class="mx-2">
                                    <i class='bx {{ $project->is_favorite ? 'bxs' : 'bx' }}-star favorite-icon text-warning'
                                        data-id="{{ $project->id }}" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title="{{ $project->is_favorite ? get_label('remove_favorite', 'Click to remove from favorite') : get_label('add_favorite', 'Click to mark as favorite') }}"
                                        data-favorite="{{ $project->is_favorite }}"></i>
                                </a>
                                <a href="{{ route('projects.info', ['id' => $project->id]) }}#navs-top-discussions" target="_blank">
                                    <i class='bx bx-message-rounded-dots text-danger' data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title="{{ get_label('discussion', 'Discussion') }}"></i>
                                </a>
                                @if ($showSettings)
                                    <div class="">
                                        <a href="javascript:void(0);" class="mx-2" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class='bx bx-cog' id="settings-icon"></i>
                                        </a>
                                        <ul class="dropdown-menu">
                                            @if ($canEditProjects)
                                                <a href="javascript:void(0);" class="edit-project"
                                                    data-id="{{ $project->id }}">
                                                    <li class="dropdown-item">
                                                        <i
                                                            class='menu-icon tf-icons bx bx-edit text-primary'></i><?= get_label('update', 'Update') ?>
                                                    </li>
                                                </a>
                                            @endif
                                            @if ($canDeleteProjects)
                                                <a href="javascript:void(0);" class="delete" data-reload="true"
                                                    data-type="projects" data-id="{{ $project->id }}">
                                                    <li class="dropdown-item">
                                                        <i
                                                            class='menu-icon tf-icons bx bx-trash text-danger'></i><?= get_label('delete', 'Delete') ?>
                                                    </li>
                                                </a>
                                            @endif
                                            @if ($canDuplicateProjects)
                                                <a href="javascript:void(0);" class="duplicate" data-type="projects"
                                                    data-id="{{ $project->id }}" data-title="{{ $project->title }}"
                                                    data-reload="true">
                                                    <li class="dropdown-item">
                                                        <i
                                                            class='menu-icon tf-icons bx bx-copy text-warning'></i><?= get_label('duplicate', 'Duplicate') ?>
                                                    </li>
                                                </a>
                                            @endif
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
                <a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm d-block create-project-btn"
                    data-bs-toggle="offcanvas" data-bs-target="#create_project_offcanvas">
                    <i class='bx bx-plus me-1'></i>{{ get_label('create_project', 'Create project') }}
                </a>
            </div>
        </div>
    @endforeach
</div>
