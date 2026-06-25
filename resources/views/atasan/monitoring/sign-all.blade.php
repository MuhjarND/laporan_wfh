@extends('layouts.admin')
@section('title', 'Tanda Tangani Semua')
@section('page-title', 'Tanda Tangani Semua Laporan')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('atasan.monitoring.pending') }}">Pending</a></li>
    <li class="breadcrumb-item active">Tanda Tangani Semua</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-signature mr-2"></i>Tanda Tangani Semua Laporan Pending</h3>
            </div>
            <form action="{{ route('atasan.monitoring.sign-all.submit') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        Sistem akan menggunakan tanda tangan yang tersimpan di menu Tanda Tangan Saya untuk menyetujui {{ $totalPending }} laporan pending dari seluruh pegawai yang dinilai.
                    </div>
                    <div class="form-group">
                        <label>Catatan (opsional)</label>
                        <textarea name="catatan_atasan" class="form-control" rows="2" placeholder="Catatan yang akan diterapkan ke semua laporan..."></textarea>
                    </div>
                    @unless(auth()->user()->signature)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Anda belum menyimpan tanda tangan. Silakan isi melalui menu
                            <a href="{{ route('signature.edit') }}" class="alert-link">Tanda Tangan Saya</a>.
                        </div>
                    @endunless
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('atasan.monitoring.pending') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success" {{ $totalPending < 1 ? 'disabled' : '' }}>
                        <i class="fas fa-check mr-1"></i> Setujui Semua
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
