@extends('layouts.admin')
@section('title', 'Buat Laporan WFH')
@section('page-title', 'Buat Laporan WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('pegawai.laporan.index') }}">Laporan WFH</a></li>
    <li class="breadcrumb-item active">Buat</li>
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
                                <option value="{{ $num }}" {{ old('bulan', request('bulan', now()->month)) == $num ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                        @error('bulan')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Tahun *</label>
                        <input type="number" name="tahun" class="form-control @error('tahun') is-invalid @enderror" value="{{ old('tahun', request('tahun', now()->year)) }}" min="2020" max="2030" required>
                        @error('tahun')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    @if(auth()->user()->signature)
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-signature mr-1"></i>
                            Laporan ini akan memakai tanda tangan yang tersimpan di menu Tanda Tangan Saya.
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Anda belum menyimpan tanda tangan. Silakan isi melalui menu
                            <a href="{{ route('signature.edit') }}" class="alert-link">Tanda Tangan Saya</a>.
                        </div>
                    @endif
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
