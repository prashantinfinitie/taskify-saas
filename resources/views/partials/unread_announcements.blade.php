@if ($unreadAnnouncementsCount > 0)
    @foreach ($unreadAnnouncements as $announcement)
        <li>
            <a class="dropdown-item update-announcement-status" data-id="{{ $announcement->id }}"
                href="{{ route('announcements.index') }}">
                <div class="d-flex align-items-center">
                    <div class="fw-semibold me-auto">{{ $announcement->title }} <small
                            class="text-muted mx-2">{{ $announcement->created_at->diffForHumans() }}</small></div>
                    <i class="bx bxs-megaphone me-2"></i>
                </div>
                <div class="mt-2">
                    {{ strlen($announcement->content) > 50 ? substr($announcement->content, 0, 50) . '...' : $announcement->content }}
                </div>
            </a>
        </li>
        <li>
            <div class="dropdown-divider"></div>
        </li>
    @endforeach
@else
    <li class="d-flex align-items-center justify-content-center p-5">
        <span>{{ get_label('no_unread_announcements', 'No unread announcements') }}</span>
    </li>
    <li>
        <div class="dropdown-divider"></div>
    </li>
@endif
