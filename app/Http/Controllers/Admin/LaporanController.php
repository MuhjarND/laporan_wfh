<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\LaporanWfh;
use App\User;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    public function index(Request $request)
    {
        $query = LaporanWfh::with('user', 'kegiatan.evidens');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }

        $laporans = $query->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        $totalLaporan = LaporanWfh::count();
        $users = User::whereIn('role', ['pegawai', 'atasan'])->orderBy('name')->get();

        return view('admin.laporan.index', compact('laporans', 'totalLaporan', 'users'));
    }

    public function preview(LaporanWfh $laporan)
    {
        $laporan->load('kegiatan.evidens', 'user', 'user.atasan');

        $isPdf = true;
        $pdf = PDF::loadView('pdf.laporan-wfh', compact('laporan', 'isPdf'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Preview_Laporan_WFH_' . $laporan->user->name . '_' . $laporan->nama_bulan . '_' . $laporan->tahun . '.pdf';

        return $pdf->stream($filename);
    }

    public function downloadPdf(LaporanWfh $laporan)
    {
        $laporan->load('kegiatan.evidens', 'user', 'user.atasan');

        $isPdf = true;
        $pdf = PDF::loadView('pdf.laporan-wfh', compact('laporan', 'isPdf'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Laporan_WFH_' . $laporan->user->name . '_' . $laporan->nama_bulan . '_' . $laporan->tahun . '.pdf';

        return $pdf->download($filename);
    }

    public function downloadAllPdf()
    {
        $laporans = LaporanWfh::with(['kegiatan.evidens', 'user', 'user.atasan'])
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($laporans->isEmpty()) {
            return redirect()->route('admin.laporan.index')
                ->with('error', 'Belum ada laporan yang dapat diunduh.');
        }

        $isPdf = true;
        $pdf = PDF::loadView('pdf.laporan-wfh-all', compact('laporans', 'isPdf'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Seluruh_Laporan_WFH.pdf');
    }
}
