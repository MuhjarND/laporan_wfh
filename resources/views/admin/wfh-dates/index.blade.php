@extends('layouts.admin')
@section('title', 'Tanggal WFH')
@section('page-title', 'Kelola Tanggal WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Tanggal WFH</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Daftar Tanggal WFH</h3>
        <a href="{{ route('admin.wfh-dates.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah Tanggal
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.wfh-dates.index') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-3">
                    <select name="bulan" class="form-control">disetujui oleh Atasan Langsung.
                    
                        <option value="">Semua Bulan</option>
                        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $nm)
                            <option value="{{ $i+1 }}" {{ request('bulan') == $i+1 ? 'selected' : '' }}>{{ $nm }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="tahun" class="form-control" placeholder="Tahun" value="{{ request('tahun') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i> Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $hariNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                    @endphp
                    @forelse($wfhDates as $i => $date)
                    <tr>
                        <td>{{ $wfhDates->firstItem() + $i }}</td>
                        <td>{{ $date->tanggal->format('d/m/Y') }}</td>
                        <td>{{ $hariNames[$date->tanggal->dayOfWeek] }}</td>
                        <td>{{ $date->keterangan ?? '-' }}</td>
                        <td>
                            @if($date->is_active)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <form action="{{ route('admin.wfh-dates.toggle-active', $date) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn {{ $date->is_active ? 'btn-secondary' : 'btn-success' }}">
                                        <i class="fas {{ $date->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.wfh-dates.destroy', $date) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus tanggal ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center" style="color: var(--text-muted);">Belum ada tanggal WFH</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $wfhDates->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
