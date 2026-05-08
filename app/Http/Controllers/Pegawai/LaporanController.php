<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\LaporanWfh;
use App\KegiatanWfh;
use App\KegiatanWfhEviden;
use App\WfhDate;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\WhatsAppNotificationService;

class LaporanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:pegawai,atasan']);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = LaporanWfh::where('user_id', $user->id);

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        $laporans = $query->orderBy('tahun', 'desc')->orderBy('bulan', 'desc')->paginate(12);

        return view('pegawai.laporan.index', compact('laporans'));
    }

    public function create()
    {
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return view('pegawai.laporan.create', compact('bulanList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020|max:2030',
            'signature_pegawai' => 'required|string|starts_with:data:image/png;base64,',
        ]);

        $user = auth()->user();

        // Check if already exists
        $exists = LaporanWfh::where('user_id', $user->id)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Laporan untuk periode ini sudah ada.');
        }

        $laporan = LaporanWfh::create([
            'user_id' => $user->id,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'status' => 'draft',
            'signature_pegawai' => $request->signature_pegawai,
        ]);

        return redirect()->route('pegawai.laporan.edit', $laporan)
            ->with('success', 'Laporan berhasil dibuat. Silakan tambahkan kegiatan.');
    }

    public function edit(LaporanWfh $laporan)
    {
        $this->checkOwnership($laporan);

        if ($laporan->status === 'approved') {
            return redirect()->route('pegawai.laporan.show', $laporan)
                ->with('info', 'Laporan yang sudah disetujui tidak dapat diedit.');
        }

        $laporan->load('kegiatan.evidens');

        $wfhDates = $this->eligibleWfhDatesForUser(auth()->user(), $laporan->bulan, $laporan->tahun);

        return view('pegawai.laporan.edit', compact('laporan', 'wfhDates'));
    }

    public function show(LaporanWfh $laporan)
    {
        $this->checkOwnership($laporan);
        $laporan->load('kegiatan.evidens', 'user', 'approver');

        return view('pegawai.laporan.show', compact('laporan'));
    }

    public function update(Request $request, LaporanWfh $laporan)
    {
        $this->checkOwnership($laporan);

        if ($laporan->status === 'approved') {
            return redirect()->back()->with('error', 'Laporan yang sudah disetujui tidak dapat diedit.');
        }

        // Reset to draft if was rejected
        if ($laporan->status === 'rejected' || $laporan->status === 'submitted') {
            $laporan->update(['status' => 'draft']);
        }

        return redirect()->route('pegawai.laporan.edit', $laporan)
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    public function addKegiatan(Request $request, LaporanWfh $laporan)
    {
        $this->checkOwnership($laporan);

        if (in_array($laporan->status, ['approved'])) {
            return redirect()->back()->with('error', 'Laporan yang sudah disetujui tidak dapat diedit.');
        }

        $request->validate([
            'tanggal' => 'required|date',
            'kegiatan' => 'required|string',
            'capaian' => 'required|string',
            'tempat_wfh' => 'required|string|max:255',
            'eviden' => 'nullable|array|max:10',
            'eviden.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
        ]);
        $this->validateEvidenFileExtensions($request);

        if (!$this->isEligibleWfhDate(auth()->user(), $request->tanggal)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tanggal WFH tidak tersedia untuk akun Anda.');
        }

        $kegiatan = KegiatanWfh::create([
            'laporan_id' => $laporan->id,
            'tanggal' => $request->tanggal,
            'kegiatan' => $this->sanitizeRichText($request->kegiatan),
            'capaian' => $this->sanitizeRichText($request->capaian),
            'tempat_wfh' => $request->tempat_wfh,
        ]);

        $this->storeEvidenFiles($request, $kegiatan);

        // Reset status if was submitted/rejected
        if (in_array($laporan->status, ['submitted', 'rejected'])) {
            $laporan->update(['status' => 'draft']);
        }

        return redirect()->route('pegawai.laporan.edit', $laporan)
            ->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function updateKegiatan(Request $request, KegiatanWfh $kegiatan)
    {
        $laporan = $kegiatan->laporan;
        $this->checkOwnership($laporan);

        if ($laporan->status === 'approved') {
            return redirect()->back()->with('error', 'Laporan yang sudah disetujui tidak dapat diedit.');
        }

        $request->validate([
            'tanggal' => 'required|date',
            'kegiatan' => 'required|string',
            'capaian' => 'required|string',
            'tempat_wfh' => 'required|string|max:255',
            'eviden' => 'nullable|array|max:10',
            'eviden.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
        ]);
        $this->validateEvidenFileExtensions($request);

        if (!$this->isEligibleWfhDate(auth()->user(), $request->tanggal)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tanggal WFH tidak tersedia untuk akun Anda.');
        }

        $data = [
            'tanggal' => $request->tanggal,
            'kegiatan' => $this->sanitizeRichText($request->kegiatan),
            'capaian' => $this->sanitizeRichText($request->capaian),
            'tempat_wfh' => $request->tempat_wfh,
        ];

        $kegiatan->update($data);
        $this->storeEvidenFiles($request, $kegiatan);

        return redirect()->route('pegawai.laporan.edit', $laporan)
            ->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function deleteKegiatan(KegiatanWfh $kegiatan)
    {
        $laporan = $kegiatan->laporan;
        $this->checkOwnership($laporan);

        if ($laporan->status === 'approved') {
            return redirect()->back()->with('error', 'Laporan yang sudah disetujui tidak dapat diedit.');
        }

        $kegiatan->delete();

        return redirect()->route('pegawai.laporan.edit', $laporan)
            ->with('success', 'Kegiatan berhasil dihapus.');
    }

    public function submit(LaporanWfh $laporan)
    {
        $this->checkOwnership($laporan);

        if (!$laporan->period_has_ended) {
            return redirect()->back()
                ->with('error', 'Laporan ' . $laporan->periode . ' baru dapat diajukan setelah bulan tersebut berakhir.');
        }

        if ($laporan->kegiatan->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Tidak dapat mengajukan laporan tanpa kegiatan.');
        }

        $laporan->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // Send notification to atasan
        $user = auth()->user();
        if ($user->atasan) {
            $user->atasan->notify(new \App\Notifications\LaporanSubmitted($laporan));
            app(WhatsAppNotificationService::class)->sendSubmittedToAtasan($laporan);
        }

        return redirect()->route('pegawai.laporan.index')
            ->with('success', 'Laporan berhasil diajukan ke atasan.');
    }

    public function preview(LaporanWfh $laporan)
    {
        $this->checkOwnership($laporan);
        $laporan->load('kegiatan.evidens', 'user', 'user.atasan');

        $isPdf = true;
        $pdf = PDF::loadView('pdf.laporan-wfh', compact('laporan', 'isPdf'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Preview_Laporan_WFH_' . $laporan->user->name . '_' . $laporan->nama_bulan . '_' . $laporan->tahun . '.pdf';

        return $pdf->stream($filename);
    }

    public function downloadPdf(LaporanWfh $laporan)
    {
        $this->checkOwnership($laporan);
        $laporan->load('kegiatan.evidens', 'user', 'user.atasan');

        $isPdf = true;
        $pdf = PDF::loadView('pdf.laporan-wfh', compact('laporan', 'isPdf'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Laporan_WFH_' . $laporan->user->name . '_' . $laporan->nama_bulan . '_' . $laporan->tahun . '.pdf';

        return $pdf->download($filename);
    }

    public function downloadAllPdf()
    {
        $user = auth()->user();
        $laporans = LaporanWfh::with(['kegiatan.evidens', 'user', 'user.atasan'])
            ->where('user_id', $user->id)
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        if ($laporans->isEmpty()) {
            return redirect()->route('pegawai.laporan.index')
                ->with('error', 'Belum ada laporan yang dapat diunduh.');
        }

        $isPdf = true;
        $pdf = PDF::loadView('pdf.laporan-wfh-all', compact('laporans', 'isPdf'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Semua_Laporan_WFH_' . $user->name . '.pdf';

        return $pdf->download($filename);
    }

    public function destroy(LaporanWfh $laporan)
    {
        $this->checkOwnership($laporan);

        if ($laporan->status === 'approved') {
            return redirect()->back()->with('error', 'Laporan yang sudah disetujui tidak dapat dihapus.');
        }

        $laporan->delete();

        return redirect()->route('pegawai.laporan.index')
            ->with('success', 'Laporan berhasil dihapus.');
    }

    private function checkOwnership(LaporanWfh $laporan)
    {
        if ($laporan->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }
    }

    private function storeEvidenFiles(Request $request, KegiatanWfh $kegiatan)
    {
        if (!$request->hasFile('eviden')) {
            return;
        }

        foreach ($this->evidenFiles($request) as $file) {
            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, $this->allowedEvidenExtensions(), true)) {
                throw ValidationException::withMessages([
                    'eviden' => 'Tipe file eviden tidak diizinkan.',
                ]);
            }

            KegiatanWfhEviden::create([
                'kegiatan_id' => $kegiatan->id,
                'token' => (string) Str::uuid(),
                'path' => $file->store('eviden_wfh', 'local'),
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    private function validateEvidenFileExtensions(Request $request)
    {
        if (!$request->hasFile('eviden')) {
            return;
        }

        foreach ($this->evidenFiles($request) as $file) {
            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, $this->allowedEvidenExtensions(), true)) {
                throw ValidationException::withMessages([
                    'eviden' => 'Tipe file eviden tidak diizinkan. Gunakan gambar, PDF, dokumen Office, atau TXT.',
                ]);
            }
        }
    }

    private function evidenFiles(Request $request)
    {
        $files = $request->file('eviden', []);

        return is_array($files) ? $files : [$files];
    }

    private function allowedEvidenExtensions()
    {
        return [
            'jpg', 'jpeg', 'png', 'gif', 'webp',
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt',
        ];
    }

    private function sanitizeRichText($html)
    {
        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><blockquote>';
        $clean = strip_tags((string) $html, $allowedTags);

        return preg_replace('/<([a-z][a-z0-9]*)\b[^>]*>/i', '<$1>', $clean);
    }

    private function eligibleWfhDatesForUser($user, $bulan, $tahun)
    {
        return WfhDate::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->whereDoesntHave('users')
                    ->orWhereHas('users', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
            })
            ->orderBy('tanggal')
            ->get();
    }

    private function isEligibleWfhDate($user, $tanggal)
    {
        $date = Carbon::parse($tanggal);

        return WfhDate::whereDate('tanggal', $date->toDateString())
            ->where('is_active', true)
            ->where(function ($query) use ($user) {
                $query->whereDoesntHave('users')
                    ->orWhereHas('users', function ($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
            })
            ->exists();
    }
}
