@extends('layouts.admin')
@section('title', 'Edit Tanggal WFH')
@section('page-title', 'Edit Tanggal WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.wfh-dates.index') }}">Tanggal WFH</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
@php
    $selectedUserIds = old('selected_user_ids');
    if ($selectedUserIds === null) {
        $selectedUserIds = $wfhDate->users->pluck('id')->map(function ($id) {
            return (string) $id;
        })->all();
    } else {
        $selectedUserIds = array_map('strval', (array) $selectedUserIds);
    }
    $registrations = $wfhDate->registrations->sortBy(function ($registration) {
        return sprintf('%s-%s', $registration->status === 'selected' ? '0' : '1', strtolower(optional($registration->user)->name));
    });
@endphp
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
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-1"></i>
                        Pegawai mendaftar sendiri dan sistem melakukan seleksi awal. Sebelum surat diajukan ke ketua, admin dapat meninjau dan memilih peserta final dari daftar pendaftar.
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

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title"><i class="fas fa-file-signature mr-2"></i>Surat Tugas WFH</h3>
                <div class="card-tools">
                    @if($wfhDate->letter_number)
                        <a href="{{ route('admin.wfh-dates.letter', $wfhDate) }}" target="_blank" class="btn btn-sm btn-success">
                            <i class="fas fa-file-pdf mr-1"></i> Preview Surat
                        </a>
                    @endif
                </div>
            </div>
            <form action="{{ route('admin.wfh-dates.publish-letter', $wfhDate) }}" method="POST" onsubmit="return confirm('Ajukan surat tugas ke pejabat approval untuk ditandatangani?');">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group mb-md-0">
                                <label>Nomor Surat *</label>
                                <input type="text" name="letter_number" class="form-control @error('letter_number') is-invalid @enderror" value="{{ old('letter_number', $wfhDate->letter_number) }}" placeholder="Contoh: W25-A/123/KP.05/IV/2026" required>
                                @error('letter_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block" {{ ($wfhDate->registrations->isEmpty() || $wfhDate->letter_status === 'approved') ? 'disabled' : '' }}>
                                <i class="fas fa-paper-plane mr-1"></i> Ajukan Approval
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge badge-info mr-1">{{ $wfhDate->users->count() }} pegawai terpilih</span>
                        <span class="badge badge-light border mr-1">Kuota: {{ $quota }} pegawai</span>
                        @if($wfhDate->letter_status === 'approved')
                            <span class="badge badge-success mr-1">Sudah TTD: {{ $wfhDate->letter_approved_at ? $wfhDate->letter_approved_at->format('d/m/Y H:i') : '-' }}</span>
                        @elseif($wfhDate->letter_status === 'pending_approval')
                            <span class="badge badge-warning mr-1">Menunggu TTD Ketua</span>
                        @else
                            <span class="badge badge-secondary mr-1">Draft</span>
                        @endif
                        @if($wfhDate->letter_notified_at)
                            <span class="badge badge-secondary">WA: {{ $wfhDate->letter_notified_at->format('d/m/Y H:i') }}</span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <label class="mb-2">Pilih Peserta Surat Tugas *</label>
                        @error('selected_user_ids')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                        @error('selected_user_ids.*')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:56px;">Pilih</th>
                                        <th>Nama / NIP</th>
                                        <th>Jabatan</th>
                                        <th>Status Sistem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($registrations as $registration)
                                        @php
                                            $user = $registration->user;
                                            $checked = $user && in_array((string) $user->id, $selectedUserIds, true);
                                            $selectionDisabled = $wfhDate->letter_status === 'approved' || !$user;
                                        @endphp
                                        <tr>
                                            <td class="align-middle">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                        name="selected_user_ids[]"
                                                        value="{{ $user ? $user->id : '' }}"
                                                        class="custom-control-input"
                                                        id="selectedUser{{ $registration->id }}"
                                                        {{ $checked ? 'checked' : '' }}
                                                        {{ $selectionDisabled ? 'disabled' : '' }}>
                                                    <label class="custom-control-label" for="selectedUser{{ $registration->id }}"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $user->name ?? '-' }}</strong><br>
                                                <small class="text-muted">{{ $user->nip ?? '-' }}</small>
                                            </td>
                                            <td>{{ $user->jabatan ?? '-' }}</td>
                                            <td>
                                                @if($registration->status === 'selected')
                                                    <span class="badge badge-success">Terseleksi Sistem</span>
                                                @elseif($registration->status === 'not_selected')
                                                    <span class="badge badge-secondary">Cadangan</span>
                                                @else
                                                    <span class="badge badge-info">Terdaftar</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pegawai yang mendaftar.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Maksimal {{ $quota }} pegawai. Pegawai yang tidak dipilih akan mendapatkan status tidak terpilih setelah surat disetujui.
                        </small>
                    </div>

                    @if($wfhDate->registrations->isEmpty())
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Surat tugas dapat diterbitkan setelah ada pegawai yang mendaftar.
                        </div>
                    @else
                        <small class="text-muted d-block mt-3">
                            Setelah approval ditandatangani, sistem akan mengirim notifikasi ke seluruh pendaftar, baik yang terpilih maupun tidak terpilih.
                        </small>
                    @endif
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users mr-2"></i>Status Pendaftaran</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Waktu Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wfhDate->registrations->sortBy('created_at') as $registration)
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
                                    <td>{{ $registration->created_at ? $registration->created_at->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">Belum ada pegawai yang mendaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
