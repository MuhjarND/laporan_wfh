@extends('layouts.admin')
@section('title', 'Approval Surat Tugas WFH')
@section('page-title', 'Approval Surat Tugas WFH')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Approval Surat Tugas WFH</li>
@endsection

@section('styles')
<style>
    .approval-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(320px, .75fr);
        gap: 16px;
    }
    .letter-frame {
        width: 100%;
        height: 72vh;
        min-height: 520px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #fff;
    }
    @media (max-width: 992px) {
        .approval-grid {
            grid-template-columns: 1fr;
        }
        .letter-frame {
            height: 60vh;
            min-height: 420px;
        }
    }
    @media (max-width: 576px) {
        .letter-frame {
            display: none;
        }
        .mobile-pdf-note {
            display: block !important;
        }
    }
</style>
@endsection

@section('content')
<div class="approval-grid">
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-file-pdf mr-2"></i>Preview Surat</h3>
            <div class="card-tools ml-auto">
                <a href="{{ route('wfh-letter-approvals.pdf', $wfhDate) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-external-link-alt mr-1"></i> Buka PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info d-none mobile-pdf-note">
                <i class="fas fa-info-circle mr-1"></i>
                Untuk tampilan mobile, gunakan tombol <strong>Buka PDF</strong> agar preview surat lebih mudah dibaca.
            </div>
            <iframe class="letter-frame" src="{{ route('wfh-letter-approvals.pdf', $wfhDate) }}"></iframe>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Info Surat</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Nomor</dt>
                    <dd class="col-7">{{ $wfhDate->letter_number ?: '-' }}</dd>
                    <dt class="col-5">Tanggal WFH</dt>
                    <dd class="col-7">{{ $wfhDate->tanggal->format('d/m/Y') }}</dd>
                    <dt class="col-5">Status</dt>
                    <dd class="col-7">
                        @if($wfhDate->letter_status === 'approved')
                            <span class="badge badge-success">Sudah Ditandatangani</span>
                        @elseif($wfhDate->letter_status === 'pending_approval')
                            <span class="badge badge-warning">Menunggu TTD</span>
                        @else
                            <span class="badge badge-secondary">Draft</span>
                        @endif
                    </dd>
                    <dt class="col-5">Approval</dt>
                    <dd class="col-7">{{ $approver->name ?? '-' }}</dd>
                    @if($wfhDate->letter_approved_at)
                        <dt class="col-5">Tanggal TTD</dt>
                        <dd class="col-7">{{ $wfhDate->letter_approved_at->format('d/m/Y H:i') }}</dd>
                    @endif
                </dl>
            </div>
            <div class="card-footer text-right">
                @if($wfhDate->letter_status === 'approved')
                    <button type="button" class="btn btn-success" disabled>
                        <i class="fas fa-check mr-1"></i> Sudah Ditandatangani
                    </button>
                @else
                    @unless(auth()->user()->signature)
                        <div class="alert alert-warning text-left">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Anda belum menyimpan tanda tangan. Silakan isi melalui menu
                            <a href="{{ route('signature.edit') }}" class="alert-link">Tanda Tangan Saya</a>.
                        </div>
                    @endunless
                    <form action="{{ route('wfh-letter-approvals.sign', $wfhDate) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-signature mr-1"></i> Tanda Tangani & Kirim Notifikasi
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users mr-2"></i>Daftar Pendaftar</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $registration)
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
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-3">Belum ada pendaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
