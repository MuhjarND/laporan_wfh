@extends('layouts.admin')
@section('title', 'Seluruh Laporan WFH')
@section('page-title', 'Seluruh Laporan WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Seluruh Laporan</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i>Daftar Seluruh Laporan WFH</h3>
        <div class="card-tools">
            <a href="{{ route('admin.laporan.download-all-pdf') }}" class="btn btn-sm btn-success {{ $totalLaporan === 0 ? 'disabled' : '' }}">
                <i class="fas fa-file-pdf mr-1"></i> Download Seluruh Laporan
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.laporan.index') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama/NIP..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Diajukan</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="bulan" class="form-control">
                        <option value="">Semua Bulan</option>
                        @foreach([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $num => $nama)
                            <option value="{{ $num }}" {{ (string) request('bulan') === (string) $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <input type="number" name="tahun" class="form-control" placeholder="Tahun" value="{{ request('tahun') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search mr-1"></i> Filter</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pegawai/Atasan</th>
                        <th>Periode</th>
                        <th>Kegiatan</th>
                        <th>Status</th>
                        <th>Terakhir Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporans as $i => $lap)
                    <tr>
                        <td>{{ $laporans->firstItem() + $i }}</td>
                        <td>
                            <strong>{{ $lap->user->name }}</strong><br>
                            <small class="text-muted">{{ $lap->user->nip }} - {{ ucfirst(str_replace('_', ' ', $lap->user->role)) }}</small>
                        </td>
                        <td><strong style="color:var(--primary);">{{ $lap->periode }}</strong></td>
                        <td>{{ $lap->kegiatan->count() }} kegiatan</td>
                        <td>{!! $lap->status_badge !!}</td>
                        <td><small class="text-muted">{{ $lap->updated_at->format('d/m/Y H:i') }}</small></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.laporan.preview', $lap) }}" target="_blank" class="btn btn-outline-secondary" title="Preview PDF">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.laporan.pdf', $lap) }}" class="btn btn-success" title="Download PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada laporan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $laporans->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
