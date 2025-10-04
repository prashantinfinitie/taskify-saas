<div class="interview-details-container">
    <div class="card mb-4 shadow-none">
        <div class="card-header bg-light">
            <h5 class="mb-0">{{ $candidate->name }}</h5>
            <p class="text-muted mb-0">{{ $candidate->position }}</p>
        </div>
        <div class="card-body">

            @if($candidate->interviews->count() > 0)
                <div class="interview-timeline">
                    @foreach($candidate->interviews as $interview)
                        <div class="interview-item mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-sm bg-primary me-3 d-flex align-items-center justify-content-center">
                                    <i class="bx bx-calendar-check text-white"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $interview->title ?? 'Interview' }}</h6>
                                    <small class="text-muted">
                                        @php
                                            $scheduledDateTime = \Carbon\Carbon::parse($interview->scheduled_at);
                                        @endphp
                                        {{ $scheduledDateTime->format('M d, Y') }} at {{ $scheduledDateTime->format('h:i A') }}
                                    </small>
                                </div>
                            </div>

                            <div class="ps-4 ms-2 border-start">
                                <div class="mb-2">
                                    <span class="badge bg-label-info">
                                        <i class="bx bx-user me-1"></i>
                                        {{ get_label("interviewer","Interviewer") }}: {{ $interview->interviewer->first_name . " " . $interview->interviewer->last_name  ?? 'Not assigned' }}
                                    </span>

                                    <span class="badge bg-label-{{ $interview->status == 'completed' ? 'success' : ($interview->status == 'scheduled' ? 'warning' : 'secondary') }} ms-1">
                                        <i class="bx bx-info-circle me-1"></i>
                                        {{ ucfirst($interview->status ?? 'Pending') }}
                                    </span>
                                </div>

                                @if(!empty($interview->notes))
                                    <div class="bg-light p-3 rounded-3 mb-3">
                                        <p class="mb-0"><strong>{{ get_label("notes","Notes") }}:</strong> {{ $interview->notes }}</p>
                                    </div>
                                @endif

                                @if($interview->feedback && $interview->feedback->count() > 0)
                                    <div class="accordion mt-3" id="feedback-accordion-{{ $interview->id }}">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="feedback-heading-{{ $interview->id }}">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#feedback-collapse-{{ $interview->id }}" aria-expanded="false"
                                                    aria-controls="feedback-collapse-{{ $interview->id }}">
                                                    {{ get_label("feedback","Feedback") }}
                                                </button>
                                            </h2>
                                            <div id="feedback-collapse-{{ $interview->id }}" class="accordion-collapse collapse"
                                                aria-labelledby="feedback-heading-{{ $interview->id }}"
                                                data-bs-parent="#feedback-accordion-{{ $interview->id }}">
                                                <div class="accordion-body">
                                                    @foreach($interview->feedback as $feedback)
                                                        <div class="mb-3">
                                                            <div class="d-flex justify-content-between">
                                                                <p class="mb-1"><strong>{{ get_label("rating","Rating") }}:</strong>
                                                                    <span class="text-{{ $feedback->rating > 3 ? 'success' : ($feedback->rating > 2 ? 'warning' : 'danger') }}">
                                                                        @for($i = 1; $i <= 5; $i++)
                                                                            <i class="bx {{ $i <= $feedback->rating ? 'bxs-star' : 'bx-star' }}"></i>
                                                                        @endfor
                                                                    </span>
                                                                </p>
                                                                <small class="text-muted">{{ format_date($feedback->created_at) }}</small>
                                                            </div>
                                                            <p class="mb-0">{{ $feedback->comments }}</p>
                                                        </div>
                                                        @if(!$loop->last)
                                                            <hr>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <i class="bx bx-info-circle me-1"></i>
                    {{ get_label("no_interviews_scheduled_for_this_candidate_yet","No interviews scheduled for this candidate yet") }}.
                </div>
            @endif
        </div>
    </div>
</div>
