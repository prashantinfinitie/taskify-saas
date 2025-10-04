<div class="d-flex justify-content-between">
    <h5 class="card-title">
        {{ get_label('discussions', 'Discussions') }} : {{ $task->title }}
    </h5>
    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#task_commentModal">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right"
            data-bs-original-title="{{ get_label('add_comment', 'Add Comment') }}">
            <i class="bx bx-plus"></i>
        </button>
    </a>
</div>
<div id="comment-thread-container">
    <div class="comment-thread">
        @if (isset($task->comments) && $task->comments->isNotEmpty())
            @foreach ($task->comments->whereNull('parent_id')->reverse() as $comment)
                <details open class="comment" id="comment-{{ $comment->id }}">
                    <a href="#comment-{{ $comment->id }}" class="comment-border-link">
                        <span class="sr-only">Jump to comment-{{ $comment->id }}</span>
                    </a>
                    <summary>
                        <div class="comment-heading">
                            <div class="comment-avatar">
                                <img src="{{ isset($comment->user->photo) ? asset('storage/' . $comment->user->photo) : asset('storage/photos/no-image.jpg') }}"
                                    class="bg-footer-theme rounded-circle border"
                                    alt="{{ isset($comment->user->first_name) && isset($comment->user->last_name) ? $comment->user->first_name . ' ' . $comment->user->last_name : 'User' }}">
                            </div>
                            <div class="comment-info">
                                <a href="{{ isset($comment->user->id) ? route('users.show', [$comment->user->id]) : '#' }}"
                                    class="comment-author fw-semibold text-body">{{ isset($comment->user->first_name) && isset($comment->user->last_name) ? $comment->user->first_name . ' ' . $comment->user->last_name : 'Unknown User' }}</a>
                                <p class="m-0">
                                    {{ isset($comment->created_at) ? $comment->created_at->diffForHumans() : '' }}
                                    @if (isset($comment->created_at) && $comment->created_at != $comment->updated_at)
                                        <span class="text-muted">({{ get_label('edited', 'Edited') }})</span>
                                    @endif
                                </p>
                            </div>
                            @if (isAdminOrHasAllDataAccess())
                                <div class="comment-actions d-flex ms-5 p-0">
                                    <a href="javascript:void(0);" data-comment-id="{{ $comment->id }}"
                                        class="btn btn-sm text-primary edit-task-comment p-0" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="{{ get_label('edit', 'Edit') }}">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);" data-comment-id="{{ $comment->id }}"
                                        class="btn btn-sm text-danger delete-task-comment p-0" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="{{ get_label('delete', 'Delete') }}">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </summary>
                    <div class="comment-body">
                        <p>{!! isset($comment->content) ? $comment->content : '' !!}</p>
                        <!-- Attachments Section -->
                        @if (isset($comment->attachments) && $comment->attachments->isNotEmpty())
                            <div class="attachments mt-2">
                                @foreach ($comment->attachments as $attachment)
                                    <div class="attachment-item d-flex align-items-center gap-3">
                                        <!-- File Preview and Name Section -->
                                        <div class="attachment-preview-container flex-grow-1">
                                            <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank"
                                                class="attachment-link text-decoration-none"
                                                data-preview-url="{{ asset('storage/' . $attachment->file_path) }}">
                                                {{ isset($attachment->file_name) ? $attachment->file_name : 'Attachment' }}
                                            </a>
                                            <div class="attachment-preview"></div>
                                        </div>
                                        <!-- Action Buttons Group -->
                                        <div class="attachment-actions d-flex gap-2">
                                            <!-- Download Button -->
                                            <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                                download="{{ isset($attachment->file_name) ? $attachment->file_name : 'file' }}"
                                                class="text-primary" title="Download">
                                                <i class="bx bx-download fs-4"></i>
                                            </a>
                                            <!-- Delete Icon -->
                                            <a href="javascript:void(0);" class="text-danger delete-attachment"
                                                data-attachment-id="{{ $attachment->id }}" title="Delete">
                                                <i class="bx bx-trash fs-4"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <button type="button" class="open-task-reply-modal mt-3"
                            data-comment-id="{{ $comment->id }}">Reply</button>
                    </div>
                    @if (isset($comment->children) && $comment->children->count() > 0)
                        <div class="replies">
                            @foreach ($comment->children->reverse() as $reply)
                                <details open class="comment" id="comment-{{ $reply->id }}">
                                    <a href="#comment-{{ $reply->id }}" class="comment-border-link">
                                        <span class="sr-only">Jump to comment-{{ $reply->id }}</span>
                                    </a>
                                    <summary>
                                        <div class="comment-heading">
                                            <div class="comment-avatar">
                                                <img src="{{ isset($reply->user->photo) ? asset('storage/' . $reply->user->photo) : asset('storage/photos/no-image.jpg') }}"
                                                    class="bg-footer-theme rounded-circle border"
                                                    alt="{{ isset($reply->user->first_name) && isset($reply->user->last_name) ? $reply->user->first_name . ' ' . $reply->user->last_name : 'User' }}">
                                            </div>
                                            <div class="comment-info">
                                                <a href="{{ isset($reply->user->id) ? route('users.show', [$reply->user->id]) : '#' }}"
                                                    class="comment-author text-body fw-light">{{ isset($reply->user->first_name) && isset($reply->user->last_name) ? $reply->user->first_name . ' ' . $reply->user->last_name : 'Unknown User' }}</a>
                                                <p class="m-0">
                                                    {{ isset($reply->created_at) ? $reply->created_at->diffForHumans() : '' }}
                                                    @if (isset($reply->created_at) && $reply->created_at != $reply->updated_at)
                                                        <span
                                                            class="text-muted">({{ get_label('edited', 'Edited') }})</span>
                                                    @endif
                                                </p>
                                            </div>
                                            @if (isAdminOrHasAllDataAccess())
                                                <div class="comment-actions d-flex ms-5 p-0">
                                                    <a href="javascript:void(0);" data-comment-id="{{ $reply->id }}"
                                                        class="btn btn-sm text-primary edit-task-comment p-0"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="{{ get_label('edit', 'Edit') }}">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" data-comment-id="{{ $reply->id }}"
                                                        class="btn btn-sm text-danger delete-task-comment p-0"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="{{ get_label('delete', 'Delete') }}">
                                                        <i class="bx bx-trash"></i>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </summary>
                                    <div class="comment-body">
                                        <p class="text-secondary">{!! isset($reply->content) ? $reply->content : '' !!}</p>
                                        <!-- Attachments Section -->
                                        @if (isset($reply->attachments) && $reply->attachments->isNotEmpty())
                                            <div class="attachments mt-2">
                                                @foreach ($reply->attachments as $attachment)
                                                    <div class="attachment-item d-flex align-items-center gap-3">
                                                        <!-- File Preview and Name Section -->
                                                        <div class="attachment-preview-container flex-grow-1">
                                                            <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                                                target="_blank"
                                                                class="attachment-link text-decoration-none"
                                                                data-preview-url="{{ asset('storage/' . $attachment->file_path) }}">
                                                                {{ isset($attachment->file_name) ? $attachment->file_name : 'Attachment' }}
                                                            </a>
                                                            <div class="attachment-preview"></div>
                                                        </div>
                                                        <!-- Action Buttons Group -->
                                                        <div class="attachment-actions d-flex gap-2">
                                                            <!-- Download Button -->
                                                            <a href="{{ asset('storage/' . $attachment->file_path) }}"
                                                                download="{{ isset($attachment->file_name) ? $attachment->file_name : 'file' }}"
                                                                class="text-primary" title="Download">
                                                                <i class="bx bx-download fs-4"></i>
                                                            </a>
                                                            <!-- Delete Icon -->
                                                            <a href="javascript:void(0);"
                                                                class="text-danger delete-attachment"
                                                                data-attachment-id="{{ $attachment->id }}"
                                                                title="Delete">
                                                                <i class="bx bx-trash fs-4"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @endif
                </details>
            @endforeach
        @else
            <p class="text-muted no_comments text-center">{{ get_label('no_comments', 'No Comments') }}</p>
        @endif
    </div>
    @if (isset($task->comments) && $task->comments->count() > 5)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="text-center">
                <button id="load-more-comments" class="btn btn-link text-body">
                    <i class="bx bx-chevron-down"></i>
                    {{ get_label('load_more', 'Load More') }}
                </button>
                <button id="hide-comments" class="btn btn-link text-body">
                    <i class="bx bx-chevron-up"></i>
                    {{ get_label('hide', 'Hide') }}
                </button>
            </div>
        </div>
    @endif
</div>
<script>
    var isAdminOrHasAllDataAccess = {{ isAdminOrHasAllDataAccess() ? 'true' : 'false' }};
</script>
