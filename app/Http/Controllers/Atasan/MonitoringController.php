<?php

namespace App\Http\Controllers\Atasan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\LaporanWfh;
use App\Services\WhatsAppNotificationService;
use Barryvdh\DomPDF\Facade as PDF;

class MonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:atasan']);
    }

    public function index()
    {
        $user = auth()->user();
        $bawahan = User::where('atasan_id', $user->id)->orderBy('name')->get();
        return view('atasan.monitoring.index', compact('bawahan'));
    }

    public function showPegawai(User $pegawai)
    {
        $user = auth()->user();
        if ($pegawai->atasan_id !== $user->id) {
            abort(403);
        }
        $laporans = LaporanWfh::where('user_id', $pegawai->id)
            ->orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->paginate(12);
        return view('atasan.monitoring.show-pegawai', compact('pegawai', 'laporans'));
    }

    public function showLaporan(LaporanWfh $laporan)
    {
        $user = auth()->user();
        $laporan->load('kegiatan.evidens', 'user', 'approver');
        if ($laporan->user->atasan_id !== $user->id) {
            abort(403);
        }
        return view('atasan.monitoring.show-laporan', compact('laporan'));
    }

    public function approve(Request $request, LaporanWfh $laporan)
    {
        $user = auth()->user();
        if ($laporan->user->atasan_id !== $user->id) { abort(403); }
        if ($laporan->status !== 'submitted') {
            return redirect()->back()->with('error', 'Hanya laporan yang sudah diajukan yang dapat disetujui.');
        }
        $request->validate([
            'catatan_atasan' => 'nullable|string',
        ]);

        if (!$user->signature) {
            return redirect()->route('signature.edit')
                ->with('error', 'Silakan isi tanda tangan terlebih dahulu sebelum menyetujui laporan.');
        }

        $laporan->update([
            'status' => 'approved',
            'catatan_atasan' => $request->catatan_atasan,
            'signature_atasan' => $user->signature,
            'approved_at' => now(),
            'approved_by' => $user->id,
        ]);
        $laporan->user->notify(new \App\Notifications\LaporanApproved($laporan));
        app(WhatsAppNotificationService::class)->sendApprovedToPegawai($laporan);
        return redirect()->route('atasan.monitoring.show-laporan', $laporan)
            ->with('success', 'Laporan berhasil disetujui.');
    }

    public function reject(Request $request, LaporanWfh $laporan)
    {
        $user = auth()->user();
        if ($laporan->user->atasan_id !== $user->id) { abort(403); }
        $request->validate(['catatan_atasan' => 'required|string']);
        $laporan->update([
            'status' => 'rejected',
            'catatan_atasan' => $request->catatan_atasan,
        ]);
        $laporan->user->notify(new \App\Notifications\LaporanRejected($laporan));
        app(WhatsAppNotificationService::class)->sendRejectedToPegawai($laporan);
        return redirect()->route('atasan.monitoring.show-laporan', $laporan)
            ->with('success', 'Laporan berhasil ditolak.');
    }

    public function preview(LaporanWfh $laporan)
    {
        $user = auth()->user();
        if ($laporan->user->atasan_id !== $user->id) { abort(403); }
        $laporan->load('kegiatan.evidens', 'user', 'user.atasan');
        $isPdf = true;
        $pdf = PDF::loadView('pdf.laporan-wfh', compact('laporan', 'isPdf'));
        $pdf->setPaper('A4', 'portrait');
        $filename = 'Preview_Laporan_WFH_' . $laporan->user->name . '_' . $laporan->nama_bulan . '_' . $laporan->tahun . '.pdf';
        return $pdf->stream($filename);
    }

    public function downloadPdf(LaporanWfh $laporan)
    {
        $user = auth()->user();
        if ($laporan->user->atasan_id !== $user->id) { abort(403); }
        $laporan->load('kegiatan.evidens', 'user', 'user.atasan');
        $isPdf = true;
        $pdf = PDF::loadView('pdf.laporan-wfh', compact('laporan', 'isPdf'));
        $pdf->setPaper('A4', 'portrait');
        $filename = 'Laporan_WFH_' . $laporan->user->name . '_' . $laporan->nama_bulan . '_' . $laporan->tahun . '.pdf';
        return $pdf->download($filename);
    }

    public function laporanPending()
    {
        $user = auth()->user();
        $bawahanIds = User::where('atasan_id', $user->id)->pluck('id');
        $laporans = LaporanWfh::with('user')
            ->whereIn('user_id', $bawahanIds)
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'desc')
            ->paginate(15);
        return view('atasan.monitoring.pending', compact('laporans'));
    }

    public function signAllForm()
    {
        $user = auth()->user();
        $bawahanIds = User::where('atasan_id', $user->id)->pluck('id');
        $totalPending = LaporanWfh::whereIn('user_id', $bawahanIds)
            ->where('status', 'submitted')
            ->count();

        return view('atasan.monitoring.sign-all', compact('totalPending'));
    }

    public function signAll(Request $request)
    {
        $request->validate([
            'catatan_atasan' => 'nullable|string',
        ]);

        $user = auth()->user();
        if (!$user->signature) {
            return redirect()->route('signature.edit')
                ->with('error', 'Silakan isi tanda tangan terlebih dahulu sebelum menyetujui laporan.');
        }

        $bawahanIds = User::where('atasan_id', $user->id)->pluck('id');
        $laporans = LaporanWfh::whereIn('user_id', $bawahanIds)
            ->where('status', 'submitted')
            ->get();

        foreach ($laporans as $laporan) {
            $laporan->update([
                'status' => 'approved',
                'catatan_atasan' => $request->catatan_atasan,
                'signature_atasan' => $user->signature,
                'approved_at' => now(),
                'approved_by' => $user->id,
            ]);
            $laporan->user->notify(new \App\Notifications\LaporanApproved($laporan));
            app(WhatsAppNotificationService::class)->sendApprovedToPegawai($laporan);
        }

        return redirect()->route('atasan.monitoring.pending')
            ->with('success', $laporans->count() . ' laporan berhasil ditandatangani dan disetujui.');
    }
}
