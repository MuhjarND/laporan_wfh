<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AppSetting;
use App\WfhDate;
use App\WfhRegistration;
use App\User;
use App\LaporanWfh;
use App\Services\WhatsAppNotificationService;
use App\Services\WfhRegistrationService;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;

class WfhDateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    public function index(Request $request, WfhRegistrationService $registrationService)
    {
        $query = WfhDate::withCount(['users', 'registrations']);

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        $wfhDates = $query->orderBy('tanggal', 'desc')->paginate(20);

        $quota = $registrationService->quota();

        return view('admin.wfh-dates.index', compact('wfhDates', 'quota'));
    }

    public function create()
    {
        return view('admin.wfh-dates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'mode' => 'required|in:friday_range,all_range',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = $request->tanggal_selesai
            ? Carbon::parse($request->tanggal_selesai)
            : $mulai->copy();

        $count = 0;
        while ($mulai->lte($selesai)) {
            if ($request->mode === 'friday_range' && !$mulai->isFriday()) {
                $mulai->addDay();
                continue;
            }

            $wfhDate = WfhDate::updateOrCreate(
                ['tanggal' => $mulai->toDateString()],
                ['keterangan' => $request->keterangan, 'is_active' => true]
            );
            $mulai->addDay();
            $count++;
        }

        if ($count < 1) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tidak ada tanggal yang dibuat. Pastikan rentang tanggal memuat hari Jumat atau pilih mode semua tanggal.');
        }

        return redirect()->route('admin.wfh-dates.index')
            ->with('success', "{$count} tanggal WFH berhasil ditambahkan.");
    }

    public function edit(WfhDate $wfhDate, WfhRegistrationService $registrationService)
    {
        $wfhDate->load(['users' => function ($query) {
            $query->orderBy('name');
        }, 'registrations.user']);

        $quota = $registrationService->quota();

        return view('admin.wfh-dates.edit', compact('wfhDate', 'quota'));
    }

    public function update(Request $request, WfhDate $wfhDate, WfhRegistrationService $registrationService)
    {
        $request->validate([
            'tanggal' => 'required|date|unique:wfh_dates,tanggal,' . $wfhDate->id,
            'keterangan' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $wfhDate->update([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'is_active' => $request->boolean('is_active'),
        ]);
        $registrationService->recalculateSelection($wfhDate->fresh());

        return redirect()->route('admin.wfh-dates.index')
            ->with('success', 'Tanggal WFH berhasil diperbarui.');
    }

    public function destroy(WfhDate $wfhDate)
    {
        $wfhDate->delete();

        return redirect()->route('admin.wfh-dates.index')
            ->with('success', 'Tanggal WFH berhasil dihapus.');
    }

    public function toggleActive(WfhDate $wfhDate)
    {
        $wfhDate->update(['is_active' => !$wfhDate->is_active]);

        return redirect()->route('admin.wfh-dates.index')
            ->with('success', 'Status tanggal WFH berhasil diperbarui.');
    }

    public function participants(WfhDate $wfhDate)
    {
        $wfhDate->load(['users' => function ($query) {
            $query->orderBy('name');
        }, 'registrations.user']);

        $selectedUserIds = $wfhDate->users->pluck('id')->all();
        $candidateUsers = User::whereIn('role', ['pegawai', 'atasan'])
            ->where('is_active', true)
            ->whereNotIn('id', $selectedUserIds)
            ->orderBy('name')
            ->get();

        return view('admin.wfh-dates.participants', compact('wfhDate', 'candidateUsers'));
    }

    public function storeParticipants(Request $request, WfhDate $wfhDate)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|integer|distinct|exists:users,id',
        ]);

        $userIds = User::whereIn('id', $request->input('user_ids', []))
            ->whereIn('role', ['pegawai', 'atasan'])
            ->where('is_active', true)
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        if (empty($userIds)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tidak ada pegawai aktif yang valid untuk ditambahkan.');
        }

        foreach ($userIds as $userId) {
            WfhRegistration::updateOrCreate([
                'wfh_date_id' => $wfhDate->id,
                'user_id' => $userId,
            ], [
                'status' => 'selected',
                'selected_at' => now(),
                'not_selected_reason' => null,
            ]);
        }

        $wfhDate->users()->syncWithoutDetaching($userIds);

        return redirect()->route('admin.wfh-dates.participants', $wfhDate)
            ->with('success', count($userIds) . ' pegawai berhasil ditambahkan sebagai peserta WFH tanggal ' . $wfhDate->tanggal->format('d/m/Y') . '.');
    }

    public function publishLetter(Request $request, WfhDate $wfhDate, WhatsAppNotificationService $whatsApp, WfhRegistrationService $registrationService)
    {
        if ($wfhDate->letter_status === 'approved') {
            return redirect()->back()
                ->with('error', 'Surat tugas yang sudah disetujui tidak dapat diajukan ulang.');
        }

        $quota = $registrationService->quota();

        $request->validate([
            'letter_number' => 'required|string|max:255',
            'selected_user_ids' => 'required|array|min:1|max:' . $quota,
            'selected_user_ids.*' => 'required|integer|distinct|exists:users,id',
        ]);

        $selectedUserIds = collect($request->input('selected_user_ids', []))
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();

        $registeredUserIds = $wfhDate->registrations()
            ->pluck('user_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();

        if (!empty(array_diff($selectedUserIds, $registeredUserIds))) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Peserta yang dipilih harus berasal dari daftar pendaftar WFH tanggal ini.');
        }

        $approver = User::find(AppSetting::value('wfh_letter_approver_user_id'));
        if (!$approver || !$approver->is_active) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Pejabat approval surat belum diset atau tidak aktif. Silakan set di menu Kelola User.');
        }

        $this->applySelectedUsersForLetter($wfhDate, $selectedUserIds);

        $selectedUsers = $this->selectedUsersForLetter($wfhDate);
        if ($selectedUsers->isEmpty()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Surat tugas belum dapat diterbitkan karena belum ada pegawai yang terpilih.');
        }

        $wfhDate->update([
            'letter_number' => $request->letter_number,
            'letter_status' => 'pending_approval',
            'letter_requested_at' => now(),
            'letter_published_at' => null,
            'letter_approved_at' => null,
            'letter_approved_by' => null,
            'letter_signature' => null,
            'letter_notified_at' => null,
        ]);

        $wfhDate = $wfhDate->fresh();
        $approvalNotification = $whatsApp->sendWfhLetterApprovalRequest($approver, $wfhDate);

        return redirect()->back()
            ->with('success', 'Surat tugas berhasil diajukan ke ' . $approver->name . ' untuk ditandatangani. Notifikasi approval: ' . ($approvalNotification ? 'terkirim' : 'tidak terkirim') . '.');
    }

    public function downloadLetter(WfhDate $wfhDate)
    {
        if (!$wfhDate->letter_number) {
            return redirect()->back()
                ->with('error', 'Nomor surat belum diisi.');
        }

        $users = $this->selectedUsersForLetter($wfhDate);
        if ($users->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Belum ada pegawai terpilih untuk surat tugas ini.');
        }

        $approver = $wfhDate->letterApprover ?: User::find(AppSetting::value('wfh_letter_approver_user_id'));
        $pdf = PDF::loadView('pdf.surat-tugas-wfh', compact('wfhDate', 'users', 'approver'))
            ->setPaper('a4', 'portrait');

        $filename = 'Surat_Tugas_WFH_' . $wfhDate->tanggal->format('Ymd') . '.pdf';

        return $pdf->stream($filename);
    }

    public function monitoring(Request $request)
    {
        $wfhDates = $this->monitoringQuery($request)->paginate(10);
        $reportStats = $this->reportStats($wfhDates->getCollection());

        return view('admin.wfh-dates.monitoring', compact('wfhDates', 'reportStats'));
    }

    public function sendReminder(WfhDate $wfhDate, WhatsAppNotificationService $whatsApp)
    {
        $wfhDate->load('users');
        $missingUsers = $this->missingActivityUsers($wfhDate);

        $sent = 0;
        $failed = 0;
        $withoutPhone = 0;

        foreach ($missingUsers as $user) {
            if (!$user->phone) {
                $withoutPhone++;
                continue;
            }

            if ($whatsApp->sendWfhReportReminder($user, $wfhDate)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return redirect()->route('admin.wfh-dates.monitoring')
            ->with('success', 'Notifikasi isi kegiatan tanggal ' . $wfhDate->tanggal->format('d/m/Y') . ' diproses. Terkirim: ' . $sent . ', tanpa nomor WA: ' . $withoutPhone . ', gagal: ' . $failed . '.');
    }

    public function sendSubmitReminder(WfhDate $wfhDate, WhatsAppNotificationService $whatsApp)
    {
        $wfhDate->load('users');
        $unsubmittedUsers = $this->unsubmittedReportUsers($wfhDate);

        $sent = 0;
        $failed = 0;
        $withoutPhone = 0;

        foreach ($unsubmittedUsers as $user) {
            if (!$user->phone) {
                $withoutPhone++;
                continue;
            }

            if ($whatsApp->sendWfhSubmitReminder($user, $wfhDate)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return redirect()->route('admin.wfh-dates.monitoring')
            ->with('success', 'Notifikasi pengiriman laporan tanggal ' . $wfhDate->tanggal->format('d/m/Y') . ' diproses. Terkirim: ' . $sent . ', tanpa nomor WA: ' . $withoutPhone . ', gagal: ' . $failed . '.');
    }

    public function sendAllActivityReminders(Request $request, WhatsAppNotificationService $whatsApp)
    {
        $wfhDates = $this->monitoringQuery($request)->get();
        $summary = $this->sendReminderToUsers($wfhDates, $whatsApp, 'activity');

        return redirect()->route('admin.wfh-dates.monitoring', $request->only(['tanggal', 'bulan', 'tahun']))
            ->with('success', 'Notifikasi isi kegiatan diproses. Tanggal: ' . $summary['dates'] . ', terkirim: ' . $summary['sent'] . ', tanpa nomor WA: ' . $summary['withoutPhone'] . ', gagal: ' . $summary['failed'] . '.');
    }

    public function sendAllSubmitReminders(Request $request, WhatsAppNotificationService $whatsApp)
    {
        $wfhDates = $this->monitoringQuery($request)->get();
        $summary = $this->sendReminderToUsers($wfhDates, $whatsApp, 'submit');

        return redirect()->route('admin.wfh-dates.monitoring', $request->only(['tanggal', 'bulan', 'tahun']))
            ->with('success', 'Notifikasi pengiriman laporan diproses. Tanggal: ' . $summary['dates'] . ', terkirim: ' . $summary['sent'] . ', tanpa nomor WA: ' . $summary['withoutPhone'] . ', gagal: ' . $summary['failed'] . '.');
    }

    public function missingActivityUsers(WfhDate $wfhDate)
    {
        $assignedUsers = $this->assignedUsersForDate($wfhDate);
        $reportedUserIds = $this->reportedUserIds($wfhDate);

        return $assignedUsers->whereNotIn('id', $reportedUserIds)->values();
    }

    public function unsubmittedReportUsers(WfhDate $wfhDate)
    {
        $assignedUsers = $this->assignedUsersForDate($wfhDate);

        $unsubmittedUserIds = LaporanWfh::where('bulan', $wfhDate->tanggal->month)
            ->where('tahun', $wfhDate->tanggal->year)
            ->whereNotIn('status', ['submitted', 'approved'])
            ->whereHas('kegiatan', function ($query) use ($wfhDate) {
                $query->whereDate('tanggal', $wfhDate->tanggal->toDateString());
            })
            ->pluck('user_id')
            ->unique();

        return $assignedUsers->whereIn('id', $unsubmittedUserIds)->values();
    }

    private function reportStats($wfhDates)
    {
        $stats = [];

        foreach ($wfhDates as $wfhDate) {
            $assignedUsers = $this->assignedUsersForDate($wfhDate);
            $reportedUserIds = $this->reportedUserIds($wfhDate);

            $reportedUsers = $assignedUsers->whereIn('id', $reportedUserIds)->values();
            $missingUsers = $assignedUsers->whereNotIn('id', $reportedUserIds)->values();
            $unsubmittedUsers = $this->unsubmittedReportUsers($wfhDate);

            $stats[$wfhDate->id] = [
                'assigned' => $assignedUsers,
                'reported' => $reportedUsers,
                'missing' => $missingUsers,
                'unsubmitted' => $unsubmittedUsers,
            ];
        }

        return $stats;
    }

    private function monitoringQuery(Request $request)
    {
        $query = WfhDate::with(['users' => function ($q) {
            $q->orderBy('name');
        }])->where('is_active', true);

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        return $query->orderBy('tanggal', 'desc');
    }

    private function sendReminderToUsers($wfhDates, WhatsAppNotificationService $whatsApp, $type)
    {
        $sent = 0;
        $failed = 0;
        $withoutPhone = 0;
        $dates = 0;
        $targets = [];

        foreach ($wfhDates as $wfhDate) {
            $dates++;
            $users = $type === 'submit'
                ? $this->unsubmittedReportUsers($wfhDate)
                : $this->missingActivityUsers($wfhDate);

            foreach ($users as $user) {
                $key = $wfhDate->id . '-' . $user->id;
                if (isset($targets[$key])) {
                    continue;
                }
                $targets[$key] = true;

                if (!$user->phone) {
                    $withoutPhone++;
                    continue;
                }

                $ok = $type === 'submit'
                    ? $whatsApp->sendWfhSubmitReminder($user, $wfhDate)
                    : $whatsApp->sendWfhReportReminder($user, $wfhDate);

                if ($ok) {
                    $sent++;
                } else {
                    $failed++;
                }
            }
        }

        return compact('dates', 'sent', 'failed', 'withoutPhone');
    }

    private function assignedUsersForDate(WfhDate $wfhDate)
    {
        if ($wfhDate->relationLoaded('users') && $wfhDate->users->isNotEmpty()) {
            return $wfhDate->users;
        }

        if (!$wfhDate->relationLoaded('users')) {
            $wfhDate->load('users');
        }

        return $wfhDate->users;
    }

    private function selectedUsersForLetter(WfhDate $wfhDate)
    {
        $users = $wfhDate->users()->get();

        return $users->sortBy(function ($user) {
            return sprintf('%03d-%s', $this->jabatanPriority($user), strtolower($user->name));
        })->values();
    }

    private function applySelectedUsersForLetter(WfhDate $wfhDate, array $selectedUserIds)
    {
        WfhRegistration::where('wfh_date_id', $wfhDate->id)->update([
            'status' => 'not_selected',
            'selected_at' => null,
            'not_selected_reason' => 'Tidak masuk daftar peserta surat tugas yang diajukan oleh admin.',
        ]);

        WfhRegistration::where('wfh_date_id', $wfhDate->id)
            ->whereIn('user_id', $selectedUserIds)
            ->update([
                'status' => 'selected',
                'selected_at' => now(),
                'not_selected_reason' => null,
            ]);

        $wfhDate->users()->sync($selectedUserIds);
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

    private function reportedUserIds(WfhDate $wfhDate)
    {
        return LaporanWfh::where('bulan', $wfhDate->tanggal->month)
            ->where('tahun', $wfhDate->tanggal->year)
            ->whereHas('kegiatan', function ($query) use ($wfhDate) {
                $query->whereDate('tanggal', $wfhDate->tanggal->toDateString());
            })
            ->pluck('user_id')
            ->unique();
    }
}
