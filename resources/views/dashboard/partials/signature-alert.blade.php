@if(!auth()->user()->signature)
    <div class="alert alert-warning d-flex align-items-start justify-content-between flex-wrap" style="gap:10px;">
        <div>
            <strong><i class="fas fa-signature mr-1"></i> Tanda tangan belum tersedia.</strong>
            <div class="mt-1">
                Silakan upload atau buat tanda tangan agar dapat digunakan otomatis pada laporan dan proses persetujuan.
            </div>
        </div>
        <a href="{{ route('signature.edit') }}" class="btn btn-sm btn-warning">
            <i class="fas fa-pen mr-1"></i> Buat TTD
        </a>
    </div>
@endif
