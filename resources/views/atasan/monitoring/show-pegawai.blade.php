@extends('layouts.admin')
@section('title', 'Laporan ' . $pegawai->name)
@section('page-title', 'Laporan Pegawai: ' . $pegawai->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('atasan.monitoring.index') }}">Monitoring</a></li>
    <li class="breadcrumb-item active">{{ $pegawai->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-user-circle fa-3x mb-3" style="color: var(--primary);"></i>
                <h5>{{ $pegawai->name }}</h5>
                <p style="color: var(--text-muted); font-size: 0.85rem;">
                    NIP: {{ $pegawai->nip }}<br>
                    {{ $pegawai->pangkat }}<br>
                    {{ $pegawai->jabatan }}<br>
                    {{ $pegawai->satuan_kerja }}
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i>Daftar Laporan WFH</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>No</th><th>Periode</th><th>Kegiatan</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse($laporans as $i => $lap)
                        <tr>
                            <td>{{ $laporans->firstItem() + $i }}</td>
                            <td style="color: var(--primary);">{{ $lap->periode }}</td>
                            <td>{{ $lap->kegiatan->count() }} kegiatan</td>
                            <td>{!! $lap->status_badge !!}</td>
                            <td>
                                <a href="{{ route('atasan.monitoring.show-laporan', $lap) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye mr-1"></i> Review
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center" style="color: var(--text-muted);">Belum ada laporan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{ $laporans->links() }}
    </div>
</div>
@endsection
