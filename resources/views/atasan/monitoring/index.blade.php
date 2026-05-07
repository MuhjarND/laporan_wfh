@extends('layouts.admin')
@section('title', 'Monitoring Pegawai')
@section('page-title', 'Monitoring Pegawai')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Monitoring</li>
@endsection

@section('content')
<div class="row">
    @forelse($bawahan as $b)
    <div class="col-lg-4 col-md-6 col-12">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-user-circle fa-3x mb-3" style="color:var(--primary);"></i>
                <h5>{{ $b->name }}</h5>
                <p class="text-muted" style="font-size:.85rem;">
                    NIP: {{ $b->nip }}<br>{{ $b->jabatan }}<br>{{ $b->pangkat }}
                </p>
                @php $countLaporan = $b->laporanWfh->count(); $countPending = $b->laporanWfh->where('status','submitted')->count(); @endphp
                <div class="d-flex justify-content-around mb-3">
                    <div><strong style="color:var(--primary);">{{ $countLaporan }}</strong><br><small class="text-muted">Laporan</small></div>
                    <div><strong style="color:#d97706;">{{ $countPending }}</strong><br><small class="text-muted">Pending</small></div>
                </div>
                <a href="{{ route('atasan.monitoring.show-pegawai', $b) }}" class="btn btn-primary btn-block"><i class="fas fa-eye mr-1"></i> Lihat Laporan</a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card"><div class="card-body text-center py-5 text-muted">
            <i class="fas fa-users fa-3x mb-3" style="opacity:.3;"></i><p>Belum ada pegawai yang dinilai.</p>
        </div></div>
    </div>
    @endforelse
</div>
@endsection
