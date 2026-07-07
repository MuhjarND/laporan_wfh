<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SignatureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        return view('signature.edit');
    }

    public function update(Request $request)
    {
        $request->validate([
            'signature_canvas' => 'nullable|string|starts_with:data:image/png;base64,',
            'signature_file' => 'nullable|file|max:2048|mimes:jpg,jpeg,png',
        ]);

        if (!$request->filled('signature_canvas') && !$request->hasFile('signature_file')) {
            throw ValidationException::withMessages([
                'signature_canvas' => 'Silakan buat tanda tangan pada canvas atau upload file tanda tangan.',
            ]);
        }

        $signature = $request->hasFile('signature_file')
            ? $this->fileToDataUri($request->file('signature_file'))
            : $request->signature_canvas;

        $signature = $this->trimTransparentPadding($signature);

        $request->user()->update([
            'signature' => $signature,
        ]);

        return redirect()->route('signature.edit')
            ->with('success', 'Tanda tangan berhasil disimpan dan akan digunakan untuk laporan berikutnya.');
    }

    private function fileToDataUri($file)
    {
        $mime = $file->getMimeType();
        $allowed = ['image/jpeg', 'image/png'];

        if (!in_array($mime, $allowed, true)) {
            throw ValidationException::withMessages([
                'signature_file' => 'File tanda tangan harus berupa JPG atau PNG.',
            ]);
        }

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
    }

    private function trimTransparentPadding($dataUri)
    {
        if (!function_exists('imagecreatefromstring') || strpos($dataUri, 'data:image/png;base64,') !== 0) {
            return $dataUri;
        }

        $binary = base64_decode(substr($dataUri, strlen('data:image/png;base64,')), true);
        if ($binary === false) {
            return $dataUri;
        }

        $source = @imagecreatefromstring($binary);
        if (!$source) {
            return $dataUri;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $minX = $width;
        $minY = $height;
        $maxX = -1;
        $maxY = -1;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($source, $x, $y);
                $alpha = ($rgba & 0x7F000000) >> 24;

                if ($alpha < 120) {
                    $minX = min($minX, $x);
                    $minY = min($minY, $y);
                    $maxX = max($maxX, $x);
                    $maxY = max($maxY, $y);
                }
            }
        }

        if ($maxX < 0 || $maxY < 0) {
            imagedestroy($source);
            return $dataUri;
        }

        $padding = 18;
        $cropX = max(0, $minX - $padding);
        $cropY = max(0, $minY - $padding);
        $cropWidth = min($width - $cropX, ($maxX - $minX + 1) + ($padding * 2));
        $cropHeight = min($height - $cropY, ($maxY - $minY + 1) + ($padding * 2));

        $target = imagecreatetruecolor($cropWidth, $cropHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefill($target, 0, 0, imagecolorallocatealpha($target, 255, 255, 255, 127));
        imagecopy($target, $source, 0, 0, $cropX, $cropY, $cropWidth, $cropHeight);

        ob_start();
        imagepng($target);
        $trimmed = ob_get_clean();

        imagedestroy($source);
        imagedestroy($target);

        return 'data:image/png;base64,' . base64_encode($trimmed);
    }
}
