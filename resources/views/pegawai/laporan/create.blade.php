@extends('layouts.admin')
@section('title', 'Buat Laporan WFH')
@section('page-title', 'Buat Laporan WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('pegawai.laporan.index') }}">Laporan WFH</a></li>
    <li class="breadcrumb-item active">Buat</li>
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
        height: 180px;
        display: block;
        border: 1px dashed #9ca3af;
        border-radius: 6px;
        background: #fff;
        touch-action: none;
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>Buat Laporan WFH Baru</h3>
            </div>
            <form action="{{ route('pegawai.laporan.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Bulan *</label>
                        <select name="bulan" class="form-control @error('bulan') is-invalid @enderror" required>
                            <option value="">Pilih Bulan</option>
                            @foreach($bulanList as $num => $nama)
                                <option value="{{ $num }}" {{ old('bulan', now()->month) == $num ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                        @error('bulan')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Tahun *</label>
                        <input type="number" name="tahun" class="form-control @error('tahun') is-invalid @enderror" value="{{ old('tahun', now()->year) }}" min="2020" max="2030" required>
                        @error('tahun')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Tanda Tangan Pegawai *</label>
                        <div class="signature-box">
                            <canvas class="signature-canvas" id="signatureCanvas"></canvas>
                            <input type="hidden" name="signature_pegawai" id="signatureInput" value="{{ old('signature_pegawai') }}" required>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="clearSignature">
                                <i class="fas fa-eraser mr-1"></i> Hapus
                            </button>
                        </div>
                        @error('signature_pegawai')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('pegawai.laporan.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Buat Laporan</button>
                </div>
            </form>
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
