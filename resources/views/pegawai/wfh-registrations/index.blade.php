@extends('layouts.admin')
@section('title', 'Pendaftaran WFH')
@section('page-title', 'Pendaftaran WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Pendaftaran WFH</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Daftar Tanggal WFH</h3>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i>
            Kuota WFH setiap tanggal adalah {{ $quota }} pegawai. Sistem memprioritaskan pegawai yang belum WFH Jumat sebelumnya dan pegawai dengan total WFH lebih sedikit.
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Pendaftar</th>
                        <th>Terpilih / Kuota</th>
                        <th>Status Saya</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hariNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']; @endphp
                    @forelse($wfhDates as $date)
                        @php
                            $myRegistration = $date->registrations->first();
                            $quotaFull = $date->users_count >= $quota;
                            $window = $registrationWindows[$date->id] ?? null;
                            $registrationOpen = $window ? $window['is_open'] : true;
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
                                @elseif($myRegistration->status === 'selected')
                                    <span class="badge badge-success">Terpilih</span>
                                @elseif($myRegistration->status === 'not_selected')
                                    <span class="badge badge-secondary">Tidak Terpilih</span>
                                    @if($myRegistration->not_selected_reason)
                                        <small class="text-muted d-block mt-1">{{ $myRegistration->not_selected_reason }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-info">Terdaftar</span>
                                @endif
                            </td>
                            <td>
                                @if(!$myRegistration)
                                    <form action="{{ route('pegawai.wfh-registrations.store', $date) }}" method="POST" onsubmit="return confirm('Daftar WFH pada tanggal ini?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary" {{ ($quotaFull || !$registrationOpen) ? 'disabled' : '' }}>
                                            <i class="fas fa-check mr-1"></i> Daftar
                                        </button>
                                    </form>
                                    @if($quotaFull)
                                        <small class="text-muted d-block mt-1">Kuota penuh</small>
                                    @elseif(!$registrationOpen && $window)
                                        <small class="text-muted d-block mt-1">{{ $window['closed_message'] }}</small>
                                    @endif
                                @elseif($myRegistration->status === 'selected' && $date->letter_status === 'approved')
                                    <a href="{{ route('pegawai.wfh-registrations.letter', $date) }}" target="_blank" class="btn btn-sm btn-success">
                                        <i class="fas fa-file-pdf mr-1"></i> Surat Tugas
                                    </a>
                                @elseif($myRegistration->status === 'selected' && $date->letter_status === 'pending_approval')
                                    <span class="text-muted">Surat menunggu TTD Ketua</span>
                                @else
                                    <span class="text-muted">Sudah diproses</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada tanggal WFH yang dibuka.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $wfhDates->links() }}</div>
    </div>
</div>
@endsection
