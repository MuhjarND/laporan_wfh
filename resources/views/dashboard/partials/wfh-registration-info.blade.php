@php
    $info = $dashboardWfhInfo ?? ['next_registration' => null, 'upcoming_count' => 0, 'open_count' => 0];
    $registration = $info['next_registration'];
    $wfhDate = $registration ? $registration->wfhDate : null;
    $statusLabels = [
        'registered' => ['class' => 'badge-info', 'text' => 'Menunggu Seleksi Sistem'],
        'selected' => ['class' => 'badge-success', 'text' => 'Terpilih'],
        'not_selected' => ['class' => 'badge-secondary', 'text' => 'Tidak Terpilih'],
    ];
    $status = $registration ? ($statusLabels[$registration->status] ?? ['class' => 'badge-secondary', 'text' => ucfirst($registration->status)]) : null;
    if ($registration && $wfhDate && $wfhDate->letter_status !== 'approved') {
        $status = ['class' => 'badge-info', 'text' => 'Menunggu Seleksi Sistem'];
    }
    $alertClass = 'alert-info';
    if ($registration && $wfhDate && $wfhDate->letter_status === 'approved' && $registration->status === 'selected') {
        $alertClass = 'alert-success';
    } elseif ($registration && (!$wfhDate || $wfhDate->letter_status !== 'approved' || $registration->status === 'registered')) {
        $alertClass = 'alert-warning';
    } elseif ($registration && $registration->status === 'not_selected') {
        $alertClass = 'alert-secondary';
    }
@endphp

<div class="alert {{ $alertClass }} d-flex align-items-start justify-content-between flex-wrap" style="gap:12px;border-left:4px solid var(--primary);">
    <div class="d-flex align-items-start" style="gap:10px;min-width:0;">
        <i class="fas fa-calendar-check mt-1" style="color:var(--primary);"></i>
        <div>
            <strong>Informasi Daftar WFH</strong>
            @if($registration && $wfhDate)
                <div>
                    Pendaftaran terdekat: <strong>{{ $wfhDate->tanggal->format('d/m/Y') }}</strong>
                    <span class="badge {{ $status['class'] }} ml-1">{{ $status['text'] }}</span>
                </div>
                @if($wfhDate->keterangan)
                    <div class="small mt-1"><strong>Keterangan:</strong> {{ $wfhDate->keterangan }}</div>
                @endif
                @if($wfhDate->letter_status === 'approved' && $registration->status === 'not_selected' && $registration->not_selected_reason)
                    <div class="small mt-1"><strong>Alasan tidak terpilih:</strong> {{ $registration->not_selected_reason }}</div>
                @elseif($wfhDate->letter_status !== 'approved')
                    <div class="small mt-1">Pendaftaran diterima dan akan diseleksi oleh sistem terlebih dahulu.</div>
                @endif
                <div class="small mt-1">
                    Total pendaftaran aktif: {{ $info['upcoming_count'] }}.
                    Tanggal belum didaftar: {{ $info['open_count'] }}.
                </div>
            @else
                <div>Belum ada pendaftaran WFH aktif. Silakan cek jadwal dan lakukan pendaftaran jika tersedia.</div>
            @endif
        </div>
    </div>
    <a href="{{ route('pegawai.wfh-registrations.index') }}" class="btn btn-sm btn-primary">
        <i class="fas fa-calendar-check mr-1"></i> Daftar WFH
    </a>
</div>
