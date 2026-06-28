@php
    $info = $dashboardWfhInfo ?? ['next_registration' => null, 'upcoming_count' => 0, 'open_count' => 0];
    $registration = $info['next_registration'];
    $wfhDate = $registration ? $registration->wfhDate : null;
    $statusLabels = [
        'registered' => ['class' => 'badge-info', 'text' => 'Terdaftar'],
        'selected' => ['class' => 'badge-success', 'text' => 'Terpilih'],
        'not_selected' => ['class' => 'badge-secondary', 'text' => 'Tidak Terpilih'],
    ];
    $status = $registration ? ($statusLabels[$registration->status] ?? ['class' => 'badge-secondary', 'text' => ucfirst($registration->status)]) : null;
@endphp

<div class="card">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-calendar-check mr-2" style="color:var(--primary);"></i>Informasi Daftar WFH</h3>
        <div class="card-tools">
            <a href="{{ route('pegawai.wfh-registrations.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list mr-1"></i> Lihat Daftar
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($registration && $wfhDate)
            <div class="d-flex align-items-start justify-content-between flex-wrap" style="gap:10px;">
                <div>
                    <div class="text-muted small">Pendaftaran terdekat</div>
                    <h5 class="mb-1" style="color:var(--primary);">{{ $wfhDate->tanggal->format('d/m/Y') }}</h5>
                    @if($wfhDate->keterangan)
                        <div class="text-muted">{{ $wfhDate->keterangan }}</div>
                    @endif
                    @if($registration->status === 'not_selected' && $registration->not_selected_reason)
                        <div class="text-muted mt-1"><small><strong>Alasan:</strong> {{ $registration->not_selected_reason }}</small></div>
                    @endif
                </div>
                <span class="badge {{ $status['class'] }}">{{ $status['text'] }}</span>
            </div>
            <div class="mt-3 d-flex flex-wrap" style="gap:8px;">
                <span class="badge badge-light border">Total pendaftaran aktif: {{ $info['upcoming_count'] }}</span>
                <span class="badge badge-light border">Tanggal belum didaftar: {{ $info['open_count'] }}</span>
            </div>
        @else
            <div class="text-center py-3">
                <i class="fas fa-calendar-plus fa-2x mb-2" style="color:var(--primary);opacity:.35;"></i>
                <p class="text-muted mb-3">Belum ada pendaftaran WFH aktif.</p>
                <a href="{{ route('pegawai.wfh-registrations.index') }}" class="btn btn-primary">
                    <i class="fas fa-calendar-check mr-1"></i> Daftar WFH
                </a>
            </div>
        @endif
    </div>
</div>
