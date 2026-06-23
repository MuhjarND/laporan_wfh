@extends('layouts.admin')
@section('title', 'Approval Surat Tugas WFH')
@section('page-title', 'Approval Surat Tugas WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Approval Surat Tugas WFH</li>
@endsection

@section('styles')
<style>
    .approval-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(320px, .75fr);
        gap: 16px;
    }
    .letter-frame {
        width: 100%;
        height: 72vh;
        min-height: 520px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #fff;
    }
    .signature-box {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #fff;
        padding: 8px;
    }
    .signature-canvas {
        width: 100%;
        height: 180px;
        border: 1px dashed #94a3b8;
        border-radius: 4px;
        touch-action: none;
        display: block;
    }
    @media (max-width: 992px) {
        .approval-grid {
            grid-template-columns: 1fr;
        }
        .letter-frame {
            height: 60vh;
            min-height: 420px;
        }
    }
    @media (max-width: 576px) {
        .letter-frame {
            display: none;
        }
        .mobile-pdf-note {
            display: block !important;
        }
    }
</style>
@endsection

@section('content')
<div class="approval-grid">
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-file-pdf mr-2"></i>Preview Surat</h3>
            <div class="card-tools ml-auto">
                <a href="{{ route('wfh-letter-approvals.pdf', $wfhDate) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-external-link-alt mr-1"></i> Buka PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info d-none mobile-pdf-note">
                <i class="fas fa-info-circle mr-1"></i>
                Untuk tampilan mobile, gunakan tombol <strong>Buka PDF</strong> agar preview surat lebih mudah dibaca.
            </div>
            <iframe class="letter-frame" src="{{ route('wfh-letter-approvals.pdf', $wfhDate) }}"></iframe>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Info Surat</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Nomor</dt>
                    <dd class="col-7">{{ $wfhDate->letter_number ?: '-' }}</dd>
                    <dt class="col-5">Tanggal WFH</dt>
                    <dd class="col-7">{{ $wfhDate->tanggal->format('d/m/Y') }}</dd>
                    <dt class="col-5">Status</dt>
                    <dd class="col-7">
                        @if($wfhDate->letter_status === 'approved')
                            <span class="badge badge-success">Sudah Ditandatangani</span>
                        @elseif($wfhDate->letter_status === 'pending_approval')
                            <span class="badge badge-warning">Menunggu TTD</span>
                        @else
                            <span class="badge badge-secondary">Draft</span>
                        @endif
                    </dd>
                    <dt class="col-5">Approval</dt>
                    <dd class="col-7">{{ $approver->name ?? '-' }}</dd>
                    @if($wfhDate->letter_approved_at)
                        <dt class="col-5">Tanggal TTD</dt>
                        <dd class="col-7">{{ $wfhDate->letter_approved_at->format('d/m/Y H:i') }}</dd>
                    @endif
                </dl>
            </div>
            <div class="card-footer text-right">
                @if($wfhDate->letter_status === 'approved')
                    <button type="button" class="btn btn-success" disabled>
                        <i class="fas fa-check mr-1"></i> Sudah Ditandatangani
                    </button>
                @else
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#signatureModal">
                        <i class="fas fa-signature mr-1"></i> Tanda Tangani
                    </button>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users mr-2"></i>Daftar Pendaftar</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $registration)
                                <tr>
                                    <td>
                                        <strong>{{ $registration->user->name ?? '-' }}</strong><br>
                                        <small class="text-muted">{{ $registration->user->nip ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($registration->status === 'selected')
                                            <span class="badge badge-success">Terpilih</span>
                                        @elseif($registration->status === 'not_selected')
                                            <span class="badge badge-secondary">Tidak Terpilih</span>
                                            @if($registration->not_selected_reason)
                                                <small class="text-muted d-block mt-1">{{ $registration->not_selected_reason }}</small>
                                            @endif
                                        @else
                                            <span class="badge badge-info">Terdaftar</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-3">Belum ada pendaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="signatureModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('wfh-letter-approvals.sign', $wfhDate) }}" method="POST" class="modal-content" id="signatureForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-signature mr-1"></i> Tanda Tangan Surat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Bubuhkan tanda tangan pada area berikut.</p>
                <div class="signature-box">
                    <canvas class="signature-canvas" id="signatureCanvas"></canvas>
                    <input type="hidden" name="letter_signature" id="signatureInput" required>
                </div>
                @error('letter_signature')<small class="text-danger">{{ $message }}</small>@enderror
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="clearSignature">
                    <i class="fas fa-eraser mr-1"></i> Hapus
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check mr-1"></i> Tanda Tangani & Kirim Notifikasi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var canvas = document.getElementById('signatureCanvas');
    var input = document.getElementById('signatureInput');
    var clearButton = document.getElementById('clearSignature');
    var form = document.getElementById('signatureForm');
    if (!canvas || !input || !form) return;

    var ctx = canvas.getContext('2d');
    var drawing = false;
    var hasDrawing = false;

    function resizeCanvas() {
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        var rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#1e3a8a';
    }

    function point(event) {
        var rect = canvas.getBoundingClientRect();
        var source = event.touches && event.touches.length ? event.touches[0] : event;
        return {
            x: source.clientX - rect.left,
            y: source.clientY - rect.top
        };
    }

    function start(event) {
        event.preventDefault();
        drawing = true;
        hasDrawing = true;
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
    }

    function stop() {
        drawing = false;
        input.value = canvas.toDataURL('image/png');
    }

    $('#signatureModal').on('shown.bs.modal', function () {
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

    clearButton.addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        input.value = '';
        hasDrawing = false;
    });

    form.addEventListener('submit', function (event) {
        if (!hasDrawing || !input.value) {
            event.preventDefault();
            alert('Tanda tangan wajib diisi.');
        }
    });
})();
</script>
@endsection
