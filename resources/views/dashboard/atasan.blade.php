@extends('layouts.admin')
@section('title', 'Dashboard Atasan')
@section('page-title', 'Dashboard Atasan')
@section('breadcrumb')<li class="breadcrumb-item active">Dashboard</li>@endsection

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner"><h3>{{ $totalBawahan }}</h3><p>Pegawai yang Dinilai</p></div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('atasan.monitoring.index') }}" class="small-box-footer">Lihat <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner"><h3>{{ $laporanSubmitted }}</h3><p>Menunggu Persetujuan</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="{{ route('atasan.monitoring.pending') }}" class="small-box-footer">Review <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner"><h3>{{ $laporanApproved }}</h3><p>Laporan Disetujui</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('atasan.monitoring.index') }}" class="small-box-footer">Lihat <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-danger">
            <div class="inner"><h3>{{ $laporanSayaTotal }}</h3><p>Laporan Saya</p></div>
            <div class="icon"><i class="fas fa-file-alt"></i></div>
            <a href="{{ route('pegawai.laporan.index') }}" class="small-box-footer">Kelola <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title"><i class="fas fa-calendar-alt mr-2" style="color:var(--primary);"></i>Laporan Saya Bulan Ini</h3>
                <div class="card-tools">
                    @if(!$currentLaporan)
                        <a href="{{ route('pegawai.laporan.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i> Buat</a>
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
                        <p class="text-muted">Belum ada laporan pribadi untuk bulan ini.</p>
                        <a href="{{ route('pegawai.laporan.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Buat Laporan</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user-clock mr-2" style="color:var(--primary);"></i>Kegiatan Saya Terbaru</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Tanggal</th><th>Kegiatan</th><th>Periode</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($recentKegiatanSaya as $keg)
                        <tr>
                            <td>{{ $keg->tanggal->format('d/m/Y') }}</td>
                            <td>{{ Str::limit(strip_tags($keg->kegiatan), 70) }}</td>
                            <td><a href="{{ route('pegawai.laporan.show', $keg->laporan) }}" style="color:var(--primary);">{{ $keg->laporan->periode }}</a></td>
                            <td><a href="{{ route('pegawai.laporan.show', $keg->laporan) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada kegiatan pribadi</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-users mr-2" style="color:var(--primary);"></i>Pegawai yang Dinilai</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Nama</th><th>Jabatan</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @foreach($bawahan as $b)
                        <tr>
                            <td><strong>{{ $b->name }}</strong><br><small class="text-muted">NIP: {{ $b->nip }}</small></td>
                            <td><small>{{ $b->jabatan }}</small></td>
                            <td><a href="{{ route('atasan.monitoring.show-pegawai', $b) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-history mr-2" style="color:var(--primary);"></i>Kegiatan Pegawai yang Dinilai Terbaru</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Pegawai</th><th>Tanggal</th><th>Kegiatan</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($recentKegiatan as $keg)
                        <tr>
                            <td>{{ $keg->laporan->user->name }}</td>
                            <td>{{ $keg->tanggal->format('d/m/Y') }}</td>
                            <td>{{ Str::limit(strip_tags($keg->kegiatan), 70) }}</td>
                            <td><a href="{{ route('atasan.monitoring.show-laporan', $keg->laporan) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada kegiatan</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
