@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Mon compte /</span> Notifications
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mes notifications</h5>
                    <div class="btn-group">
                        <form action="{{ route('notifications.mark-all-as-read') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-check-double me-1"></i> Tout marquer comme lu
                            </button>
                        </form>
                        <form action="{{ route('notifications.destroy-all-read') }}" method="POST" class="d-inline ms-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer toutes les notifications lues ?')">
                                <i class="bx bx-trash me-1"></i> Supprimer les notifications lues
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($notifications->isEmpty())
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-1"></i>
                            Vous n'avez aucune notification pour le moment.
                        </div>
                    @else
                        <div class="notifications-list">
                            @foreach($notifications as $notification)
                                <div class="card mb-3 {{ $notification->is_read ? 'bg-light' : 'border-primary' }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-0">
                                                    @if(!$notification->is_read)
                                                        <span class="badge bg-primary me-1">Nouveau</span>
                                                    @endif
                                                    @switch($notification->type)
                                                        @case('reservation')
                                                            <i class="bx bx-calendar me-1 text-primary"></i> Réservation
                                                            @break
                                                        @case('order')
                                                            <i class="bx bx-food-menu me-1 text-success"></i> Commande
                                                            @break
                                                        @case('review')
                                                            <i class="bx bx-star me-1 text-warning"></i> Avis
                                                            @break
                                                        @case('review_approved')
                                                            <i class="bx bx-check-circle me-1 text-success"></i> Avis approuvé
                                                            @break
                                                        @default
                                                            <i class="bx bx-bell me-1"></i> Notification
                                                    @endswitch
                                                </h6>
                                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                @if(!$notification->is_read)
                                                    <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-primary btn-sm">
                                                            <i class="bx bx-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <p class="mb-0">{{ $notification->message }}</p>
                                    </div>
                                </div>
                            @endforeach

                            <div class="d-flex justify-content-center mt-4">
                                {{ $notifications->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
