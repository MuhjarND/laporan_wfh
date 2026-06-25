<?php

namespace App\Http\Controllers;

use App\User;
use App\WfhDate;
use App\LaporanWfh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WfhLetterLinkController extends Controller
{
    public function open(Request $request, WfhDate $wfhDate, User $user, $type)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Tautan tidak valid atau sudah kedaluwarsa.');
        }

        if (!$user->is_active || !in_array($user->role, ['pegawai', 'atasan'], true)) {
            abort(403, 'Akun tidak aktif atau tidak memiliki akses.');
        }

        if ($type === 'letter') {
            if ($wfhDate->letter_status !== 'approved' || !$wfhDate->users()->where('users.id', $user->id)->exists()) {
                abort(403, 'Surat tugas belum tersedia untuk akun ini.');
            }
        } elseif (in_array($type, ['activity', 'submit'], true)) {
            if (!$wfhDate->users()->where('users.id', $user->id)->exists()) {
                abort(403, 'Tanggal WFH tidak tersedia untuk akun ini.');
            }
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($type === 'letter') {
            return redirect()->route('pegawai.wfh-registrations.letter', $wfhDate);
        }

        if (in_array($type, ['activity', 'submit'], true)) {
            $laporan = LaporanWfh::where('user_id', $user->id)
                ->where('bulan', $wfhDate->tanggal->month)
                ->where('tahun', $wfhDate->tanggal->year)
                ->first();

            if ($laporan) {
                return redirect()->route('pegawai.laporan.edit', $laporan);
            }

            return redirect()->route('pegawai.laporan.create', [
                'bulan' => $wfhDate->tanggal->month,
                'tahun' => $wfhDate->tanggal->year,
            ]);
        }

        return redirect()->route('pegawai.wfh-registrations.index');
    }
}
