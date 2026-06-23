@extends('layouts.admin')
@section('title', 'Monitoring Laporan WFH')
@section('page-title', 'Monitoring Laporan WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('wfh-letter-approvals.index') }}">Approval Surat</a></li>
    <li class="breadcrumb-item active">Monitoring Laporan</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Monitoring Pembuatan Laporan Pegawai</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('wfh-letter-approvals.monitoring') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <select name="bulan" class="form-control">
                        <option value="">Semua Bulan</option>
                        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bulan)
                            <option value="{{ $i + 1 }}" {{ request('bulan') == $i + 1 ? 'selected' : '' }}>{{ $bulan }}</option>
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

        @forelse($wfhDates as $date)
            @php $stats = $monitoringStats[$date->id]; @endphp
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center">
                    <div>
                        <strong>{{ $date->tanggal->format('d/m/Y') }}</strong>
                        <small class="text-muted d-block">{{ $date->keterangan ?: '-' }}</small>
                    </div>
                    <div class="ml-auto text-right">
                        <span class="badge badge-info">Ditugaskan: {{ $stats['assigned_count'] }}</span>
                        <span class="badge badge-success">Sudah Isi: {{ $stats['activity_count'] }}</span>
                        <span class="badge {{ $stats['missing_activity_count'] > 0 ? 'badge-danger' : 'badge-success' }}">Belum Isi: {{ $stats['missing_activity_count'] }}</span>
                        <span class="badge badge-primary">Diajukan/Disetujui: {{ $stats['submitted_count'] }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 46px;">No</th>
                                    <th>Pegawai</th>
                                    <th>Kegiatan Tanggal Ini</th>
                                    <th>Status Laporan Bulanan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['rows'] as $index => $row)
                                    @php
                                        $user = $row['user'];
                                        $laporan = $row['laporan'];
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $user->name }}</strong><br>
                                            <small class="text-muted">{{ $user->nip ?: '-' }}</small>
                                        </td>
                                        <td>
                                            @if($row['has_activity'])
                                                <span class="badge badge-success">{{ $row['kegiatan_count'] }} kegiatan</span>
                                            @else
                                                <span class="badge badge-danger">Belum isi kegiatan</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$laporan)
                                                <span class="badge badge-secondary">Belum ada laporan</span>
                                            @elseif($laporan->status === 'draft')
                                                <span class="badge badge-secondary">Draft</span>
                                            @elseif($laporan->status === 'submitted')
                                                <span class="badge badge-info">Diajukan</span>
                                            @elseif($laporan->status === 'approved')
                                                <span class="badge badge-success">Disetujui</span>
                                            @elseif($laporan->status === 'rejected')
                                                <span class="badge badge-danger">Ditolak</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($laporan)
                                                <a href="{{ route('wfh-letter-approvals.report', $laporan) }}" class="btn btn-xs btn-outline-primary">
                                                    <i class="fas fa-eye mr-1"></i> Detail
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada pegawai yang ditugaskan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">Belum ada tanggal WFH aktif.</div>
        @endforelse

        <div class="mt-3">{{ $wfhDates->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
