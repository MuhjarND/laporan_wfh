@extends('layouts.admin')
@section('title', 'Laporan Pending')
@section('page-title', 'Laporan Menunggu Persetujuan')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Pending</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Laporan Menunggu Persetujuan</h3>
        @if($laporans->total() > 0)
            <div class="card-tools">
                <a href="{{ route('atasan.monitoring.sign-all') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-signature mr-1"></i> Tanda Tangani Semua
                </a>
            </div>
        @endif
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>No</th><th>Pegawai</th><th>Periode</th><th>Kegiatan</th><th>Diajukan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($laporans as $i => $lap)
                <tr>
                    <td>{{ $laporans->firstItem() + $i }}</td>
                    <td>
                        <strong>{{ $lap->user->name }}</strong><br>
                        <small style="color: var(--text-muted);">{{ $lap->user->nip }}</small>
                    </td>
                    <td style="color: var(--primary);">{{ $lap->periode }}</td>
                    <td>{{ $lap->kegiatan->count() }} kegiatan</td>
                    <td><small>{{ $lap->submitted_at ? $lap->submitted_at->format('d/m/Y H:i') : '-' }}</small></td>
                    <td>
                        <a href="{{ route('atasan.monitoring.show-laporan', $lap) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye mr-1"></i> Review
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4" style="color: var(--text-muted);">
                        <i class="fas fa-check-circle fa-2x d-block mb-2" style="color: var(--primary-light); opacity: 0.5;"></i>
                        Tidak ada laporan yang menunggu persetujuan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $laporans->links() }}
@endsection
