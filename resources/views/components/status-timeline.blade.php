<h5 class="card-header">{{ get_label('status_timeline', 'Status Timeline') }}</h5>
<div class="card-body">
    <ul class="timeline mb-0">
        @if($timelines->count() > 0)
        @foreach ($timelines as $timeline )
        <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-{{ $timeline->new_color }}"></span>
            <div class="timeline-event">
                <div class="timeline-header mb-3">
                    <h5 class="mb-0"> {{ format_date($timeline->created_at ,true) }}</h5>
                    <small class="text-muted">{{ $timeline->created_at->diffForHumans() }}</small>
                </div>
                <div class="d-flex align-items-center mb-2 ">
                    <p class="mb-2">
                        {{ get_label('status_changed_from','Status changed from') }}
                        <span class="fw-bold badge bg-label-{{ $timeline->old_color ?? 'primary' }}">{{ $timeline->previous_status == '-' ? get_label('initial_status', 'Initial Status') : $timeline->previous_status }}</span>
                        <span class="text-dark"><i class='bx bxs-chevrons-right'></i></span>
                        <span class="fw-bold badge bg-label-{{ $timeline->new_color }}">{{ $timeline->status }}</span </div>
                    </p>
                </div>
        </li>
        @endforeach
        @else
        <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-info"></span>
            <div class="timeline-event">
                <div class="timeline-header mb-3">
                    <h5 class="mb-0"> {{ get_label('no_status_change','No Status Change')  }}</h5>
                </div>
            </div>
        </li>
        @endif
    </ul>
</div>

