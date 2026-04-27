@extends('layouts.admin')
@section('title', 'Detail Laporan WFH')
@section('page-title', 'Detail Laporan - ' . $laporan->periode)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('pegawai.laporan.index') }}">Laporan WFH</a></li>
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

                <div class="mt-3">
                    @if(in_array($laporan->status, ['draft', 'rejected']))
                        <a href="{{ route('pegawai.laporan.edit', $laporan) }}" class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-edit mr-1"></i> Edit Laporan
                        </a>
                    @endif
                    @if($laporan->kegiatan->count() > 0)
                        <a href="#" class="btn btn-outline-secondary btn-block mb-2" data-toggle="modal" data-target="#previewModal">
                            <i class="fas fa-eye mr-1"></i> Preview Laporan
                        </a>
                    @endif
                    @if($laporan->status === 'approved')
                        <a href="{{ route('pegawai.laporan.pdf', $laporan) }}" class="btn btn-success btn-block mb-2">
                            <i class="fas fa-file-pdf mr-1"></i> Download PDF
                        </a>
                    @endif
                    <a href="{{ route('pegawai.laporan.index') }}" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                </div>
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
                            @forelse($laporan->kegiatan as $i => $keg)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $keg->tanggal->format('d/m/Y') }}</td>
                                <td><div class="rich-content">{!! $keg->kegiatan !!}</div></td>
                                <td>
                                    <div class="rich-content">{!! $keg->capaian !!}</div>
                                    @if($keg->eviden_preview_link)
                                        <br><a href="{{ $keg->eviden_preview_link }}" target="_blank" rel="noopener" style="color:var(--primary);font-weight:600;"><i class="fas fa-link mr-1"></i>Lihat Eviden</a>
                                    @endif
                                </td>
                                <td>{{ $keg->tempat_wfh }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada kegiatan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl" style="max-width:900px;">
        <div class="modal-content">
            <div class="modal-header" style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                <h5 class="modal-title" style="color:#0f4c3a;font-weight:700;">
                    <i class="fas fa-file-alt mr-2"></i>Preview Laporan - {{ $laporan->periode }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0" style="background:#e5e7eb;">
                <iframe id="previewFrame" src="{{ route('pegawai.laporan.preview', $laporan) }}"
                    style="width:100%;height:70vh;border:none;display:block;background:#fff;margin:0 auto;">
                </iframe>
            </div>
            <div class="modal-footer" style="background:#f9fafb;border-top:1px solid #e5e7eb;">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Tutup
                </button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('previewFrame').contentWindow.print();">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
                @if($laporan->status === 'approved')
                <a href="{{ route('pegawai.laporan.pdf', $laporan) }}" class="btn btn-success">
                    <i class="fas fa-file-pdf mr-1"></i> Download PDF
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
