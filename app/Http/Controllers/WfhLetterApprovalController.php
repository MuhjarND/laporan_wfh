<?php

namespace App\Http\Controllers;

use App\AppSetting;
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

        $request->validate([
            'letter_signature' => 'required|string|starts_with:data:image/png;base64,',
        ]);

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
            'letter_signature' => $request->letter_signature,
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
