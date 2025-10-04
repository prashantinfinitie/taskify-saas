@extends('layout')
@section('title')
    {{ get_label('create_ticket', 'Create Ticket') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row mt-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}" class="text-decoration-none">
                                <i class="bi bi-house-door"></i> {{ get_label('home', 'Home') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('support.index') }}" class="text-decoration-none">
                                <i class="bi bi-life-preserver"></i> {{ get_label('support', 'Support') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('create_ticket', 'Create Ticket') }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-ticket-detailed me-2"></i>{{ get_label('create_new_ticket', 'Create New Ticket') }}
                </h4>
            </div>
            <div class="card-body">
                <form class="form-submit-event" action="{{ route('support.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="redirect_url" value="{{ route('support.index') }}">
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold">{{ get_label('title', 'Title') }}</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                            name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="description"
                            class="form-label fw-bold">{{ get_label('description', 'Description') }}</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                            rows="5" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="priority"
                                class="form-label fw-bold">{{ get_label('priority', 'Priority') }}</label>
                            <select class="form-select @error('priority_id') is-invalid @enderror" id="priority"
                                name="priority_id" required>
                                <option value="" disabled selected>
                                    {{ get_label('select_priorities', 'Select Priorities') }}</option>
                                @foreach ($ticket_priorities as $priority)
                                    <option value="{{ $priority->id }}"
                                        {{ old('priority_id') == $priority->id ? 'selected' : '' }}>
                                        {{ ucfirst($priority->name) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('priority_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                    <div class="mb-4">
                        <label for="media" class="form-label fw-bold">{{ get_label('media', 'Attach Media') }}</label>
                        <input type="file" class="form-control @error('media.*') is-invalid @enderror" id="media"
                            name="media[]" accept="image/jpeg,image/png,image/jpg,image/gif,image/svg,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"  multiple>
                        <span
                            class="form-text text-muted">{{ get_label('media_help', 'You can attach multiple files. Allowed file types: jpeg,png,jpg,gif,svg,pdf,doc,docx.') }}</span>
                        @error('media.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-send me-2"></i>{{ get_label('submit_ticket', 'Submit Ticket') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
