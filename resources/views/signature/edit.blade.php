@extends('layouts.admin')
@section('title', 'Tanda Tangan')
@section('page-title', 'Tanda Tangan Saya')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Tanda Tangan</li>
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
        height: 220px;
        display: block;
        border: 1px dashed #9ca3af;
        border-radius: 6px;
        background: #fff;
        touch-action: none;
    }
    .signature-preview {
        min-height: 160px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #f9fafb;
    }
    .signature-preview img {
        max-width: 100%;
        max-height: 150px;
        object-fit: contain;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-signature mr-2"></i>Buat / Upload Tanda Tangan</h3>
            </div>
            <form action="{{ route('signature.update') }}" method="POST" enctype="multipart/form-data" id="signatureForm">
                @csrf
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        Tanda tangan ini akan dipakai otomatis saat membuat laporan dan saat menyetujui laporan.
                    </div>

                    <div class="form-group">
                        <label>Buat Tanda Tangan</label>
                        <div class="signature-box">
                            <canvas class="signature-canvas" id="signatureCanvas"></canvas>
                            <input type="hidden" name="signature_canvas" id="signatureInput" value="{{ old('signature_canvas') }}">
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="clearSignature">
                                <i class="fas fa-eraser mr-1"></i> Hapus Canvas
                            </button>
                        </div>
                        @error('signature_canvas')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>Atau Upload File Tanda Tangan</label>
                        <input type="file" name="signature_file" class="form-control @error('signature_file') is-invalid @enderror" accept="image/png,image/jpeg">
                        <small class="text-muted">Format: JPG atau PNG. Maksimal 2 MB.</small>
                        @error('signature_file')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Tanda Tangan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-eye mr-2"></i>TTD Tersimpan</h3>
            </div>
            <div class="card-body">
                <div class="signature-preview">
                    @if(auth()->user()->signature)
                        <img src="{{ auth()->user()->signature }}" alt="Tanda Tangan">
                    @else
                        <span class="text-muted">Belum ada tanda tangan.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var canvas = document.getElementById('signatureCanvas');
    var input = document.getElementById('signatureInput');
    var clearBtn = document.getElementById('clearSignature');
    var ctx = canvas.getContext('2d');
    var drawing = false;
    var hasDrawn = false;

    function resizeCanvas() {
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        var rect = canvas.getBoundingClientRect();
        var current = input.value;
        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.lineWidth = 4.2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#111827';
        if (current) input.value = current;
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

    resizeCanvas();
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
})();
</script>
@endsection
