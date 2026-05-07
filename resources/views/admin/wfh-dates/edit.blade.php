@extends('layouts.admin')
@section('title', 'Edit Tanggal WFH')
@section('page-title', 'Edit Tanggal WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.wfh-dates.index') }}">Tanggal WFH</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Edit Tanggal WFH</h3>
            </div>
            <form action="{{ route('admin.wfh-dates.update', $wfhDate) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label>Tanggal *</label>
                        <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', $wfhDate->tanggal->format('Y-m-d')) }}" required>
                        @error('tanggal')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" value="{{ old('keterangan', $wfhDate->keterangan) }}" placeholder="Contoh: WFH Reguler, Pandemi, dll">
                        @error('keterangan')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="isActive" {{ old('is_active', $wfhDate->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="isActive">Tanggal WFH aktif</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Pegawai yang Melakukan WFH *</label>
                        @php
                            $selectedUserIds = collect(old('user_ids', $wfhDate->users->pluck('id')->all()))->map(function ($id) {
                                return (int) $id;
                            })->all();
                        @endphp
                        <div class="border rounded p-2" style="max-height:280px;overflow-y:auto;background:#fff;">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="checkAllUsers">
                                <label class="custom-control-label" for="checkAllUsers"><strong>Pilih Semua</strong></label>
                            </div>
                            <hr class="my-2">
                            @foreach($users as $user)
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="custom-control-input user-check" id="user-{{ $user->id }}" {{ in_array($user->id, $selectedUserIds) ? 'checked' : '' }}>
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
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function syncCheckAllUsers() {
    var total = $('.user-check').length;
    var checked = $('.user-check:checked').length;
    $('#checkAllUsers').prop('checked', total > 0 && total === checked);
}

$('#checkAllUsers').on('change', function () {
    $('.user-check').prop('checked', this.checked);
});

$('.user-check').on('change', syncCheckAllUsers);
syncCheckAllUsers();
</script>
@endsection
