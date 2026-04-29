<?php

namespace App\Http\Controllers;

use App\KegiatanWfhEviden;
use App\KegiatanWfh;
use Illuminate\Support\Facades\Storage;

class EvidenController extends Controller
{
    public function preview($token)
    {
        $eviden = KegiatanWfhEviden::with('kegiatan.laporan.user')
            ->where('token', $token)
            ->first();

        if ($eviden) {
            $kegiatan = $eviden->kegiatan;
            return view('public.eviden-preview', compact('kegiatan', 'eviden'));
        }

        $kegiatan = KegiatanWfh::with('laporan.user')
            ->where('eviden_token', $token)
            ->whereNotNull('eviden_path')
            ->firstOrFail();
        $eviden = null;

        return view('public.eviden-preview', compact('kegiatan', 'eviden'));
    }

    public function file($token)
    {
        $eviden = KegiatanWfhEviden::where('token', $token)->first();

        if ($eviden) {
            abort_unless(Storage::disk('local')->exists($eviden->path), 404);
            $this->abortIfFileExtensionIsNotAllowed($eviden->original_name ?: $eviden->path);

            $path = Storage::disk('local')->path($eviden->path);
            $headers = [
                'Content-Type' => $eviden->mime ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . addslashes($eviden->original_name ?: 'eviden') . '"',
            ];

            return response()->file($path, $headers);
        }

        $kegiatan = KegiatanWfh::where('eviden_token', $token)
            ->whereNotNull('eviden_path')
            ->firstOrFail();

        abort_unless(Storage::disk('local')->exists($kegiatan->eviden_path), 404);
        $this->abortIfFileExtensionIsNotAllowed($kegiatan->eviden_original_name ?: $kegiatan->eviden_path);

        $path = Storage::disk('local')->path($kegiatan->eviden_path);
        $headers = [
            'Content-Type' => $kegiatan->eviden_mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($kegiatan->eviden_original_name ?: 'eviden') . '"',
        ];

        return response()->file($path, $headers);
    }

    private function abortIfFileExtensionIsNotAllowed($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = [
            'jpg', 'jpeg', 'png', 'gif', 'webp',
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt',
        ];

        abort_unless(in_array($extension, $allowed, true), 403);
    }
}
