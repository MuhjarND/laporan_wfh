@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')
@section('breadcrumb')<li class="breadcrumb-item active">Dashboard</li>@endsection

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner"><h3>{{ $totalUsers }}</h3><p>Total User</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('admin.users.index') }}" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner"><h3>{{ $totalLaporan }}</h3><p>Total Laporan</p></div>
            <div class="icon"><i class="fas fa-file-alt"></i></div>
            <a href="{{ route('admin.laporan.index') }}" class="small-box-footer">Lihat <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner"><h3>{{ $laporanSubmitted }}</h3><p>Menunggu Approval</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="#" class="small-box-footer">Info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-danger">
            <div class="inner"><h3>{{ $totalWfhDates }}</h3><p>Tanggal WFH Aktif</p></div>
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
            <a href="{{ route('admin.wfh-dates.index') }}" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie mr-2" style="color:var(--primary);"></i>Statistik User</h3></div>
            <div class="card-body">
                <div class="d-flex justify-content-around text-center">
                    <div><h4 style="color:var(--primary);">{{ $totalPegawai }}</h4><small class="text-muted">Pegawai</small></div>
                    <div><h4 style="color:var(--accent);">{{ $totalAtasan }}</h4><small class="text-muted">Atasan</small></div>
                    <div><h4 style="color:#0891b2;">{{ $laporanApproved }}</h4><small class="text-muted">Disetujui</small></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-2" style="color:var(--primary);"></i>Laporan Terbaru</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Pegawai</th><th>Periode</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($recentLaporan as $lap)
                        <tr><td>{{ $lap->user->name }}</td><td>{{ $lap->periode }}</td><td>{!! $lap->status_badge !!}</td></tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">Belum ada laporan</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
