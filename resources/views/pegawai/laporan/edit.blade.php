@extends('layouts.admin')
@section('title', 'Edit Laporan WFH')
@section('page-title', 'Edit Laporan - ' . $laporan->periode)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('pegawai.laporan.index') }}">Laporan WFH</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('styles')
<style>
    .submit-action-card .submit-actions {
        margin-left: auto;
        justify-content: flex-end;
    }

    .kegiatan-table .btn-group {
        white-space: nowrap;
    }
    .rich-content p,
    .rich-content ul,
    .rich-content ol,
    .rich-content blockquote {
        margin-bottom: .35rem;
    }
    .rich-content :last-child {
        margin-bottom: 0;
    }
    .cke {
        border-radius: 6px;
        overflow: hidden;
    }

    @media (max-width: 767.98px) {
        .kegiatan-table-wrap {
            overflow: visible;
        }

        .kegiatan-table {
            border-collapse: separate;
            border-spacing: 0 12px;
        }

        .kegiatan-table thead {
            display: none;
        }

        .kegiatan-table,
        .kegiatan-table tbody,
        .kegiatan-table tr,
        .kegiatan-table td {
            display: block;
            width: 100%;
        }

        .kegiatan-table tr {
            background: #fff !important;
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 10px 12px;
        }

        .kegiatan-table td {
            border: 0;
            padding: 8px 0;
        }

        .kegiatan-table td:not(.kegiatan-mobile-actions) {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            border-bottom: 1px solid #eef2f7;
        }

        .kegiatan-table td:not(.kegiatan-mobile-actions)::before {
            content: attr(data-label);
            color: var(--text-muted);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
            flex: 0 0 38%;
        }

        .kegiatan-table td:not(.kegiatan-mobile-actions) > * {
            text-align: right;
        }

        .kegiatan-table .kegiatan-mobile-actions {
            padding-top: 12px;
        }

        .kegiatan-table .kegiatan-mobile-actions::before {
            content: attr(data-label);
            display: block;
            color: var(--text-muted);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .3px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .kegiatan-table .btn-group {
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: minmax(42px, max-content);
            justify-content: end;
        }

        .kegiatan-table .btn-group .btn,
        .kegiatan-table .btn-group form .btn {
            min-width: 42px;
            min-height: 36px;
        }

        .submit-action-card .card-body {
            align-items: stretch !important;
        }

        .submit-action-card .submit-actions {
            width: 100%;
            margin-left: 0;
        }

        .submit-action-card .submit-actions .btn,
        .submit-action-card .submit-actions form {
            width: 100%;
        }
    }

    @media (max-width: 420px) {
        .kegiatan-table td:not(.kegiatan-mobile-actions) {
            display: block;
        }

        .kegiatan-table td:not(.kegiatan-mobile-actions)::before {
            display: block;
            margin-bottom: 3px;
        }

        .kegiatan-table td:not(.kegiatan-mobile-actions) > * {
            display: block;
            text-align: left;
        }

        .kegiatan-table .btn-group {
            justify-content: start;
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        @if($laporan->status === 'rejected' && $laporan->catatan_atasan)
            <div class="alert alert-danger" style="font-size:.85rem;">
                <strong><i class="fas fa-comment-alt mr-1"></i> Catatan Atasan:</strong><br>{{ $laporan->catatan_atasan }}
            </div>
        @endif

        @if($wfhDates->count() > 0)
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar mr-2" style="color:var(--primary);"></i>Tanggal WFH</h3></div>
            <div class="card-body p-0" style="max-height:200px;overflow-y:auto;">
                <table class="table table-sm mb-0">
                    @foreach($wfhDates as $wdate)
                    <tr><td style="font-size:.85rem;"><i class="fas fa-check-circle mr-1" style="color:var(--primary);"></i> {{ $wdate->tanggal->format('d/m/Y') }} @if($wdate->keterangan)<small class="text-muted">({{ $wdate->keterangan }})</small>@endif</td></tr>
                    @endforeach
                </table>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-plus-circle mr-2" style="color:var(--primary);"></i>Tambah Kegiatan</h3></div>
            <form action="{{ route('pegawai.laporan.add-kegiatan', $laporan) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 col-6">
                            <div class="form-group"><label>Tanggal *</label><input type="date" name="tanggal" class="form-control" required value="{{ old('tanggal') }}"></div>
                        </div>
                        <div class="col-md-8 col-6">
                            <div class="form-group"><label>Tempat WFH *</label><input type="text" name="tempat_wfh" class="form-control" required value="{{ old('tempat_wfh', 'Rumah') }}"></div>
                        </div>
                    </div>
                    <div class="form-group"><label>Kegiatan / Tugas *</label><textarea name="kegiatan" id="kegiatan" class="form-control js-ckeditor" rows="3" required placeholder="Uraian kegiatan...">{{ old('kegiatan') }}</textarea></div>
                    <div class="form-group"><label>Capaian / Hasil *</label><textarea name="capaian" id="capaian" class="form-control js-ckeditor" rows="3" required placeholder="Capaian/hasil...">{{ old('capaian') }}</textarea></div>
                    <div class="form-group">
                        <label>Eviden (opsional)</label>
                        <input type="file" name="eviden" class="form-control-file">
                        <small class="form-text text-muted">Upload file eviden maksimal 10 MB. Link preview akan dibuat otomatis dari aplikasi.</small>
                    </div>
                </div>
                <div class="card-footer text-right" style="background:#fafbfc;"><button type="submit" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambah</button></div>
            </form>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-2" style="color:var(--primary);"></i>Daftar Kegiatan ({{ $laporan->kegiatan->count() }})</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive kegiatan-table-wrap">
                    <table class="table table-hover mb-0 kegiatan-table">
                        <thead><tr><th style="width:5%">No</th><th style="width:12%">Tanggal</th><th style="width:30%">Kegiatan</th><th style="width:25%">Capaian</th><th style="width:13%">Tempat</th><th style="width:15%">Aksi</th></tr></thead>
                        <tbody>
                            @forelse($laporan->kegiatan as $i => $keg)
                            <tr>
                                <td data-label="No"><span>{{ $i+1 }}</span></td>
                                <td data-label="Tanggal"><small>{{ $keg->tanggal->format('d/m/Y') }}</small></td>
                                <td data-label="Kegiatan"><small>{{ Str::limit(strip_tags($keg->kegiatan), 80) }}</small></td>
                                <td data-label="Capaian">
                                    <small>{{ Str::limit(strip_tags($keg->capaian), 60) }}</small>
                                    @if($keg->eviden_preview_link)
                                        <br><a href="{{ $keg->eviden_preview_link }}" target="_blank" rel="noopener" style="font-size:.75rem;color:var(--primary);font-weight:600;"><i class="fas fa-paperclip mr-1"></i>{{ Str::limit($keg->eviden_original_name ?? 'Eviden', 28) }}</a>
                                    @endif
                                </td>
                                <td data-label="Tempat"><small>{{ $keg->tempat_wfh }}</small></td>
                                <td data-label="Aksi" class="kegiatan-mobile-actions">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-warning btn-edit" data-id="{{ $keg->id }}" data-tanggal="{{ $keg->tanggal->format('Y-m-d') }}" data-tempat="{{ $keg->tempat_wfh }}"><i class="fas fa-edit"></i></button>
                                        <form action="{{ route('pegawai.kegiatan.delete', $keg) }}" method="POST" class="d-inline">@csrf @method('DELETE')<button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button></form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2" style="opacity:.3;"></i>Belum ada kegiatan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($laporan->kegiatan->count() > 0)
<div class="card submit-action-card">
    <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
        <div class="mb-3 mb-md-0">
            <strong style="color:var(--text-dark);">Laporan {{ $laporan->periode }}</strong>
            <div class="text-muted" style="font-size:.85rem;">{{ $laporan->kegiatan->count() }} kegiatan siap diajukan ke atasan.</div>
        </div>
        <div class="d-flex flex-column flex-sm-row submit-actions" style="gap:8px;">
            <a href="#" class="btn btn-outline-secondary" data-toggle="modal" data-target="#previewModal">
                <i class="fas fa-eye mr-1"></i> Preview Laporan
            </a>
            <form action="{{ route('pegawai.laporan.submit', $laporan) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-block">
                    <i class="fas fa-paper-plane mr-1"></i> Ajukan ke Atasan
                </button>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-header"><h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Kegiatan</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="modal-body">
                    <div class="form-group"><label>Tanggal</label><input type="date" name="tanggal" id="edit_tanggal" class="form-control" required></div>
                    <div class="form-group"><label>Tempat WFH</label><input type="text" name="tempat_wfh" id="edit_tempat" class="form-control" required></div>
                    <div class="form-group"><label>Kegiatan</label><textarea name="kegiatan" id="edit_kegiatan" class="form-control" rows="3" required></textarea></div>
                    <div class="form-group"><label>Capaian</label><textarea name="capaian" id="edit_capaian" class="form-control" rows="3" required></textarea></div>
                    <div class="form-group">
                        <label>Eviden (opsional)</label>
                        <input type="file" name="eviden" id="edit_eviden" class="form-control-file">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti file eviden.</small>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl" style="max-width:900px;">
        <div class="modal-content">
            <div class="modal-header" style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                <h5 class="modal-title" style="color:#0f4c3a;font-weight:700;">
                    <i class="fas fa-file-alt mr-2"></i>Preview Laporan - {{ $laporan->periode }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0" style="background:#e5e7eb;">
                <iframe id="previewFrame" src="{{ route('pegawai.laporan.preview', $laporan) }}"
                    style="width:100%;height:70vh;border:none;display:block;background:#fff;margin:0 auto;">
                </iframe>
            </div>
            <div class="modal-footer" style="background:#f9fafb;border-top:1px solid #e5e7eb;">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Tutup
                </button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('previewFrame').contentWindow.print();">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
                @if($laporan->status === 'approved')
                <a href="{{ route('pegawai.laporan.pdf', $laporan) }}" class="btn btn-success">
                    <i class="fas fa-file-pdf mr-1"></i> Download PDF
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
<script>
var kegiatanItems = @json($laporan->kegiatan->mapWithKeys(function ($keg) {
    return [$keg->id => [
        'kegiatan' => $keg->kegiatan,
        'capaian' => $keg->capaian,
    ]];
}));

var editorConfig = {
    height: 130,
    versionCheck: false,
    resize_enabled: false,
    removePlugins: 'elementspath,image,table,link,about',
    toolbar: [
        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'RemoveFormat'] },
        { name: 'paragraph', items: ['NumberedList', 'BulletedList', 'Blockquote'] },
        { name: 'clipboard', items: ['Undo', 'Redo'] }
    ]
};

function createEditor(id) {
    if (!window.CKEDITOR) {
        return;
    }

    if (!CKEDITOR.instances[id]) {
        CKEDITOR.replace(id, editorConfig);
    }
}

$(function () {
    if (!window.CKEDITOR) {
        $('.js-ckeditor').first().before('<div class="alert alert-danger py-2">CKEditor tidak berhasil dimuat. Pastikan file public/vendor/ckeditor/ckeditor.js dapat diakses.</div>');
        return;
    }

    createEditor('kegiatan');
    createEditor('capaian');
});

$(document).on('click','.btn-edit',function(){
    var id=$(this).data('id');
    var item = kegiatanItems[id] || { kegiatan: '', capaian: '' };
    $('#editForm').attr('action','{{ url("pegawai/kegiatan") }}/'+id);
    $('#edit_tanggal').val($(this).data('tanggal'));
    $('#edit_kegiatan').val(item.kegiatan);
    $('#edit_capaian').val(item.capaian);
    if (window.CKEDITOR && CKEDITOR.instances.edit_kegiatan) CKEDITOR.instances.edit_kegiatan.setData(item.kegiatan);
    if (window.CKEDITOR && CKEDITOR.instances.edit_capaian) CKEDITOR.instances.edit_capaian.setData(item.capaian);
    $('#edit_tempat').val($(this).data('tempat'));
    $('#editModal').modal('show');
});

$('#editModal').on('shown.bs.modal', function () {
    createEditor('edit_kegiatan');
    createEditor('edit_capaian');
    if (window.CKEDITOR && CKEDITOR.instances.edit_kegiatan) CKEDITOR.instances.edit_kegiatan.setData($('#edit_kegiatan').val());
    if (window.CKEDITOR && CKEDITOR.instances.edit_capaian) CKEDITOR.instances.edit_capaian.setData($('#edit_capaian').val());
});

$(document).on('submit', 'form', function () {
    if (!window.CKEDITOR) return;
    for (var name in CKEDITOR.instances) {
        CKEDITOR.instances[name].updateElement();
    }
});
</script>
@endsection
