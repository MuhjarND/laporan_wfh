@extends('layouts.admin')
@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Notifikasi</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-bell mr-2" style="color:var(--primary);"></i>Semua Notifikasi</h3>
        <div class="card-tools">
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">@csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-check-double mr-1"></i> Tandai Semua Dibaca</button>
                </form>
            @endif
        </div>
    </div>
    <div class="card-body p-0">
        @forelse($notifications as $notif)
        <div class="p-3 border-bottom" style="{{ $notif->read_at ? '' : 'background:#f0fdf4;' }}">
            <div class="d-flex align-items-start">
                <div class="mr-3">
                    @if(!$notif->read_at)<i class="fas fa-circle" style="color:var(--primary);font-size:.5rem;"></i>@endif
                </div>
                <div class="flex-grow-1">
                    <strong style="color:var(--primary);">{{ $notif->data['title'] ?? 'Notifikasi' }}</strong>
                    <p class="mb-1" style="font-size:.9rem;">{{ $notif->data['message'] ?? '' }}</p>
                    <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                </div>
                @if(!$notif->read_at)
                <form action="{{ route('notifications.read', $notif->id) }}" method="POST">@csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="p-4 text-center text-muted">
            <i class="far fa-bell-slash fa-2x mb-2 d-block" style="opacity:.3;"></i>Tidak ada notifikasi.
        </div>
        @endforelse
    </div>
</div>
{{ $notifications->links() }}
@endsection
