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
                        Anda dapat menambahkan tanggal WFH untuk satu hari atau rentang tanggal.
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
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}" placeholder="Contoh: WFH Reguler, Pandemi, dll">
                    </div>
                    <div class="form-group">
                        <label>Pegawai yang Melakukan WFH *</label>
                        <div class="border rounded p-2" style="max-height:280px;overflow-y:auto;background:#fff;">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="checkAllUsers">
                                <label class="custom-control-label" for="checkAllUsers"><strong>Pilih Semua</strong></label>
                            </div>
                            <hr class="my-2">
                            @foreach($users as $user)
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="custom-control-input user-check" id="user-{{ $user->id }}" {{ in_array($user->id, old('user_ids', [])) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="user-{{ $user->id }}">
                                        {{ $user->name }}
                                        <small class="text-muted">({{ $user->nip }} - {{ ucfirst($user->role) }})</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('user_ids')<small class="text-danger">{{ $message }}</small>@enderror
                        @error('user_ids.*')<small class="text-danger d-block">{{ $message }}</small>@enderror
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

@section('scripts')
<script>
$('#checkAllUsers').on('change', function () {
    $('.user-check').prop('checked', this.checked);
});
</script>
@endsection
