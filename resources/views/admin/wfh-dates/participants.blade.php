@extends('layouts.admin')
@section('title', 'Tambah Peserta WFH')
@section('page-title', 'Tambah Peserta WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.wfh-dates.index') }}">Tanggal WFH</a></li>
    <li class="breadcrumb-item active">Tambah Peserta</li>
@endsection

@section('content')
@php
    $hariNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
@endphp

<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Informasi Tanggal WFH</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <th style="width:130px;">Tanggal</th>
                        <td>{{ $wfhDate->tanggal->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Hari</th>
                        <td>{{ $hariNames[$wfhDate->tanggal->dayOfWeek] }}</td>
                    </tr>
                    <tr>
                        <th>Keterangan</th>
                        <td>{{ $wfhDate->keterangan ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Peserta Saat Ini</th>
                        <td>{{ $wfhDate->users->count() }} pegawai</td>
                    </tr>
                    <tr>
                        <th>Surat</th>
                        <td>
                            @if($wfhDate->letter_status === 'approved')
                                <span class="badge badge-success">Terbit</span>
                            @elseif($wfhDate->letter_status === 'pending_approval')
                                <span class="badge badge-warning">Menunggu TTD Ketua</span>
                            @else
                                <span class="badge badge-secondary">Belum</span>
                            @endif
                        </td>
                    </tr>
                </table>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    Admin dapat menambahkan peserta meskipun tanggal WFH sudah lewat. Pegawai yang ditambahkan akan langsung berhak mengisi kegiatan pada tanggal ini.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users mr-2"></i>Peserta Saat Ini</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nama / NIP</th>
                                <th>Jabatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wfhDate->users as $user)
                                <tr>
                                    <td>
                                        <strong>{{ $user->name }}</strong><br>
                                        <small class="text-muted">{{ $user->nip ?: '-' }}</small>
                                    </td>
                                    <td>{{ $user->jabatan ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-4">Belum ada peserta.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Tambah Pegawai</h3>
            </div>
            <form action="{{ route('admin.wfh-dates.participants.store', $wfhDate) }}" method="POST" onsubmit="return confirm('Tambahkan pegawai yang dipilih sebagai peserta WFH?');">
                @csrf
                <div class="card-body">
                    @error('user_ids')<div class="alert alert-danger">{{ $message }}</div>@enderror
                    @error('user_ids.*')<div class="alert alert-danger">{{ $message }}</div>@enderror

                    @if($candidateUsers->isEmpty())
                        <div class="text-center text-muted py-4">
                            Semua pegawai aktif sudah menjadi peserta pada tanggal ini.
                        </div>
                    @else
                        <div class="table-responsive border rounded" style="max-height:480px;overflow-y:auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:54px;">Pilih</th>
                                        <th>Nama / NIP</th>
                                        <th>Role</th>
                                        <th>Jabatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($candidateUsers as $user)
                                        <tr>
                                            <td class="align-middle">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="custom-control-input" id="user{{ $user->id }}" {{ in_array((string) $user->id, array_map('strval', (array) old('user_ids', [])), true) ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="user{{ $user->id }}"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $user->name }}</strong><br>
                                                <small class="text-muted">{{ $user->nip ?: '-' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge {{ $user->role === 'atasan' ? 'badge-primary' : 'badge-success' }}">
                                                    {{ $user->role === 'atasan' ? 'Atasan' : 'Pegawai' }}
                                                </span>
                                            </td>
                                            <td>{{ $user->jabatan ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.wfh-dates.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary" {{ $candidateUsers->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-save mr-1"></i> Tambahkan Peserta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
