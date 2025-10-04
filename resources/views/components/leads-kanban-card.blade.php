<div class="kanban-board d-flex bg-body gap-3 overflow-auto p-3">
    @foreach ($stages as $stage)
        <div class="kanban-column card" data-stage-id="{{ $stage->id }}">
            <div
                class="kanban-column-header card-header bg-label-{{ $stage->color }} d-flex justify-content-between align-items-center p-3">
                <div class="fw-semibold">
                    {{ $stage->name }}
                </div>
                <div class="column-count badge text-{{ $stage->color }} bg-white">
                    {{ $leads->where('stage_id', $stage->id)->count() }}/{{ $leads->count() }}
                </div>
            </div>
            <div class="kanban-column-body card-body bg-body p-3">
                @foreach ($leads->where('stage_id', $stage->id) as $lead)
                    <div class="kanban-card card mb-3" data-card-id="{{ $lead->id }}">
                        <div class="card-body p-3 py-0 pb-3">
                            <div class="d-flex justify-content-end align-items-end">
                                <!-- Actions Dropdown -->
                                <div class="dropdown">
                                    <button class="btn btn-link text-dark p-0" type="button" id="actionsDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                                        <i class="bx bx-dots-horizontal-rounded fs-4"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown">
                                        <li>
                                            <a class="dropdown-item text-info"
                                                href="{{ route('leads.show', ['id' => $lead->id]) }}" data-id="{{ $lead->id }}">
                                                <i class="bx bx-show-alt"></i>
                                                {{ get_label('quick_view', 'Quick View') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-primary edit-lead"
                                                href="{{ route('leads.edit', ['id' => $lead->id]) }}" data-id="{{ $lead->id }}">
                                                <i class="bx bx-edit"></i> {{ get_label('edit', 'Edit') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger delete" href="javascript:void(0);"
                                                data-reload="true" data-type="leads" data-id="{{ $lead->id }}">
                                                <i class="bx bx-trash"></i> {{ get_label('delete', 'Delete') }}
                                            </a>
                                        </li>
                                        @if ($lead->is_converted == 0)
                                            <li>
                                                <a class="dropdown-item text-primary convert-to-client" href="javascript:void(0);"
                                                    data-id="{{ $lead->id }}"><i
                                                        class="bx bxs-analyse me-1"></i>{{ get_label('convert_to_client', 'Convert To Client') }}
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <!-- Avatar -->
                                <div class="avatar avatar-md me-2">
                                    <span class="avatar-initial rounded-circle bg-primary text-white">
                                        {{ substr($lead->first_name, 0, 1) . substr($lead->last_name, 0, 1) }}
                                    </span>
                                </div>
                                <!-- Lead Source Badge -->
                                <span class="small badge bg-label-{{ $lead->stage->color }} text-uppercase fw-medium px-2 py-1">
                                    {{ $lead->source->name ?? 'N/A' }}
                                </span>
                            </div>
                            <h6 class="fw-semibold text-truncate mb-1">
                                {{ ucfirst($lead->first_name) }} {{ ucfirst($lead->last_name) }}
                            </h6>
                            <p class="small text-muted mb-1">
                                {{ $lead->job_title }} @ {{ $lead->company }}
                            </p>
                            @if ($lead->phone)
                                <p class="small mb-1">
                                    <i class="bx bx-phone text-primary"></i>
                                    <a href="tel:{{ $lead->country_code }}{{ $lead->phone }}">{{ $lead->country_code }}
                                        {{ $lead->phone }}</a>
                                </p>
                            @endif
                            @if ($lead->email)
                                <p class="small mb-1">
                                    <i class="bx bx-envelope text-info"></i>
                                    <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                                </p>
                            @endif
                            <div class="d-flex mt-2 gap-2">
                                @if ($lead->linkedin)
                                    <a href="{{ $lead->linkedin }}" target="_blank" class="text-secondary" title="LinkedIn">
                                        <i class="bx bxl-linkedin-square fs-5"></i>
                                    </a>
                                @endif
                                @if ($lead->facebook)
                                    <a href="{{ $lead->facebook }}" target="_blank" class="text-secondary" title="Facebook">
                                        <i class="bx bxl-facebook-circle fs-5"></i>
                                    </a>
                                @endif
                                @if ($lead->instagram)
                                    <a href="{{ $lead->instagram }}" target="_blank" class="text-secondary" title="Instagram">
                                        <i class="bx bxl-instagram fs-5"></i>
                                    </a>
                                @endif
                                @if ($lead->pinterest)
                                    <a href="{{ $lead->pinterest }}" target="_blank" class="text-secondary" title="Pinterest">
                                        <i class="bx bxl-pinterest fs-5"></i>
                                    </a>
                                @endif
                            </div>
                            @if($lead->is_converted == 1)
                                <div class="small text-black-50 mb-0">
                                    {{ get_label('converted_to_client_at', 'Converted to Client At') }} :
                                    {{ format_date($lead->converted_at, true) }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
