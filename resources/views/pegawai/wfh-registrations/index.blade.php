@extends('layouts.admin')
@section('title', 'Pendaftaran WFH')
@section('page-title', 'Pendaftaran WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Pendaftaran WFH</li>
@endsection

@section('styles')
<style>
    .wfh-mobile-list { display: none; }
    .wfh-date-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        overflow: hidden;
    }
    .wfh-date-card + .wfh-date-card { margin-top: 10px; }
    .wfh-date-card-header {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 12px;
        background: #f9fafb;
        border-bottom: 1px solid #eef2f7;
    }
    .wfh-date-title {
        color: var(--primary);
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .wfh-date-day {
        color: #6b7280;
        font-size: .78rem;
        font-weight: 700;
        margin-top: 2px;
    }
    .wfh-date-card-body {
        padding: 12px;
    }
    .wfh-date-note,
    .wfh-date-close {
        color: #6b7280;
        font-size: .78rem;
        line-height: 1.4;
    }
    .wfh-date-close { margin-top: 4px; }
    .wfh-date-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin: 10px 0;
    }
    .wfh-date-stat {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px;
        background: #fbfdff;
    }
    .wfh-date-stat span {
        display: block;
        color: #6b7280;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .wfh-date-stat strong {
        display: block;
        margin-top: 2px;
        color: #111827;
        font-size: .9rem;
    }
    .wfh-date-action {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding-top: 10px;
        border-top: 1px solid #eef2f7;
    }
    .wfh-date-action .btn,
    .wfh-date-action form {
        width: auto;
        margin: 0;
    }
    @media (max-width: 768px) {
        .wfh-registration-table { display: none; }
        .wfh-mobile-list { display: block; }
        .wfh-page-alert {
            font-size: .8rem;
            line-height: 1.45;
            margin-bottom: 12px;
        }
        .wfh-date-action {
            align-items: stretch;
            flex-direction: column;
        }
        .wfh-date-action .btn,
        .wfh-date-action form,
        .wfh-date-action form .btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Daftar Tanggal WFH</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info wfh-page-alert">
            <i class="fas fa-info-circle mr-1"></i>
            Kuota WFH setiap tanggal adalah {{ $quota }} pegawai. Sistem memprioritaskan pegawai yang belum WFH Jumat sebelumnya dan pegawai dengan total WFH lebih sedikit.
        </div>

        <div class="table-responsive wfh-registration-table">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Pendaftar</th>
                        <th>Terseleksi / Kuota</th>
                        <th>Status Saya</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hariNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']; @endphp
                    @forelse($wfhDates as $date)
                        @php
                            $myRegistration = $date->registrations->first();
                            $window = $registrationWindows[$date->id] ?? null;
                            $registrationOpen = $window ? $window['is_open'] : true;
                            $finalPublished = $date->letter_status === 'approved';
                            $canCancelRegistration = $myRegistration && $registrationOpen && !in_array($date->letter_status, ['pending_approval', 'approved'], true);
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $date->tanggal->format('d/m/Y') }}</strong>
                                @if($date->keterangan)<br><small class="text-muted">{{ $date->keterangan }}</small>@endif
                                @if($window)
                                    <br><small class="text-muted">Tutup daftar: {{ $window['closes_at']->format('d/m/Y H:i') }} WIT</small>
                                @endif
                            </td>
                            <td>{{ $hariNames[$date->tanggal->dayOfWeek] }}</td>
                            <td>{{ $date->registrations_count }} pegawai</td>
                            <td>{{ $date->users_count }} / {{ $quota }} pegawai</td>
                            <td>
                                @if(!$myRegistration)
                                    <span class="badge badge-secondary">Belum Daftar</span>
                                @elseif($myRegistration->status === 'selected' && $finalPublished)
                                    <span class="badge badge-success">Terpilih</span>
                                @elseif($myRegistration->status === 'not_selected' && $finalPublished)
                                    <span class="badge badge-secondary">Tidak Terpilih</span>
                                    @if($myRegistration->not_selected_reason)
                                        <small class="text-muted d-block mt-1">{{ $myRegistration->not_selected_reason }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-info">Menunggu Seleksi Sistem</span>
                                    <small class="text-muted d-block mt-1">Pendaftaran diterima dan akan diseleksi oleh sistem.</small>
                                @endif
                            </td>
                            <td>
                                @if(!$myRegistration)
                                    <form action="{{ route('pegawai.wfh-registrations.store', $date) }}" method="POST" onsubmit="return confirm('Daftar WFH pada tanggal ini?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary" {{ !$registrationOpen ? 'disabled' : '' }}>
                                            <i class="fas fa-check mr-1"></i> Daftar
                                        </button>
                                    </form>
                                    @if(!$registrationOpen && $window)
                                        <small class="text-muted d-block mt-1">{{ $window['closed_message'] }}</small>
                                    @endif
                                @elseif($myRegistration->status === 'selected' && $date->letter_status === 'approved')
                                    <a href="{{ route('pegawai.wfh-registrations.letter', $date) }}" target="_blank" class="btn btn-sm btn-success">
                                        <i class="fas fa-file-pdf mr-1"></i> Surat Tugas
                                    </a>
                                @elseif($myRegistration->status === 'selected' && $date->letter_status === 'pending_approval')
                                    <span class="text-muted">Surat menunggu TTD Ketua</span>
                                @else
                                    <span class="text-muted d-block mb-2">Menunggu seleksi sistem</span>
                                @endif
                                @if($canCancelRegistration)
                                    <form action="{{ route('pegawai.wfh-registrations.destroy', $date) }}" method="POST" onsubmit="return confirm('Batalkan pendaftaran WFH pada tanggal ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times mr-1"></i> Batalkan
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada tanggal WFH yang dibuka.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="wfh-mobile-list">
            @php $hariNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']; @endphp
            @forelse($wfhDates as $date)
                @php
                    $myRegistration = $date->registrations->first();
                    $window = $registrationWindows[$date->id] ?? null;
                    $registrationOpen = $window ? $window['is_open'] : true;
                    $finalPublished = $date->letter_status === 'approved';
                    $canCancelRegistration = $myRegistration && $registrationOpen && !in_array($date->letter_status, ['pending_approval', 'approved'], true);
                @endphp
                <div class="wfh-date-card">
                    <div class="wfh-date-card-header">
                        <div>
                            <div class="wfh-date-title">{{ $date->tanggal->format('d/m/Y') }}</div>
                            <div class="wfh-date-day">{{ $hariNames[$date->tanggal->dayOfWeek] }}</div>
                        </div>
                        <div class="text-right">
                            @if(!$myRegistration)
                                <span class="badge badge-secondary">Belum Daftar</span>
                            @elseif($myRegistration->status === 'selected' && $finalPublished)
                                <span class="badge badge-success">Terpilih</span>
                            @elseif($myRegistration->status === 'not_selected' && $finalPublished)
                                <span class="badge badge-secondary">Tidak Terpilih</span>
                            @else
                                <span class="badge badge-info">Menunggu Seleksi</span>
                            @endif
                        </div>
                    </div>
                    <div class="wfh-date-card-body">
                        @if($date->keterangan)
                            <div class="wfh-date-note">{{ $date->keterangan }}</div>
                        @endif
                        @if($window)
                            <div class="wfh-date-close"><i class="fas fa-clock mr-1"></i>Tutup daftar: {{ $window['closes_at']->format('d/m/Y H:i') }} WIT</div>
                        @endif

                        <div class="wfh-date-stats">
                            <div class="wfh-date-stat">
                                <span>Pendaftar</span>
                                <strong>{{ $date->registrations_count }} pegawai</strong>
                            </div>
                            <div class="wfh-date-stat">
                                <span>Terseleksi / Kuota</span>
                                <strong>{{ $date->users_count }} / {{ $quota }} pegawai</strong>
                            </div>
                        </div>

                        @if($myRegistration && $myRegistration->status === 'not_selected' && $finalPublished && $myRegistration->not_selected_reason)
                            <div class="alert alert-light border py-2 px-3 mb-2">
                                <small class="text-muted"><strong>Alasan:</strong> {{ $myRegistration->not_selected_reason }}</small>
                            </div>
                        @elseif($myRegistration && !$finalPublished)
                            <div class="alert alert-info py-2 px-3 mb-2">
                                <small>Pendaftaran diterima dan akan diseleksi oleh sistem terlebih dahulu.</small>
                            </div>
                        @endif

                        <div class="wfh-date-action">
                            @if(!$myRegistration)
                                <form action="{{ route('pegawai.wfh-registrations.store', $date) }}" method="POST" onsubmit="return confirm('Daftar WFH pada tanggal ini?');">
                                    @csrf
                                    <button type="submit" class="btn btn-primary" {{ !$registrationOpen ? 'disabled' : '' }}>
                                        <i class="fas fa-check mr-1"></i> Daftar WFH
                                    </button>
                                </form>
                                @if(!$registrationOpen && $window)
                                    <small class="text-muted">{{ $window['closed_message'] }}</small>
                                @endif
                            @elseif($myRegistration->status === 'selected' && $date->letter_status === 'approved')
                                <a href="{{ route('pegawai.wfh-registrations.letter', $date) }}" target="_blank" class="btn btn-success">
                                    <i class="fas fa-file-pdf mr-1"></i> Surat Tugas
                                </a>
                            @elseif($myRegistration->status === 'selected' && $date->letter_status === 'pending_approval')
                                <small class="text-muted"><i class="fas fa-hourglass-half mr-1"></i>Surat menunggu TTD Ketua</small>
                            @else
                                <small class="text-muted"><i class="fas fa-check-circle mr-1"></i>Menunggu seleksi sistem</small>
                            @endif
                            @if($canCancelRegistration)
                                <form action="{{ route('pegawai.wfh-registrations.destroy', $date) }}" method="POST" onsubmit="return confirm('Batalkan pendaftaran WFH pada tanggal ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-times mr-1"></i> Batalkan Pendaftaran
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">Belum ada tanggal WFH yang dibuka.</div>
            @endforelse
        </div>

        <div class="mt-3">{{ $wfhDates->links() }}</div>
    </div>
</div>
@endsection
