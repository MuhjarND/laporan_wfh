@extends('layouts.admin')
@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Kelola User</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-edit mr-2"></i>Edit User: {{ $user->name }}</h3>
            </div>
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Lengkap *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIP *</label>
                                <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $user->nip) }}" required>
                                @error('nip')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor WhatsApp</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 081234567890">
                                @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                <small class="form-text text-muted">Digunakan untuk notifikasi pengajuan dan hasil approval laporan.</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Role *</label>
                                <select name="role" class="form-control" id="roleSelect" required>
                                    <option value="pegawai" {{ old('role', $user->role) == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                                    <option value="atasan" {{ old('role', $user->role) == 'atasan' ? 'selected' : '' }}>Atasan</option>
                                    <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pangkat/Gol. Ruang</label>
                                <input type="text" name="pangkat" class="form-control" value="{{ old('pangkat', $user->pangkat) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jabatan</label>
                                <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan', $user->jabatan) }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Satuan Kerja</label>
                                <input type="text" name="satuan_kerja" class="form-control" value="{{ old('satuan_kerja', $user->satuan_kerja) }}">
                            </div>
                        </div>
                        <div class="col-md-6" id="atasanField">
                            <div class="form-group">
                                <label>Atasan Langsung</label>
                                <select name="atasan_id" class="form-control">
                                    <option value="">-- Pilih Atasan --</option>
                                    @foreach($atasanList as $at)
                                        <option value="{{ $at->id }}" {{ old('atasan_id', $user->atasan_id) == $at->id ? 'selected' : '' }}>{{ $at->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr style="border-color: var(--border-color);">
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Kosongkan password jika tidak ingin mengubah.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password Baru</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$('#roleSelect').on('change', function() {
    $('#atasanField').toggle($(this).val() === 'pegawai' || $(this).val() === 'atasan');
}).trigger('change');
</script>
@endsection
