<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview Eviden</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f0f2f5; color: #1f2937; }
        .header {
            min-height: 58px; padding: 12px 18px; background: #fff; border-bottom: 1px solid #e5e7eb;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
        }
        .title { margin: 0; font-size: 16px; font-weight: 700; color: #0f4c3a; }
        .meta { margin-top: 3px; font-size: 12px; color: #6b7280; }
        .btn {
            display: inline-block; padding: 8px 12px; border-radius: 6px; background: #0f4c3a;
            color: #fff; font-size: 13px; font-weight: 700; text-decoration: none; white-space: nowrap;
        }
        .frame-wrap { height: calc(100vh - 58px); padding: 12px; }
        iframe { width: 100%; height: 100%; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; }
        .fallback { padding: 18px; font-size: 13px; color: #6b7280; }
        @media (max-width: 600px) {
            .header { align-items: flex-start; flex-direction: column; }
            .btn { width: 100%; text-align: center; }
            .frame-wrap { height: calc(100vh - 116px); padding: 8px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1 class="title">Preview Eviden WFH</h1>
            <div class="meta">
                {{ $kegiatan->laporan->user->name ?? '-' }} - {{ $kegiatan->tanggal->format('d/m/Y') }}
                @if($kegiatan->eviden_original_name)
                    - {{ $kegiatan->eviden_original_name }}
                @endif
            </div>
        </div>
        <a class="btn" href="{{ route('eviden.file', $kegiatan->eviden_token) }}" target="_blank" rel="noopener">Buka File</a>
    </div>
    <div class="frame-wrap">
        <iframe src="{{ route('eviden.file', $kegiatan->eviden_token) }}" title="Preview Eviden">
            <div class="fallback">
                Browser tidak dapat menampilkan preview. Gunakan tombol Buka File.
            </div>
        </iframe>
    </div>
</body>
</html>
