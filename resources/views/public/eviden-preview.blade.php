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
        .image-wrap {
            min-height: calc(100vh - 82px);
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .image-preview {
            display: block;
            max-width: min(100%, 1080px);
            max-height: calc(100vh - 112px);
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
        }
        .fallback { padding: 18px; font-size: 13px; color: #6b7280; }
        @media (max-width: 600px) {
            .header { align-items: flex-start; flex-direction: column; }
            .btn { width: 100%; text-align: center; }
            .frame-wrap { height: calc(100vh - 116px); padding: 8px; }
        }
    </style>
</head>
<body>
    @php
        $token = $eviden ? $eviden->token : $kegiatan->eviden_token;
        $mime = $eviden ? $eviden->mime : $kegiatan->eviden_mime;
        $name = $eviden ? $eviden->original_name : $kegiatan->eviden_original_name;
        $extension = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));
        $isImage = ($mime && strpos($mime, 'image/') === 0) || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    @endphp
    <div class="header">
        <div>
            <h1 class="title">Preview Eviden WFH</h1>
            <div class="meta">
                {{ $kegiatan->laporan->user->name ?? '-' }} - {{ $kegiatan->tanggal->format('d/m/Y') }}
                @if($name)
                    - {{ $name }}
                @endif
            </div>
        </div>
        <a class="btn" href="{{ route('eviden.file', $token) }}" target="_blank" rel="noopener">Buka File</a>
    </div>
    @if($isImage)
        <div class="image-wrap">
            <img src="{{ route('eviden.file', $token) }}" class="image-preview" alt="Preview Eviden">
        </div>
    @else
        <div class="frame-wrap">
            <iframe src="{{ route('eviden.file', $token) }}" title="Preview Eviden">
                <div class="fallback">
                    Browser tidak dapat menampilkan preview. Gunakan tombol Buka File.
                </div>
            </iframe>
        </div>
    @endif
</body>
</html>
