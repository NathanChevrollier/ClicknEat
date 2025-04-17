<li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
        <i class="bx bx-bell bx-sm"></i>
        @if(auth()->check() && auth()->user()->unread_notifications_count > 0)
            <span class="badge bg-danger rounded-pill badge-notifications">{{ auth()->user()->unread_notifications_count }}</span>
        @endif
    </a>
    <ul class="dropdown-menu dropdown-menu-end py-0">
        <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
                <h5 class="text-body mb-0 me-auto">Notifications</h5>
                <a href="{{ route('notifications.mark-all-as-read') }}" 
                   class="dropdown-notifications-all text-body"
                   data-bs-toggle="tooltip"
                   data-bs-placement="top"
                   title="Tout marquer comme lu"
                   onclick="event.preventDefault(); document.getElementById('mark-all-read-form').submit();">
                    <i class="bx bx-check-double fs-4"></i>
                </a>
                <form id="mark-all-read-form" action="{{ route('notifications.mark-all-as-read') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </li>
        <li class="dropdown-notifications-list scrollable-container">
            <ul class="list-group list-group-flush">
                @if(auth()->check())
                    @php
                        $recentNotifications = auth()->user()->customNotifications()
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp
                    
                    @forelse($recentNotifications as $notification)
                        <li class="list-group-item list-group-item-action dropdown-notifications-item {{ $notification->is_read ? '' : 'marked-as-unread' }}">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    @switch($notification->type)
                                        @case('reservation')
                                            <div class="avatar bg-primary">
                                                <span class="avatar-initial rounded-circle"><i class="bx bx-calendar"></i></span>
                                            </div>
                                            @break
                                        @case('order')
                                            <div class="avatar bg-success">
                                                <span class="avatar-initial rounded-circle"><i class="bx bx-food-menu"></i></span>
                                            </div>
                                            @break
                                        @case('review')
                                            <div class="avatar bg-warning">
                                                <span class="avatar-initial rounded-circle"><i class="bx bx-star"></i></span>
                                            </div>
                                            @break
                                        @case('review_approved')
                                            <div class="avatar bg-success">
                                                <span class="avatar-initial rounded-circle"><i class="bx bx-check-circle"></i></span>
                                            </div>
                                            @break
                                        @default
                                            <div class="avatar bg-info">
                                                <span class="avatar-initial rounded-circle"><i class="bx bx-bell"></i></span>
                                            </div>
                                    @endswitch
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ Str::limit($notification->message, 50) }}</h6>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="flex-shrink-0 dropdown-notifications-actions">
                                    @if(!$notification->is_read)
                                        <a href="{{ route('notifications.mark-as-read', $notification->id) }}" 
                                           class="dropdown-notifications-read"
                                           onclick="event.preventDefault(); document.getElementById('mark-read-form-{{ $notification->id }}').submit();">
                                            <span class="badge rounded-pill badge-dot bg-danger"></span>
                                        </a>
                                        <form id="mark-read-form-{{ $notification->id }}" action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item list-group-item-action dropdown-notifications-item">
                            <div class="d-flex justify-content-center align-items-center py-2">
                                <p class="mb-0 text-muted">Aucune notification</p>
                            </div>
                        </li>
                    @endforelse
                @endif
            </ul>
        </li>
        <li class="dropdown-menu-footer border-top">
            <a href="{{ route('notifications.index') }}" class="dropdown-item d-flex justify-content-center p-3">
                Voir toutes les notifications
            </a>
        </li>
    </ul>
</li>
