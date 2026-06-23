@extends('layouts.admin')
@section('title', 'Approval Surat Tugas')
@section('page-title', 'Approval Surat Tugas')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Approval Surat Tugas</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-signature mr-2"></i>Daftar Approval Surat Tugas WFH</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor Surat</th>
                        <th>Tanggal WFH</th>
                        <th>Pendaftar</th>
                        <th>Terpilih</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($letters as $index => $letter)
                        <tr>
                            <td>{{ $letters->firstItem() + $index }}</td>
                            <td>
                                <strong>{{ $letter->letter_number }}</strong>
                                @if($letter->letter_requested_at)
                                    <br><small class="text-muted">Diajukan: {{ $letter->letter_requested_at->format('d/m/Y H:i') }}</small>
                                @endif
                            </td>
                            <td>{{ $letter->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $letter->registrations_count }} pegawai</td>
                            <td>{{ $letter->selected_registrations_count }} pegawai</td>
                            <td>
                                @if($letter->letter_status === 'approved')
                                    <span class="badge badge-success">Sudah Ditandatangani</span>
                                    @if($letter->letter_approved_at)
                                        <small class="text-muted d-block mt-1">{{ $letter->letter_approved_at->format('d/m/Y H:i') }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-warning">Menunggu TTD</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('wfh-letter-approvals.show', $letter) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada surat tugas yang perlu ditindaklanjuti.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($letters->hasPages())
        <div class="card-footer">
            {{ $letters->links() }}
        </div>
    @endif
</div>
@endsection
