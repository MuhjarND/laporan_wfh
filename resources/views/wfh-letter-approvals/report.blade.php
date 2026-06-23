@extends('layouts.admin')
@section('title', 'Detail Laporan WFH')
@section('page-title', 'Detail Laporan - ' . $laporan->periode)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('wfh-letter-approvals.monitoring') }}">Monitoring Laporan</a></li>
    <li class="breadcrumb-item active">Detail</li>
@endsection

@section('styles')
<style>
    .rich-content p,
    .rich-content ul,
    .rich-content ol,
    .rich-content blockquote {
        margin-bottom: .35rem;
    }
    .rich-content :last-child {
        margin-bottom: 0;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Info Laporan</h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td style="color: var(--text-muted);">Nama</td><td>{{ $laporan->user->name }}</td></tr>
                    <tr><td style="color: var(--text-muted);">NIP</td><td>{{ $laporan->user->nip }}</td></tr>
                    <tr><td style="color: var(--text-muted);">Periode</td><td style="color: var(--primary);">{{ $laporan->periode }}</td></tr>
                    <tr><td style="color: var(--text-muted);">Status</td><td>{!! $laporan->status_badge !!}</td></tr>
                    @if($laporan->approved_at)
                        <tr><td style="color: var(--text-muted);">Disetujui</td><td>{{ $laporan->approved_at->format('d/m/Y H:i') }}</td></tr>
                    @endif
                    @if($laporan->approver)
                        <tr><td style="color: var(--text-muted);">Oleh</td><td>{{ $laporan->approver->name }}</td></tr>
                    @endif
                </table>

                @if($laporan->catatan_atasan)
                    <div class="alert {{ $laporan->status == 'rejected' ? 'alert-danger' : 'alert-info' }} mt-3" style="font-size: 0.85rem;">
                        <strong>Catatan Atasan:</strong><br>{{ $laporan->catatan_atasan }}
                    </div>
                @endif

                <a href="{{ route('wfh-letter-approvals.monitoring') }}" class="btn btn-outline-secondary btn-block mt-3">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Daftar Kegiatan ({{ $laporan->kegiatan->count() }})</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Kegiatan</th>
                                <th>Capaian</th>
                                <th>Tempat WFH</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laporan->kegiatan as $index => $kegiatan)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $kegiatan->tanggal->format('d/m/Y') }}</td>
                                    <td><div class="rich-content">{!! $kegiatan->kegiatan !!}</div></td>
                                    <td>
                                        <div class="rich-content">{!! $kegiatan->capaian !!}</div>
                                        @if($kegiatan->all_evidens->isNotEmpty())
                                            <div class="mt-1">
                                                @foreach($kegiatan->all_evidens as $eviden)
                                                    <a href="{{ $eviden->preview_link }}" target="_blank" rel="noopener" class="d-block" style="color:var(--primary);font-weight:600;">
                                                        <i class="fas fa-link mr-1"></i>{{ Str::limit($eviden->original_name ?? 'Lihat Eviden', 32) }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $kegiatan->tempat_wfh }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">Belum ada kegiatan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
