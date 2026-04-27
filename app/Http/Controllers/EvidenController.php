<?php

namespace App\Http\Controllers;

use App\KegiatanWfh;
use Illuminate\Support\Facades\Storage;

class EvidenController extends Controller
{
    public function preview($token)
    {
        $kegiatan = KegiatanWfh::with('laporan.user')
            ->where('eviden_token', $token)
            ->whereNotNull('eviden_path')
            ->firstOrFail();

        return view('public.eviden-preview', compact('kegiatan'));
    }

    public function file($token)
    {
        $kegiatan = KegiatanWfh::where('eviden_token', $token)
            ->whereNotNull('eviden_path')
            ->firstOrFail();

        abort_unless(Storage::disk('local')->exists($kegiatan->eviden_path), 404);

        $path = Storage::disk('local')->path($kegiatan->eviden_path);
        $headers = [
            'Content-Type' => $kegiatan->eviden_mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($kegiatan->eviden_original_name ?: 'eviden') . '"',
        ];

        return response()->file($path, $headers);
    }
}
