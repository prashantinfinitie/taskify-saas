@extends('layout')

@section('title')
    {{ get_label('asset_details', 'Asset Details') }}
@endsection

@section('content')

<div class="container-fluid mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item">
                <a href="{{ url('home') }}">{{ get_label('home', 'Home') }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('assets.index') }}">{{ get_label('assets', 'Assets') }}</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ $asset->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-xl-4 col-lg-5 col-md-6">
            <div class="card mb-6">
                <div class="card-body pt-4">
                    <div class="customer-avatar-section">
                        <div class="d-flex align-items-center flex-column">
                            <div class="position-relative mb-4">
                                <div class="image-box-85 rounded-circle border shadow-sm overflow-hidden d-flex align-items-center justify-content-center" style="width: 120px; height: 120px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                    @if ($asset->getFirstMediaUrl('asset-media'))
                                        <img src="{{ $asset->getFirstMediaUrl('asset-media') }}" alt="Asset Image"
                                            class="img-fluid rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                                    @else
                                        <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                                    @endif
                                </div>
                                @php
                                    $statusConfig = match ($asset->status) {
                                        'available' => ['bg' => 'success', 'icon' => 'bx-check-circle'],
                                        'lent' => ['bg' => 'warning', 'icon' => 'bx-share'],
                                        'non-functional' => ['bg' => 'danger', 'icon' => 'bx-x-circle'],
                                        'lost' => ['bg' => 'dark', 'icon' => 'bx-search-alt'],
                                        'damaged' => ['bg' => 'danger', 'icon' => 'bx-error'],
                                        'under-maintenance' => ['bg' => 'info', 'icon' => 'bx-wrench'],
                                        default => ['bg' => 'secondary', 'icon' => 'bx-info-circle'],
                                    };
                                @endphp
                            </div>
                            <div class="position-absolute top-0 end-0">
                                <span class="badge bg-{{ $statusConfig['bg'] }} rounded-pill">
                                    <i class="bx {{ $statusConfig['icon'] }} me-1"></i>
                                    {{ str_replace('-', ' ', ucfirst($asset->status)) }}
                                </span>
                            </div>

                            <div class="customer-info text-center mb-4">
                                <h5 class="mb-0">{{ $asset->name }}</h5>
                                <span class="text-muted">{{ get_label('asset_tag', 'Asset Tag') }} #{{ $asset->asset_tag }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <div class="avatar-initial rounded bg-label-primary">
                                        <i class="bx bx-money icon-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    @php
                                        $general_settings = get_settings('general_settings');
                                        $currency_symbol = $general_settings['currency_symbol'] ?? '₹';
                                    @endphp
                                    <h6 class="mb-0">
                                        @if ($asset->purchase_cost)
                                            {{ $currency_symbol }}{{ number_format($asset->purchase_cost, 2) }}
                                        @else
                                            -
                                        @endif
                                    </h6>
                                    <small class="text-muted">{{ get_label('purchase_cost', 'Purchase Cost') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    <div class="avatar-initial rounded bg-label-success">
                                        <i class="bx bx-calendar icon-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $asset->histories->count() }}</h6>
                                    <small class="text-muted">{{ get_label('history_records', 'History Records') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-container">
                        <h6 class="pb-3 border-bottom text-capitalize mb-4">{{ get_label('details', 'Details') }}</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-medium">{{ get_label('category', 'Category') }}:</span>
                                    <span class="text-muted">{{ optional($asset->category)->name ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-medium">{{ get_label('assigned_to', 'Assigned To') }}:</span>
                                    <span class="text-muted">
                                        @if($asset->assignedUser)
                                            {!! formatUserHtml($asset->assignedUser) !!}
                                        @else
                                            {{ get_label('not_assigned','Not Assigned') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-medium">{{ get_label('status', 'Status') }}:</span>
                                    <span class="badge bg-label-{{ $statusConfig['bg'] }}">
                                        {{ str_replace('-', ' ', ucfirst($asset->status)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-medium">{{ get_label('purchase_date', 'Purchase Date') }}:</span>
                                    <span class="text-muted">{{ format_date($asset->purchase_date, false) ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-column">
                                    <span class="fw-medium mb-2">{{ get_label('description', 'Description') }}:</span>
                                    <span class="text-muted">{{ $asset->description ?? 'No description available' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2 mb-4">
                            @if ($asset->isAvailable() && isAdminOrHasAllDataAccess())
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#lendAssetModal">
                                    <i class="bx bx-share me-1"></i> {{ get_label('lend_asset', 'Lend Asset') }}
                                </button>
                            @elseif(isset($asset->assigned_to) && (isAdminOrHasAllDataAccess() || $asset->assigned_to == auth()->id()))
                                <button type="button" class="btn btn-warning"
                                    data-bs-toggle="modal" data-bs-target="#returnAssetModal">
                                    <i class="bx bx-undo me-1"></i> {{ get_label('return_asset', 'Return Asset') }}
                                </button>
                            @endif
                            <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i> {{ get_label('back', 'Back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7 col-md-6">
            @if(isAdminOrHasAllDataAccess())
            <div class="card">
                <h5 class="card-header">{{ get_label('asset_history', 'Asset History') }}</h5>
                <div class="card-body p-0">
                    <div class="timeline-container" style="max-height: 600px; overflow-y: auto; padding: 1.5rem;">
                        <ul class="timeline mb-0">
                            @if($asset->histories->count() > 0)
                                @foreach ($asset->histories as $history)
                                    <li class="timeline-item timeline-item-transparent">
                                        @php
                                            $timelineColor = match ($history->action) {
                                                'Created' => 'success',
                                                'Lent' => 'warning',
                                                'Returned' => 'info',
                                                'Updated' => 'primary',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="timeline-point timeline-point-{{ $timelineColor }}"></span>
                                        <div class="timeline-event">
                                            <div class="timeline-header mb-3">
                                                <h6 class="mb-0">{{ format_date($history->created_at, true) }}</h6>
                                                <small class="text-muted">{{ $history->created_at->diffForHumans() }}</small>
                                            </div>

                                            @if($history->action === 'Lent')
                                                <div class="d-flex align-items-center mb-2">
                                                    <p class="mb-2">
                                                        {{ get_label('asset_lent_to', 'Asset lent to') }}
                                                        <span class="fw-bold text-primary">{{ optional($history->lentToUser)->first_name . ' ' . optional($history->lentToUser)->last_name ?? 'N/A' }}</span>
                                                        @if($history->estimated_return_date)
                                                            <br><small class="text-muted">{{ get_label('estimated_return', 'Expected return') }}: {{ $history->estimated_return_date->format('d M Y') }}</small>
                                                        @endif
                                                        @if($history->actual_return_date)
                                                            <br><small class="text-success">{{ get_label('returned_on', 'Returned on') }}: {{ $history->actual_return_date->format('d M Y') }}</small>
                                                        @else
                                                            <br><span class="badge bg-label-warning">{{ get_label('currently_active', 'Currently Active') }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center mb-2">
                                                    <p class="mb-2">
                                                        {{ get_label('action_performed', 'Action performed') }}
                                                        <span class="fw-bold badge bg-label-{{ $timelineColor }}">{{ $history->action }}</span>
                                                        {{ get_label('by', 'by') }}
                                                        <span class="fw-bold">{{ optional($history->user)->first_name . ' ' . optional($history->user)->last_name ?? 'N/A' }}</span>
                                                    </p>
                                                </div>
                                            @endif

                                            @if($history->notes)
                                                <div class="mt-2">
                                                    <small class="text-muted">{{ get_label('notes', 'Notes') }}: {{ $history->notes }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <li class="timeline-item timeline-item-transparent">
                                    <span class="timeline-point timeline-point-info"></span>
                                    <div class="timeline-event">
                                        <div class="timeline-header mb-3">
                                            <h6 class="mb-0">{{ get_label('no_history', 'No History') }}</h6>
                                        </div>
                                        <p class="text-muted">{{ get_label('no_history_found', 'No history found for this asset') }}</p>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if(isAdminOrHasAllDataAccess())
    <div class="modal fade" id="lendAssetModal" tabindex="-1" aria-labelledby="lendAssetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lendAssetModalLabel">{{ get_label('lend_asset', 'Lend Asset') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="lendAssetForm">
                    <div class="modal-body">
                        <input type="hidden" name="asset_id" value="{{ $asset->id }}">

                        <div class="mb-3">
                            <label for="update-asset-assign-to" class="form-label">{{ get_label('lend_to', 'Lend To') }} <span class="text-danger">*</span></label>
                            <select class="form-select select-asset-assigned_to" id="update-asset-assign-to"
                                name="lent_to" data-placeholder="{{ get_label('select_user', 'Select User') }}"
                                data-single-select="true" required>
                                <option value="">{{ get_label('select_user', 'Select User') }}</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="estimated_return_date" class="form-label">{{ get_label('estimated_return_date', 'Estimated Return Date') }} </label>
                            <input type="datetime-local" class="form-control" id="estimated_return_date"
                                name="estimated_return_date">
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ get_label('notes', 'Notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                placeholder="{{ get_label('enter_notes', 'Enter notes (optional)') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ get_label('cancel', 'Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ get_label('lend_asset', 'Lend Asset') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if(isset($asset->assigned_to) && (isAdminOrHasAllDataAccess() || $asset->assigned_to == auth()->id()))
    <div class="modal fade" id="returnAssetModal" tabindex="-1" aria-labelledby="returnAssetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnAssetModalLabel">{{ get_label('return_asset', 'Return Asset') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="returnAssetForm">
                    <div class="modal-body">
                        <input type="hidden" name="asset_id" value="{{ $asset->id }}">
                        <p>Are you sure you want to return the asset <strong>{{ $asset->name }}</strong>?</p>
                        <div class="mb-3">
                            <label for="return_notes" class="form-label">{{ get_label('notes', 'Notes') }}</label>
                            <textarea class="form-control" id="return_notes" name="notes" rows="3"
                                placeholder="{{ get_label('enter_notes', 'Enter notes (optional)') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ get_label('cancel', 'Cancel') }}</button>
                        <button type="submit" class="btn btn-warning">{{ get_label('return_asset', 'Return Asset') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<script src="{{ asset('assets/js/asset-plugin/assets.js') }}"></script>
@endsection
