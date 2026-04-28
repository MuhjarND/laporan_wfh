@extends('layouts.admin')
@section('title', 'Laporan WFH')
@section('page-title', 'Laporan WFH Saya')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Laporan WFH</li>
@endsection

@section('styles')
<style>
    .laporan-card .card-header {
        min-height: 52px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .laporan-card .card-title {
        line-height: 32px;
    }

    .laporan-card .card-tools {
        margin-right: 0;
        margin-left: auto;
    }

    .laporan-action-group {
        gap: 8px;
    }

    .laporan-table .btn-group {
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .laporan-card .card-header {
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        .laporan-card .card-title {
            line-height: 1.4;
            margin-bottom: 12px;
        }

        .laporan-card .card-tools {
            float: none;
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            width: 100%;
        }

        .laporan-card .card-tools .btn {
            width: 100%;
            min-height: 36px;
        }

        .laporan-table {
            border-collapse: separate;
            border-spacing: 0 12px;
        }

        .laporan-table thead {
            display: none;
        }

        .laporan-table,
        .laporan-table tbody,
        .laporan-table tr,
        .laporan-table td {
            display: block;
            width: 100%;
        }

        .laporan-table tr {
            background: #fff !important;
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 10px 12px;
        }

        .laporan-table td {
            border: 0;
            padding: 8px 0;
        }

        .laporan-table td:not(.laporan-mobile-actions) {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            border-bottom: 1px solid #eef2f7;
        }

        .laporan-table td:not(.laporan-mobile-actions)::before {
            content: attr(data-label);
            color: var(--text-muted);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
            flex: 0 0 42%;
        }

        .laporan-table td:not(.laporan-mobile-actions) > * {
            text-align: right;
        }

        .laporan-table .laporan-mobile-actions {
            padding-top: 12px;
        }

        .laporan-table .laporan-mobile-actions::before {
            content: attr(data-label);
            display: block;
            color: var(--text-muted);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .laporan-table .btn-group {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: minmax(42px, max-content);
            justify-content: end;
        }

        .laporan-table .btn-group .btn,
        .laporan-table .btn-group form .btn {
            min-width: 42px;
            min-height: 36px;
        }

        .laporan-table .btn-group form {
            display: inline-block;
        }
    }

    @media (max-width: 420px) {
        .laporan-card .card-tools {
            grid-template-columns: 1fr;
        }

        .laporan-table td:not(.laporan-mobile-actions) {
            display: block;
        }

        .laporan-table td:not(.laporan-mobile-actions)::before {
            display: block;
            margin-bottom: 3px;
        }

        .laporan-table td:not(.laporan-mobile-actions) > * {
            display: block;
            text-align: left;
        }

        .laporan-table .btn-group {
            justify-content: start;
        }
    }
</style>
@endsection

@section('content')
<div class="card laporan-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i>Daftar Laporan WFH</h3>
        <div class="card-tools d-flex flex-wrap justify-content-end laporan-action-group">
            <a href="{{ route('pegawai.laporan.download-all-pdf') }}" class="btn btn-sm btn-success {{ $laporans->total() === 0 ? 'disabled' : '' }}" title="Download semua laporan dalam satu PDF">
                <i class="fas fa-file-pdf mr-1"></i> Download Semua
            </a>
            <a href="{{ route('pegawai.laporan.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus mr-1"></i> Buat Laporan
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped laporan-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Periode</th>
                        <th>Jumlah Kegiatan</th>
                        <th>Status</th>
                        <th>Terakhir Diupdate</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporans as $i => $lap)
                    <tr>
                        <td data-label="No"><span>{{ $laporans->firstItem() + $i }}</span></td>
                        <td data-label="Periode"><strong style="color: var(--primary);">{{ $lap->periode }}</strong></td>
                        <td data-label="Jumlah Kegiatan"><span>{{ $lap->kegiatan->count() }} kegiatan</span></td>
                        <td data-label="Status"><span>{!! $lap->status_badge !!}</span></td>
                        <td data-label="Terakhir Diupdate"><small style="color: var(--text-muted);">{{ $lap->updated_at->format('d/m/Y H:i') }}</small></td>
                        <td data-label="Aksi" class="laporan-mobile-actions">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('pegawai.laporan.show', $lap) }}" class="btn btn-outline-secondary" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array($lap->status, ['draft', 'rejected']))
                                    <a href="{{ route('pegawai.laporan.edit', $lap) }}" class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if($lap->status === 'approved')
                                    <a href="{{ route('pegawai.laporan.pdf', $lap) }}" class="btn btn-success" title="Download PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                @endif
                                @if($lap->status === 'draft')
                                    <form action="{{ route('pegawai.laporan.destroy', $lap) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus laporan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4" style="color: var(--text-muted);">
                            <i class="fas fa-folder-open fa-2x mb-2 d-block" style="opacity: 0.3;"></i>
                            Belum ada laporan. <a href="{{ route('pegawai.laporan.create') }}" style="color: var(--primary);">Buat sekarang</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $laporans->links() }}</div>
    </div>
</div>
@endsection
