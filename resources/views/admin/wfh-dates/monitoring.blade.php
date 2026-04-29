@extends('layouts.admin')
@section('title', 'Monitoring Pelaporan WFH')
@section('page-title', 'Monitoring Pelaporan Kegiatan WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.wfh-dates.index') }}">Tanggal WFH</a></li>
    <li class="breadcrumb-item active">Monitoring</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Monitoring Pelaporan Kegiatan WFH</h3>
        <div class="card-tools">
            <form action="{{ route('admin.wfh-dates.send-all-activity-reminders') }}" method="POST" onsubmit="return confirm('Kirim WA ke semua pegawai yang belum mengisi kegiatan sesuai filter monitoring saat ini?');">
                @csrf
                <input type="hidden" name="tanggal" value="{{ request('tanggal') }}">
                <input type="hidden" name="bulan" value="{{ request('bulan') }}">
                <input type="hidden" name="tahun" value="{{ request('tahun') }}">
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fab fa-whatsapp mr-1"></i> Semua Isi Kegiatan
                </button>
            </form>
            <form action="{{ route('admin.wfh-dates.send-all-submit-reminders') }}" method="POST" onsubmit="return confirm('Kirim WA ke semua pegawai yang belum mengirim/mengajukan laporan sesuai filter monitoring saat ini?');">
                @csrf
                <input type="hidden" name="tanggal" value="{{ request('tanggal') }}">
                <input type="hidden" name="bulan" value="{{ request('bulan') }}">
                <input type="hidden" name="tahun" value="{{ request('tahun') }}">
                <button type="submit" class="btn btn-sm btn-warning">
                    <i class="fab fa-whatsapp mr-1"></i> Semua Kirim Laporan
                </button>
            </form>
            <a href="{{ route('admin.wfh-dates.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Tanggal WFH
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.wfh-dates.monitoring') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <select name="bulan" class="form-control">
                        <option value="">Semua Bulan</option>
                        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $nm)
                            <option value="{{ $i+1 }}" {{ request('bulan') == $i+1 ? 'selected' : '' }}>{{ $nm }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <input type="number" name="tahun" class="form-control" placeholder="Tahun" value="{{ request('tahun') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter mr-1"></i> Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Ditugaskan</th>
                        <th>Sudah Isi</th>
                        <th>Belum Isi Kegiatan</th>
                        <th>Belum Kirim Laporan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wfhDates as $i => $date)
                        @php
                            $stats = $reportStats[$date->id];
                            $missingNames = $stats['missing']->take(5)->pluck('name')->implode(', ');
                            $unsubmittedNames = $stats['unsubmitted']->take(5)->pluck('name')->implode(', ');
                        @endphp
                        <tr>
                            <td>{{ $wfhDates->firstItem() + $i }}</td>
                            <td>
                                <strong>{{ $date->tanggal->format('d/m/Y') }}</strong><br>
                                <small class="text-muted">{{ $date->keterangan ?: '-' }}</small>
                            </td>
                            <td>{{ $stats['assigned']->count() }} orang</td>
                            <td><span class="badge badge-success">{{ $stats['reported']->count() }}</span></td>
                            <td>
                                <span class="badge {{ $stats['missing']->count() > 0 ? 'badge-danger' : 'badge-success' }}">{{ $stats['missing']->count() }}</span>
                                @if($stats['missing']->count() > 0)
                                    <br><small class="text-muted">{{ $missingNames }}{{ $stats['missing']->count() > 5 ? ', ...' : '' }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $stats['unsubmitted']->count() > 0 ? 'badge-warning' : 'badge-success' }}">{{ $stats['unsubmitted']->count() }}</span>
                                @if($stats['unsubmitted']->count() > 0)
                                    <br><small class="text-muted">{{ $unsubmittedNames }}{{ $stats['unsubmitted']->count() > 5 ? ', ...' : '' }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-end" style="gap:6px;">
                                    <form action="{{ route('admin.wfh-dates.send-reminder', $date) }}" method="POST" onsubmit="return confirm('Kirim notifikasi WhatsApp agar pegawai mengisi kegiatan WFH pada tanggal ini?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success {{ $stats['missing']->count() === 0 ? 'disabled' : '' }}" {{ $stats['missing']->count() === 0 ? 'disabled' : '' }} title="Kirim reminder isi kegiatan">
                                            <i class="fab fa-whatsapp mr-1"></i> Isi Kegiatan
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.wfh-dates.send-submit-reminder', $date) }}" method="POST" onsubmit="return confirm('Kirim notifikasi WhatsApp agar pegawai mengirim/mengajukan laporan kegiatan?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning {{ $stats['unsubmitted']->count() === 0 ? 'disabled' : '' }}" {{ $stats['unsubmitted']->count() === 0 ? 'disabled' : '' }} title="Kirim reminder ajukan laporan">
                                            <i class="fab fa-whatsapp mr-1"></i> Kirim Laporan
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada tanggal WFH aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $wfhDates->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
