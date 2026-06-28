<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppNotificationService;
use App\Services\WfhRegistrationService;
use App\User;
use App\WfhDate;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;

class WfhRegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:pegawai,atasan']);
    }

    public function index(WfhRegistrationService $registrationService)
    {
        $user = auth()->user();
        $wfhDates = WfhDate::with([
                'users',
                'registrations' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                },
            ])
            ->withCount(['users', 'registrations'])
            ->where('is_active', true)
            ->whereDate('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->paginate(12);

        $quota = $registrationService->quota();
        $registrationWindows = [];
        foreach ($wfhDates->getCollection() as $date) {
            $registrationWindows[$date->id] = [
                'closes_at' => $registrationService->registrationClosesAt($date),
                'is_open' => $registrationService->isRegistrationOpen($date),
                'closed_message' => $registrationService->registrationClosedMessage($date),
            ];
        }

        return view('pegawai.wfh-registrations.index', compact('wfhDates', 'quota', 'registrationWindows'));
    }

    public function store(WfhDate $wfhDate, WfhRegistrationService $registrationService)
    {
        [$canRegister, $message] = $registrationService->canRegister(auth()->user(), $wfhDate);

        if (!$canRegister) {
            return redirect()->route('pegawai.wfh-registrations.index')
                ->with('error', $message);
        }

        $registrationService->register(auth()->user(), $wfhDate);

        return redirect()->route('pegawai.wfh-registrations.index')
            ->with('success', 'Pendaftaran berhasil. Data Anda masuk ke daftar seleksi sistem untuk WFH tanggal ' . $wfhDate->tanggal->format('d/m/Y') . '. Hasil final akan diinformasikan setelah surat tugas disetujui.');
    }

    public function destroy(WfhDate $wfhDate, WfhRegistrationService $registrationService, WhatsAppNotificationService $whatsApp)
    {
        [$canCancel, $message] = $registrationService->canCancel(auth()->user(), $wfhDate);

        if (!$canCancel) {
            return redirect()->route('pegawai.wfh-registrations.index')
                ->with('error', $message);
        }

        $replacementRegistrations = $registrationService->cancel(auth()->user(), $wfhDate);
        $sent = 0;
        $failed = 0;
        $withoutPhone = 0;

        foreach ($replacementRegistrations as $registration) {
            $user = $registration->user;
            if (!$user || !$user->phone) {
                $withoutPhone++;
                continue;
            }

            if ($whatsApp->sendWfhReplacementSelected($user, $wfhDate)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        $message = 'Pendaftaran WFH tanggal ' . $wfhDate->tanggal->format('d/m/Y') . ' berhasil dibatalkan.';
        if ($replacementRegistrations->isNotEmpty()) {
            $message .= ' Sistem telah memilih pengganti. Notifikasi pengganti diproses: terkirim ' . $sent . ', tanpa nomor WA ' . $withoutPhone . ', gagal ' . $failed . '.';
        }

        return redirect()->route('pegawai.wfh-registrations.index')
            ->with('success', $message);
    }

    public function letter(WfhDate $wfhDate)
    {
        if (!$wfhDate->letter_number || $wfhDate->letter_status !== 'approved') {
            return redirect()->route('pegawai.wfh-registrations.index')
                ->with('error', 'Surat tugas untuk tanggal WFH ini belum diterbitkan.');
        }

        $isSelected = $wfhDate->users()
            ->where('users.id', auth()->id())
            ->exists();

        if (!$isSelected) {
            abort(403, 'Anda tidak memiliki akses ke surat tugas ini.');
        }

        $users = $this->selectedUsersForLetter($wfhDate);
        $approver = $wfhDate->letterApprover;
        $pdf = PDF::loadView('pdf.surat-tugas-wfh', compact('wfhDate', 'users', 'approver'))
            ->setPaper('a4', 'portrait');

        $filename = 'Surat_Tugas_WFH_' . $wfhDate->tanggal->format('Ymd') . '.pdf';

        return $pdf->stream($filename);
    }

    private function selectedUsersForLetter(WfhDate $wfhDate)
    {
        $users = $wfhDate->users()->get();

        return $users->sortBy(function ($user) {
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
