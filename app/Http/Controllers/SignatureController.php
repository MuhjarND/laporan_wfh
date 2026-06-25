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
}
