@extends('layouts.admin')
@section('title', 'Dashboard Pegawai')
@section('page-title', 'Dashboard')
@section('breadcrumb')<li class="breadcrumb-item active">Dashboard</li>@endsection

@section('content')
@include('dashboard.partials.signature-alert')

<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner"><h3>{{ $totalLaporan }}</h3><p>Total Laporan</p></div>
            <div class="icon"><i class="fas fa-file-alt"></i></div>
            <a href="{{ route('pegawai.laporan.index') }}" class="small-box-footer">Lihat <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner"><h3>{{ $laporanDraft }}</h3><p>Draft</p></div>
            <div class="icon"><i class="fas fa-edit"></i></div>
            <a href="{{ route('pegawai.laporan.index') }}" class="small-box-footer">Lihat <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner"><h3>{{ $laporanSubmitted }}</h3><p>Diajukan</p></div>
            <div class="icon"><i class="fas fa-paper-plane"></i></div>
            <a href="{{ route('pegawai.laporan.index') }}" class="small-box-footer">Lihat <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-danger">
            <div class="inner"><h3>{{ $laporanApproved }}</h3><p>Disetujui</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('pegawai.laporan.index') }}" class="small-box-footer">Lihat <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title"><i class="fas fa-calendar-alt mr-2" style="color:var(--primary);"></i>Laporan Bulan Ini</h3>
                <div class="card-tools">
                    @if(!$currentLaporan)
                        <a href="{{ route('pegawai.laporan.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i> Buat Laporan</a>
                    @endif
                </div>
            </div>
            <div class="card-body text-center">
                @if($currentLaporan)
                    <h5 style="color:var(--primary);">{{ $currentLaporan->periode }}</h5>
                    <div class="mt-2">{!! $currentLaporan->status_badge !!}</div>
                    <p class="mt-2 text-muted">{{ $currentLaporan->kegiatan->count() }} kegiatan tercatat</p>
                    <div class="d-flex justify-content-end">
                        @if(in_array($currentLaporan->status, ['draft', 'rejected']))
                            <a href="{{ route('pegawai.laporan.edit', $currentLaporan) }}" class="btn btn-primary mr-2"><i class="fas fa-plus mr-1"></i> Tambah Kegiatan</a>
                        @endif
                        <a href="{{ route('pegawai.laporan.show', $currentLaporan) }}" class="btn btn-outline-secondary"><i class="fas fa-eye mr-1"></i> Detail</a>
                    </div>
                @else
                    <div class="py-4">
                        <i class="fas fa-calendar-plus fa-3x mb-3" style="color:var(--primary);opacity:.3;"></i>
                        <p class="text-muted">Belum ada laporan untuk bulan ini.</p>
                        <a href="{{ route('pegawai.laporan.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Buat Laporan Sekarang</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        @include('dashboard.partials.wfh-registration-info')
    </div>
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-2" style="color:var(--primary);"></i>Kegiatan Terbaru</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Tanggal</th><th>Kegiatan</th><th>Periode</th></tr></thead>
                    <tbody>
                        @forelse($recentKegiatan as $keg)
                        <tr>
                            <td>{{ $keg->tanggal->format('d/m/Y') }}</td>
                            <td>{{ Str::limit(strip_tags($keg->kegiatan), 70) }}</td>
                            <td><a href="{{ route('pegawai.laporan.show', $keg->laporan) }}" style="color:var(--primary);">{{ $keg->laporan->periode }}</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted">Belum ada kegiatan</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
