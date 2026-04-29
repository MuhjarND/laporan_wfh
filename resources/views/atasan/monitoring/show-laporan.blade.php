@extends('layouts.admin')
@section('title', 'Review Laporan')
@section('page-title', 'Review Laporan - ' . $laporan->user->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('atasan.monitoring.index') }}">Monitoring</a></li>
    <li class="breadcrumb-item active">Review</li>
@endsection

@section('styles')
<style>
    .signature-box {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        padding: 10px;
    }
    .signature-canvas {
        width: 100%;
        height: 150px;
        display: block;
        border: 1px dashed #9ca3af;
        border-radius: 6px;
        background: #fff;
        touch-action: none;
    }
    .review-card-tools {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-left: auto;
    }
    .rich-content p,
    .rich-content ul,
    .rich-content ol,
    .rich-content blockquote {
        margin-bottom: .35rem;
    }
    .rich-content :last-child {
        margin-bottom: 0;
    }
    @media (max-width: 576px) {
        .review-card-tools {
            float: none;
            width: 100%;
            margin-top: 10px;
            flex-direction: column;
        }
        .review-card-tools .btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user mr-2"></i>Info Pegawai</h3></div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td style="color: var(--text-muted);">Nama</td><td>{{ $laporan->user->name }}</td></tr>
                    <tr><td style="color: var(--text-muted);">NIP</td><td>{{ $laporan->user->nip }}</td></tr>
                    <tr><td style="color: var(--text-muted);">Pangkat</td><td>{{ $laporan->user->pangkat }}</td></tr>
                    <tr><td style="color: var(--text-muted);">Jabatan</td><td>{{ $laporan->user->jabatan }}</td></tr>
                    <tr><td style="color: var(--text-muted);">Periode</td><td style="color: var(--primary);"><strong>{{ $laporan->periode }}</strong></td></tr>
                    <tr><td style="color: var(--text-muted);">Status</td><td>{!! $laporan->status_badge !!}</td></tr>
                </table>

                @if($laporan->catatan_atasan && $laporan->status !== 'submitted')
                    <div class="mt-3 alert {{ $laporan->status == 'rejected' ? 'alert-danger' : 'alert-info' }}">
                        <strong>Catatan:</strong><br>{{ $laporan->catatan_atasan }}
                    </div>
                @endif

            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Daftar Kegiatan ({{ $laporan->kegiatan->count() }})</h3>
                <div class="review-card-tools">
                    @if($laporan->kegiatan->count() > 0)
                        <a href="#" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#previewModal">
                            <i class="fas fa-eye mr-1"></i> Preview
                        </a>
                    @endif
                    @if($laporan->status === 'submitted')
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#actionModal">
                            <i class="fas fa-gavel mr-1"></i> Tindaklanjuti
                        </button>
                    @elseif($laporan->status === 'approved')
                        <a href="{{ route('atasan.monitoring.pdf', $laporan) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-file-pdf mr-1"></i> Download PDF
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>No</th><th>Tanggal</th><th>Kegiatan</th><th>Capaian</th><th>Tempat WFH</th></tr>
                        </thead>
                        <tbody>
                            @forelse($laporan->kegiatan as $i => $keg)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $keg->tanggal->format('d/m/Y') }}</td>
                                <td><div class="rich-content">{!! $keg->kegiatan !!}</div></td>
                                <td>
                                    <div class="rich-content">{!! $keg->capaian !!}</div>
                                    @if($keg->all_evidens->isNotEmpty())
                                        <div class="mt-1">
                                            @foreach($keg->all_evidens as $eviden)
                                                <a href="{{ $eviden->preview_link }}" target="_blank" rel="noopener" class="d-block" style="color:var(--primary);font-weight:600;">
                                                    <i class="fas fa-link mr-1"></i>{{ Str::limit($eviden->original_name ?? 'Lihat Eviden', 32) }}
                                                </a>
                                            @endforeach
                                        </div>
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

<!-- Action Modal -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                <h5 class="modal-title" style="color:#0f4c3a;font-weight:700;">
                    <i class="fas fa-gavel mr-2"></i>Tindaklanjuti Laporan
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                @if($laporan->status === 'submitted')
                    <form action="{{ route('atasan.monitoring.approve', $laporan) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="form-group">
                            <label>Catatan (opsional)</label>
                            <textarea name="catatan_atasan" class="form-control" rows="2" placeholder="Catatan untuk pegawai..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Tanda Tangan Atasan *</label>
                            <div class="signature-box">
                                <canvas class="signature-canvas" id="signatureCanvas"></canvas>
                                <input type="hidden" name="signature_atasan" id="signatureInput" required>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="clearSignature">
                                    <i class="fas fa-eraser mr-1"></i> Hapus
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-check mr-1"></i> Setujui
                        </button>
                    </form>
                    <hr>
                    <form action="{{ route('atasan.monitoring.reject', $laporan) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Alasan Penolakan *</label>
                            <textarea name="catatan_atasan" class="form-control" rows="2" required placeholder="Alasan penolakan..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-times mr-1"></i> Tolak
                        </button>
                    </form>
                @endif
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
                    <i class="fas fa-file-alt mr-2"></i>Preview Laporan - {{ $laporan->user->name }} ({{ $laporan->periode }})
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0" style="background:#e5e7eb;">
                <iframe id="previewFrame" src="{{ route('atasan.monitoring.preview', $laporan) }}"
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
                <a href="{{ route('atasan.monitoring.pdf', $laporan) }}" class="btn btn-success">
                    <i class="fas fa-file-pdf mr-1"></i> Download PDF
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var canvas = document.getElementById('signatureCanvas');
    if (!canvas) return;
    var input = document.getElementById('signatureInput');
    var clearBtn = document.getElementById('clearSignature');
    var ctx = canvas.getContext('2d');
    var drawing = false;
    var hasDrawn = false;

    function resizeCanvas() {
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        var rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#111827';
    }

    function point(event) {
        var rect = canvas.getBoundingClientRect();
        var touch = event.touches ? event.touches[0] : event;
        return { x: touch.clientX - rect.left, y: touch.clientY - rect.top };
    }

    function start(event) {
        event.preventDefault();
        drawing = true;
        var p = point(event);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
    }

    function move(event) {
        if (!drawing) return;
        event.preventDefault();
        var p = point(event);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        hasDrawn = true;
        input.value = canvas.toDataURL('image/png');
    }

    function stop() {
        drawing = false;
        if (hasDrawn) input.value = canvas.toDataURL('image/png');
    }

    $('#actionModal').on('shown.bs.modal', function () {
        resizeCanvas();
    });
    window.addEventListener('resize', resizeCanvas);
    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    canvas.addEventListener('mouseup', stop);
    canvas.addEventListener('mouseleave', stop);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    canvas.addEventListener('touchend', stop);
    clearBtn.addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        input.value = '';
        hasDrawn = false;
    });

    @if(request('tindaklanjuti') === '1' && $laporan->status === 'submitted')
    $('#actionModal').modal('show');
    @endif
})();
</script>
@endsection
