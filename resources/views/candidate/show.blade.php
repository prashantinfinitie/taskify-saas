@extends('layout')
@section('title')
    <?= get_label('candidate_profile', 'Candidate Profile') ?>
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
                        <li class="breadcrumb-item">
                            <a href="{{ route('candidate.index') }}"><?= get_label('candidates', 'Candidates') ?></a>
                        </li>

                    </ol>
                </nav>
            </div>
            <div>

                <a href="{{ route('candidate.index') }}">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="{{ get_label('list_view', 'List view') }}">
                        <i class='bx bx-list-ul'></i>
                    </button>
                </a>
                <a href="{{ route('candidate.kanban_view') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('kanban_view', 'Kanban View') ?>"><i
                            class="bx bx-layout"></i></button></a>
            </div>
        </div>
        @php
            $name = $candidates->name ?? '';
            $initials = collect(explode(' ', $name))->map(fn($n) => strtoupper($n[0]))->join('');
        @endphp

        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">

                    <div class="candidate-avatar bg-primary text-white fw-bold">
                        {{ $initials }}
                    </div>


                    {{-- Candidate Info --}}
                    <div>
                        <div class="fw-bold text-dark">{{ $candidates->name }}</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge bg-primary text-uppercase small">{{ $candidates->position }}</span>
                            <span
                                class="badge bg-info text-uppercase small">{{ $candidates->status->name ?? 'No Status' }}</span>
                        </div>
                    </div>

                    {{-- Edit Icon --}}
                    <div class="ms-auto">
                        <a href="javascript:void(0)"
                            class="btn btn-sm btn-outline-primary rounded-circle edit-candidate-btn update-users-clients"
                            data-candidate='@json($candidates)'>
                            <i class="bx bx-edit"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <h5 class="card-header"><?= get_label('candidate_details', 'Candidate Details') ?></h5>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= get_label('phone_number', 'Phone Number') ?></label>
                        <input type="tel" class="form-control" value="{{ $candidates->phone ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= get_label('email', 'E-mail') ?></label>
                        <input class="form-control" type="text" value="{{ $candidates->email }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= get_label('position', 'Position') ?></label>
                        <input class="form-control" type="text" value="{{ $candidates->position }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= get_label('source', 'Source') ?></label>
                        <input class="form-control" type="text" value="{{ $candidates->source }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= get_label('status', 'Status') ?></label>
                        <input class="form-control" type="text" value="{{ $candidates->status->name ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= get_label('created_at', 'Created At') ?></label>
                        <input class="form-control" value="{{ format_date($candidates->created_at) }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <h5 class="card-header"><?= get_label('attachments', 'Attachments') ?></h5>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h6><?= get_label('documents', 'Documents') ?></h6>
                    <span data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="{{ get_label('upload', 'Upload') }}">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#uploadAttachmentModal">
                            <i class="bx bx-upload"></i>
                        </button>
                    </span>
                </div>

                @if ($candidates->getMedia('candidate-media')->count() > 0)
                    <div class="table-responsive text-nowrap">
                        <input type="hidden" id="attachment_type" value="media">
                        @php
                            $visibleColumns = getUserPreferences('candidate-media');
                        @endphp

                        <table id="table" data-toggle="table"
                            data-url="{{ route('candidate.attachmentsList', $candidates->id) }}"
                            data-loading-template="loadingTemplate" data-icons-prefix="bx" data-icons="icons"
                            data-show-refresh="true" data-total-field="total" data-data-field="rows"
                            data-page-list="[5, 10, 20, 50, 100]" data-search="true" data-side-pagination="server"
                            data-pagination="true" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                            data-query-params="queryParams" data-show-columns="true">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true"
                                        data-visible="{{ in_array('id', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('id', 'ID') }}
                                    </th>
                                    <th data-field="name" data-sortable="true"
                                        data-visible="{{ in_array('name', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('name', 'Name') }}
                                    </th>
                                    <th data-field="type"
                                        data-visible="{{ in_array('type', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('type', 'Type') }}
                                    </th>
                                    <th data-field="size" data-escap="false"
                                        data-visible="{{ in_array('size', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('size', 'Size') }}
                                    </th>
                                    <th data-field="created_at" data-sortable="true"
                                        data-visible="{{ in_array('created_at', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}">
                                        {{ get_label('uploaded_at', 'Uploaded At') }}
                                    </th>
                                    <th data-field="actions"
                                        data-visible="{{ in_array('actions', $visibleColumns) || empty($visibleColumns) ? 'true' : 'false' }}"
                                        data-width="170" data-align="center">
                                        {{ get_label('actions', 'Actions') }}
                                    </th>

                                </tr>
                            </thead>
                        </table>

                    </div>
                @else
                    <div class="py-5 text-center">
                        <div class="mb-3">
                            <i class="bx bx-file text-primary"></i>
                        </div>
                        <h6><?= get_label('no_attachments', 'No Attachments Found') ?></h6>
                        <p class="text-muted">
                            <?= get_label('no_attachments_desc', 'Upload documents related to this candidate') ?>
                        </p>
                    </div>
                @endif
            </div>
        </div>
        {{-- inteterview --}}
        @php
            $visibleColumns = getUserPreferences('interviews');
        @endphp
        @if (is_countable($interviews) && count($interviews) > 0)
            <div class="card mb-4 shadow-sm">
                <h5 class="card-header">{{ get_label('interviews', 'Interviews') }}</h5>
                <div class="card-body">
                    <div class="d-flex justify-content-end align-items-center mb-3">
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#createInterviewModal">
                            <button type="button" class="btn btn-sm btn-primary action_create_template" data-bs-toggle="tooltip"
                                data-bs-placement="left"
                                data-bs-original-title="{{ get_label('schedule_interview', 'Schedule Interview') }}">
                                <i class='bx bx-plus'></i>
                            </button>
                        </a>
                    </div>


                    <div class="table-responsive text-nowrap">
                        <table id="table" data-toggle="table" data-loading-template="loadingTemplate" data-show-columns="true"
                            data-url="{{ route('interviews.list') }}" data-icons-prefix="bx" data-icons="icons"
                            data-show-refresh="true" data-total-field="total" data-data-field="rows"
                            data-page-list="[5, 10, 20, 50, 100]" data-side-pagination="server" data-pagination="true"
                            data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                            data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">{{ get_label('id', 'ID') }}</th>
                                    <th data-field="candidate" data-sortable="true">{{ get_label('candidate', 'Candidate') }}
                                    </th>
                                    <th data-field="interviewer">{{ get_label('interviewer', 'Interviewer') }}</th>
                                    <th data-field="round" data-escap="false">{{ get_label('round', 'Round') }}</th>
                                    <th data-field="scheduled_at" data-sortable="true">
                                        {{ get_label('scheduled_at', 'Scheduled At') }}
                                    </th>
                                    <th data-field="status" data-sortable="true">{{ get_label('status', 'Status') }}</th>
                                    <th data-field="location" data-sortable="true">{{ get_label('location', 'Location') }}</th>
                                    <th data-field="mode" data-sortable="true">{{ get_label('mode', 'Mode') }}</th>
                                    <th data-field="created_at" data-sortable="true">{{ get_label('created_at', 'Created At') }}
                                    </th>
                                    <th data-field="updated_at" data-sortable="true">{{ get_label('updated_at', 'Updated At') }}
                                    </th>
                                    <th data-field="actions">{{ get_label('actions', 'Actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

        @else
            <?php    $type = 'Interview'; ?>
            <x-empty-state-card :type="$type" />
        @endif
    </div>

    <script src="{{ asset('assets/js/pages/candidate.js') }}"></script>
    <script src="{{ asset('assets/js/pages/candidate-profile.js') }}"></script>
@endsection
