@extends('layout')
@section('title')
    {{ get_label('view_ticket', 'View Ticket') }}
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home.index') }}" class="text-primary">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('support.index') }}"
                            class="text-secondary">{{ get_label('support', 'Support') }}</a>
                    </li>
                    <li class="breadcrumb-item text-muted" aria-current="page">
                        {{ get_label('view_ticket', 'View Ticket') }}
                    </li>
                    <li class="breadcrumb-item active">
                        <a href="{{ route('support.show', ['support' => $ticket->id]) }}">{{ $ticket->title }}</a>
                    </li>
                </ol>
            </nav>
            <div>
                <a href="{{ route('support.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>{{ get_label('back_to_list', 'Back to List') }}
                </a>
            </div>
        </div>
        <!-- Ticket Details Card -->
        <div class="card border-light mb-4 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <!-- Ticket Details Title -->
                    <h4 class="">{{ get_label('ticket_details', 'Ticket Details') }}</h4>
                    @if (auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('manager'))
                        <!-- Status Update Dropdown in Top Right of Card (Only for Superadmin) -->
                        @if ($ticket->status !== 'closed')
                            <form method="POST" action="{{ route('support.update-status', ['ticket' => $ticket->id]) }}">
                                @csrf
                                @method('PUT')
                                <div class="input-group">
                                    <select name="status" class="form-select form-select-md" onchange="this.form.submit()">
                                        <option class="bg-label-success" value="open"
                                            {{ $ticket->status == 'open' ? 'selected' : '' }}>
                                            {{ get_label('open', 'Open') }}</option>
                                        <option class="bg-label-warning" value="in_progress"
                                            {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>
                                            {{ get_label('in_progress', 'In Progress') }}</option>
                                        <option class="bg-label-danger" value="closed"
                                            {{ $ticket->status == 'closed' ? 'selected' : '' }}>
                                            {{ get_label('closed', 'Closed') }}</option>
                                    </select>
                                    <button class="btn btn-primary btn-sm"
                                        type="submit">{{ get_label('update_status', 'Update Status') }}</button>
                                </div>
                            </form>
                        @else
                            <!-- Ticket is closed, display the status as disabled -->
                            <span class="badge bg-label-danger">{{ ucfirst($ticket->status) }}</span>
                        @endif
                    @endif
                </div>
                <!-- Ticket Details -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h5 class="fw-semibold text-muted">{{ get_label('title', 'Title') }}</h5>
                        <p class="card-text">{{ $ticket->title }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h5 class="fw-semibold text-muted">{{ get_label('description', 'Description') }}</h5>
                        <p class="card-text">{{ $ticket->description }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h5 class="fw-semibold text-muted">{{ get_label('priority', 'Priority') }}</h5>
                        <span
                            class="badge bg-{{ $ticket->priority->color }} text-uppercase">{{ ucfirst($ticket->priority->name) }}</span>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h5 class="fw-semibold text-muted">{{ get_label('status', 'Status') }}</h5>
                        <span
                            class="badge bg-{{ $ticket->status == 'closed' ? 'danger' : ($ticket->status == 'in_progress' ? 'warning' : 'success') }}">{{ ucfirst($ticket->status) }}</span>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h5 class="fw-semibold text-muted">{{ get_label('created_at', 'Created At') }}</h5>
                        <p class="card-text">{{ $ticket->created_at->format('d-m-Y H:i') }}</p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <h5 class="fw-semibold text-muted">{{ get_label('created_by', 'Created By') }}</h5>
                        <p class="card-text">{{ $createdBy->first_name }} {{ $createdBy->last_name }}
                            ({{ $createdBy->email }})</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Subscription Details Card -->
        <div class="card border-light mb-4 shadow-sm">
            <div class="card-body">
                <h4 class="text-dark mb-4">{{ get_label('subscription_details', 'Subscription Details') }}</h4>
                @if ($subscriptionDetails)
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <h5 class="fw-semibold text-muted">{{ get_label('subscription_plan', 'Plan') }}</h5>
                            <p class="card-text">{{ $subscriptionDetails->plan->name }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h5 class="fw-semibold text-muted">{{ get_label('subscription_start_date', 'Start Date') }}
                            </h5>
                            <p class="card-text">{{ $subscriptionDetails->starts_at }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h5 class="fw-semibold text-muted">{{ get_label('subscription_end_date', 'End Date') }}</h5>
                            <p class="card-text">{{ $subscriptionDetails->ends_at }}</p>
                        </div>
                    </div>
                @else
                    <p class="card-text text-muted">
                        {{ get_label('no_active_subscription', 'No active subscription found.') }}</p>
                @endif
            </div>
        </div>
        <!-- Media Attachments -->
        @if ($ticket->media->count())
            <div class="card border-light mb-4 shadow-sm">
                <div class="card-header">
                    <h5 class="text-dark">{{ get_label('media_attachments', 'Media Attachments') }}</h5>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold text-dark mb-3">{{ get_label('attached_media', 'Attached Media') }}</h5>
                    <div class="table-responsive">
                        <table class="table-hover table">
                            <thead>
                                <tr>
                                    <th>{{ get_label('file_name', 'File Name') }}</th>
                                    <th>{{ get_label('file_type', 'File Type') }}</th>
                                    <th>{{ get_label('actions', 'Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ticket->media as $media)
                                    @php
                                        $fileExtension = pathinfo($media->media_path, PATHINFO_EXTENSION);
                                        $fileName = basename($media->media_path);
                                    @endphp
                                    <tr>
                                        <td>
                                            @if (in_array($fileExtension, ['jpg', 'jpeg', 'png']))
                                                <i class="bx bx-image text-primary me-2"></i>
                                            @elseif ($fileExtension == 'pdf')
                                                <i class="bx bx-file-pdf text-danger me-2"></i>
                                            @elseif (in_array($fileExtension, ['doc', 'docx']))
                                                <i class="bx bx-file text-primary me-2"></i>
                                            @elseif (in_array($fileExtension, ['xls', 'xlsx']))
                                                <i class="bx bx-file text-success me-2"></i>
                                            @else
                                                <i class="bx bx-file text-muted me-2"></i>
                                            @endif
                                            {{ $fileName }}
                                        </td>
                                        <td>{{ strtoupper($fileExtension) }}</td>
                                        <td>
                                            @if (in_array($fileExtension, ['jpg', 'jpeg', 'png']))
                                                <a href="{{ asset('storage/' . $media->media_path) }}"
                                                    data-lightbox="ticket-media"
                                                    class="btn btn-sm btn-primary me-2">{{ get_label('view', 'View') }}</a>
                                            @endif
                                            <a href="{{ asset('storage/' . $media->media_path) }}"
                                                download="{{ $fileName }}"
                                                class="btn btn-sm btn-outline-secondary">{{ get_label('download', 'Download') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        <!-- Replies Section -->
        <div class="card chat-box shadow-sm">
            <div class="card-header bg-light">
                <h5 class="text-dark mb-0">{{ get_label('conversions', 'Conversations') }}</h5>
            </div>
            <div class="card-body max-height-450 bg-body scrollbar-thin overflow-auto p-3" id="chatBox">
                @foreach ($ticket->replies as $reply)
                    <div
                        class="d-flex align-items-start justify-content-{{ $reply->sender_id == auth()->id() ? 'end' : 'start' }} mb-4">
                        @if ($reply->sender_id != auth()->id())
                            <div class="avatar me-3">
                                <img src="{{ isset($reply->sender->photo) ? asset('storage/' . $reply->sender->photo) : asset('/storage/photos/no-image.jpg') }}"
                                    class="rounded-circle" alt="User">
                            </div>
                        @endif
                        <div class="card rounded-4 w-25 p-3 shadow-lg">
                            <div class="fs-5 fw-semibold mb-2">
                                {{ $reply->message }}
                            </div>
                            <small class="text-muted">
                                {{ ucfirst($reply->sender->getResult()) }}, {{ $reply->created_at->diffForHumans() }}
                            </small>
                        </div>
                        @if ($reply->sender_id == auth()->id())
                            <div class="avatar ms-3">
                                <img src="{{ isset($reply->sender->photo) ? asset('storage/' . $reply->sender->photo) : asset('/storage/photos/no-image.jpg') }}"
                                    class="rounded-circle" alt="User">
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="card-footer bg-light">
                <form class="form-submit-event" method="POST"
                    action="{{ route('reply.store', ['id' => $ticket->id]) }}">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="message" class="form-control" placeholder="Type a message..."
                            required>
                        <button class="btn btn-primary" type="submit">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
