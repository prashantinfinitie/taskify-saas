<div class="kanban-board d-flex bg-body flex-nowrap gap-3 p-3"
    style="min-height: calc(100vh - 220px); overflow-x: auto;">


    @foreach ($candidateStatuses as $status)

        <div class="kanban-column card" data-status-id="{{ $status->id }}"
            style="min-width: 300px; max-width: 300px; height: calc(100vh - 180px);">
            {{-- @dd($status); --}}
            {{-- @dd($candidates[0]->status_id, $status->id); --}}
            <div
                class="kanban-column-header card-header bg-label-{{ $status->color }} d-flex justify-content-between align-items-center p-3">
                <div class="fw-semibold text-truncate" style="max-width: 80%;">
                    {{ $status->name }}
                </div>
                <div class="column-count badge text-{{ $status->color }} bg-white">
                    {{ $candidates->where('status_id', $status->id)->count() }}/{{ $candidates->count() }}
                </div>
            </div>
            <div class="kanban-column-body card-body bg-body h-100 overflow-auto p-3">

                @foreach ($candidates->where('status_id', $status->id) as $candidate)

                    <div class="kanban-card card mb-3" data-card-id="{{ $candidate->id }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title text-truncate mb-0" style="max-width: 100%;">
                                    <a href="{{ route('candidate.show', $candidate->id) }}"
                                        class="text-body text-primary view-candidate-details" data-id="{{ $candidate->id }}">

                                        {{$candidate->name }}
                                    </a>
                                </h5>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-label-primary text-truncate me-1"
                                    style="max-width: 100%;">{{ $candidate->position }}</span>
                            </div>
                            <div class="text-truncate mb-2">
                                <i class='bx bx-envelope'></i> {{ $candidate->email }}
                            </div>
                            @if ($candidate->phone)
                                <div class="text-truncate mb-2">
                                    <i class='bx bx-phone'></i> {{ $candidate->phone }}
                                </div>
                            @endif
                            <div class="text-truncate mb-2">
                                <i class='bx bx-search'></i> Source: {{ $candidate->source }}
                            </div>
                            <div class="card-actions d-flex align-items-center">
                                <a href="javascript:void(0);" class="quick-candidate-view" data-id="{{ $candidate->id }}"
                                    data-type="candidate">
                                    <i class='bx bx-info-circle text-info' data-bs-toggle="tooltip" data-bs-placement="right"
                                        data-bs-original-title="{{ get_label('quick_view', 'Quick View') }}"></i>
                                </a>
                                @if ($showSettings)
                                    <a href="javascript:void(0);" class="ms-2" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class='bx bx-cog' id="settings-icon"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        @if ($canEditCandidates)
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0);"
                                                    class="edit-candidate-btn text-primary d-flex align-items-center"
                                                    data-candidate='@json($candidate)' title="{{ get_label('update', 'Update') }}">
                                                    <i class="bx bx-edit me-2"></i> {{ get_label('update', 'Update') }}
                                                </a>
                                            </li>
                                        @endif
                                        @if ($canDeleteCandidates)
                                            <li class="dropdown-item">
                                                <a href="javascript:void(0);" class="delete text-danger d-flex align-items-center"
                                                    data-reload="true" data-type="candidate" data-id="{{ $candidate->id }}">
                                                    <i class="bx bx-trash me-2"></i>
                                                    {{ get_label('delete', 'Delete') }}
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                @endif
                            </div>
                            <div class="text-truncate mt-2">
                                <i class='bx bx-calendar text-success'></i> {{ format_date($candidate->created_at) }}
                            </div>
                        </div>
                    </div>
                @endforeach
                @if ($canCreateCandidates)
                    <a href="javascript:void(0);"
                        class="btn btn-outline-secondary btn-sm d-block create-candidate-btn text-truncate"
                        data-bs-toggle="modal" data-bs-target="#candidateModal" data-status-id="{{ $status->id }}">
                        <i class='bx bx-plus me-1'></i>{{ get_label('create_candidate', 'Create candidate') }}
                    </a>
                @endif
            </div>
        </div>
    @endforeach
</div>
