<?php

namespace App\Http\Controllers;

use App\AppSetting;
use App\LaporanWfh;
use App\Services\WhatsAppNotificationService;
use App\User;
use App\WfhDate;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;

class WfhLetterApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorizeApprover();

        $letters = WfhDate::withCount(['registrations', 'selectedRegistrations'])
            ->whereNotNull('letter_number')
            ->whereIn('letter_status', ['pending_approval', 'approved'])
            ->orderByRaw("CASE WHEN letter_status = 'pending_approval' THEN 0 ELSE 1 END")
            ->orderBy('tanggal', 'desc')
            ->paginate(12);

        return view('wfh-letter-approvals.index', compact('letters'));
    }

    public function show(WfhDate $wfhDate)
    {
        $this->authorizeApprover();

        $wfhDate->load(['registrations.user', 'letterApprover']);
        $users = $this->selectedUsersForLetter($wfhDate);
        $registrations = $wfhDate->registrations->sortBy(function ($registration) {
            return sprintf('%s-%s', $registration->status === 'selected' ? '0' : '1', strtolower(optional($registration->user)->name));
        })->values();

        $approver = $this->approverFor($wfhDate);

        return view('wfh-letter-approvals.show', compact('wfhDate', 'users', 'registrations', 'approver'));
    }

    public function monitoring(Request $request)
    {
        $this->authorizeApprover();

        $query = WfhDate::with(['users' => function ($q) {
                $q->orderBy('name');
            }])
            ->where('is_active', true);

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        $wfhDates = $query->orderBy('tanggal', 'desc')->paginate(8);
        $monitoringStats = $this->monitoringStats($wfhDates->getCollection());

        return view('wfh-letter-approvals.monitoring', compact('wfhDates', 'monitoringStats'));
    }

    public function report(LaporanWfh $laporan)
    {
        $this->authorizeApprover();

        $laporan->load('kegiatan.evidens', 'user', 'approver');

        return view('wfh-letter-approvals.report', compact('laporan'));
    }

    public function pdf(WfhDate $wfhDate)
    {
        $this->authorizeApprover();

        if (!$wfhDate->letter_number) {
            return redirect()->back()->with('error', 'Nomor surat belum diisi.');
        }

        $users = $this->selectedUsersForLetter($wfhDate);
        $approver = $this->approverFor($wfhDate);

        $pdf = PDF::loadView('pdf.surat-tugas-wfh', compact('wfhDate', 'users', 'approver'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Preview_Surat_Tugas_WFH_' . $wfhDate->tanggal->format('Ymd') . '.pdf');
    }

    public function sign(Request $request, WfhDate $wfhDate, WhatsAppNotificationService $whatsApp)
    {
        $this->authorizeApprover();
        $user = auth()->user();

        if (!$user->signature) {
            return redirect()->route('signature.edit')
                ->with('error', 'Silakan isi tanda tangan terlebih dahulu sebelum menandatangani surat tugas.');
        }

        if (!$wfhDate->letter_number) {
            return redirect()->back()->with('error', 'Nomor surat belum diisi.');
        }

        if ($wfhDate->letter_status === 'approved') {
            return redirect()->route('wfh-letter-approvals.show', $wfhDate)
                ->with('error', 'Surat tugas ini sudah ditandatangani.');
        }

        $wfhDate->update([
            'letter_status' => 'approved',
            'letter_approved_at' => now(),
            'letter_published_at' => now(),
            'letter_approved_by' => auth()->id(),
            'letter_signature' => $user->signature,
        ]);

        $wfhDate = $wfhDate->fresh(['registrations.user']);
        $sent = 0;
        $failed = 0;
        $withoutPhone = 0;

        foreach ($wfhDate->registrations as $registration) {
            $user = $registration->user;
            if (!$user || !$user->phone) {
                $withoutPhone++;
                continue;
            }

            $ok = $registration->status === 'selected'
                ? $whatsApp->sendWfhAssignmentLetterPublished($user, $wfhDate)
                : $whatsApp->sendWfhAssignmentNotSelected($user, $wfhDate, $registration->not_selected_reason);

            if ($ok) {
                $sent++;
            } else {
                $failed++;
            }
        }

        $wfhDate->update(['letter_notified_at' => now()]);

        return redirect()->route('wfh-letter-approvals.show', $wfhDate)
            ->with('success', 'Surat tugas berhasil ditandatangani. Notifikasi diproses. Terkirim: ' . $sent . ', tanpa nomor WA: ' . $withoutPhone . ', gagal: ' . $failed . '.');
    }

    private function authorizeApprover()
    {
        $approverId = (int) AppSetting::value('wfh_letter_approver_user_id');

        if (!$approverId || (int) auth()->id() !== $approverId) {
            abort(403, 'Anda tidak memiliki akses approval surat tugas WFH.');
        }
    }

    private function approverFor(WfhDate $wfhDate)
    {
        return $wfhDate->letterApprover ?: User::find(AppSetting::value('wfh_letter_approver_user_id'));
    }

    private function selectedUsersForLetter(WfhDate $wfhDate)
    {
        return $wfhDate->users()->get()->sortBy(function ($user) {
            return sprintf('%03d-%s', $this->jabatanPriority($user), strtolower($user->name));
        })->values();
    }

    private function monitoringStats($wfhDates)
    {
        $stats = [];

        foreach ($wfhDates as $wfhDate) {
            $assignedUsers = $wfhDate->users;
            $userIds = $assignedUsers->pluck('id')->all();

            $laporans = LaporanWfh::withCount(['kegiatan' => function ($query) use ($wfhDate) {
                    $query->whereDate('tanggal', $wfhDate->tanggal->toDateString());
                }])
                ->whereIn('user_id', $userIds)
                ->where('bulan', $wfhDate->tanggal->month)
                ->where('tahun', $wfhDate->tanggal->year)
                ->get()
                ->keyBy('user_id');

            $rows = $assignedUsers->map(function ($user) use ($laporans) {
                $laporan = $laporans->get($user->id);
                $kegiatanCount = $laporan ? (int) $laporan->kegiatan_count : 0;

                return [
                    'user' => $user,
                    'laporan' => $laporan,
                    'kegiatan_count' => $kegiatanCount,
                    'has_activity' => $kegiatanCount > 0,
                    'report_status' => $laporan ? $laporan->status : 'belum_ada',
                ];
            })->values();

            $stats[$wfhDate->id] = [
                'rows' => $rows,
                'assigned_count' => $rows->count(),
                'activity_count' => $rows->where('has_activity', true)->count(),
                'missing_activity_count' => $rows->where('has_activity', false)->count(),
                'submitted_count' => $rows->filter(function ($row) {
                    return in_array($row['report_status'], ['submitted', 'approved'], true);
                })->count(),
                'draft_count' => $rows->filter(function ($row) {
                    return in_array($row['report_status'], ['draft', 'rejected'], true);
                })->count(),
            ];
        }

        return $stats;
    }

    private function jabatanPriority(User $user)
    {
        $jabatan = strtolower((string) $user->jabatan);

        if (strpos($jabatan, 'wakil ketua') !== false) {
            return 20;
        }
        if (strpos($jabatan, 'ketua') !== false) {
            return 10;
        }
        if (strpos($jabatan, 'panitera') !== false && strpos($jabatan, 'pengganti') === false && strpos($jabatan, 'muda') === false) {
            return 30;
        }
        if (strpos($jabatan, 'sekretaris') !== false) {
            return 31;
        }
        if (strpos($jabatan, 'hakim') !== false) {
            return 40;
        }
        if (strpos($jabatan, 'kepala bagian') !== false || strpos($jabatan, 'kabag') !== false) {
            return 50;
        }
        if (strpos($jabatan, 'kepala sub bagian') !== false || strpos($jabatan, 'kasubbag') !== false) {
            return 60;
        }
        if (strpos($jabatan, 'panitera muda') !== false || strpos($jabatan, 'panitera pengganti') !== false) {
            return 70;
        }
        if (strpos($jabatan, 'jurusita') !== false) {
            return 80;
        }
        if (strpos($jabatan, 'pranata') !== false || strpos($jabatan, 'analis') !== false || strpos($jabatan, 'pengelola') !== false || strpos($jabatan, 'bendahara') !== false) {
            return 90;
        }

        return 100;
    }
}
