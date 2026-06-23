@extends('layouts.admin')
@section('title', 'Tambah Tanggal WFH')
@section('page-title', 'Tambah Tanggal WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.wfh-dates.index') }}">Tanggal WFH</a></li>
    <li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-plus mr-2"></i>Tambah Tanggal WFH</h3>
            </div>
            <form action="{{ route('admin.wfh-dates.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        Secara default sistem membuat tanggal WFH setiap hari Jumat dalam rentang yang dipilih. Pegawai akan mendaftar sendiri dan sistem memilih peserta sesuai kuota.
                    </div>
                    <div class="form-group">
                        <label>Tanggal Mulai *</label>
                        <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai') }}" required>
                        @error('tanggal_mulai')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai <small>(opsional, untuk rentang)</small></label>
                        <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai') }}">
                        @error('tanggal_selesai')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Mode Pembuatan Tanggal *</label>
                        <select name="mode" class="form-control @error('mode') is-invalid @enderror" required>
                            <option value="friday_range" {{ old('mode', 'friday_range') === 'friday_range' ? 'selected' : '' }}>Setiap Jumat dalam rentang tanggal</option>
                            <option value="all_range" {{ old('mode') === 'all_range' ? 'selected' : '' }}>Semua tanggal dalam rentang (untuk tambahan hari lain)</option>
                        </select>
                        @error('mode')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}" placeholder="Contoh: WFH Reguler, Pandemi, dll">
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.wfh-dates.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
